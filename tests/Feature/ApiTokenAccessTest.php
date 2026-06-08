<?php

declare(strict_types=1);

use App\Enums\StatusToolEnum;
use App\Models\Category;
use App\Models\Checkout;
use App\Models\Tool;
use App\Models\User;
use App\Models\Worker;

function apiToken(): string
{
    return User::factory()->create()->createToken('test')->plainTextToken;
}

it('rejects unauthenticated requests to the protected api', function (string $uri) {
    $this->getJson($uri)->assertUnauthorized();
})->with([
    '/api/workers',
    '/api/tools',
    '/api/checkouts',
]);

it('lists workers for an authenticated token', function () {
    Worker::factory()->count(3)->create();

    $this->withToken(apiToken())
        ->getJson('/api/workers')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'first_name', 'last_name', 'status', 'is_active']],
            'meta' => ['current_page', 'total'],
        ]);
});

it('lists tools for an authenticated token', function () {
    $category = Category::factory()->create();
    Tool::create([
        'name' => 'Cordless Drill',
        'category_id' => $category->id,
        'status' => StatusToolEnum::Available,
    ]);

    $this->withToken(apiToken())
        ->getJson('/api/tools')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Cordless Drill')
        ->assertJsonPath('data.0.is_available', true);
});

it('lists all checkouts for an authenticated token', function () {
    $worker = Worker::factory()->create();
    $category = Category::factory()->create();
    $tool = Tool::create([
        'name' => 'Hammer',
        'category_id' => $category->id,
        'status' => StatusToolEnum::CheckedOut,
    ]);
    Checkout::create([
        'tool_id' => $tool->id,
        'worker_id' => $worker->id,
        'checked_out_at' => now(),
    ]);

    $this->withToken(apiToken())
        ->getJson('/api/checkouts')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.tool.name', 'Hammer')
        ->assertJsonPath('data.0.worker.id', $worker->id)
        ->assertJsonPath('data.0.is_active', true);
});

it('runs the scanner workflow through the token api', function () {
    $worker = Worker::factory()->create();
    $category = Category::factory()->create();
    $tool = Tool::create([
        'name' => 'Wrench',
        'category_id' => $category->id,
        'status' => StatusToolEnum::Available,
    ]);

    $token = apiToken();

    // Step 1: scan the worker
    $this->withToken($token)
        ->postJson('/api/scan-worker', [
            'qr_data' => json_encode(['type' => 'worker', 'id' => $worker->id]),
        ])
        ->assertOk()
        ->assertJsonPath('data.worker.id', $worker->id);

    // Step 2: check out a tool to that worker
    $this->withToken($token)
        ->postJson('/api/checkout', [
            'tool_id' => $tool->id,
            'worker_id' => $worker->id,
        ])
        ->assertOk()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('checkouts', [
        'tool_id' => $tool->id,
        'worker_id' => $worker->id,
        'returned_at' => null,
    ]);
});

it('rejects the scanner workflow without a token', function () {
    $this->postJson('/api/checkout', ['tool_id' => 1, 'worker_id' => 1])
        ->assertUnauthorized();
});

it('generates a token for a user via the artisan command', function () {
    $user = User::factory()->create(['email' => 'tech@example.com']);

    $this->artisan('api:token', ['email' => 'tech@example.com'])
        ->assertSuccessful();

    expect($user->tokens()->count())->toBe(1);
});

it('fails the token command for an unknown user', function () {
    $this->artisan('api:token', ['email' => 'nobody@example.com'])
        ->assertFailed();
});
