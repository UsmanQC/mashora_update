<x-filament::section
    :heading="__('Welcome doctor')"
    :icon="\Filament\Support\Icons\Heroicon::OutlinedUserCircle"
>
    <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex min-w-0 items-start gap-4">
            @if (filled($avatarUrl))
                <img
                    src="{{ $avatarUrl }}"
                    alt=""
                    class="h-16 w-16 shrink-0 rounded-2xl object-cover ring-2 ring-gray-950/5 dark:ring-white/10"
                />
            @else
                <div
                    class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-primary-50 text-xl font-semibold text-primary-700 ring-1 ring-gray-950/10 dark:bg-gray-800 dark:text-primary-400 dark:ring-white/10"
                    aria-hidden="true"
                >
                    {{ $nameInitial }}
                </div>
            @endif

            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                    {{ $greeting }}
                </p>
                <p class="mt-1 text-xl font-semibold tracking-tight text-gray-950 dark:text-white">
                    {{ __('Dr. :name', ['name' => $displayName]) }}
                </p>
            </div>
        </div>

        <div class="flex shrink-0 flex-col gap-2 sm:items-end">
            <x-filament::button
                tag="a"
                :href="$todayAppointmentsUrl"
                color="primary"
                :icon="\Filament\Support\Icons\Heroicon::OutlinedCalendarDays"
            >
                {{ __('Today\'s appointments') }}
            </x-filament::button>
        </div>
    </div>
</x-filament::section>
