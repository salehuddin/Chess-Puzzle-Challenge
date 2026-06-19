<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminSettingsPageTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    public function test_admin_settings_page_renders_for_super_admin(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@chess.test',
        ]);
        $user->assignRole('super_admin');

        $this->actingAs($user)
            ->get('/admin/settings')
            ->assertOk();
    }
}
