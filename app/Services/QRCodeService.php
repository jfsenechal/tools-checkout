<?php

namespace App\Services;

use App\Models\Tool;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class QRCodeService
{
    /**
     * Generate QR code for a tool
     */
    public function generateForTool(Tool $tool): string
    {
        $qrCodeData = json_encode([
            'type' => 'tool',
            'id' => $tool->id,
            'code' => $tool->code,
        ]);

        $filename = 'tool-' . $tool->code . '-' . time() . '.svg';
        
        $qrCode = QrCode::format('svg')
            ->size(300)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($qrCodeData);

        Storage::disk('public')->put('qrcodes/' . $filename, $qrCode);

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
            Storage::disk('public')->delete('qrcodes/' . $tool->qr_code);
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
            $deleted = Storage::disk('public')->delete('qrcodes/' . $tool->qr_code);
            if ($deleted) {
                $tool->update(['qr_code' => null]);
            }
            return $deleted;
        }

        return false;
    }
}
