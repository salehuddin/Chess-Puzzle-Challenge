<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create
                            {email : Admin email address}
                            {--name=Admin : Admin display name}
                            {--password= : Password (will prompt if not provided)}';

    protected $description = 'Create or update a super_admin user.';

    public function handle(): int
    {
        $email = $this->argument('email');
        $name = $this->option('name');
        $password = $this->option('password') ?: $this->secret('Enter password for admin user');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
            ]
        );

        if (! $user->wasRecentlyCreated) {
            $this->info("User already existed: {$email}");
        }

        $user->assignRole('super_admin');

        $this->info("Super admin ready: {$email}");

        return self::SUCCESS;
    }
}
