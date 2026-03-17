<?php

declare(strict_types=1);

namespace App\Filament\Resources\Workers;

use App\Filament\Resources\Workers\Schemas\WorkerForm;
use App\Filament\Resources\Workers\Tables\WorkersTable;
use App\Models\Worker;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

final class WorkerResource extends Resource
{
    protected static ?string $model = Worker::class;

    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-user-group';

    protected static string|null|UnitEnum $navigationGroup = 'Paramètres';

    protected static ?string $navigationLabel = 'Travailleurs';

    protected static ?string $modelLabel = 'travailleur';

    protected static ?string $pluralModelLabel = 'travailleurs';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return WorkerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkers::route('/'),
            'create' => Pages\CreateWorker::route('/create'),
            'edit' => Pages\EditWorker::route('/{record}/edit'),
        ];
    }
}
