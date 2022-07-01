<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Item;
use App\Models\checkin;
use App\Models\Contact;
use App\Models\Checkout;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockSyncTest extends TestCase
{
    use HelperTrait;
    use RefreshDatabase;

    public function setup(): void
    {
        parent::setup();

        $this->warehouse = Warehouse::factory()->create(['account_id' => $this->account->id]);
        $this->contacts  = Contact::factory()->count(10)->create(['account_id' => $this->account->id]);
        // $this->items           = Item::factory()->count(10)->create(['account_id' => $this->account->id, 'track_quantity' => 1]);
        $items           = Item::factory()->count(10)->make(['account_id' => $this->account->id, 'track_quantity' => 1]);
        foreach ($items as $item) {
            (new Item())->fill($item->toArray())->saveQuietly();
        }
        $this->items = Item::all();
    }

    public function test_stock_sync_on_checkin()
    {
        // Carbon::setTestNow(now()->subMinutes(5));
        $this->actingAs($this->createUser('Super Admin', $this->account));

        // check on create
        $form = $this->checkinform(true);
        $this->post('/checkins', $form)->assertSessionHas('message');

        $checkin = checkin::where('reference', $form['reference'])->first();
        foreach ($checkin->items as $checkinItem) {
            $stock = $checkinItem->item->stock()->ofWarehouse($checkin->warehouse_id)->first();
            $this->assertEquals($checkinItem->quantity, $stock->quantity);
        }

        // Check on update
        // Carbon::setTestNow(now()->addMinute());
        foreach ($form['items'] as &$item) {
            foreach ($checkin->items as $checkinItem) {
                if ($checkinItem->item_id == $item['item_id']) {
                    $item['quantity'] *= 2;
                    $item['id'] = $checkinItem->id;
                }
            }
        }
        $this->put('/checkins/' . $checkin->id, $form)->assertSessionHas('message');

        $checkin->refresh();
        foreach ($checkin->items as $checkinItem) {
            $stock = $checkinItem->item->stock()->ofWarehouse($checkin->warehouse_id)->first();
            $this->assertEquals($checkinItem->quantity, $stock->quantity);
        }

        // Check on delete
        $this->delete('/checkins/' . $checkin->id)->assertSessionHas('message');

        $checkin->refresh();
        foreach ($checkin->items as $checkinItem) {
            $stock = $checkinItem->item->stock()->ofWarehouse($checkin->warehouse_id)->first();
            $this->assertEquals(0, $stock->quantity);
        }

        // Check on restore
        $this->put('/checkins/' . $checkin->id . '/restore')->assertSessionHas('message');

        $checkin->refresh();
        foreach ($checkin->items as $checkinItem) {
            $stock = $checkinItem->item->stock()->ofWarehouse($checkin->warehouse_id)->first();
            $this->assertEquals($checkinItem->quantity, $stock->quantity);
        }

        // check on force delete
        $this->delete('/checkins/' . $checkin->id . '/permanently')->assertSessionHas('message');

        foreach ($checkin->items as $checkinItem) {
            $stock = $checkinItem->item->stock()->ofWarehouse($checkin->warehouse_id)->first();
            $this->assertEquals(0, $stock->quantity);
        }
    }

    public function test_stock_sync_on_checkout()
    {
        $this->actingAs($this->createUser('Super Admin', $this->account));

        // check on create
        $form = $this->checkoutform(true);
        $this->post('/checkouts', $form)->assertSessionHas('message');

        $checkout = Checkout::where('reference', $form['reference'])->first();
        foreach ($checkout->items as $checkoutItem) {
            $stock = $checkoutItem->item->stock()->ofWarehouse($checkout->warehouse_id)->first();
            $this->assertEquals(0 - $checkoutItem->quantity, $stock->quantity);
        }

        // Check on update
        foreach ($form['items'] as &$item) {
            foreach ($checkout->items as $checkoutItem) {
                if ($checkoutItem->item_id == $item['item_id']) {
                    $item['quantity'] *= 2;
                    $item['id'] = $checkoutItem->id;
                }
            }
        }
        $this->put('/checkouts/' . $checkout->id, $form)->assertSessionHas('message');

        $checkout->refresh();
        foreach ($checkout->items as $checkoutItem) {
            $stock = $checkoutItem->item->stock()->ofWarehouse($checkout->warehouse_id)->first();
            $this->assertEquals(0 - $checkoutItem->quantity, $stock->quantity);
        }

        // Check on delete
        $this->delete('/checkouts/' . $checkout->id)->assertSessionHas('message');

        $checkout->refresh();
        foreach ($checkout->items as $checkoutItem) {
            $stock = $checkoutItem->item->stock()->ofWarehouse($checkout->warehouse_id)->first();
            $this->assertEquals(0, $stock->quantity);
        }

        // Check on restore
        $this->put('/checkouts/' . $checkout->id . '/restore')->assertSessionHas('message');

        $checkout->refresh();
        foreach ($checkout->items as $checkoutItem) {
            $stock = $checkoutItem->item->stock()->ofWarehouse($checkout->warehouse_id)->first();
            $this->assertEquals(0 - $checkoutItem->quantity, $stock->quantity);
        }

        // check on force delete
        $this->delete('/checkouts/' . $checkout->id . '/permanently')->assertSessionHas('message');

        foreach ($checkout->items as $checkoutItem) {
            $stock = $checkoutItem->item->stock()->ofWarehouse($checkout->warehouse_id)->first();
            $this->assertEquals(0, $stock->quantity);
        }
    }
}
