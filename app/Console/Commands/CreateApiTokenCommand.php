<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

use function Laravel\Prompts\text;

final class CreateApiTokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:token {email? : The email of the user the token belongs to} {--name=api : A label for the token}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a personal access (bearer) token for a user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email') ?? text(
            label: 'User email',
            required: true,
        );

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->error("No user found with email [{$email}].");

            return self::FAILURE;
        }

        $token = $user->createToken($this->option('name'));

        $this->info("Token created for {$user->email}:");
        $this->newLine();
        $this->line($token->plainTextToken);
        $this->newLine();
        $this->comment('Use it as a header: Authorization: Bearer <token>');

        return self::SUCCESS;
    }
}
