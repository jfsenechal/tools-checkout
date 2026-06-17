<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tool;

/**
 * Builds DYMO Connect ".dymo" label files (DesktopLabel XML) for a tool.
 *
 * The structure mirrors a label exported by DYMO Connect itself: the QR code
 * is a native <QRCodeObject> carrying the same "GSTOCK:T:{id}" payload used by
 * the scanner, so the printed code scans identically to the app's SVG codes.
 */
final class DymoLabelGenerator
{
    /**
     * Inch dimensions and DYMO Connect media names per supported size.
     *
     * @var array<string, array{width: float, height: float, label: string}>
     */
    private const SIZES = [
        // 25 x 25 mm square (LW 550 durable ref 2112286 / equivalent S0929120).
        '25x25' => ['width' => 0.9843, 'height' => 0.9843, 'label' => 'SmallS0929120'],
        // 57 x 32 mm multipurpose (LW 550 durable ref 2112289 / equivalent 11354).
        '32x57' => ['width' => 2.2441, 'height' => 1.2598, 'label' => 'Multipurpose11354'],
    ];

    public function generateForTool(Tool $tool, string $size): string
    {
        $config = self::SIZES[$size] ?? self::SIZES['25x25'];

        $data = 'GSTOCK:T:'.$tool->id;
        $isWide = $size === '32x57';

        $width = $config['width'];
        $height = $config['height'];
        $margin = 0.06;

        if ($isWide) {
            // QR on the left (square, full height), tool name on the right.
            $qrSide = $height - 2 * $margin;
            $objects = $this->qrCodeObject($data, $margin, $margin, $qrSide, $qrSide);

            $textX = $margin + $qrSide + 0.08;
            $objects .= "\n".$this->textObject(
                $tool->name,
                $textX,
                $margin,
                $width - $textX - $margin,
                $height - 2 * $margin,
            );
        } else {
            // Centred QR filling the square label.
            $qrSide = $width - 2 * $margin;
            $objects = $this->qrCodeObject($data, $margin, $margin, $qrSide, $qrSide);
        }

        return $this->wrap($config['label'], $width, $height, $objects);
    }

    public function filename(Tool $tool, string $size): string
    {
        $slug = preg_replace('/[^A-Za-z0-9_-]+/', '-', $tool->name) ?: 'tool';

        return mb_trim($slug, '-')."-{$tool->id}-{$size}.dymo";
    }

    private function wrap(string $labelName, float $width, float $height, string $objects): string
    {
        $labelName = $this->escape($labelName);

        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<DesktopLabel Version="1">
  <DYMOLabel Version="3">
    <Description>DYMO Label</Description>
    <Orientation>Landscape</Orientation>
    <LabelName>{$labelName}</LabelName>
    <InitialLength>0</InitialLength>
    <BorderStyle>SolidLine</BorderStyle>
    <DYMORect>
      <DYMOPoint>
        <X>0</X>
        <Y>0</Y>
      </DYMOPoint>
      <Size>
        <Width>{$width}</Width>
        <Height>{$height}</Height>
      </Size>
    </DYMORect>
    <BorderColor>
      <SolidColorBrush>
        <Color A="1" R="0" G="0" B="0"></Color>
      </SolidColorBrush>
    </BorderColor>
    <BorderThickness>1</BorderThickness>
    <Show_Border>False</Show_Border>
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

    private function qrCodeObject(string $data, float $x, float $y, float $width, float $height): string
    {
        $data = $this->escape($data);

        return <<<XML
        <QRCodeObject>
          <Name>BARCODE</Name>
          <Brushes>
            <BackgroundBrush>
              <SolidColorBrush>
                <Color A="1" R="1" G="1" B="1"></Color>
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
                <Color A="1" R="0" G="0" B="0"></Color>
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
          <BarcodeFormat>QRCode</BarcodeFormat>
          <Data>
            <DataString>{$data}</DataString>
          </Data>
          <HorizontalAlignment>Center</HorizontalAlignment>
          <VerticalAlignment>Middle</VerticalAlignment>
          <Size>Medium</Size>
          <EQRCodeType>QRCodeText</EQRCodeType>
          <TextDataHolder>
            <Value>{$data}</Value>
          </TextDataHolder>
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
        </QRCodeObject>
XML;
    }

    private function textObject(string $text, float $x, float $y, float $width, float $height): string
    {
        $text = $this->escape($text);

        return <<<XML
        <TextObject>
          <Name>NameText</Name>
          <Brushes>
            <BackgroundBrush>
              <SolidColorBrush>
                <Color A="0" R="1" G="1" B="1"></Color>
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
          <HorizontalAlignment>Center</HorizontalAlignment>
          <VerticalAlignment>Middle</VerticalAlignment>
          <FitMode>ShrinkToFit</FitMode>
          <IsVertical>False</IsVertical>
          <FormattedText>
            <FitMode>ShrinkToFit</FitMode>
            <HorizontalAlignment>Center</HorizontalAlignment>
            <VerticalAlignment>Middle</VerticalAlignment>
            <IsVertical>False</IsVertical>
            <LineTextSpan>
              <TextSpan>
                <Text>{$text}</Text>
                <FontInfo>
                  <FontName>Arial</FontName>
                  <FontSize>10</FontSize>
                  <IsBold>True</IsBold>
                  <IsItalic>False</IsItalic>
                  <IsUnderline>False</IsUnderline>
                  <FontBrush>
                    <SolidColorBrush>
                      <Color A="1" R="0" G="0" B="0"></Color>
                    </SolidColorBrush>
                  </FontBrush>
                </FontInfo>
              </TextSpan>
            </LineTextSpan>
          </FormattedText>
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
        </TextObject>
XML;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
