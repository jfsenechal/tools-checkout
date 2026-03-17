<?php

declare(strict_types=1);

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
        'status' => 'checked_out',
    ]);

    $checkout = Checkout::create([
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
        'status' => 'available',
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
