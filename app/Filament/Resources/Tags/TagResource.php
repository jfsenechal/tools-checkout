<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tags;

use App\Filament\Resources\Tags\Schemas\TagForm;
use App\Filament\Resources\Tags\Tables\TagsTable;
use App\Models\Tag;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

final class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-bookmark';

    protected static string|null|UnitEnum $navigationGroup = 'Paramètres';

    protected static ?string $navigationLabel = 'Tags';

    protected static ?string $modelLabel = 'tag';

    protected static ?string $pluralModelLabel = 'tags';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return TagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TagsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTags::route('/'),
            'create' => Pages\CreateTag::route('/create'),
            'edit' => Pages\EditTag::route('/{record}/edit'),
        ];
    }
}
