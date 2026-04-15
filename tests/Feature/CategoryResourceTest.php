<?php

declare(strict_types=1);

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('can list categories', function () {
    $categories = Category::factory()->count(3)->create();

    livewire(ListCategories::class)
        ->loadTable()
        ->assertCanSeeTableRecords($categories);
});

it('can create a category', function () {
    livewire(CreateCategory::class)
        ->fillForm([
            'name' => 'Power Tools',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Category::class, [
        'name' => 'Power Tools',
    ]);
});

it('validates name is required', function () {
    livewire(CreateCategory::class)
        ->fillForm([
            'name' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

it('validates name is unique', function () {
    Category::factory()->create(['name' => 'Power Tools']);

    livewire(CreateCategory::class)
        ->fillForm([
            'name' => 'Power Tools',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique']);
});

it('can edit a category', function () {
    $category = Category::factory()->create();

    livewire(EditCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm([
            'name' => 'Updated Name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Category::class, [
        'id' => $category->id,
        'name' => 'Updated Name',
    ]);
});

it('can search categories by name', function () {
    $category = Category::factory()->create(['name' => 'Power Tools']);
    $other = Category::factory()->create(['name' => 'Hand Tools']);

    livewire(ListCategories::class)
        ->loadTable()
        ->searchTable('Power')
        ->assertCanSeeTableRecords([$category])
        ->assertCanNotSeeTableRecords([$other]);
});
