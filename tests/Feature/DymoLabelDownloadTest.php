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

it('generates well-formed DesktopLabel XML with the tool qr payload', function () {
    $tool = makeTool();

    $xml = app(DymoLabelGenerator::class)->generateForTool($tool, '25x25');

    $doc = simplexml_load_string($xml);

    expect($doc)->not->toBeFalse()
        ->and($doc->getName())->toBe('DesktopLabel')
        ->and($xml)->toContain('<BarcodeFormat>QRCode</BarcodeFormat>')
        ->and($xml)->toContain('<DataString>GSTOCK:T:'.$tool->id.'</DataString>')
        ->and($xml)->toContain('<Value>GSTOCK:T:'.$tool->id.'</Value>');
});

it('includes the tool name only on the wide 32x57 label', function () {
    $tool = makeTool('Big Hammer');
    $generator = app(DymoLabelGenerator::class);

    expect($generator->generateForTool($tool, '32x57'))
        ->toContain('<Text>Big Hammer</Text>')
        ->toContain('Durable2112289');

    expect($generator->generateForTool($tool, '25x25'))
        ->not->toContain('<Text>Big Hammer</Text>');
});

it('escapes special characters in the tool name', function () {
    $tool = makeTool('Drill <Pro> & Co');

    $xml = app(DymoLabelGenerator::class)->generateForTool($tool, '32x57');

    expect(simplexml_load_string($xml))->not->toBeFalse()
        ->and($xml)->toContain('Drill &lt;Pro&gt; &amp; Co');
});

it('downloads a .dymo attachment for the requested size', function () {
    $tool = makeTool();

    $response = $this->get(route('print.qr-dymo', ['tool' => $tool, 'size' => '32x57']));

    $response->assertOk()
        ->assertHeader('content-type', 'application/xml');

    expect($response->headers->get('content-disposition'))
        ->toContain('attachment')
        ->toContain('-32x57.dymo');
});

it('defaults to the 25x25 dymo label for an unknown size', function () {
    $tool = makeTool();

    $response = $this->get(route('print.qr-dymo', ['tool' => $tool, 'size' => 'bogus']));

    $response->assertOk();
    expect($response->headers->get('content-disposition'))->toContain('-25x25.dymo');
});
