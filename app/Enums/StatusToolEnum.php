<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusToolEnum: string implements HasColor, HasLabel
{
    case Available = 'available';
    case CheckedOut = 'checked_out';
    case Maintenance = 'maintenance';
    case Retired = 'retired';

    public function getLabel(): string
    {
        return match ($this) {
            self::Available => 'Disponible',
            self::CheckedOut => 'Emprunté',
            self::Maintenance => 'En maintenance',
            self::Retired => 'Retiré',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::CheckedOut => 'warning',
            self::Maintenance => 'info',
            self::Retired => 'danger',
        };
    }
}
