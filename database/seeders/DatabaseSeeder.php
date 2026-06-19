<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            SettingsSeeder::class,
        ]);

        // ── Fixed admin account for development ───────────────────────────────

        $admin = User::query()->firstOrCreate([
            'email' => 'admin@chess.test',
        ], [
            'name' => 'Admin',
            'password' => Hash::make('password'),
            'address_line1' => '1 Jalan Satu',
            'address_line2' => '',
            'city' => 'Kuala Lumpur',
            'state' => 'Wilayah Persekutuan',
            'postcode' => '50000',
            'country' => 'MY',
        ]);

        $admin->assignRole('super_admin');
    }
}
