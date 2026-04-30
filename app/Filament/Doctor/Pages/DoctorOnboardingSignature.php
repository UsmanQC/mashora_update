<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Pages;

use App\Models\Doctor;
use Filament\Facades\Filament;
use Illuminate\Contracts\Support\Htmlable;

final class DoctorOnboardingSignature extends DoctorSimplePanelPage
{
    protected static string $layout = 'filament-panels::components.layout.simple';

    protected static ?string $slug = 'onboarding-signature';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.doctor.onboarding.signature';

    public static function canAccess(): bool
    {
        return Filament::auth()->check();
    }

    public function mount(): void
    {
        /** @var Doctor $doctor */
        $doctor = Filament::auth()->user();

        if ($doctor->hasBeenSigned()) {
            $this->redirect(DoctorOnboardingDuration::getUrl(panel: 'doctor'));
        }
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('Signature');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('Step 5 of 8 — sign using your mouse or touchscreen.');
    }
}
