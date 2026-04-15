<?php

declare(strict_types=1);

use App\Filament\Resources\Tags\Pages\CreateTag;
use App\Filament\Resources\Tags\Pages\EditTag;
use App\Filament\Resources\Tags\Pages\ListTags;
use App\Models\Tag;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('can list tags', function () {
    $tags = Tag::factory()->count(3)->create();

    livewire(ListTags::class)
        ->loadTable()
        ->assertCanSeeTableRecords($tags);
});

it('can create a tag', function () {
    livewire(CreateTag::class)
        ->fillForm([
            'name' => 'Électricien',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Tag::class, [
        'name' => 'Électricien',
    ]);
});

it('validates name is required', function () {
    livewire(CreateTag::class)
        ->fillForm([
            'name' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

it('validates name is unique', function () {
    Tag::factory()->create(['name' => 'Électricien']);

    livewire(CreateTag::class)
        ->fillForm([
            'name' => 'Électricien',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique']);
});

it('can edit a tag', function () {
    $tag = Tag::factory()->create();

    livewire(EditTag::class, ['record' => $tag->getRouteKey()])
        ->fillForm([
            'name' => 'Updated Name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Tag::class, [
        'id' => $tag->id,
        'name' => 'Updated Name',
    ]);
});

it('can search tags by name', function () {
    $tag = Tag::factory()->create(['name' => 'Électricien']);
    $other = Tag::factory()->create(['name' => 'Plombier']);

    livewire(ListTags::class)
        ->loadTable()
        ->searchTable('Électricien')
        ->assertCanSeeTableRecords([$tag])
        ->assertCanNotSeeTableRecords([$other]);
});
