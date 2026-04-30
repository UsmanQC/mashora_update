<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Pages;

use App\Models\Doctor;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\Rule;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class DoctorOnboardingBasicInfo extends DoctorSimplePanelPage
{
    protected static ?string $slug = 'onboarding-basic-info';

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
            'name_ar' => $doctor->name_ar ?? '',
            'name' => $doctor->name ?? '',
            'gender' => $doctor->gender ?? '',
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name_ar')
                ->label(__('Arabic name'))
                ->required()
                ->maxLength(255),
            TextInput::make('name')
                ->label(__('English name'))
                ->required()
                ->maxLength(255),
            Select::make('gender')
                ->label(__('Sex'))
                ->options([
                    'male' => __('Male'),
                    'female' => __('Female'),
                ])
                ->required()
                ->native(false),
            FileUpload::make('profile_photo')
                ->label(__('Profile photo'))
                ->image()
                ->disk('public')
                ->directory('doctor-profiles')
                ->visibility('public')
                ->maxSize(10240),
        ]);
    }

    public function save(): void
    {
        /** @var Doctor $doctor */
        $doctor = Filament::auth()->user();

        $state = $this->form->getState();
        $payload = $state['data'] ?? $state;

        $validated = validator($payload, [
            'name_ar' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'profile_photo' => ['nullable'],
        ])->validate();

        if (! empty($validated['profile_photo'])) {
            $photo = $validated['profile_photo'];
            if ($photo instanceof TemporaryUploadedFile) {
                $path = $photo->store('doctor-profiles', 'public');
            } else {
                $path = $photo;
            }
            $doctor->profile_photo_path = $path;
        }

        $doctor->fill([
            'name_ar' => $validated['name_ar'],
            'name' => $validated['name'],
            'gender' => $validated['gender'],
        ])->save();

        $this->redirect(DoctorOnboardingAdditionalInfo::getUrl(panel: 'doctor'));
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
        return __('Your basic details');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('Step 1 of 8 — names, gender, and optional photo.');
    }
}
