<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_not_create_unit()
    {
        $this->post('/units', ['name' => 'Unit'])->assertRedirect(route('login'));
    }

    public function test_super_admin_can_manage_units()
    {
        $this->actingAs($this->createUser());

        // Can create
        $this->post('/units', ['code' => 'u', 'name' => 'Unit'])
            ->assertSessionHas('message', __choice('action_text', ['record' => 'Unit', 'action' => 'created']));

        $unit = Unit::first();
        $this->assertEquals('u', $unit->code);
        $this->assertEquals('Unit', $unit->name);

        // Can update
        $this->put('/units/' . $unit->id, ['code' => 'u1', 'name' => 'Unit 1'])->assertSessionHas('message');

        $unit->refresh();
        $this->assertEquals('u1', $unit->code);
        $this->assertEquals('Unit 1', $unit->name);

        // Add child unit
        $this->post('/units', ['code' => 'su', 'name' => 'Sub Unit', 'base_unit_id' => $unit->id])
            ->assertSessionHas('message', __choice('action_text', ['record' => 'Unit', 'action' => 'created']));

        $this->assertDatabaseCount('units', 2);

        // Can't delete parent when has child
        $this->delete('/units/' . $unit->id)->assertSessionHas('error');
        $this->delete('/units/' . $unit->id . '/permanently')->assertSessionHas('error');

        $child_unit = Unit::where('code', 'su')->first();

        // Can delete
        $this->delete('/units/' . $child_unit->id)->assertSessionHas('message');
        $this->delete('/units/' . $unit->id)->assertSessionHas('message');
        $this->assertSoftDeleted($unit);
        $this->assertTrue($unit->refresh()->trashed());

        // can restore
        $this->put('/units/' . $unit->id . '/restore')->assertSessionHas('message');
        $this->assertFalse($unit->refresh()->trashed());

        // Can delete permanently
        $this->delete('/units/' . $unit->id . '/permanently')->assertSessionHas('message');
        $this->assertDeleted($unit);

        $this->assertDatabaseCount('units', 0);
    }

    public function test_unit_validation()
    {
        $this->actingAs($this->createUser());
        $response = $this->post('/units', []);
        $response->assertSessionHasErrors(['name', 'code']);
    }

    public function test_user_with_no_permissions_can_not_create_unit()
    {
        $this->actingAs($this->createUser('Admin'));
        $this->post('/units', ['code' => 'u', 'name' => 'Unit'])->assertSessionHas('error');
    }

    public function test_user_with_permissions_can_manage_units()
    {
        $admin = $this->createUserWithPermissions('Admin', ['read-units', 'create-units']);

        // can create
        $this->actingAs($admin)->post('/units', ['code' => 'u', 'name' => 'Unit'])->assertSessionHas('message');

        $unit = Unit::first();
        $this->assertEquals('u', $unit->code);
        $this->assertEquals('Unit', $unit->name);

        // Can't update or delete
        $this->put('/units/' . $unit->id, ['code' => 'u1', 'name' => 'Unit 1'])->assertSessionHas('error');
        $this->delete('/units/' . $unit->id)->assertSessionHas('error');
        $this->assertFalse($unit->refresh()->trashed());

        // New admin with update and delete permissions
        $admin2 = $this->createUserWithPermissions('Admin', ['read-units', 'create-units', 'update-units', 'delete-units'], $admin->account);

        // Can update
        $this->actingAs($admin2)
            ->put('/units/' . $unit->id, ['code' => 'u2', 'name' => 'Unit 2'])->assertSessionHas('message');

        $unit->refresh();
        $this->assertEquals('u2', $unit->code);
        $this->assertEquals('Unit 2', $unit->name);

        // can delete
        $this->actingAs($admin2)->delete('/units/' . $unit->id)->assertSessionHas('message');
        $this->assertSoftDeleted($unit);

        $this->actingAs($admin2)->delete('/units/' . $unit->id . '/permanently')->assertSessionHas('message');
        $this->assertDeleted($unit);
    }
}
