<div class="space-y-4">
    <p class="text-sm text-gray-600 dark:text-gray-400">
        {{ __('Choose your preferred language for this panel.') }}
    </p>
    <div class="flex flex-wrap gap-2">
        <x-filament::button type="button" color="gray" wire:click="switchLocale('en')">
            EN
        </x-filament::button>
        <x-filament::button type="button" color="gray" wire:click="switchLocale('ar')">
            عربي
        </x-filament::button>
    </div>
</div>
