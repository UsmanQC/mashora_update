<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Pages;

use App\Filament\Doctor\Widgets\DoctorAppointmentStatsWidget;
use App\Filament\Doctor\Widgets\DoctorInvoiceStatsWidget;
use App\Filament\Doctor\Widgets\DoctorUpcomingAppointmentsWidget;
use App\Filament\Doctor\Widgets\DoctorWelcomeWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Contracts\Support\Htmlable;

final class DoctorDashboard extends BaseDashboard
{
    public static function getNavigationLabel(): string
    {
        return __('Home');
    }

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    public function getTitle(): string|Htmlable
    {
        return __('Home');
    }

    public function getColumns(): int|array
    {
        return 1;
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getWidgetsContentComponent(),
            ]);
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return [
            DoctorWelcomeWidget::class,
            DoctorInvoiceStatsWidget::class,
            DoctorAppointmentStatsWidget::class,
            DoctorUpcomingAppointmentsWidget::class,
        ];
    }
}
