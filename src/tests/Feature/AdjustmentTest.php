<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Item;
use App\Models\Contact;
use App\Models\Warehouse;
use App\Models\Adjustment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdjustmentTest extends TestCase
{
    use HelperTrait;
    use RefreshDatabase;

    public function setup(): void
    {
        parent::setup();

        $this->warehouse = Warehouse::factory()->create(['account_id' => $this->account->id]);
        $this->items     = Item::factory()->count(10)->create(['account_id' => $this->account->id]);
        $this->contacts  = Contact::factory()->count(10)->create(['account_id' => $this->account->id]);
    }

    public function test_adjustment_validation()
    {
        $this->actingAs($this->createUser());
        $response = $this->post('/adjustments', []);
        $response->assertSessionHasErrors(['date', 'items', 'type', 'warehouse_id']);
    }

    public function test_adjustment_with_no_permissions_can_not_create_adjustment()
    {
        $this->actingAs($this->createUser('Admin'));
        $this->post('/adjustments', $this->adjustmentform())->assertSessionHas('error');
    }

    public function test_guest_can_not_create_adjustment()
    {
        $this->post('/adjustments', [])->assertRedirect(route('login'));
    }

    public function test_super_admin_can_manage_adjustments()
    {
        $this->actingAs($this->createUser('Super Admin', $this->account));

        // Can create
        $form = $this->adjustmentform();
        $this->post('/adjustments', $form)->assertSessionHas('message');

        $adjustment = Adjustment::where('reference', $form['reference'])->first();
        $this->assertEquals(Carbon::parse($form['date'])->format('Y-m-d'), Carbon::parse($adjustment->date)->format('Y-m-d'));
        $this->assertEquals($form['details'], $adjustment->details);

        // Can update
        $form = $this->adjustmentform();
        $this->put('/adjustments/' . $adjustment->id, $form)->assertSessionHas('message');

        $adjustment->refresh();
        $this->assertEquals(Carbon::parse($form['date'])->format('Y-m-d'), Carbon::parse($adjustment->date)->format('Y-m-d'));
        $this->assertEquals($form['details'], $adjustment->details);
        $this->assertEquals($form['reference'], $adjustment->reference);

        // Can delete
        $this->delete('/adjustments/' . $adjustment->id)->assertSessionHas('message');
        $this->assertSoftDeleted($adjustment);
        $this->assertTrue($adjustment->refresh()->trashed());

        // can restore
        $this->put('/adjustments/' . $adjustment->id . '/restore')->assertSessionHas('message');
        $this->assertFalse($adjustment->refresh()->trashed());

        // Can delete permanently
        $this->delete('/adjustments/' . $adjustment->id . '/permanently')->assertSessionHas('message');
        $this->assertDeleted($adjustment);
    }

    public function test_user_with_permissions_can_manage_adjustments()
    {
        $admin = $this->createUserWithPermissions('Admin', ['read-adjustments', 'create-adjustments'], $this->account);

        // can create
        $form = $this->adjustmentform();
        $this->actingAs($admin)->post('/adjustments', $form)->assertSessionHas('message');

        $adjustment = Adjustment::where('reference', $form['reference'])->first();
        $this->assertEquals(Carbon::parse($form['date'])->format('Y-m-d'), Carbon::parse($adjustment->date)->format('Y-m-d'));
        $this->assertEquals($form['details'], $adjustment->details);

        // Can't update or delete
        $form = $this->adjustmentform();
        $this->put('/adjustments/' . $adjustment->id, $form)->assertSessionHas('error');
        $this->delete('/adjustments/' . $adjustment->id)->assertSessionHas('error');
        $this->assertFalse($adjustment->refresh()->trashed());

        // New admin with update and delete permissions
        $admin2 = $this->createUserWithPermissions('Admin', ['read-adjustments', 'create-adjustments', 'update-adjustments', 'delete-adjustments'], $admin->account);

        // Can update
        $form = $this->adjustmentform();
        $this->actingAs($admin2)->put('/adjustments/' . $adjustment->id, $form)->assertSessionHas('message');

        $adjustment->refresh();
        $this->assertEquals(Carbon::parse($form['date'])->format('Y-m-d'), Carbon::parse($adjustment->date)->format('Y-m-d'));
        $this->assertEquals($form['details'], $adjustment->details);
        $this->assertEquals($form['reference'], $adjustment->reference);

        // can delete
        $this->actingAs($admin2)->delete('/adjustments/' . $adjustment->id)->assertSessionHas('message');
        $this->assertSoftDeleted($adjustment);

        $this->actingAs($admin2)->delete('/adjustments/' . $adjustment->id . '/permanently')->assertSessionHas('message');
        $this->assertDeleted($adjustment);
    }
}
