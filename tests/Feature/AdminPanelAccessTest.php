<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_the_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_regular_authenticated_customer_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_user_with_admin_panel_permission_can_access_it(): void
    {
        $user = User::factory()->create();
        Permission::findOrCreate('view admin panel', 'web');
        $user->givePermissionTo('view admin panel');

        $this->actingAs($user)->get('/admin')->assertOk();
    }
}
