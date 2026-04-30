<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Concerns;

use App\Filament\Doctor\DoctorWorkingHoursDays;
use App\Models\Doctor;
use Filament\Facades\Filament;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

trait HandlesDoctorWorkingHours
{
    /** @var list<string> */
    public array $availabilities = [];

    /** @var array<string, list<array{start_time: string, end_time: string}>> */
    public array $workingHours = [];

    public function addSlot(string $day): void
    {
        $startTime = '09:00';
        $endTime = '10:00';

        if (! empty($this->workingHours[$day])) {
            $latest = end($this->workingHours[$day]);
            $startTime = Carbon::createFromFormat('H:i', $latest['end_time'])->format('H:i');
            $endTime = Carbon::createFromFormat('H:i', $startTime)->addHour()->format('H:i');
        }

        $this->workingHours[$day][] = [
            'start_time' => $startTime,
            'end_time' => $endTime,
        ];
    }

    public function removeSlot(string $day, int $key): void
    {
        if (isset($this->workingHours[$day][$key])) {
            unset($this->workingHours[$day][$key]);
            $this->workingHours[$day] = array_values($this->workingHours[$day]);
        }
    }

    protected function loadWorkingHoursState(?Doctor $doctor = null): void
    {
        $doctor ??= Filament::auth()->user();
        if (! $doctor instanceof Doctor) {
            return;
        }

        if ($doctor->workingDays()->exists()) {
            $this->availabilities = $doctor->workingDays()->pluck('day_of_week')->all();

            foreach ($doctor->workingDays()->get() as $day) {
                $this->workingHours[$day->day_of_week] = $day->workingHours()
                    ->get()
                    ->map(fn ($wh): array => [
                        'start_time' => substr((string) $wh->start_time, 0, 5),
                        'end_time' => substr((string) $wh->end_time, 0, 5),
                    ])
                    ->all();
            }

            return;
        }

        $this->availabilities = DoctorWorkingHoursDays::ALL;

        foreach (DoctorWorkingHoursDays::ALL as $day) {
            $this->workingHours[$day] = [
                ['start_time' => '10:00', 'end_time' => '14:00'],
            ];
        }
    }

    protected function persistWorkingHours(?Doctor $doctor = null): void
    {
        $doctor ??= Filament::auth()->user();
        if (! $doctor instanceof Doctor) {
            return;
        }

        $dayList = implode(',', DoctorWorkingHoursDays::ALL);

        $validated = validator([
            'availabilities' => $this->availabilities,
            'workingHours' => $this->workingHours,
        ], [
            'availabilities' => ['required', 'array', 'min:1'],
            'availabilities.*' => ['string', 'in:'.$dayList],
            'workingHours' => ['required', 'array'],
            'workingHours.*' => ['array', 'min:1'],
            'workingHours.*.*.start_time' => ['required', 'date_format:H:i'],
            'workingHours.*.*.end_time' => ['required', 'date_format:H:i'],
        ])->validate();

        $workingDays = $validated['availabilities'];

        $doctor->workingDays()->whereNotIn('day_of_week', $workingDays)->delete();

        foreach ($workingDays as $dayOfWeek) {
            $slots = $validated['workingHours'][$dayOfWeek] ?? [];

            if ($slots === []) {
                throw ValidationException::withMessages([
                    "workingHours.{$dayOfWeek}" => __('Add at least one time range for each selected day.'),
                ]);
            }

            $workingDay = $doctor->workingDays()->updateOrCreate(
                ['doctor_id' => $doctor->id, 'day_of_week' => $dayOfWeek],
                [
                    'day_of_week' => $dayOfWeek,
                    'is_working' => true,
                ]
            );

            $workingDay->workingHours()->delete();

            foreach ($slots as $slot) {
                $start = strlen($slot['start_time']) === 5 ? $slot['start_time'].':00' : $slot['start_time'];
                $end = strlen($slot['end_time']) === 5 ? $slot['end_time'].':00' : $slot['end_time'];

                $workingDay->workingHours()->create([
                    'start_time' => $start,
                    'end_time' => $end,
                ]);
            }
        }
    }
}
