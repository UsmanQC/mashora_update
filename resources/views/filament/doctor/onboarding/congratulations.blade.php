<div class="fi-simple-page-content mx-auto w-full max-w-lg space-y-6 px-4 py-6">
    <p class="text-center text-gray-600 dark:text-gray-300">
        {{ __('Thank you for completing every step — matching the legacy Mashora doctor registration flow.') }}
    </p>

    <div class="flex justify-center">
        <x-filament::button wire:click="start" size="lg">
            {{ __('Open dashboard') }}
        </x-filament::button>
    </div>
</div>
