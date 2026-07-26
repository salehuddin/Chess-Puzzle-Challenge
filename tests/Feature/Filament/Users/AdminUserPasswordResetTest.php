<?php

namespace Tests\Feature\Filament\Users;

use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUserPasswordResetTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected User $admin;

    protected User $target;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);

        $this->admin = User::factory()->create(['email' => 'admin@chess.test']);
        $this->admin->assignRole('super_admin');

        $this->target = User::factory()->create([
            'name' => 'Jane Player',
            'email' => 'jane@chess.test',
        ]);
    }

    public function test_send_password_reset_link_action_dispatches_reset_email_and_logs_activity(): void
    {
        Notification::fake();

        Livewire::actingAs($this->admin)
            ->test(ListUsers::class)
            ->callTableAction('sendPasswordResetLink', $this->target);

        Notification::assertSentTo($this->target, ResetPassword::class);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => User::class,
            'subject_id' => $this->target->id,
            'causer_type' => User::class,
            'causer_id' => $this->admin->id,
            'description' => 'Sent password reset link to user',
        ]);
    }

    public function test_set_user_password_action_updates_password_and_dispatches_reset_email(): void
    {
        Notification::fake();

        $originalHash = $this->target->fresh()->password;

        Livewire::actingAs($this->admin)
            ->test(ListUsers::class)
            ->callTableAction('setUserPassword', $this->target, data: [
                'password' => 'n3w-s3cret!',
                'password_confirmation' => 'n3w-s3cret!',
            ]);

        $this->assertNotEquals($originalHash, $this->target->fresh()->password);
        $this->assertTrue(Hash::check('n3w-s3cret!', $this->target->fresh()->password));

        Notification::assertSentTo($this->target, ResetPassword::class);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => User::class,
            'subject_id' => $this->target->id,
            'causer_type' => User::class,
            'causer_id' => $this->admin->id,
            'description' => 'Reset user password',
        ]);
    }

    public function test_set_user_password_action_validates_password_confirmation(): void
    {
        Notification::fake();

        Livewire::actingAs($this->admin)
            ->test(ListUsers::class)
            ->callTableAction('setUserPassword', $this->target, data: [
                'password' => 'n3w-s3cret!',
                'password_confirmation' => 'different-value',
            ])
            ->assertHasTableActionErrors(['password' => 'confirmed']);

        Notification::assertNothingSent();
    }

    public function test_send_password_reset_link_action_is_hidden_for_own_account(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ListUsers::class)
            ->assertTableActionHidden('sendPasswordResetLink', $this->admin);

        Livewire::actingAs($this->admin)
            ->test(ListUsers::class)
            ->assertTableActionHidden('setUserPassword', $this->admin);
    }

    public function test_non_admin_cannot_access_users_list(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole('editor');

        $this->actingAs($editor)
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_password_actions_render_on_users_list(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ListUsers::class)
            ->assertTableActionVisible('sendPasswordResetLink', $this->target)
            ->assertTableActionVisible('setUserPassword', $this->target);
    }

    public function test_password_actions_render_on_edit_user_header(): void
    {
        Livewire::actingAs($this->admin)
            ->test(EditUser::class, ['record' => $this->target->getRouteKey()])
            ->assertActionVisible(['Password', 'sendPasswordResetLink'])
            ->assertActionVisible(['Password', 'setUserPassword']);
    }

    public function test_password_actions_render_on_view_user_header(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ViewUser::class, ['record' => $this->target->getRouteKey()])
            ->assertActionVisible(['Password', 'sendPasswordResetLink'])
            ->assertActionVisible(['Password', 'setUserPassword']);
    }
}
