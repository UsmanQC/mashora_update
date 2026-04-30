<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Pages;

use App\Models\Doctor;
use App\Models\Speciality;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Illuminate\Contracts\Support\Htmlable;

final class DoctorOnboardingSpecialties extends DoctorSimplePanelPage
{
    protected static ?string $slug = 'onboarding-specialties';

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
            'speciality_ids' => $doctor->specialities()->get()->pluck('id')->all(),
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            CheckboxList::make('speciality_ids')
                ->label(__('Specialities'))
                ->options(fn (): array => Speciality::query()->where('status', true)->orderBy('title')->get()->mapWithKeys(fn (Speciality $s): array => [$s->id => $s->title_locale])->all())
                ->columns(2)
                ->required(),
        ]);
    }

    public function save(): void
    {
        /** @var Doctor $doctor */
        $doctor = Filament::auth()->user();

        $state = $this->form->getState();
        $payload = $state['data'] ?? $state;

        $validated = validator($payload, [
            'speciality_ids' => ['required', 'array', 'min:1'],
            'speciality_ids.*' => ['integer', 'exists:specialities,id'],
        ])->validate();

        $doctor->specialities()->sync($validated['speciality_ids']);

        $this->redirect(DoctorOnboardingLicense::getUrl(panel: 'doctor'));
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
        return __('Specialities');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('Step 3 of 8 — choose one or more specialties.');
    }
}
