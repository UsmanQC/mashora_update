<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Concerns;

use Filament\Facades\Filament;

trait BelongsToDoctorPanel
{
    public static function canView(): bool
    {
        return Filament::getCurrentOrDefaultPanel()?->getId() === 'doctor';
    }
}
