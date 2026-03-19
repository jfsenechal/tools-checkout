<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\UserResource;
use App\Ldap\UserHandler;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

final class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return $this->getAllTableRecordsCount().' utilisateurs';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Les utilisateurs se synchronisent avec la LDAP';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('ImportUser')
                ->label('Ajouter un utilisateur')
                ->icon(Heroicon::UserPlus)
                ->modal()
                ->modalHeading('Importer un utilisateur de la LDAP')
                ->schema(fn (Schema $schema) => UserForm::add($schema))
                ->action(function (array $data) {
                    try {
                        $user = UserHandler::createUserFromLdap($data);
                        Notification::make()
                            ->success()
                            ->title('Utilisateur ajouté')
                            ->send();
                        if ($user) {
                            $this->redirect(UserResource::getUrl('view', ['record' => $user]));
                        }
                    } catch (Exception $exception) {
                        Notification::make()
                            ->danger()
                            ->title($exception->getMessage())
                            ->send();
                    }
                }),
        ];
    }
}
