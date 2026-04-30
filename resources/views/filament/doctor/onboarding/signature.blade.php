<div class="fi-simple-page-content mx-auto w-full max-w-3xl space-y-6 px-4 py-6">
    <form id="doctor-sign-form" method="POST" action="{{ route('doctor.sign-pad.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="sign" id="sign-field" value="">

        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 overflow-hidden">
            <canvas id="sign-pad" class="w-full touch-none" height="220"></canvas>
        </div>

        <div class="flex flex-wrap gap-3 justify-between">
            <x-filament::button type="button" color="gray" outlined="true" id="sign-clear">
                {{ __('Clear') }}
            </x-filament::button>

            <x-filament::button type="submit" id="sign-submit">
                {{ __('Save signature') }}
            </x-filament::button>
        </div>
    </form>

    <p class="text-sm text-gray-500 dark:text-gray-400">
        {{ __('After saving you will continue with consultation pricing and availability.') }}
    </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const canvas = document.getElementById('sign-pad');
        const input = document.getElementById('sign-field');
        const form = document.getElementById('doctor-sign-form');
        if (!canvas || !window.SignaturePad) return;

        const pad = new SignaturePad(canvas, { backgroundColor: 'rgba(0,0,0,0)' });

        const resize = () => {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.height * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            pad.clear();
        };
        resize();
        window.addEventListener('resize', resize);

        document.getElementById('sign-clear')?.addEventListener('click', (e) => {
            e.preventDefault();
            pad.clear();
        });

        form?.addEventListener('submit', (e) => {
            if (pad.isEmpty()) {
                e.preventDefault();
                alert(@json(__('Please draw your signature first.')));
                return;
            }
            input.value = pad.toDataURL('image/png');
        });
    });
</script>
