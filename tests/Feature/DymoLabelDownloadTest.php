<?php

declare(strict_types=1);

use App\Enums\StatusToolEnum;
use App\Models\Category;
use App\Models\Tool;
use App\Services\DymoLabelGenerator;

function makeTool(string $name = 'Impact Drill'): Tool
{
    return Tool::create([
        'name' => $name,
        'category_id' => Category::factory()->create(['name' => 'Power Tools'])->id,
        'status' => StatusToolEnum::Available,
    ]);
}

it('generates valid DesktopLabel XML embedding a PNG QR image', function () {
    $tool = makeTool();

    $xml = app(DymoLabelGenerator::class)->generateForTool($tool, '25x25');

    $doc = simplexml_load_string($xml);

    expect($doc)->not->toBeFalse()
        ->and($doc->getName())->toBe('DesktopLabel')
        ->and($xml)->toContain('<ImageObject>')
        ->and($xml)->toContain('<ScaleMode>Uniform</ScaleMode>');

    // The <Data> payload must be a real base64 PNG so DYMO can render it.
    $data = (string) $doc->DYMOLabel->DynamicLayoutManager->LabelObjects->ImageObject->Data;
    $png = base64_decode($data, true);

    expect($png)->not->toBeFalse()
        ->and(bin2hex(substr((string) $png, 0, 4)))->toBe('89504e47');
});

it('uses the matching DYMO durable label name and orientation per size', function () {
    $tool = makeTool();
    $generator = app(DymoLabelGenerator::class);

    expect($generator->generateForTool($tool, '25x25'))
        ->toContain('<LabelName>LW DURABLE 25MM X 25MM</LabelName>')
        ->toContain('<Orientation>Portrait</Orientation>');

    expect($generator->generateForTool($tool, '32x57'))
        ->toContain('<LabelName>LW DURABLE 57MM X 32MM</LabelName>')
        ->toContain('<Orientation>Landscape</Orientation>');
});

it('downloads a .dymo attachment for the requested size', function () {
    $tool = makeTool();

    $response = $this->get(route('print.qr-dymo', ['tool' => $tool, 'size' => '32x57']));

    $response->assertOk()
        ->assertHeader('content-type', 'application/xml');

    expect($response->headers->get('content-disposition'))
        ->toContain('attachment')
        ->toContain('-32x57.dymo');

    // The body must be clean XML with no leaked warnings/output before <?xml.
    $body = $response->getContent();
    expect(str_starts_with($body, '<?xml'))->toBeTrue()
        ->and(simplexml_load_string($body))->not->toBeFalse();
});

it('defaults to the 25x25 dymo label for an unknown size', function () {
    $tool = makeTool();

    $response = $this->get(route('print.qr-dymo', ['tool' => $tool, 'size' => 'bogus']));

    $response->assertOk();
    expect($response->headers->get('content-disposition'))->toContain('-25x25.dymo');
});
