<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Widgets;

use App\Filament\Doctor\Concerns\BelongsToDoctorPanel;
use App\Models\Appointment;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

final class DoctorAppointmentStatsWidget extends StatsOverviewWidget
{
    use BelongsToDoctorPanel;

    protected static ?int $sort = -10;

    /** @var int | array<string, ?int> | null */
    protected int|array|null $columns = [
        'default' => 2,
        '@lg' => 4,
    ];

    protected function getHeading(): ?string
    {
        return __('Appointments');
    }

    protected function getDescription(): ?string
    {
        return __('Scheduled appointment dates in each period.');
    }

    protected function getStats(): array
    {
        $doctorId = (int) Filament::auth()->id();

        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->copy()->endOfWeek();
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->copy()->endOfMonth();

        return [
            Stat::make(__('Today'), (string) $this->appointmentCount($doctorId, $today, $today))
                ->color('primary')
                ->icon(Heroicon::OutlinedCalendarDays),
            Stat::make(__('Yesterday'), (string) $this->appointmentCount($doctorId, $yesterday, $yesterday))
                ->color('gray')
                ->icon(Heroicon::OutlinedCalendarDays),
            Stat::make(__('This week'), (string) $this->appointmentCount($doctorId, $weekStart, $weekEnd))
                ->color('success')
                ->icon(Heroicon::OutlinedCalendarDays),
            Stat::make(__('This month'), (string) $this->appointmentCount($doctorId, $monthStart, $monthEnd))
                ->color('info')
                ->icon(Heroicon::OutlinedCalendarDays),
        ];
    }

    private function appointmentCount(int $doctorId, Carbon $start, Carbon $end): int
    {
        return Appointment::query()
            ->where('doctor_id', $doctorId)
            ->whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()])
            ->count();
    }
}
