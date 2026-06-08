<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tool;
use App\Models\Worker;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

final class QRCodeService
{
    /**
     * Generate QR code for a tool
     */
    public function generateForTool(Tool $tool): string
    {
        $qrCodeData = 'GSTOCK:T:'.$tool->id;

        $filename = 'tool-'.$tool->id.'-'.time().'.svg';

        $svg = $this->renderLabeledQr($qrCodeData, $tool->name);

        Storage::disk('public')->put('qrcodes/'.$filename, $svg);

        return $filename;
    }

    /**
     * Generate QR codes for multiple tools
     */
    public function generateBatch(array $toolIds): array
    {
        $generated = [];

        foreach ($toolIds as $toolId) {
            $tool = Tool::find($toolId);
            if ($tool) {
                $filename = $this->generateForTool($tool);
                $tool->update(['qr_code' => $filename]);
                $generated[$toolId] = $filename;
            }
        }

        return $generated;
    }

    /**
     * Regenerate QR code for a tool
     */
    public function regenerateForTool(Tool $tool): string
    {
        // Delete old QR code if exists
        if ($tool->qr_code) {
            Storage::disk('public')->delete('qrcodes/'.$tool->qr_code);
        }

        $filename = $this->generateForTool($tool);
        $tool->update(['qr_code' => $filename]);

        return $filename;
    }

    /**
     * Delete QR code for a tool
     */
    public function deleteForTool(Tool $tool): bool
    {
        if ($tool->qr_code) {
            $deleted = Storage::disk('public')->delete('qrcodes/'.$tool->qr_code);
            if ($deleted) {
                $tool->update(['qr_code' => null]);
            }

            return $deleted;
        }

        return false;
    }

    /**
     * Generate QR code for a worker
     */
    public function generateForWorker(Worker $worker): string
    {
        $qrCodeData = 'GSTOCK:W:'.$worker->id;

        $filename = 'worker-'.$worker->id.'-'.time().'.svg';

        $label = mb_trim($worker->first_name.' '.$worker->last_name);

        $svg = $this->renderLabeledQr($qrCodeData, $label);

        Storage::disk('public')->put('qrcodes/'.$filename, $svg);

        return $filename;
    }

    /**
     * Generate QR codes for multiple workers
     */
    public function generateBatchWorkers(array $workerIds): array
    {
        $generated = [];

        foreach ($workerIds as $workerId) {
            $worker = Worker::find($workerId);
            if ($worker) {
                $filename = $this->generateForWorker($worker);
                $worker->update(['qr_code' => $filename]);
                $generated[$workerId] = $filename;
            }
        }

        return $generated;
    }

    /**
     * Regenerate QR code for a worker
     */
    public function regenerateForWorker(Worker $worker): string
    {
        if ($worker->qr_code) {
            Storage::disk('public')->delete('qrcodes/'.$worker->qr_code);
        }

        $filename = $this->generateForWorker($worker);
        $worker->update(['qr_code' => $filename]);

        return $filename;
    }

    /**
     * Render a QR code SVG with a visible text label underneath.
     */
    private function renderLabeledQr(string $data, string $label): string
    {
        $qrSize = 300;
        $labelHeight = 40;
        $totalHeight = $qrSize + $labelHeight;

        $qrSvg = (string) QrCode::format('svg')
            ->size($qrSize)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($data);

        // Strip XML prolog so the QR <svg> can be embedded as a nested element.
        $qrInner = preg_replace('/<\?xml[^>]*\?>\s*/', '', $qrSvg);

        $escapedLabel = htmlspecialchars($label, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $textX = (int) ($qrSize / 2);
        $textY = $qrSize + 26;

        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="{$qrSize}" height="{$totalHeight}" viewBox="0 0 {$qrSize} {$totalHeight}">
    {$qrInner}
    <text x="{$textX}" y="{$textY}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="16" font-weight="bold" fill="#000">{$escapedLabel}</text>
</svg>
SVG;
    }
}
