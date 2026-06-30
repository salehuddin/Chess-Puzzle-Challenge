<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserRoleAssignmentTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);

        $this->admin = User::factory()->create(['email' => 'admin@chess.test']);
        $this->admin->assignRole('super_admin');
    }

    public function test_super_admin_can_assign_roles_when_creating_a_user(): void
    {
        $this->actingAs($this->admin);

        $editorRoleId = Role::where('name', 'editor')->value('id');

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'New Editor',
                'email' => 'editor@chess.test',
                'password' => 'supersecret',
                'roles' => [$editorRoleId],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'editor@chess.test')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('editor'));
    }

    public function test_super_admin_can_change_roles_when_editing_a_user(): void
    {
        $this->actingAs($this->admin);

        $user = User::factory()->create(['email' => 'staff@chess.test']);
        $user->assignRole('editor');

        $fulfillmentRoleId = Role::where('name', 'fulfillment')->value('id');

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm([
                'name' => $user->name,
                'email' => $user->email,
                'roles' => [$fulfillmentRoleId],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();

        $this->assertFalse($user->hasRole('editor'));
        $this->assertTrue($user->hasRole('fulfillment'));
    }

    public function test_super_admin_can_promote_a_user_to_super_admin(): void
    {
        $this->actingAs($this->admin);

        $user = User::factory()->create();
        $superAdminRoleId = Role::where('name', 'super_admin')->value('id');

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm([
                'name' => $user->name,
                'email' => $user->email,
                'roles' => [$superAdminRoleId],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($user->refresh()->hasRole('super_admin'));
    }

    public function test_editor_cannot_access_user_resource_at_all(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole('editor');

        $this->actingAs($editor)
            ->get('/admin/users')
            ->assertForbidden();

        $this->actingAs($editor)
            ->get('/admin/users/create')
            ->assertForbidden();
    }
}
