<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Filament\Doctor\Pages\DoctorDashboard;
use App\Filament\Doctor\Pages\DoctorOnboardingBasicInfo;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mirrors Mashorapwa-prod {@code doctor.profile.completed}: incomplete profiles stay on onboarding;
 * completed doctors cannot revisit onboarding except the congratulations screen right after completion.
 */
final class RedirectDoctorOnboarding
{
    private const STEP_FRAGMENTS = [
        'onboarding-basic-info',
        'onboarding-additional-info',
        'onboarding-specialties',
        'onboarding-license',
        'onboarding-signature',
        'onboarding-duration',
        'onboarding-working-hours',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->guard('doctor')->check()) {
            return $next($request);
        }

        $doctor = auth()->guard('doctor')->user();
        $name = $request->route()?->getName();

        if ($name === 'doctor.sign-pad.store') {
            return $next($request);
        }

        $step = $this->matchesFragments($name, self::STEP_FRAGMENTS);
        $congrats = $this->isCongratulationsRoute($name);

        if (! $doctor->profile_completed) {
            if ($congrats) {
                return redirect()->to(DoctorOnboardingBasicInfo::getUrl(panel: 'doctor'));
            }

            if ($step) {
                return $next($request);
            }

            return redirect()->to(DoctorOnboardingBasicInfo::getUrl(panel: 'doctor'));
        }

        if (($step || $congrats) && ! $congrats) {
            return redirect()->to(DoctorDashboard::getUrl(panel: 'doctor'));
        }

        return $next($request);
    }

    private function matchesFragments(?string $name, array $fragments): bool
    {
        if ($name === null) {
            return false;
        }

        foreach ($fragments as $fragment) {
            if (str_contains($name, $fragment)) {
                return true;
            }
        }

        return false;
    }

    private function isCongratulationsRoute(?string $name): bool
    {
        return $name !== null && str_contains($name, 'onboarding-congratulations');
    }
}
