<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Filament\Auth\Pages\EditProfile;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class EditProfileTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    public function test_profile_page_renders_for_staff(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->actingAs($admin)
            ->get('/admin/profile')
            ->assertOk();
    }

    public function test_non_staff_user_cannot_access_profile_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/profile')
            ->assertForbidden();
    }

    public function test_staff_can_update_their_name(): void
    {
        $admin = User::factory()->create(['name' => 'Old Name']);
        $admin->assignRole('super_admin');

        $this->actingAs($admin);

        Livewire::test(EditProfile::class)
            ->fillForm(['name' => 'New Name'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('New Name', $admin->refresh()->name);
    }

    public function test_staff_can_update_their_email(): void
    {
        $admin = User::factory()->create(['email' => 'admin@chess.test']);
        $admin->assignRole('super_admin');

        $this->actingAs($admin);

        Livewire::test(EditProfile::class)
            ->fillForm(['email' => 'updated@chess.test', 'currentPassword' => 'password'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('updated@chess.test', $admin->refresh()->email);
    }

    public function test_password_change_requires_current_password(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->actingAs($admin);

        Livewire::test(EditProfile::class)
            ->fillForm([
                'password' => 'brandnewpassword',
                'passwordConfirmation' => 'brandnewpassword',
                'currentPassword' => 'wrong-current-password',
            ])
            ->call('save')
            ->assertHasFormErrors(['currentPassword']);

        $this->assertFalse(Hash::check('brandnewpassword', $admin->refresh()->password));
    }

    public function test_staff_can_change_their_password_with_correct_current_password(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->actingAs($admin);

        Livewire::test(EditProfile::class)
            ->fillForm([
                'password' => 'brandnewpassword',
                'passwordConfirmation' => 'brandnewpassword',
                'currentPassword' => 'password',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check('brandnewpassword', $admin->refresh()->password));
    }
}
