<?php

declare(strict_types=1);

namespace App\Filament\Resources\Workers;

use App\Filament\Resources\Workers\Schemas\WorkerForm;
use App\Filament\Resources\Workers\Schemas\WorkerInfolist;
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

    protected static ?string $navigationLabel = 'Agents';

    protected static ?string $modelLabel = 'Agent';

    protected static ?string $pluralModelLabel = 'Agents';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return WorkerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WorkerInfolist::configure($schema);
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
            'view' => Pages\ViewWorker::route('/{record}'),
            'edit' => Pages\EditWorker::route('/{record}/edit'),
        ];
    }
}
