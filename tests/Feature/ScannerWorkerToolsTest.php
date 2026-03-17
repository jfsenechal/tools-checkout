<?php

declare(strict_types=1);

use App\Enums\StatusToolEnum;
use App\Models\Checkout;
use App\Models\Tool;
use App\Models\Worker;

it('returns active checkouts for a worker', function () {
    $worker = Worker::create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'status' => 'active',
    ]);

    $tool = Tool::create([
        'name' => 'Test Drill',
        'category' => 'Power Tools',
        'status' => StatusToolEnum::CheckedOut,
    ]);

    Checkout::create([
        'tool_id' => $tool->id,
        'worker_id' => $worker->id,
        'checked_out_at' => now(),
    ]);

    $response = $this->getJson("/api/scanner/workers/{$worker->id}/tools");

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.worker.id', $worker->id)
        ->assertJsonCount(1, 'data.checkouts')
        ->assertJsonPath('data.checkouts.0.tool.name', 'Test Drill');
});

it('excludes returned checkouts', function () {
    $worker = Worker::create([
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.com',
        'status' => 'active',
    ]);

    $tool = Tool::create([
        'name' => 'Returned Drill',
        'category' => 'Power Tools',
        'status' => StatusToolEnum::Available,
    ]);

    Checkout::create([
        'tool_id' => $tool->id,
        'worker_id' => $worker->id,
        'checked_out_at' => now()->subDay(),
        'returned_at' => now(),
    ]);

    $response = $this->getJson("/api/scanner/workers/{$worker->id}/tools");

    $response->assertOk()
        ->assertJsonCount(0, 'data.checkouts');
});

it('scans a worker QR code and returns worker with checkouts', function () {
    $worker = Worker::create([
        'first_name' => 'Alice',
        'last_name' => 'Smith',
        'email' => 'alice@example.com',
        'status' => 'active',
    ]);

    $tool = Tool::create([
        'name' => 'Hammer',
        'category' => 'Hand Tools',
        'status' => StatusToolEnum::CheckedOut,
    ]);

    Checkout::create([
        'tool_id' => $tool->id,
        'worker_id' => $worker->id,
        'checked_out_at' => now(),
    ]);

    $qrData = json_encode(['type' => 'worker', 'id' => $worker->id]);

    $response = $this->postJson('/api/scanner/scan-worker', ['qr_data' => $qrData]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.worker.id', $worker->id)
        ->assertJsonPath('data.worker.first_name', 'Alice')
        ->assertJsonCount(1, 'data.checkouts')
        ->assertJsonPath('data.checkouts.0.tool.name', 'Hammer');
});

it('rejects non-worker QR code in scan-worker endpoint', function () {
    $qrData = json_encode(['type' => 'tool', 'id' => 1]);

    $response = $this->postJson('/api/scanner/scan-worker', ['qr_data' => $qrData]);

    $response->assertStatus(400)
        ->assertJsonPath('success', false);
});

it('returns 404 for inactive worker QR scan', function () {
    $worker = Worker::create([
        'first_name' => 'Bob',
        'last_name' => 'Inactive',
        'email' => 'bob@example.com',
        'status' => 'inactive',
    ]);

    $qrData = json_encode(['type' => 'worker', 'id' => $worker->id]);

    $response = $this->postJson('/api/scanner/scan-worker', ['qr_data' => $qrData]);

    $response->assertStatus(404)
        ->assertJsonPath('success', false);
});
