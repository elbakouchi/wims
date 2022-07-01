<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Item;
use App\Models\Contact;
use App\Models\Transfer;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransferTest extends TestCase
{
    use HelperTrait;
    use RefreshDatabase;

    public function setup(): void
    {
        parent::setup();

        $this->warehouses = Warehouse::factory(2)->create(['account_id' => $this->account->id]);
        $this->items      = Item::factory()->count(10)->create(['account_id' => $this->account->id]);
        $this->contacts   = Contact::factory()->count(10)->create(['account_id' => $this->account->id]);
    }

    public function test_guest_can_not_create_transfer()
    {
        $this->post('/transfers', [])->assertRedirect(route('login'));
    }

    public function test_super_admin_can_manage_transfers()
    {
        $this->actingAs($this->createUser('Super Admin', $this->account));

        // Can create
        $form = $this->transferform();
        $this->post('/transfers', $form)->assertSessionHas('message');

        $transfer = Transfer::where('reference', $form['reference'])->first();
        $this->assertEquals(Carbon::parse($form['date'])->format('Y-m-d'), Carbon::parse($transfer->date)->format('Y-m-d'));
        $this->assertEquals($form['details'], $transfer->details);

        // Can update
        $form = $this->transferform();
        $this->put('/transfers/' . $transfer->id, $form)->assertSessionHas('message');

        $transfer->refresh();
        $this->assertEquals(Carbon::parse($form['date'])->format('Y-m-d'), Carbon::parse($transfer->date)->format('Y-m-d'));
        $this->assertEquals($form['details'], $transfer->details);
        $this->assertEquals($form['reference'], $transfer->reference);

        // Can delete
        $this->delete('/transfers/' . $transfer->id)->assertSessionHas('message');
        $this->assertSoftDeleted($transfer);
        $this->assertTrue($transfer->refresh()->trashed());

        // can restore
        $this->put('/transfers/' . $transfer->id . '/restore')->assertSessionHas('message');
        $this->assertFalse($transfer->refresh()->trashed());

        // Can delete permanently
        $this->delete('/transfers/' . $transfer->id . '/permanently')->assertSessionHas('message');
        $this->assertDeleted($transfer);
    }

    public function test_transfer_validation()
    {
        $this->actingAs($this->createUser());
        $response = $this->post('/transfers', []);
        $response->assertSessionHasErrors(['date', 'items', 'from_warehouse_id', 'to_warehouse_id']);
    }

    public function test_transfer_with_no_permissions_can_not_create_transfer()
    {
        $this->actingAs($this->createUser('Admin'));
        $this->post('/transfers', $this->transferform())->assertSessionHas('error');
    }

    public function test_user_with_permissions_can_manage_transfers()
    {
        $admin = $this->createUserWithPermissions('Admin', ['read-transfers', 'create-transfers'], $this->account);

        // can create
        $form = $this->transferform();
        $this->actingAs($admin)->post('/transfers', $form)->assertSessionHas('message');

        $transfer = Transfer::where('reference', $form['reference'])->first();
        $this->assertEquals(Carbon::parse($form['date'])->format('Y-m-d'), Carbon::parse($transfer->date)->format('Y-m-d'));
        $this->assertEquals($form['details'], $transfer->details);

        // Can't update or delete
        $form = $this->transferform();
        $this->put('/transfers/' . $transfer->id, $form)->assertSessionHas('error');
        $this->delete('/transfers/' . $transfer->id)->assertSessionHas('error');
        $this->assertFalse($transfer->refresh()->trashed());

        // New admin with update and delete permissions
        $admin2 = $this->createUserWithPermissions('Admin', ['read-transfers', 'create-transfers', 'update-transfers', 'delete-transfers'], $admin->account);

        // Can update
        $form = $this->transferform();
        $this->actingAs($admin2)->put('/transfers/' . $transfer->id, $form)->assertSessionHas('message');

        $transfer->refresh();
        $this->assertEquals(Carbon::parse($form['date'])->format('Y-m-d'), Carbon::parse($transfer->date)->format('Y-m-d'));
        $this->assertEquals($form['details'], $transfer->details);
        $this->assertEquals($form['reference'], $transfer->reference);

        // can delete
        $this->actingAs($admin2)->delete('/transfers/' . $transfer->id)->assertSessionHas('message');
        $this->assertSoftDeleted($transfer);

        $this->actingAs($admin2)->delete('/transfers/' . $transfer->id . '/permanently')->assertSessionHas('message');
        $this->assertDeleted($transfer);
    }
}
