<?php

namespace App\Providers;

use App\Filament\Doctor\Pages\DoctorOnboardingBasicInfo;
use Filament\Auth\Http\Responses\Contracts\RegistrationResponse;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Livewire\Features\SupportRedirects\Redirector as LivewireRedirector;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(RegistrationResponse::class, function (): RegistrationResponse {
            return new class implements RegistrationResponse
            {
                public function toResponse($request): RedirectResponse|LivewireRedirector
                {
                    if (Filament::getCurrentPanel()?->getId() === 'doctor') {
                        return redirect()->to(DoctorOnboardingBasicInfo::getUrl(panel: 'doctor'));
                    }

                    return redirect()->intended(Filament::getUrl());
                }
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        //
    }
}
