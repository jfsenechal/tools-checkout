<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tools;

use App\Filament\Resources\Tools\Schemas\ToolForm;
use App\Filament\Resources\Tools\Tables\ToolsTable;
use App\Models\Tool;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

final class ToolResource extends Resource
{
    protected static ?string $model = Tool::class;

    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static string|null|UnitEnum $navigationGroup = 'Inventaire';

    protected static ?string $navigationLabel = 'Outils';

    protected static ?string $modelLabel = 'outil';

    protected static ?string $pluralModelLabel = 'outils';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ToolForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ToolsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTools::route('/'),
            'create' => Pages\CreateTool::route('/create'),
            'edit' => Pages\EditTool::route('/{record}/edit'),
        ];
    }
}
