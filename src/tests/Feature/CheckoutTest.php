<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Item;
use App\Models\Contact;
use App\Models\Checkout;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CheckoutTest extends TestCase
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

    public function test_checkout_validation()
    {
        $this->actingAs($this->createUser());
        $response = $this->post('/checkouts', []);
        $response->assertSessionHasErrors(['date', 'items', 'contact_id', 'warehouse_id']);
    }

    public function test_checkout_with_no_permissions_can_not_create_checkout()
    {
        $this->actingAs($this->createUser('Admin'));
        $this->post('/checkouts', $this->checkoutform())->assertSessionHas('error');
    }

    public function test_guest_can_not_create_checkout()
    {
        $this->post('/checkouts', [])->assertRedirect(route('login'));
    }

    public function test_super_admin_can_manage_checkouts()
    {
        $this->actingAs($this->createUser('Super Admin', $this->account));

        // Can create
        $form = $this->checkoutform();
        $this->post('/checkouts', $form)->assertSessionHas('message');

        $checkout = Checkout::where('reference', $form['reference'])->first();
        $this->assertEquals(Carbon::parse($form['date'])->format('Y-m-d'), Carbon::parse($checkout->date)->format('Y-m-d'));
        $this->assertEquals($form['details'], $checkout->details);

        // Can update
        $form = $this->checkoutform();
        $this->put('/checkouts/' . $checkout->id, $form)->assertSessionHas('message');

        $checkout->refresh();
        $this->assertEquals(Carbon::parse($form['date'])->format('Y-m-d'), Carbon::parse($checkout->date)->format('Y-m-d'));
        $this->assertEquals($form['details'], $checkout->details);
        $this->assertEquals($form['reference'], $checkout->reference);

        // Can delete
        $this->delete('/checkouts/' . $checkout->id)->assertSessionHas('message');
        $this->assertSoftDeleted($checkout);
        $this->assertTrue($checkout->refresh()->trashed());

        // can restore
        $this->put('/checkouts/' . $checkout->id . '/restore')->assertSessionHas('message');
        $this->assertFalse($checkout->refresh()->trashed());

        // Can delete permanently
        $this->delete('/checkouts/' . $checkout->id . '/permanently')->assertSessionHas('message');
        $this->assertDeleted($checkout);
    }

    public function test_user_with_permissions_can_manage_checkouts()
    {
        $admin = $this->createUserWithPermissions('Admin', ['read-checkouts', 'create-checkouts'], $this->account);

        // can create
        $form = $this->checkoutform();
        $this->actingAs($admin)->post('/checkouts', $form)->assertSessionHas('message');

        $checkout = Checkout::where('reference', $form['reference'])->first();
        $this->assertEquals(Carbon::parse($form['date'])->format('Y-m-d'), Carbon::parse($checkout->date)->format('Y-m-d'));
        $this->assertEquals($form['details'], $checkout->details);

        // Can't update or delete
        $form = $this->checkoutform();
        $this->put('/checkouts/' . $checkout->id, $form)->assertSessionHas('error');
        $this->delete('/checkouts/' . $checkout->id)->assertSessionHas('error');
        $this->assertFalse($checkout->refresh()->trashed());

        // New admin with update and delete permissions
        $admin2 = $this->createUserWithPermissions('Admin', ['read-checkouts', 'create-checkouts', 'update-checkouts', 'delete-checkouts'], $admin->account);

        // Can update
        $form = $this->checkoutform();
        $this->actingAs($admin2)->put('/checkouts/' . $checkout->id, $form)->assertSessionHas('message');

        $checkout->refresh();
        $this->assertEquals(Carbon::parse($form['date'])->format('Y-m-d'), Carbon::parse($checkout->date)->format('Y-m-d'));
        $this->assertEquals($form['details'], $checkout->details);
        $this->assertEquals($form['reference'], $checkout->reference);

        // can delete
        $this->actingAs($admin2)->delete('/checkouts/' . $checkout->id)->assertSessionHas('message');
        $this->assertSoftDeleted($checkout);

        $this->actingAs($admin2)->delete('/checkouts/' . $checkout->id . '/permanently')->assertSessionHas('message');
        $this->assertDeleted($checkout);
    }
}
