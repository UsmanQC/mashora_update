<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Pages;

use App\Models\Degree;
use App\Models\Doctor;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Illuminate\Contracts\Support\Htmlable;

final class DoctorOnboardingAdditionalInfo extends DoctorSimplePanelPage
{
    protected static ?string $slug = 'onboarding-additional-info';

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

        $this->form->fill([
            'experience' => $doctor->experience,
            'degree_id' => $doctor->degree_id,
            'about_ar' => $doctor->about_ar ?? '',
            'about' => $doctor->about ?? '',
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('experience')
                ->label(__('Years of experience'))
                ->options(collect(range(1, 30))->mapWithKeys(fn (int $y): array => [$y => trans_choice(':count year|:count years', $y, ['count' => $y])]))
                ->required()
                ->native(false),
            Select::make('degree_id')
                ->label(__('Qualification / category'))
                ->options(fn (): array => Degree::query()->where('status', true)->orderBy('id')->get()->mapWithKeys(fn (Degree $d): array => [$d->id => $d->title_locale])->all())
                ->required()
                ->native(false)
                ->searchable(),
            Textarea::make('about_ar')
                ->label(__('About you (Arabic)'))
                ->required()
                ->maxLength(3000)
                ->rows(4),
            Textarea::make('about')
                ->label(__('About you (English)'))
                ->required()
                ->maxLength(3000)
                ->rows(4),
        ]);
    }

    public function save(): void
    {
        /** @var Doctor $doctor */
        $doctor = Filament::auth()->user();

        $state = $this->form->getState();
        $payload = $state['data'] ?? $state;

        $validated = validator($payload, [
            'experience' => ['required', 'integer', 'min:1', 'max:30'],
            'degree_id' => ['required', 'integer', 'exists:degrees,id'],
            'about_ar' => ['required', 'string', 'max:3000'],
            'about' => ['required', 'string', 'max:3000'],
        ])->validate();

        $doctor->update($validated);

        $this->redirect(DoctorOnboardingSpecialties::getUrl(panel: 'doctor'));
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
        return __('Professional profile');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('Step 2 of 8 — experience, qualification, and biography.');
    }
}
