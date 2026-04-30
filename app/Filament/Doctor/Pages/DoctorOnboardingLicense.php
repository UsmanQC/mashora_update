<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Pages;

use App\Models\Doctor;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class DoctorOnboardingLicense extends DoctorSimplePanelPage
{
    protected static ?string $slug = 'onboarding-license';

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
            'registration_number' => $doctor->registration_number ?? '',
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('registration_number')
                ->label(__('License / registration number'))
                ->required()
                ->maxLength(255),
            FileUpload::make('medical_license')
                ->label(__('Medical license files'))
                ->multiple()
                ->disk('public')
                ->directory('medical-licenses')
                ->visibility('public')
                ->acceptedFileTypes(['image/png', 'image/jpeg', 'application/pdf'])
                ->maxSize(10240)
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
            'registration_number' => ['required', 'string', 'max:255'],
            'medical_license' => ['required', 'array', 'min:1'],
        ])->validate();

        $doctor->update(['registration_number' => $validated['registration_number']]);

        $doctor->clearMediaCollection('medical_license');

        foreach ($validated['medical_license'] as $file) {
            if ($file instanceof TemporaryUploadedFile) {
                $doctor->addMedia($file->getRealPath())
                    ->usingFileName($file->getClientOriginalName())
                    ->toMediaCollection('medical_license');
            } elseif (is_string($file)) {
                $doctor->addMediaFromDisk($file, 'public')->toMediaCollection('medical_license');
            }
        }

        $this->redirect(DoctorOnboardingSignature::getUrl(panel: 'doctor'));
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
        return __('License');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('Step 4 of 8 — registration number and supporting documents.');
    }
}
