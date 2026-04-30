<div class="fi-simple-page-content mx-auto w-full max-w-4xl space-y-6 px-4 py-6">
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
        'availabilities' => $availabilities ?? [],
        'workingHours' => $workingHours ?? [],
    ])

    <div class="flex justify-end border-t border-gray-200 pt-6 dark:border-white/10">
        <x-filament::button type="button" wire:click.prevent="save" wire:loading.attr="disabled">
            {{ __('Finish onboarding') }}
        </x-filament::button>
    </div>
</div>
