<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Item;
use App\Models\transfer;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransferStockSyncTest extends TestCase
{
    use HelperTrait;
    use RefreshDatabase;

    public function setup(): void
    {
        parent::setup();

        $this->warehouse = Warehouse::factory(3)->create(['account_id' => $this->account->id]);
        // $this->items     = Item::factory()->count(10)->create(['account_id' => $this->account->id, 'track_quantity' => 1]);
        $items = Item::factory()->count(10)->make(['account_id' => $this->account->id, 'track_quantity' => 1]);
        foreach ($items as $item) {
            (new Item())->fill($item->toArray())->saveQuietly();
        }
        $this->items = Item::all();
    }

    public function test_stock_sync_on_transfer()
    {
        // Carbon::setTestNow(now()->subMinutes(5));
        $this->actingAs($this->createUser('Super Admin', $this->account));

        // check on create
        $form = $this->transferform(true);
        $this->post('/transfers', $form)->assertSessionHas('message');

        $transfer = Transfer::where('reference', $form['reference'])->first();
        foreach ($transfer->items as $transferItem) {
            $fromStock = $transferItem->item->stock()->ofWarehouse($transfer->from_warehouse_id)->first();
            $this->assertEquals(0 - $transferItem->quantity, $fromStock->quantity);
            $toStock = $transferItem->item->stock()->ofWarehouse($transfer->to_warehouse_id)->first();
            $this->assertEquals($transferItem->quantity, $toStock->quantity);
        }

        // Check on update
        // Carbon::setTestNow(now()->addMinute());
        foreach ($form['items'] as &$item) {
            foreach ($transfer->items as $transferItem) {
                if ($transferItem->item_id == $item['item_id']) {
                    $item['quantity'] *= 2;
                    $item['id'] = $transferItem->id;
                }
            }
        }
        $this->put('/transfers/' . $transfer->id, $form)->assertSessionHas('message');

        $transfer->refresh();
        foreach ($transfer->items as $transferItem) {
            $fromStock = $transferItem->item->stock()->ofWarehouse($transfer->from_warehouse_id)->first();
            $this->assertEquals(0 - $transferItem->quantity, $fromStock->quantity);
            $toStock = $transferItem->item->stock()->ofWarehouse($transfer->to_warehouse_id)->first();
            $this->assertEquals($transferItem->quantity, $toStock->quantity);
        }

        // Check on delete
        $this->delete('/transfers/' . $transfer->id)->assertSessionHas('message');

        $transfer->refresh();
        foreach ($transfer->items as $transferItem) {
            $fromStock = $transferItem->item->stock()->ofWarehouse($transfer->from_warehouse_id)->first();
            $this->assertEquals(0, $fromStock->quantity);
            $toStock = $transferItem->item->stock()->ofWarehouse($transfer->to_warehouse_id)->first();
            $this->assertEquals(0, $toStock->quantity);
        }

        // Check on restore
        $this->put('/transfers/' . $transfer->id . '/restore')->assertSessionHas('message');

        $transfer->refresh();
        foreach ($transfer->items as $transferItem) {
            $fromStock = $transferItem->item->stock()->ofWarehouse($transfer->from_warehouse_id)->first();
            $this->assertEquals(0 - $transferItem->quantity, $fromStock->quantity);
            $toStock = $transferItem->item->stock()->ofWarehouse($transfer->to_warehouse_id)->first();
            $this->assertEquals($transferItem->quantity, $toStock->quantity);
        }

        // check on force delete
        $this->delete('/transfers/' . $transfer->id . '/permanently')->assertSessionHas('message');

        foreach ($transfer->items as $transferItem) {
            $fromStock = $transferItem->item->stock()->ofWarehouse($transfer->from_warehouse_id)->first();
            $this->assertEquals(0, $fromStock->quantity);
            $toStock = $transferItem->item->stock()->ofWarehouse($transfer->to_warehouse_id)->first();
            $this->assertEquals(0, $toStock->quantity);
        }
    }

    public function test_stock_sync_on_transfer_of_changed_warehouse()
    {
        // Carbon::setTestNow(now()->subMinutes(5));
        $this->actingAs($this->createUser('Super Admin', $this->account));

        // check on create
        $form = $this->transferform(true);
        $this->post('/transfers', $form)->assertSessionHas('message');

        $transfer = Transfer::where('reference', $form['reference'])->first();
        foreach ($transfer->items as $transferItem) {
            $fromStock = $transferItem->item->stock()->ofWarehouse($transfer->from_warehouse_id)->first();
            $this->assertEquals(0 - $transferItem->quantity, $fromStock->quantity);
            $toStock = $transferItem->item->stock()->ofWarehouse($transfer->to_warehouse_id)->first();
            $this->assertEquals($transferItem->quantity, $toStock->quantity);
        }

        // Check on update
        // Carbon::setTestNow(now()->addMinute());
        $form['from_warehouse_id'] = 3;
        foreach ($form['items'] as &$item) {
            foreach ($transfer->items as $transferItem) {
                if ($transferItem->item_id == $item['item_id']) {
                    $item['quantity'] *= 2;
                    $item['id'] = $transferItem->id;
                }
            }
        }
        $this->put('/transfers/' . $transfer->id, $form)->assertSessionHas('message');

        $transfer->refresh();
        foreach ($transfer->items as $transferItem) {
            $fromStock = $transferItem->item->stock()->ofWarehouse(1)->first();
            $this->assertEquals(0, $fromStock->quantity);
            $fromStock = $transferItem->item->stock()->ofWarehouse(3)->first();
            $this->assertEquals(0 - $transferItem->quantity, $fromStock->quantity);
            $toStock = $transferItem->item->stock()->ofWarehouse(2)->first();
            $this->assertEquals($transferItem->quantity, $toStock->quantity);
        }
    }
}
