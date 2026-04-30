<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Pages;

use App\Filament\Doctor\Concerns\HandlesDoctorWorkingHours;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\View as SchemaView;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

final class DoctorSettingsWorkingHours extends Page
{
    use HandlesDoctorWorkingHours;

    protected static bool $shouldRegisterNavigation = true;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?int $navigationSort = 41;

    protected static ?string $slug = 'settings-working-hours';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Working hours');
    }

    public static function canAccess(): bool
    {
        return Filament::auth()->check();
    }

    public function mount(): void
    {
        $this->loadWorkingHoursState();
    }

    public function saveWorkingHours(): void
    {
        $this->persistWorkingHours();

        Notification::make()
            ->success()
            ->title(__('Saved'))
            ->send();
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->gap(true)
                    ->schema([
                        SchemaView::make('filament.doctor.profile.working-hours-tab')
                            ->viewData(fn (): array => [
                                'availabilities' => $this->availabilities,
                                'workingHours' => $this->workingHours,
                            ]),
                        Actions::make([
                            Action::make('saveWorkingHours')
                                ->label(__('Save schedule'))
                                ->color('primary')
                                ->action('saveWorkingHours'),
                        ])
                            ->alignment(Alignment::Center)
                            ->fullWidth(),
                    ]),
            ]);
    }

    public function getTitle(): string|Htmlable
    {
        return __('Working hours');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('Choose which days you accept bookings and your time slots.');
    }
}
