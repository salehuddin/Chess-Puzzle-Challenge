<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class StaffAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    protected function createUserWithRole(string $role): User
    {
        $user = User::factory()->create(['email' => "{$role}@chess.test"]);
        $user->assignRole($role);

        return $user;
    }

    public function test_super_admin_can_access_everything(): void
    {
        $admin = $this->createUserWithRole('super_admin');

        foreach ($this->allRoutes() as $route) {
            $this->actingAs($admin)
                ->get($route)
                ->assertOk();
        }
    }

    public function test_editor_can_access_content_and_commerce_but_not_users_settings_or_medal_inventory(): void
    {
        $editor = $this->createUserWithRole('editor');

        foreach ($this->editorAllowedRoutes() as $route) {
            $this->actingAs($editor)->get($route)->assertOk();
        }

        foreach ($this->editorDeniedRoutes() as $route) {
            $this->actingAs($editor)->get($route)->assertForbidden();
        }
    }

    public function test_fulfillment_can_access_operations_but_not_content_users_or_settings(): void
    {
        $fulfillment = $this->createUserWithRole('fulfillment');

        foreach ($this->fulfillmentAllowedRoutes() as $route) {
            $this->actingAs($fulfillment)->get($route)->assertOk();
        }

        foreach ($this->fulfillmentDeniedRoutes() as $route) {
            $this->actingAs($fulfillment)->get($route)->assertForbidden();
        }
    }

    public function test_non_staff_user_is_denied_panel_access(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser)
            ->get('/admin')
            ->assertForbidden();
    }

    /**
     * @return array<int, string>
     */
    protected function editorAllowedRoutes(): array
    {
        return [
            '/admin/challenges',
            '/admin/puzzles',
            '/admin/bundles',
            '/admin/orders',
            '/admin/enrollments',
            '/admin/fulfillments',
            '/admin/fulfillment-queue',
            '/admin/reviews',
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function editorDeniedRoutes(): array
    {
        return [
            '/admin/users',
            '/admin/settings',
            '/admin/medal-inventory',
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function fulfillmentAllowedRoutes(): array
    {
        return [
            '/admin/orders',
            '/admin/enrollments',
            '/admin/fulfillments',
            '/admin/fulfillment-queue',
            '/admin/medal-inventory',
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function fulfillmentDeniedRoutes(): array
    {
        return [
            '/admin/challenges',
            '/admin/puzzles',
            '/admin/bundles',
            '/admin/users',
            '/admin/settings',
            '/admin/reviews',
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function allRoutes(): array
    {
        return array_unique(array_merge(
            $this->editorAllowedRoutes(),
            $this->editorDeniedRoutes(),
            $this->fulfillmentAllowedRoutes(),
            $this->fulfillmentDeniedRoutes(),
        ));
    }
}
