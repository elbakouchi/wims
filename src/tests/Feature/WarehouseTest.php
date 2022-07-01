<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WarehouseTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_not_create_warehouse()
    {
        $this->post('/warehouses', ['code' => 'w1', 'name' => 'Test Name'])->assertRedirect(route('login'));
    }

    public function test_super_admin_can_manage_warehouses()
    {
        $this->actingAs($this->createUser());

        // Can create
        $this->post('/warehouses', ['code' => 'w1', 'name' => 'Test Name'])
            ->assertSessionHas('message', __choice('action_text', ['record' => 'Warehouse', 'action' => 'created']));

        $warehouse = Warehouse::first();
        $this->assertEquals('w1', $warehouse->code);
        $this->assertEquals('Test Name', $warehouse->name);

        // Can update
        $this->put('/warehouses/' . $warehouse->id, ['code' => 'w2', 'name' => 'Update Name'])->assertSessionHas('message');

        $warehouse->refresh();
        $this->assertEquals('w2', $warehouse->code);
        $this->assertEquals('Update Name', $warehouse->name);

        // Can delete
        $this->delete('/warehouses/' . $warehouse->id)->assertSessionHas('message');
        $this->assertSoftDeleted($warehouse);
        $this->assertTrue($warehouse->refresh()->trashed());

        // can restore
        $this->put('/warehouses/' . $warehouse->id . '/restore')->assertSessionHas('message');
        $this->assertFalse($warehouse->refresh()->trashed());

        // Can delete permanently
        $this->delete('/warehouses/' . $warehouse->id . '/permanently')->assertSessionHas('message');
        $this->assertDeleted($warehouse);
    }

    public function test_user_with_no_permissions_can_not_create_warehouse()
    {
        $this->actingAs($this->createUser('Admin'));
        $this->post('/warehouses', ['code' => 'w1', 'name' => 'Test Name'])->assertSessionHas('error');
    }

    public function test_user_with_permissions_can_manage_warehouses()
    {
        $admin = $this->createUserWithPermissions('Admin', ['read-warehouses', 'create-warehouses']);

        // can create
        $this->actingAs($admin)->post('/warehouses', ['code' => 'w1', 'name' => 'Test Name'])->assertSessionHas('message');

        $warehouse = Warehouse::first();
        $this->assertEquals('w1', $warehouse->code);
        $this->assertEquals('Test Name', $warehouse->name);

        // Can't update or delete
        $this->put('/warehouses/' . $warehouse->id, ['code' => 'w2', 'name' => 'Update Name'])->assertSessionHas('error');
        $this->delete('/warehouses/' . $warehouse->id)->assertSessionHas('error');
        $this->assertFalse($warehouse->refresh()->trashed());

        // New admin with update and delete permissions
        $admin2 = $this->createUserWithPermissions('Admin', ['read-warehouses', 'create-warehouses', 'update-warehouses', 'delete-warehouses'], $admin->account);

        // Can update
        $this->actingAs($admin2)->put('/warehouses/' . $warehouse->id, ['code' => 'w2', 'name' => 'Update Name'])->assertSessionHas('message');

        $warehouse->refresh();
        $this->assertEquals('w2', $warehouse->code);
        $this->assertEquals('Update Name', $warehouse->name);

        // can delete
        $this->actingAs($admin2)->delete('/warehouses/' . $warehouse->id)->assertSessionHas('message');
        $this->assertSoftDeleted($warehouse);

        $this->actingAs($admin2)->delete('/warehouses/' . $warehouse->id . '/permanently')->assertSessionHas('message');
        $this->assertDeleted($warehouse);
    }

    public function test_warehouse_validation()
    {
        $this->actingAs($this->createUser());
        $response = $this->post('/warehouses', []);
        $response->assertSessionHasErrors(['code', 'name']);
    }
}
