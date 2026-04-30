<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Pages;

use App\Models\Communication;
use App\Models\Doctor;
use App\Models\Duration;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class DoctorOnboardingDuration extends DoctorSimplePanelPage
{
    protected static ?string $slug = 'onboarding-duration';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $layout = 'filament-panels::components.layout.simple';

    protected string $view = 'filament-panels::pages.simple';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return Filament::auth()->check();
    }

    public function mount(): void
    {
        /** @var Doctor $doctor */
        $doctor = Filament::auth()->user();

        self::ensureCommunicationChannelsExist();
        self::ensureDefaultDurationsExist();

        $allowedMinutes = self::allowedDurationMinuteStrings();

        if ($doctor->durations()->exists()) {
            $durationMinutes = array_values(array_intersect(
                $doctor->durations->pluck('duration')->map(fn ($m): string => (string) $m)->all(),
                $allowedMinutes,
            ));
        } else {
            $durationMinutes = array_values(array_intersect(['15', '30', '45', '60'], $allowedMinutes));
        }

        if ($durationMinutes === []) {
            $durationMinutes = array_values(array_intersect(['15', '30', '45', '60'], $allowedMinutes));
        }

        $durationPrices = [];
        foreach ($doctor->durations as $row) {
            $key = (string) $row->duration;
            if (in_array($key, $durationMinutes, true)) {
                $durationPrices[$key] = (string) $row->pivot->price;
            }
        }

        $allowedCommunicationCodes = self::allowedCommunicationCodes();

        $communications = $doctor->communications()->exists()
            ? array_values(array_intersect(
                $doctor->communications->pluck('communication')->map(fn ($c): string => (string) $c)->all(),
                $allowedCommunicationCodes,
            ))
            : array_values(array_intersect(['chat', 'video_call', 'voice_call'], $allowedCommunicationCodes));

        if ($communications === []) {
            $communications = array_values(array_intersect(['chat', 'video_call', 'voice_call'], $allowedCommunicationCodes));
        }

        if (! in_array('chat', array_map('strval', $communications), true)
            && in_array('chat', $allowedCommunicationCodes, true)) {
            $communications[] = 'chat';
            $communications = array_values(array_unique($communications));
        }

        $this->form->fill([
            'doctor_durations' => $durationMinutes,
            'duration_prices' => $durationPrices,
            'doctor_communications' => $communications,
            'accept_instant_appointment' => (bool) $doctor->accept_instant_appointment,
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        self::ensureCommunicationChannelsExist();
        self::ensureDefaultDurationsExist();

        $durationFields = Duration::query()->orderBy('duration')->get()->map(function (Duration $duration): TextInput {
            $minutes = (string) $duration->duration;

            return TextInput::make("duration_prices.{$minutes}")
                ->label(__('Price (:minutes min)', ['minutes' => $minutes]).' (SAR)')
                ->numeric()
                ->minValue(0)
                ->visible(function (Get $get) use ($minutes): bool {
                    $selected = array_map('strval', Arr::wrap($get('doctor_durations') ?? []));

                    return in_array($minutes, $selected, true);
                });
        })->all();

        return $schema->components([
            CheckboxList::make('doctor_durations')
                ->label(__('Session lengths (minutes)'))
                ->options(fn (): array => Duration::query()->orderBy('duration')->get()->mapWithKeys(fn (Duration $d): array => [(string) $d->duration => $d->title_locale])->all())
                ->columns(2)
                ->live()
                ->required(),
            ...$durationFields,
            CheckboxList::make('doctor_communications')
                ->label(__('Appointment channels'))
                ->helperText(__('Chat stays enabled so patients can always message you; you can add video or voice as well.'))
                ->options(fn (): array => self::communicationOptions())
                ->columns(2)
                ->required(),
            Toggle::make('accept_instant_appointment')
                ->label(__('Accept instant appointment notifications'))
                ->default(true),
        ]);
    }

    public function save(): void
    {
        /** @var Doctor $doctor */
        $doctor = Filament::auth()->user();

        self::ensureCommunicationChannelsExist();
        self::ensureDefaultDurationsExist();

        $state = $this->form->getState();
        $payload = $state['data'] ?? $state;

        // CheckboxList / Livewire may submit ints; Rule::in uses strict DB string keys — normalize so validation never fails quietly.
        if (isset($payload['doctor_durations'])) {
            $payload['doctor_durations'] = array_values(array_map(
                static fn ($v): string => (string) $v,
                Arr::wrap($payload['doctor_durations']),
            ));
        }
        if (isset($payload['doctor_communications'])) {
            // Must stay a zero-indexed list of codes. Associative shapes like code => [] or code => bool
            // make Laravel BelongsToMany::sync() collapse into [[],[]] and insert communication 0 (truncated enum).
            $payload['doctor_communications'] = self::normalizeDoctorCommunicationCodes($payload['doctor_communications']);
        }

        $allowedCommunicationCodes = self::allowedCommunicationCodes();

        $allowedMinuteStrings = self::allowedDurationMinuteStrings();

        $validated = validator($payload, [
            'doctor_durations' => ['required', 'array', 'min:1'],
            'doctor_durations.*' => ['string', Rule::in($allowedMinuteStrings)],
            'duration_prices' => ['required', 'array'],
            'doctor_communications' => ['required', 'array', 'min:1'],
            'doctor_communications.*' => ['string', Rule::in($allowedCommunicationCodes)],
            'accept_instant_appointment' => ['sometimes', 'boolean'],
        ])->validate();

        $communicationsToSync = array_values(array_unique(array_merge(
            $validated['doctor_communications'],
            ['chat'],
        )));

        $communicationsToSync = array_values(array_intersect($communicationsToSync, $allowedCommunicationCodes));

        $communicationsToSync = array_values(array_filter(
            array_map(static fn ($c): string => (string) $c, $communicationsToSync),
            static fn (string $c): bool => $c !== '' && $c !== '0',
        ));

        $syncDurations = [];
        foreach ($validated['doctor_durations'] as $minutes) {
            $minutes = (int) $minutes;
            $price = Arr::get($validated['duration_prices'], (string) $minutes);
            if ($price === null || $price === '') {
                throw ValidationException::withMessages([
                    "data.duration_prices.{$minutes}" => __('Enter a price for each selected duration.'),
                ]);
            }
            $syncDurations[$minutes] = ['price' => (float) $price];
        }

        $doctor->durations()->sync($syncDurations);
        $doctor->communications()->sync($communicationsToSync);
        $doctor->update([
            'accept_instant_appointment' => $validated['accept_instant_appointment'] ?? false,
        ]);

        $this->redirect(DoctorOnboardingWorkingHours::getUrl(panel: 'doctor'));
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label(__('Next'))->submit('save'),
                    ])
                        ->alignment(Alignment::Center)
                        ->fullWidth(),
                ]),
        ]);
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('Pricing & channels');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('Step 6 of 8 — session prices and communication types.');
    }

    /**
     * Ensures {@code durations} rows exist (migration FK); empty options here blocks step 6 submit like communications.
     */
    private static function ensureDefaultDurationsExist(): void
    {
        $defaults = [
            15 => ['title' => '15 minutes', 'title_ar' => '15 دقيقة'],
            30 => ['title' => '30 minutes', 'title_ar' => '30 دقيقة'],
            45 => ['title' => '45 minutes', 'title_ar' => '45 دقيقة'],
            60 => ['title' => '60 minutes', 'title_ar' => '60 دقيقة'],
        ];

        foreach ($defaults as $minutes => $titles) {
            $row = Duration::withTrashed()->firstOrCreate(
                ['duration' => $minutes],
                $titles,
            );

            if ($row->trashed()) {
                $row->restore();
            }
        }
    }

    /**
     * @return list<string>
     */
    private static function allowedDurationMinuteStrings(): array
    {
        return Duration::query()
            ->orderBy('duration')
            ->get()
            ->pluck('duration')
            ->map(fn ($m): string => (string) $m)
            ->values()
            ->all();
    }

    /**
     * CheckboxList only accepts values present in {@see communicationOptions()}; orphaned pivots or an empty
     * communications table otherwise trigger the standard "selected ... is invalid" validation message.
     */
    private static function ensureCommunicationChannelsExist(): void
    {
        $defaults = [
            'chat' => ['title' => 'Chat', 'title_ar' => 'Chat'],
            'voice_call' => ['title' => 'Voice Call', 'title_ar' => 'Voice call'],
            'video_call' => ['title' => 'Video Call', 'title_ar' => 'Video call'],
        ];

        foreach ($defaults as $code => $titles) {
            $row = Communication::withTrashed()->firstOrCreate(
                ['communication' => $code],
                $titles,
            );

            if ($row->trashed()) {
                $row->restore();
            }
        }
    }

    /**
     * @return list<string>
     */
    private static function allowedCommunicationCodes(): array
    {
        return Communication::query()
            ->orderBy('communication')
            ->get()
            ->pluck('communication')
            ->map(fn ($c): string => (string) $c)
            ->values()
            ->all();
    }

    /**
     * Reduce CheckboxList / Livewire payloads to a flat list of {@code communication} codes safe for
     * {@see BelongsToMany::sync()} (associative pivot-shaped arrays break Laravel's parseIds/formatRecordsList).
     *
     * @return list<string>
     */
    private static function normalizeDoctorCommunicationCodes(mixed $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        if (! is_array($raw)) {
            return [(string) $raw];
        }

        if ($raw === []) {
            return [];
        }

        if (! array_is_list($raw)) {
            $vals = array_values($raw);

            if ($vals !== [] && collect($vals)->every(static fn ($v): bool => is_bool($v))) {
                return collect($raw)
                    ->filter(static fn ($on): bool => $on === true)
                    ->keys()
                    ->map(static fn ($k): string => (string) $k)
                    ->values()
                    ->all();
            }

            if (collect($raw)->every(static fn ($v): bool => is_array($v) && $v === [])) {
                return collect($raw)
                    ->keys()
                    ->map(static fn ($k): string => (string) $k)
                    ->values()
                    ->all();
            }
        }

        return collect($raw)
            ->flatten()
            ->filter(static fn ($v): bool => is_string($v) && $v !== '' && $v !== '0')
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private static function communicationOptions(): array
    {
        return Communication::query()
            ->orderBy('communication')
            ->get()
            ->mapWithKeys(function (Communication $c): array {
                $label = app()->getLocale() === 'ar'
                    ? ($c->title_ar ?: $c->title)
                    : ($c->title ?: $c->title_ar);

                return [(string) $c->communication => (string) ($label ?: $c->communication)];
            })
            ->all();
    }
}
