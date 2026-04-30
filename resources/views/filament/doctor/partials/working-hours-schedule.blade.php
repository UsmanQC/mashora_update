@php
    use App\Filament\Doctor\DoctorWorkingHoursDays;

    $availabilities = $availabilities ?? [];
    $workingHours = $workingHours ?? [];

    $dayLabels = [
        'sunday' => __('Sunday'),
        'monday' => __('Monday'),
        'tuesday' => __('Tuesday'),
        'wednesday' => __('Wednesday'),
        'thursday' => __('Thursday'),
        'friday' => __('Friday'),
        'saturday' => __('Saturday'),
    ];

    $inputTimeClass =
        'fi-input block w-full max-w-xs rounded-lg border-0 bg-white px-3 py-2 text-sm text-gray-950 shadow-sm ring-1 ring-gray-950/10 transition placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/15 dark:placeholder:text-gray-500 dark:focus:ring-primary-500';
@endphp

<div class="flex flex-col gap-6">
    @foreach (DoctorWorkingHoursDays::ALL as $day)
        @php($open = in_array($day, $availabilities, true))
        @php($slots = $workingHours[$day] ?? [])
        @php($heading = $dayLabels[$day] ?? \Illuminate\Support\Str::title($day))

        <x-filament::section :heading="$heading" compact>
            <div class="flex flex-wrap items-center gap-3">
                <input
                    type="checkbox"
                    wire:model.live="availabilities"
                    value="{{ $day }}"
                    class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-600 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-900 dark:checked:bg-primary-500 dark:checked:border-primary-500 dark:focus:ring-primary-500 dark:focus:ring-offset-gray-900"
                    aria-label="{{ __('Available on :day', ['day' => $heading]) }}"
                />
                <span class="text-sm text-gray-700 dark:text-gray-200">
                    {{ $open ? __('This day is available for booking.') : __('This day is off — patients cannot book.') }}
                </span>
            </div>

            @if ($open)
                <div class="mt-4 flex flex-col gap-4 border-t border-gray-200 pt-4 dark:border-white/10">
                    @forelse ($slots as $idx => $slot)
                        <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end">
                            <div class="flex flex-col gap-1">
                                <label class="fi-fo-field-label text-sm">
                                    {{ __('Start') }}
                                </label>
                                <input
                                    type="time"
                                    wire:model.live="workingHours.{{ $day }}.{{ $idx }}.start_time"
                                    class="{{ $inputTimeClass }}"
                                />
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="fi-fo-field-label text-sm">
                                    {{ __('End') }}
                                </label>
                                <input
                                    type="time"
                                    wire:model.live="workingHours.{{ $day }}.{{ $idx }}.end_time"
                                    class="{{ $inputTimeClass }}"
                                />
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @if ($loop->first)
                                    <x-filament::button type="button" size="sm" wire:click.prevent="addSlot('{{ $day }}')">
                                        {{ __('Add slot') }}
                                    </x-filament::button>
                                @else
                                    <x-filament::button type="button" color="danger" size="sm" outlined wire:click.prevent="removeSlot('{{ $day }}', {{ $idx }})">
                                        {{ __('Remove') }}
                                    </x-filament::button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('No time slots yet for this day.') }}
                        </p>
                        <x-filament::button type="button" size="sm" wire:click.prevent="addSlot('{{ $day }}')">
                            {{ __('Add first slot') }}
                        </x-filament::button>
                    @endforelse

                    @error('workingHours.'.$day)
                        <p class="text-sm font-medium text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>
            @endif
        </x-filament::section>
    @endforeach
</div>
