<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Widgets;

use App\Filament\Doctor\Concerns\BelongsToDoctorPanel;
use App\Filament\Doctor\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

final class DoctorUpcomingAppointmentsWidget extends TableWidget
{
    use BelongsToDoctorPanel;

    protected static ?int $sort = -22;

    /** @var int | string | array<string, int | null> */
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Upcoming appointments'))
            ->description(__('Scheduled visits from today onward (excluding completed or cancelled).'))
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->query($this->upcomingAppointmentsQuery())
            ->columns([
                TextColumn::make('appointment_number')
                    ->label(__('Appointment'))
                    ->description(fn (Appointment $record): ?string => $record->appointment_type === 'instant' ? __('Instant') : null),
                TextColumn::make('patient_name')
                    ->label(__('Patient'))
                    ->description(fn (Appointment $record): ?string => $record->patient_phone ?: null),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->formatStateUsing(fn (?string $state, Appointment $record): string => $this->formatAppointmentStatus($state, $record)),
                TextColumn::make('appointment_date')
                    ->label(__('When'))
                    ->formatStateUsing(fn ($state, Appointment $record): string => $record->appointment_date?->format('d M Y') ?? '—')
                    ->description(function (Appointment $record): ?string {
                        if (! $record->start_time || ! $record->end_time) {
                            return null;
                        }
                        $start = Carbon::parse($record->start_time)->format('h:i A');
                        $end = Carbon::parse($record->end_time)->format('h:i A');

                        return "{$start} – {$end}";
                    }),
            ])
            ->recordActions([
                Action::make('open')
                    ->label(__('View'))
                    ->url(fn (Appointment $record): string => AppointmentResource::getUrl('view', ['record' => $record], panel: 'doctor')),
            ])
            ->headerActions([
                Action::make('viewAll')
                    ->label(__('View all'))
                    ->url(AppointmentResource::getUrl(panel: 'doctor').'?tab=upcoming')
                    ->link(),
            ])
            ->emptyStateHeading(__('No upcoming appointments'))
            ->emptyStateDescription(__('Bookings for today and future dates will show here.'));
    }

    private function upcomingAppointmentsQuery(): Builder
    {
        $doctorId = (int) Filament::auth()->id();

        return Appointment::query()
            ->where('doctor_id', $doctorId)
            ->whereDate('appointment_date', '>=', Carbon::today())
            ->whereNotIn('status', ['cancelled', 'completed', 'not_attended'])
            ->orderBy('appointment_date')
            ->orderBy('start_time');
    }

    private function formatAppointmentStatus(?string $state, Appointment $record): string
    {
        $base = Str::ucfirst(str_replace('_', ' ', (string) $state));
        if ($state === 'cancelled' && filled($record->appointment_cancel_status)) {
            return trim($base.' '.$record->appointment_cancel_status);
        }

        return $base;
    }
}
