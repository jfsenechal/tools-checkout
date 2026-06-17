<?php

declare(strict_types=1);

use App\Enums\StatusToolEnum;
use App\Filament\Resources\Tools\Pages\ListTools;
use App\Models\Category;
use App\Models\Tool;
use App\Models\User;
use Filament\Actions\Testing\TestAction;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('downloads the DYMO label as a file from the table action', function () {
    $tool = Tool::create([
        'name' => 'Impact Drill',
        'category_id' => Category::factory()->create()->id,
        'status' => StatusToolEnum::Available,
    ]);

    livewire(ListTools::class)
        ->callAction(TestAction::make('qr_dymo_25x25')->table($tool))
        ->assertFileDownloaded("impact-drill-{$tool->id}-25x25.dymo");
});
