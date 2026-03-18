<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

final class ScannerLinkWidget extends Widget
{
    protected static ?int $sort = 1;

    protected string $view = 'filament.widgets.scanner-link-widget';

    protected int|string|array $columnSpan = 'full';
}
