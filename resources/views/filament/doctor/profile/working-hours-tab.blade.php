@php
    $availabilities = $availabilities ?? [];
    $workingHours = $workingHours ?? [];
@endphp

<div class="mx-auto w-full max-w-4xl space-y-6">
    @if ($errors->has('availabilities') || $errors->has('workingHours'))
        <x-filament::section :heading="__('Please fix these issues')">
            <ul class="list-inside list-disc space-y-1 text-sm text-danger-600 dark:text-danger-400">
                @error('availabilities')
                    <li>{{ $message }}</li>
                @enderror
                @error('workingHours')
                    <li>{{ $message }}</li>
                @enderror
            </ul>
        </x-filament::section>
    @endif

    @include('filament.doctor.partials.working-hours-schedule', [
        'availabilities' => $availabilities,
        'workingHours' => $workingHours,
    ])
</div>
