<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CheckoutResource\Pages;
use App\Filament\Resources\CheckoutResource\Schemas\CheckoutForm;
use App\Filament\Resources\CheckoutResource\Tables\CheckoutsTable;
use App\Models\Checkout;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

final class CheckoutResource extends Resource
{
    protected static ?string $model = Checkout::class;

    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static string|null|UnitEnum $navigationGroup = 'Transactions';

    protected static ?string $navigationLabel = 'Emprunts';

    protected static ?string $modelLabel = 'emprunt';

    protected static ?string $pluralModelLabel = 'emprunts';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return CheckoutForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CheckoutsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCheckouts::route('/'),
            'create' => Pages\CreateCheckout::route('/create'),
            'edit' => Pages\EditCheckout::route('/{record}/edit'),
        ];
    }
}
