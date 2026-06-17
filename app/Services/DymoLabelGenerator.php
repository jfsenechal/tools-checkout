<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tool;
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

        $png = $this->qrPng('GSTOCK:T:'.$tool->id);

        $image = $this->imageObject(
            $png,
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
        $slug = preg_replace('/[^A-Za-z0-9_-]+/', '-', $tool->name) ?: 'tool';

        return mb_trim($slug, '-')."-{$tool->id}-{$size}.dymo";
    }

    /**
     * Render the QR code as a base64-encoded PNG (high error correction so the
     * thermal print scans reliably).
     */
    private function qrPng(string $data): string
    {
        // Wrap generation in an output buffer: the underlying QR/Imagick stack
        // can emit deprecation notices which would otherwise leak into the HTTP
        // response body and corrupt the downloaded .dymo file.
        ob_start();

        try {
            $png = (string) QrCode::format('png')
                ->size(600)
                ->margin(1)
                ->errorCorrection('H')
                ->generate($data);
        } finally {
            ob_end_clean();
        }

        return base64_encode($png);
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
