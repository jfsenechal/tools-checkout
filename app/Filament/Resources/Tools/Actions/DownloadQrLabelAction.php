<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tools\Actions;

use App\Models\Tool;
use App\Services\DymoLabelGenerator;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DownloadQrLabelAction
{
    public static function make(): ActionGroup
    {
        return ActionGroup::make([
            self::printAction('25x25', 'Imprimer 25 × 25 mm')
                ->visible(fn (Tool $record): bool => (bool) $record->qr_code),
            self::printAction('32x57', 'Imprimer 32 × 57 mm')
                ->visible(fn (Tool $record): bool => (bool) $record->qr_code),
            self::dymoAction('25x25', 'DYMO 25 × 25 mm'),
            self::dymoAction('32x57', 'DYMO 32 × 57 mm'),
        ])
            ->label('Étiquette QR')
            ->icon(Heroicon::Printer)
            ->button();
    }

    private static function printAction(string $size, string $label): Action
    {
        return Action::make('qr_label_'.$size)
            ->label($label)
            ->icon(Heroicon::QrCode)
            ->url(fn (Tool $record): string => route('print.qr-label', [
                'tool' => $record,
                'size' => $size,
            ]))
            ->openUrlInNewTab();
    }

    private static function dymoAction(string $size, string $label): Action
    {
        return Action::make('qr_dymo_'.$size)
            ->label($label)
            ->icon(Heroicon::ArrowDownTray)
            // Stream the file as a download response so Livewire/SPA navigation
            // cannot render it inline as a page (the attachment is honoured).
            ->action(function (Tool $record) use ($size): StreamedResponse {
                $generator = app(DymoLabelGenerator::class);

                return response()->streamDownload(
                    fn () => print ($generator->generateForTool($record, $size)),
                    $generator->filename($record, $size),
                    ['Content-Type' => 'application/xml'],
                );
            });
    }
}
