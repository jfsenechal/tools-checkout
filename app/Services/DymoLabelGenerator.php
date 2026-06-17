<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tool;
use Illuminate\Support\Str;
use Imagick;
use ImagickException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Builds DYMO Connect ".dymo" label files (DesktopLabel XML) for a tool.
 *
 * The QR code is rendered server-side as a PNG and embedded as an <ImageObject>,
 * which DYMO scales to fill its layout box (a native <QRCodeObject> only renders
 * at fixed enum sizes and cannot fill the label). The schema mirrors a label
 * exported by DYMO Connect 1.4 for the LW Durable rolls. The QR payload matches
 * the scanner format "GSTOCK:T:{id}".
 */
final class DymoLabelGenerator
{
    /**
     * Per-size geometry, in inches, matching DYMO Connect's media definitions.
     * "rect" is the printable rectangle; "img" is the image layout box (the QR
     * is scaled uniformly and centred within it).
     *
     * @var array<string, array{
     *     orientation: string,
     *     label: string,
     *     rectX: float, rectY: float, rectW: float, rectH: float,
     *     imgX: float, imgY: float, imgW: float, imgH: float,
     * }>
     */
    private const SIZES = [
        // 25 x 25 mm square — DYMO LW Durable ref 2112286 (exact export values).
        '25x25' => [
            'orientation' => 'Portrait',
            'label' => 'LW DURABLE 25MM X 25MM',
            'rectX' => 0.04, 'rectY' => 0.1, 'rectW' => 0.9033, 'rectH' => 0.84,
            'imgX' => 0.0658, 'imgY' => 0.11, 'imgW' => 0.8685, 'imgH' => 0.8121,
        ],
        // 57 x 32 mm — DYMO LW Durable ref 2112289. QR scaled to the printable
        // height and centred; ScaleMode "Uniform" keeps it square.
        '32x57' => [
            'orientation' => 'Landscape',
            'label' => 'LW DURABLE 57MM X 32MM',
            'rectX' => 0.04, 'rectY' => 0.08, 'rectW' => 2.1641, 'rectH' => 1.1,
            'imgX' => 0.04, 'imgY' => 0.08, 'imgW' => 2.1641, 'imgH' => 1.1,
        ],
    ];

    public function generateForTool(Tool $tool, string $size): string
    {
        $config = self::SIZES[$size] ?? self::SIZES['25x25'];

        $qrPng = $this->qrPng('GSTOCK:T:'.$tool->id);

        // The wide label pairs the QR with the tool name. Both are composed into
        // a single PNG so we only rely on the verified <ImageObject> schema.
        $png = $size === '32x57'
            ? $this->composeQrWithName($qrPng, $tool->name, $config['imgW'], $config['imgH'])
            : $qrPng;

        $image = $this->imageObject(
            base64_encode($png),
            $config['imgX'],
            $config['imgY'],
            $config['imgW'],
            $config['imgH'],
        );

        return $this->wrap(
            $config['orientation'],
            $config['label'],
            $config['rectX'],
            $config['rectY'],
            $config['rectW'],
            $config['rectH'],
            $image,
        );
    }

    public function filename(Tool $tool, string $size): string
    {
        $slug = Str::slug($tool->name) ?: 'tool';

        return "{$slug}-{$tool->id}-{$size}.dymo";
    }

    /**
     * Render the QR code as a PNG (high error correction so the thermal print
     * scans reliably). Returns the raw PNG bytes.
     */
    private function qrPng(string $data): string
    {
        // Wrap generation in an output buffer: the underlying QR/Imagick stack
        // can emit deprecation notices which would otherwise leak into the HTTP
        // response body and corrupt the downloaded .dymo file.
        ob_start();

        try {
            return (string) QrCode::format('png')
                ->size(600)
                ->margin(1)
                ->errorCorrection('H')
                ->generate($data);
        } finally {
            ob_end_clean();
        }
    }

    /**
     * Compose the QR (left) and the tool name (right, auto-fitted) into a single
     * PNG whose aspect ratio matches the image box, so it fills the label.
     *
     * @throws ImagickException
     */
    private function composeQrWithName(string $qrPng, string $name, float $aspectW, float $aspectH): string
    {
        $height = 600;
        $width = (int) round($height * ($aspectW / $aspectH));

        $qr = new Imagick;
        $qr->readImageBlob($qrPng);
        $qr->resizeImage($height, $height, Imagick::FILTER_BOX, 1);

        $canvas = new Imagick;
        $canvas->newImage($width, $height, 'white', 'png');
        $canvas->compositeImage($qr, Imagick::COMPOSITE_OVER, 0, 0);

        $name = mb_trim($name);

        if ($name !== '') {
            $textX = $height + 24;
            $textW = $width - $textX - 20;
            $caption = $this->captionImage($name, $textW, $height - 60);
            $offsetY = (int) max(0, ($height - $caption->getImageHeight()) / 2);
            $canvas->compositeImage($caption, Imagick::COMPOSITE_OVER, $textX, $offsetY);
            $caption->clear();
        }

        $canvas->setImageFormat('png');
        $blob = $canvas->getImageBlob();

        $qr->clear();
        $canvas->clear();

        return $blob;
    }

    /**
     * Build a caption image whose font size is auto-fitted to the given box.
     *
     * @throws ImagickException
     */
    private function captionImage(string $text, int $width, int $height): Imagick
    {
        $caption = new Imagick;
        $caption->setBackgroundColor('white');
        $caption->setFont($this->fontPath());
        $caption->setGravity(Imagick::GRAVITY_CENTER);
        $caption->newPseudoImage($width, $height, 'caption:'.$text);
        $caption->setImageFormat('png');

        return $caption;
    }

    private function fontPath(): string
    {
        $candidates = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return 'Helvetica';
    }

    private function wrap(
        string $orientation,
        string $labelName,
        float $rectX,
        float $rectY,
        float $rectW,
        float $rectH,
        string $objects,
    ): string {
        $labelName = $this->escape($labelName);

        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<DesktopLabel Version="1">
  <DYMOLabel Version="4">
    <Description>DYMO Label</Description>
    <Orientation>{$orientation}</Orientation>
    <LabelName>{$labelName}</LabelName>
    <InitialLength>0</InitialLength>
    <BorderStyle>SolidLine</BorderStyle>
    <DYMORect>
      <DYMOPoint>
        <X>{$rectX}</X>
        <Y>{$rectY}</Y>
      </DYMOPoint>
      <Size>
        <Width>{$rectW}</Width>
        <Height>{$rectH}</Height>
      </Size>
    </DYMORect>
    <BorderColor>
      <SolidColorBrush>
        <Color A="1" R="0" G="0" B="0"></Color>
      </SolidColorBrush>
    </BorderColor>
    <BorderThickness>1</BorderThickness>
    <Show_Border>False</Show_Border>
    <HasFixedLength>False</HasFixedLength>
    <FixedLengthValue>0</FixedLengthValue>
    <DynamicLayoutManager>
      <RotationBehavior>ClearObjects</RotationBehavior>
      <LabelObjects>
{$objects}
      </LabelObjects>
    </DynamicLayoutManager>
  </DYMOLabel>
  <LabelApplication>Blank</LabelApplication>
  <DataTable>
    <Columns></Columns>
    <Rows></Rows>
  </DataTable>
</DesktopLabel>
XML;
    }

    private function imageObject(string $base64Png, float $x, float $y, float $width, float $height): string
    {
        return <<<XML
        <ImageObject>
          <Name>ImageObject0</Name>
          <Brushes>
            <BackgroundBrush>
              <SolidColorBrush>
                <Color A="0" R="0" G="0" B="0"></Color>
              </SolidColorBrush>
            </BackgroundBrush>
            <BorderBrush>
              <SolidColorBrush>
                <Color A="1" R="0" G="0" B="0"></Color>
              </SolidColorBrush>
            </BorderBrush>
            <StrokeBrush>
              <SolidColorBrush>
                <Color A="1" R="0" G="0" B="0"></Color>
              </SolidColorBrush>
            </StrokeBrush>
            <FillBrush>
              <SolidColorBrush>
                <Color A="0" R="0" G="0" B="0"></Color>
              </SolidColorBrush>
            </FillBrush>
          </Brushes>
          <Rotation>Rotation0</Rotation>
          <OutlineThickness>1</OutlineThickness>
          <IsOutlined>False</IsOutlined>
          <BorderStyle>SolidLine</BorderStyle>
          <Margin>
            <DYMOThickness Left="0" Top="0" Right="0" Bottom="0" />
          </Margin>
          <Data>{$base64Png}</Data>
          <ScaleMode>Uniform</ScaleMode>
          <HorizontalAlignment>Center</HorizontalAlignment>
          <VerticalAlignment>Middle</VerticalAlignment>
          <ObjectLayout>
            <DYMOPoint>
              <X>{$x}</X>
              <Y>{$y}</Y>
            </DYMOPoint>
            <Size>
              <Width>{$width}</Width>
              <Height>{$height}</Height>
            </Size>
          </ObjectLayout>
        </ImageObject>
XML;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
