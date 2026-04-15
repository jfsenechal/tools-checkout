<?php

declare(strict_types=1);

use App\Enums\StatusToolEnum;
use App\Filament\Widgets\CheckedOutToolsWidget;
use App\Models\Category;
use App\Models\Checkout;
use App\Models\Tool;
use App\Models\User;
use App\Models\Worker;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('displays checked out tools on the dashboard', function () {
    $worker = Worker::create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'status' => 'active',
    ]);

    $category = Category::factory()->create(['name' => 'Power Tools']);

    $tool = Tool::create([
        'name' => 'Impact Drill',
        'category_id' => $category->id,
        'status' => StatusToolEnum::CheckedOut,
    ]);

    $checkout = Checkout::create([
        'tool_id' => $tool->id,
        'worker_id' => $worker->id,
        'checked_out_at' => now(),
    ]);

    livewire(CheckedOutToolsWidget::class)
        ->call('loadTable')
        ->assertCanSeeTableRecords([$checkout]);
});

it('does not show returned tools', function () {
    $worker = Worker::create([
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.com',
        'status' => 'active',
    ]);

    $category = Category::factory()->create(['name' => 'Power Tools']);

    $tool = Tool::create([
        'name' => 'Returned Saw',
        'category_id' => $category->id,
        'status' => StatusToolEnum::Available,
    ]);

    $checkout = Checkout::create([
        'tool_id' => $tool->id,
        'worker_id' => $worker->id,
        'checked_out_at' => now()->subDay(),
        'returned_at' => now(),
    ]);

    livewire(CheckedOutToolsWidget::class)
        ->call('loadTable')
        ->assertCanNotSeeTableRecords([$checkout]);
});
