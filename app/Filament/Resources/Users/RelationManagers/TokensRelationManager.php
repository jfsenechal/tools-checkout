<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Models\User;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Laravel\Sanctum\PersonalAccessToken;

final class TokensRelationManager extends RelationManager
{
    public ?string $generatedToken = null;

    protected static string $relationship = 'tokens';

    protected static ?string $title = 'Jetons API';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedKey;

    protected static bool $isLazy = false;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nom')
                ->required()
                ->maxLength(255),
            TagsInput::make('abilities')
                ->label('Permissions')
                ->helperText('Laisser "*" pour un accès complet.')
                ->default(['*']),
            DateTimePicker::make('expires_at')
                ->label('Expire le')
                ->helperText('Laisser vide pour un jeton sans expiration.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),
                TextColumn::make('abilities')
                    ->label('Permissions')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => is_array($state) ? implode(', ', $state) : (string) $state),
                TextColumn::make('last_used_at')
                    ->label('Dernière utilisation')
                    ->dateTime()
                    ->placeholder('Jamais')
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label('Expiration')
                    ->dateTime()
                    ->placeholder('Aucune')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('createToken')
                    ->label('Nouveau jeton')
                    ->icon(Heroicon::Plus)
                    ->schema($this->form(...))
                    ->action(function (array $data): void {
                        /** @var User $user */
                        $user = $this->getOwnerRecord();

                        $abilities = empty($data['abilities']) ? ['*'] : $data['abilities'];

                        $token = $user->createToken(
                            $data['name'],
                            $abilities,
                            isset($data['expires_at']) ? Carbon::parse($data['expires_at']) : null,
                        );

                        $this->generatedToken = $token->plainTextToken;

                        $this->replaceMountedAction('revealToken');
                    }),
            ])
            ->recordActions([
                Action::make('revoke')
                    ->label('Révoquer')
                    ->icon(Heroicon::Trash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (PersonalAccessToken $record) => $record->delete()),
            ]);
    }

    public function revealTokenAction(): Action
    {
        return Action::make('revealToken')
            ->modalHeading('Jeton créé')
            ->modalDescription('Copiez ce jeton maintenant : pour des raisons de sécurité, il ne sera plus affiché.')
            ->modalIcon(Heroicon::Key)
            ->schema([
                TextInput::make('token')
                    ->label('Jeton')
                    ->readOnly()
                    ->default(fn (): ?string => $this->generatedToken)
                    ->copyable(copyMessage: 'Jeton copié', copyMessageDuration: 1500),
            ])
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fermer');
    }
}
