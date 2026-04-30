<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Widgets;

use App\Filament\Doctor\Concerns\BelongsToDoctorPanel;
use App\Filament\Doctor\Resources\Appointments\AppointmentResource;
use App\Models\Doctor;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class DoctorWelcomeWidget extends Widget
{
    use BelongsToDoctorPanel;

    protected static ?int $sort = -30;

    protected int|string|array $columnSpan = 'full';

    /**
     * @var view-string
     */
    protected string $view = 'filament.doctor.widgets.doctor-welcome-widget';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        /** @var Doctor|null $doctor */
        $doctor = Filament::auth()->user();

        $displayName = __('Doctor');
        $avatarUrl = null;

        if ($doctor instanceof Doctor) {
            $displayName = trim((string) ($doctor->name ?: $doctor->name_ar ?: __('Doctor')));

            if (filled($doctor->profile_photo_path)) {
                $path = $doctor->profile_photo_path;
                $avatarUrl = is_string($path) && str_starts_with($path, 'http')
                    ? $path
                    : Storage::disk('public')->url($path);
            }
        }

        return [
            'avatarUrl' => $avatarUrl,
            'displayName' => $displayName,
            'nameInitial' => Str::upper(Str::substr($displayName, 0, 1)),
            'greeting' => $this->timeBasedGreeting(),
            'todayAppointmentsUrl' => AppointmentResource::getUrl(panel: 'doctor').'?tab=today',
        ];
    }

    private function timeBasedGreeting(): string
    {
        $hour = Carbon::now()->hour;

        if ($hour < 12) {
            return __('Good morning');
        }

        if ($hour < 17) {
            return __('Good afternoon');
        }

        return __('Good evening');
    }
}
