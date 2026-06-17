<?php

declare(strict_types=1);

use App\Enums\StatusToolEnum;
use App\Models\Category;
use App\Models\Tool;

function createToolWithQr(): Tool
{
    return Tool::create([
        'name' => 'Impact Drill',
        'category_id' => Category::factory()->create(['name' => 'Power Tools'])->id,
        'status' => StatusToolEnum::Available,
        'qr_code' => 'tool-1-123.svg',
    ]);
}

it('renders the 25x25 label for a tool with a qr code', function () {
    $tool = createToolWithQr();

    $this->get(route('print.qr-label', ['tool' => $tool, 'size' => '25x25']))
        ->assertOk()
        ->assertSee('size: 25mm 25mm', false)
        ->assertSee($tool->qr_code_url, false);
});

it('renders the 32x57 label with the tool name and category', function () {
    $tool = createToolWithQr();

    $this->get(route('print.qr-label', ['tool' => $tool, 'size' => '32x57']))
        ->assertOk()
        ->assertSee('size: 57mm 32mm', false)
        ->assertSee('Impact Drill')
        ->assertSee('Power Tools');
});

it('defaults to the 25x25 label for an unknown size', function () {
    $tool = createToolWithQr();

    $this->get(route('print.qr-label', ['tool' => $tool, 'size' => 'bogus']))
        ->assertOk()
        ->assertSee('size: 25mm 25mm', false);
});

it('returns 404 when the tool has no qr code', function () {
    $tool = Tool::create([
        'name' => 'No QR Tool',
        'category_id' => Category::factory()->create()->id,
        'status' => StatusToolEnum::Available,
    ]);

    $this->get(route('print.qr-label', ['tool' => $tool, 'size' => '25x25']))
        ->assertNotFound();
});
