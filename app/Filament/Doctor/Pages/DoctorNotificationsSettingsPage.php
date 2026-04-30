<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Pages;

use App\Models\Doctor;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

final class DoctorNotificationsSettingsPage extends Page
{
    protected static bool $shouldRegisterNavigation = true;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    protected static ?int $navigationSort = 40;

    protected static ?string $slug = 'notifications-settings';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Notifications');
    }

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
            'accept_instant_appointment' => (bool) $doctor->accept_instant_appointment,
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Toggle::make('accept_instant_appointment')
                ->label(__('Accept instant appointment notifications'))
                ->helperText(__('When enabled, you can receive alerts for on-demand bookings.')),
        ]);
    }

    public function save(): void
    {
        /** @var Doctor $doctor */
        $doctor = Filament::auth()->user();

        $state = $this->form->getState();
        $payload = $state['data'] ?? $state;

        $validated = validator($payload, [
            'accept_instant_appointment' => ['sometimes', 'boolean'],
        ])->validate();

        $doctor->update([
            'accept_instant_appointment' => $validated['accept_instant_appointment'] ?? false,
        ]);

        Notification::make()
            ->success()
            ->title(__('Saved'))
            ->send();

        $this->redirect(DoctorDashboard::getUrl(panel: 'doctor'));
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label(__('Save'))->submit('save'),
                    ])
                        ->alignment(Alignment::Center)
                        ->fullWidth(),
                ]),
        ]);
    }

    public function getTitle(): string|Htmlable
    {
        return __('Notifications');
    }
}
