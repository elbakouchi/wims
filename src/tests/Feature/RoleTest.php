<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_not_create_role()
    {
        $this->post('/roles', ['name' => 'Role'])->assertRedirect(route('login'));
    }

    public function test_role_validation()
    {
        $this->actingAs($this->createUser());
        $response = $this->post('/roles', []);
        $response->assertSessionHasErrors(['name']);
    }

    public function test_super_admin_can_manage_roles()
    {
        $this->actingAs($this->createUser());

        // Can create
        $this->post('/roles', ['name' => 'Role'])
            ->assertSessionHas('message', __choice('action_text', ['record' => 'Role', 'action' => 'created']));

        $role = Role::where('name', 'Role')->first();
        $this->assertEquals('Role', $role->name);

        // Can update
        $this->put('/roles/' . $role->id, ['name' => 'Role 1'])->assertSessionHas('message');

        $role->refresh();
        $this->assertEquals('Role 1', $role->name);

        // Can delete
        $this->delete('/roles/' . $role->id)->assertSessionHas('message');
        $this->assertSoftDeleted($role);
        $this->assertTrue($role->refresh()->trashed());

        // can restore
        $this->put('/roles/' . $role->id . '/restore')->assertSessionHas('message');
        $this->assertFalse($role->refresh()->trashed());

        // Can delete permanently
        $this->delete('/roles/' . $role->id . '/permanently')->assertSessionHas('message');
        $this->assertDeleted($role);

        // Can't delete own role
        $this->delete('/roles/1')->assertSessionHas('error');
    }

    public function test_user_with_no_permissions_can_not_create_role()
    {
        $this->actingAs($this->createUser('Admin'));
        $this->post('/roles', ['name' => 'Role'])->assertSessionHas('error');
    }

    public function test_user_with_permissions_can_manage_roles()
    {
        $admin = $this->createUserWithPermissions('Admin', ['read-roles', 'create-roles']);

        // can create
        $this->actingAs($admin)->post('/roles', ['name' => 'Role'])->assertSessionHas('message');

        $role = Role::where('name', 'Role')->first();
        $this->assertEquals('Role', $role->name);

        // Can't update or delete
        $this->put('/roles/' . $role->id, ['name' => 'Role 1'])->assertSessionHas('error');
        $this->delete('/roles/' . $role->id)->assertSessionHas('error');
        $this->assertFalse($role->refresh()->trashed());

        // New admin with update and delete permissions
        $admin2 = $this->createUserWithPermissions('Admin', ['read-roles', 'create-roles', 'update-roles', 'delete-roles'], $admin->account);

        // Can update
        $this->actingAs($admin2)->put('/roles/' . $role->id, ['name' => 'Role 2'])->assertSessionHas('message');

        $role->refresh();
        $this->assertEquals('Role 2', $role->name);

        // can delete
        $this->actingAs($admin2)->delete('/roles/' . $role->id)->assertSessionHas('message');
        $this->assertSoftDeleted($role);

        $this->actingAs($admin2)->delete('/roles/' . $role->id . '/permanently')->assertSessionHas('message');
        $this->assertDeleted($role);

        // Can't delete own and super role
        $this->delete('/roles/' . $role->id)->assertSessionHas('error');

        $this->createUser();
        $super = Role::where('name', 'Super Admin')->first();
        $this->delete('/roles/' . $super->id)->assertSessionHas('error');
        $this->assertDatabaseCount('roles', 2);
    }
}
