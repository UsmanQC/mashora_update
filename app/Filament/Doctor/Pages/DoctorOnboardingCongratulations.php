<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Pages;

use App\Models\Doctor;
use Filament\Facades\Filament;
use Illuminate\Contracts\Support\Htmlable;

final class DoctorOnboardingCongratulations extends DoctorSimplePanelPage
{
    protected static string $layout = 'filament-panels::components.layout.simple';

    protected static ?string $slug = 'onboarding-congratulations';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.doctor.onboarding.congratulations';

    public static function canAccess(): bool
    {
        return Filament::auth()->check();
    }

    public function mount(): void
    {
        /** @var Doctor $doctor */
        $doctor = Filament::auth()->user();

        if (! $doctor->profile_completed) {
            $this->redirect(DoctorOnboardingBasicInfo::getUrl(panel: 'doctor'));
        }
    }

    public function start(): void
    {
        $this->redirect(DoctorDashboard::getUrl(panel: 'doctor'));
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('Congratulations');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('Your onboarding is complete. Continue to the doctor dashboard.');
    }
}
