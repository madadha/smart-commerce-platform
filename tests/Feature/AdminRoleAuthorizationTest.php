<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_orders_manager_can_manage_orders_but_cannot_open_catalog_or_payment_settings(): void
    {
        $user = User::factory()->create();
        $user->assignRole('orders-manager');

        $this->actingAs($user)->get('/admin/orders')->assertOk();
        $this->actingAs($user)->get('/admin/products')->assertForbidden();
        $this->actingAs($user)->get('/admin/payment-provider-settings')->assertForbidden();
    }

    public function test_catalog_manager_cannot_access_customer_orders_or_shipping(): void
    {
        $user = User::factory()->create();
        $user->assignRole('catalog-manager');

        $this->actingAs($user)->get('/admin/products')->assertOk();
        $this->actingAs($user)->get('/admin/orders')->assertForbidden();
        $this->actingAs($user)->get('/admin/customers')->assertForbidden();
        $this->actingAs($user)->get('/admin/shipments')->assertForbidden();
    }

    public function test_support_can_view_but_not_edit_orders(): void
    {
        $user = User::factory()->create();
        $user->assignRole('support');
        $order = Order::query()->forceCreate([
            'order_number' => 'ORD-AUTH-1', 'status' => 'pending', 'payment_status' => 'unpaid', 'is_active' => true,
        ]);

        $this->actingAs($user)->get('/admin/orders')->assertOk();
        $this->actingAs($user)->get("/admin/orders/{$order->id}/edit")->assertForbidden();
    }

    public function test_only_privileged_admin_can_manage_users_and_roles(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $manager = User::factory()->create();
        $manager->assignRole('orders-manager');

        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($manager)->get('/admin/users')->assertForbidden();
    }
}
