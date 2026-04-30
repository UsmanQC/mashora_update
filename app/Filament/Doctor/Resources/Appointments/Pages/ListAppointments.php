<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Resources\Appointments\Pages;

use App\Filament\Doctor\Resources\Appointments\AppointmentResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

final class ListAppointments extends ListRecords
{
    protected static string $resource = AppointmentResource::class;

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('All')),
            'today' => Tab::make(__('Today'))
                ->query(fn (Builder $query): Builder => $query->whereDate(
                    $query->qualifyColumn('appointment_date'),
                    Carbon::today(),
                )),
            'upcoming' => Tab::make(__('Upcoming'))
                ->query(fn (Builder $query): Builder => $query->whereDate(
                    $query->qualifyColumn('appointment_date'),
                    '>',
                    Carbon::today(),
                )),
            'completed' => Tab::make(__('Completed'))
                ->query(fn (Builder $query): Builder => $query->where(
                    $query->qualifyColumn('status'),
                    'completed',
                )),
            'cancelled' => Tab::make(__('Cancelled'))
                ->query(fn (Builder $query): Builder => $query->where(
                    $query->qualifyColumn('status'),
                    'cancelled',
                )),
        ];
    }
}
