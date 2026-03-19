<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Ldap\UserLdap;
use App\Models\User;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SfCommand;

final class SyncUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gstock:sync-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync users with ldap';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        foreach (UserLdap::all() as $userLdap) {
            if (! $userLdap->getFirstAttribute('mail')) {
                continue;
            }
            if (! $this->isActive($userLdap)) {
                continue;
            }
            $username = $userLdap->getFirstAttribute('samaccountname');
            if ($user = User::where('username', $username)->first()) {
                $this->updateUser($user, $userLdap);
            }
        }

        $this->removeOldUsers();

        return SfCommand::SUCCESS;
    }

    private function updateUser(User $user, UserLdap $userLdap): void
    {
        $user->update(User::generateDataFromLdap($userLdap));
    }

    private function removeOldUsers(): void
    {
        $ldapUsernames = [];

        foreach (UserLdap::all() as $userLdap) {
            $ldapUsernames[] = $userLdap->getFirstAttribute('samaccountname');
        }

        if (count($ldapUsernames) > 200) {
            foreach (User::all() as $user) {
                if (! in_array($user->username, $ldapUsernames)) {
                    $user->delete();
                    $this->info('Removed from pst'.$user->first_name.' '.$user->last_name);
                }
            }
        }
    }

    private function isActive(UserLdap $userLdap): bool
    {
        return $userLdap->getFirstAttribute('userAccountControl') !== 66050;
    }
}
