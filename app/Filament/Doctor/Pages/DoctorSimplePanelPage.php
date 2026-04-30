<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Pages;

use Filament\Pages\Concerns\HasMaxWidth;
use Filament\Pages\Concerns\HasTopbar;
use Filament\Pages\Page;
use Filament\Pages\SimplePage;

/** Mirrors Filament {@see SimplePage} layout hooks while extending {@see Page} for routing. */
abstract class DoctorSimplePanelPage extends Page
{
    use HasMaxWidth;
    use HasTopbar;

    public function hasLogo(): bool
    {
        return true;
    }

    protected function getLayoutData(): array
    {
        return [
            'hasTopbar' => $this->hasTopbar(),
            'maxContentWidth' => $maxContentWidth = $this->getMaxWidth() ?? $this->getMaxContentWidth(),
            'maxWidth' => $maxContentWidth,
        ];
    }
}
