<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Pages;

use App\Filament\Doctor\Concerns\HandlesDoctorWorkingHours;
use App\Models\Doctor;
use Filament\Facades\Filament;
use Illuminate\Contracts\Support\Htmlable;

final class DoctorOnboardingWorkingHours extends DoctorSimplePanelPage
{
    use HandlesDoctorWorkingHours;

    protected static string $layout = 'filament-panels::components.layout.simple';

    protected static ?string $slug = 'onboarding-working-hours';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.doctor.onboarding.working-hours';

    public static function canAccess(): bool
    {
        return Filament::auth()->check();
    }

    public function mount(): void
    {
        $this->loadWorkingHoursState();
    }

    public function save(): void
    {
        /** @var Doctor $doctor */
        $doctor = Filament::auth()->user();

        $this->persistWorkingHours($doctor);

        $doctor->forceFill(['profile_completed' => true])->save();

        $this->redirect(DoctorOnboardingCongratulations::getUrl(panel: 'doctor'));
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('Working hours');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('Step 7 of 8 — when patients can book you.');
    }
}
