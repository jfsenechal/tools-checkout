<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ToolResource\Pages;
use App\Models\Tool;
use App\Services\QRCodeService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ToolResource extends Resource
{
    protected static ?string $model = Tool::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static string|null|\UnitEnum $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Tool Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Unique identifier for this tool'),

                        Forms\Components\Select::make('category')
                            ->options([
                                'Power Tools' => 'Power Tools',
                                'Hand Tools' => 'Hand Tools',
                                'Measuring Tools' => 'Measuring Tools',
                                'Safety Equipment' => 'Safety Equipment',
                                'Ladders & Scaffolding' => 'Ladders & Scaffolding',
                                'Other' => 'Other',
                            ])
                            ->searchable()
                            ->native(false),

                        Forms\Components\Select::make('status')
                            ->options([
                                'available' => 'Available',
                                'checked_out' => 'Checked Out',
                                'maintenance' => 'In Maintenance',
                                'retired' => 'Retired',
                            ])
                            ->default('available')
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('location')
                            ->maxLength(255)
                            ->helperText('Storage location or bin number'),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Purchase Information')
                    ->schema([
                        Forms\Components\TextInput::make('manufacturer')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('model')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('purchase_price')
                            ->numeric()
                            ->prefix('$')
                            ->maxValue(99999999.99),

                        Forms\Components\DatePicker::make('purchase_date')
                            ->native(false),
                    ])->columns(2)
                    ->collapsible(),

                Section::make('Additional Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn(Tool $record): string => $record->description ?? ''),

                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'available' => 'success',
                        'checked_out' => 'warning',
                        'maintenance' => 'info',
                        'retired' => 'danger',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('qr_code')
                    ->boolean()
                    ->label('QR Code')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'available' => 'Available',
                        'checked_out' => 'Checked Out',
                        'maintenance' => 'In Maintenance',
                        'retired' => 'Retired',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'Power Tools' => 'Power Tools',
                        'Hand Tools' => 'Hand Tools',
                        'Measuring Tools' => 'Measuring Tools',
                        'Safety Equipment' => 'Safety Equipment',
                        'Ladders & Scaffolding' => 'Ladders & Scaffolding',
                        'Other' => 'Other',
                    ])
                    ->multiple(),
            ])
            ->actions([
                Action::make('generate_qr')
                    ->label('Generate QR')
                    ->icon('heroicon-o-qr-code')
                    ->color('info')
                    ->action(function (Tool $record, QRCodeService $qrService) {
                        $qrService->regenerateForTool($record);

                        Notification::make()
                            ->title('QR Code Generated')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Tool $record) => !$record->qr_code),

                Action::make('view_qr')
                    ->label('View QR')
                    ->icon('heroicon-o-eye')
                    ->url(fn(Tool $record): string => $record->qr_code_url)
                    ->openUrlInNewTab()
                    ->visible(fn(Tool $record) => $record->qr_code),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('generate_qr_codes')
                        ->label('Generate QR Codes')
                        ->icon('heroicon-o-qr-code')
                        ->action(function ($records, QRCodeService $qrService) {
                            $qrService->generateBatch($records->pluck('id')->toArray());

                            Notification::make()
                                ->title('QR Codes Generated')
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
