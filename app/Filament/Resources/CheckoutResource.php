<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CheckoutResource\Pages;
use App\Models\Checkout;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CheckoutResource extends Resource
{
    protected static ?string $model = Checkout::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static string|null|\UnitEnum $navigationGroup = 'Transactions';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Checkout Information')
                    ->schema([
                        Forms\Components\Select::make('tool_id')
                            ->label('Tool')
                            ->relationship('tool', 'name', fn(Builder $query) => $query->where('status', 'available')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->helperText('Only available tools are shown'),

                        Forms\Components\Select::make('worker_id')
                            ->label('Worker')
                            ->relationship('worker', 'name', fn(Builder $query) => $query->where('status', 'active')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->helperText('Only active workers are shown'),

                        Forms\Components\DateTimePicker::make('checked_out_at')
                            ->label('Checked Out At')
                            ->default(now())
                            ->required()
                            ->native(false),

                        Forms\Components\DateTimePicker::make('expected_return_at')
                            ->label('Expected Return Date')
                            ->native(false)
                            ->after('checked_out_at'),

                        Forms\Components\Select::make('condition_out')
                            ->label('Condition (Out)')
                            ->options([
                                'excellent' => 'Excellent',
                                'good' => 'Good',
                                'fair' => 'Fair',
                                'poor' => 'Poor',
                            ])
                            ->default('good')
                            ->required()
                            ->native(false),

                        Forms\Components\Textarea::make('checkout_notes')
                            ->label('Checkout Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Return Information')
                    ->schema([
                        Forms\Components\DateTimePicker::make('returned_at')
                            ->label('Returned At')
                            ->native(false)
                            ->after('checked_out_at'),

                        Forms\Components\Select::make('condition_in')
                            ->label('Condition (Return)')
                            ->options([
                                'excellent' => 'Excellent',
                                'good' => 'Good',
                                'fair' => 'Fair',
                                'poor' => 'Poor',
                            ])
                            ->native(false),

                        Forms\Components\Textarea::make('return_notes')
                            ->label('Return Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2)
                    ->visible(fn($record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tool.code')
                    ->label('Tool Code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('tool.name')
                    ->label('Tool Name')
                    ->searchable()
                    ->sortable()
                    ->description(fn(Checkout $record): string => $record->tool->category ?? ''
                    ),

                Tables\Columns\TextColumn::make('worker.name')
                    ->label('Worker')
                    ->searchable()
                    ->sortable()
                    ->description(fn(Checkout $record): string => $record->worker->badge_number
                    ),

                Tables\Columns\TextColumn::make('checked_out_at')
                    ->label('Checked Out')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('expected_return_at')
                    ->label('Expected Return')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('returned_at')
                    ->label('Returned')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not returned')
                    ->badge()
                    ->color(fn($state): string => $state ? 'success' : 'warning'),

                Tables\Columns\IconColumn::make('is_overdue')
                    ->label('Overdue')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('danger')
                    ->falseIcon('heroicon-o-check-circle')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('condition_out')
                    ->label('Condition Out')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('condition_in')
                    ->label('Condition In')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->label('Active Checkouts')
                    ->query(fn(Builder $query): Builder => $query->whereNull('returned_at'))
                    ->default(),

                Tables\Filters\Filter::make('overdue')
                    ->label('Overdue')
                    ->query(fn(Builder $query): Builder => $query->whereNull('returned_at')
                        ->where('expected_return_at', '<', now())
                    ),

                Tables\Filters\SelectFilter::make('tool')
                    ->relationship('tool', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('worker')
                    ->relationship('worker', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                EditAction::make(),

                Action::make('return')
                    ->label('Return Tool')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->form([
                        Forms\Components\DateTimePicker::make('returned_at')
                            ->label('Return Date')
                            ->default(now())
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('condition_in')
                            ->label('Condition')
                            ->options([
                                'excellent' => 'Excellent',
                                'good' => 'Good',
                                'fair' => 'Fair',
                                'poor' => 'Poor',
                            ])
                            ->default('good')
                            ->required()
                            ->native(false),

                        Forms\Components\Textarea::make('return_notes')
                            ->label('Notes')
                            ->rows(3),
                    ])
                    ->action(function (Checkout $record, array $data): void {
                        $record->update([
                            'returned_at' => $data['returned_at'],
                            'condition_in' => $data['condition_in'],
                            'return_notes' => $data['return_notes'] ?? null,
                            'returned_by' => auth()->id(),
                        ]);

                        // Update tool status
                        $tool = $record->tool;
                        if (in_array($data['condition_in'], ['poor', 'fair'])) {
                            $tool->markAsInMaintenance();
                        } else {
                            $tool->markAsAvailable();
                        }
                    })
                    ->visible(fn(Checkout $record): bool => !$record->is_returned)
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('checked_out_at', 'desc');
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
