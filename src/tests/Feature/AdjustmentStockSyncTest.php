<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Item;
use App\Models\Warehouse;
use App\Models\Adjustment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdjustmentStockSyncTest extends TestCase
{
    use HelperTrait;
    use RefreshDatabase;

    public function setup(): void
    {
        parent::setup();

        $this->warehouse = Warehouse::factory()->create(['account_id' => $this->account->id]);
        // $this->items     = Item::factory()->count(10)->create(['account_id' => $this->account->id, 'track_quantity' => 1]);
        $items = Item::factory()->count(10)->make(['account_id' => $this->account->id, 'track_quantity' => 1]);
        foreach ($items as $item) {
            (new Item())->fill($item->toArray())->saveQuietly();
        }
        $this->items = Item::all();
    }

    public function test_stock_sync_on_addition_to_subtraction()
    {
        // Carbon::setTestNow(now()->subMinutes(5));
        $this->actingAs($this->createUser('Super Admin', $this->account));

        // check on create
        $form         = $this->adjustmentform(true);
        $form['type'] = 'Addition';
        $this->post('/adjustments', $form)->assertSessionHas('message');

        $adjustment = Adjustment::where('reference', $form['reference'])->first();
        foreach ($adjustment->items as $adjustmentItem) {
            $stock = $adjustmentItem->item->stock()->ofWarehouse($adjustment->warehouse_id)->first();
            $this->assertEquals($adjustmentItem->quantity, $stock->quantity);
        }

        // Check on update
        // Carbon::setTestNow(now()->addMinute());
        $form['type'] = 'Subtraction';
        foreach ($form['items'] as &$item) {
            foreach ($adjustment->items as $adjustmentItem) {
                if ($adjustmentItem->item_id == $item['item_id']) {
                    $item['quantity'] *= 2;
                    $item['id'] = $adjustmentItem->id;
                }
            }
        }
        $this->put('/adjustments/' . $adjustment->id, $form)->assertSessionHas('message');

        $adjustment->refresh();
        foreach ($adjustment->items as $adjustmentItem) {
            $stock = $adjustmentItem->item->stock()->ofWarehouse($adjustment->warehouse_id)->first();
            $this->assertEquals(0 - $adjustmentItem->quantity, $stock->quantity);
        }
    }

    public function test_stock_sync_on_adjustment()
    {
        // Carbon::setTestNow(now()->subMinutes(5));
        $this->actingAs($this->createUser('Super Admin', $this->account));

        // check on create
        $form = $this->adjustmentform(true);
        $this->post('/adjustments', $form)->assertSessionHas('message');

        $adjustment = Adjustment::where('reference', $form['reference'])->first();
        foreach ($adjustment->items as $adjustmentItem) {
            $stock = $adjustmentItem->item->stock()->ofWarehouse($adjustment->warehouse_id)->first();
            if ($adjustment->type == 'Addition') {
                $this->assertEquals($adjustmentItem->quantity, $stock->quantity);
            } else {
                $this->assertEquals(0 - $adjustmentItem->quantity, $stock->quantity);
            }
        }

        // Check on update
        // Carbon::setTestNow(now()->addMinute());
        $form['type'] = ['Damage', 'Addition', 'Subtraction'][mt_rand(0, 2)];
        foreach ($form['items'] as &$item) {
            foreach ($adjustment->items as $adjustmentItem) {
                if ($adjustmentItem->item_id == $item['item_id']) {
                    $item['quantity'] *= 2;
                    $item['id'] = $adjustmentItem->id;
                }
            }
        }
        $this->put('/adjustments/' . $adjustment->id, $form)->assertSessionHas('message');

        $adjustment->refresh();
        foreach ($adjustment->items as $adjustmentItem) {
            $stock = $adjustmentItem->item->stock()->ofWarehouse($adjustment->warehouse_id)->first();
            if ($adjustment->type == 'Addition') {
                $this->assertEquals($adjustmentItem->quantity, $stock->quantity);
            } else {
                $this->assertEquals(0 - $adjustmentItem->quantity, $stock->quantity);
            }
        }

        // Check on delete
        $this->delete('/adjustments/' . $adjustment->id)->assertSessionHas('message');

        $adjustment->refresh();
        foreach ($adjustment->items as $adjustmentItem) {
            $stock = $adjustmentItem->item->stock()->ofWarehouse($adjustment->warehouse_id)->first();
            $this->assertEquals(0, $stock->quantity);
        }

        // Check on restore
        $this->put('/adjustments/' . $adjustment->id . '/restore')->assertSessionHas('message');

        $adjustment->refresh();
        foreach ($adjustment->items as $adjustmentItem) {
            $stock = $adjustmentItem->item->stock()->ofWarehouse($adjustment->warehouse_id)->first();
            if ($adjustment->type == 'Addition') {
                $this->assertEquals($adjustmentItem->quantity, $stock->quantity);
            } else {
                $this->assertEquals(0 - $adjustmentItem->quantity, $stock->quantity);
            }
        }

        // check on force delete
        $this->delete('/adjustments/' . $adjustment->id . '/permanently')->assertSessionHas('message');

        foreach ($adjustment->items as $adjustmentItem) {
            $stock = $adjustmentItem->item->stock()->ofWarehouse($adjustment->warehouse_id)->first();
            $this->assertEquals(0, $stock->quantity);
        }
    }

    public function test_stock_sync_on_damage_to_addition()
    {
        // Carbon::setTestNow(now()->subMinutes(5));
        $this->actingAs($this->createUser('Super Admin', $this->account));

        // check on create
        $form         = $this->adjustmentform(true);
        $form['type'] = 'Damage';
        $this->post('/adjustments', $form)->assertSessionHas('message');

        $adjustment = Adjustment::where('reference', $form['reference'])->first();
        foreach ($adjustment->items as $adjustmentItem) {
            $stock = $adjustmentItem->item->stock()->ofWarehouse($adjustment->warehouse_id)->first();
            $this->assertEquals(0 - $adjustmentItem->quantity, $stock->quantity);
        }

        // Check on update
        // Carbon::setTestNow(now()->addMinute());
        $form['type'] = 'Addition';
        foreach ($form['items'] as &$item) {
            foreach ($adjustment->items as $adjustmentItem) {
                if ($adjustmentItem->item_id == $item['item_id']) {
                    $item['quantity'] *= 2;
                    $item['id'] = $adjustmentItem->id;
                }
            }
        }
        $this->put('/adjustments/' . $adjustment->id, $form)->assertSessionHas('message');

        $adjustment->refresh();
        foreach ($adjustment->items as $adjustmentItem) {
            $stock = $adjustmentItem->item->stock()->ofWarehouse($adjustment->warehouse_id)->first();
            $this->assertEquals($adjustmentItem->quantity, $stock->quantity);
        }
    }

    public function test_stock_sync_on_damage_to_subtraction()
    {
        // Carbon::setTestNow(now()->subMinutes(5));
        $this->actingAs($this->createUser('Super Admin', $this->account));

        // check on create
        $form         = $this->adjustmentform(true);
        $form['type'] = 'Damage';
        $this->post('/adjustments', $form)->assertSessionHas('message');

        $adjustment = Adjustment::where('reference', $form['reference'])->first();
        foreach ($adjustment->items as $adjustmentItem) {
            $stock = $adjustmentItem->item->stock()->ofWarehouse($adjustment->warehouse_id)->first();
            $this->assertEquals(0 - $adjustmentItem->quantity, $stock->quantity);
        }

        // Check on update
        // Carbon::setTestNow(now()->addMinute());
        $form['type'] = 'Subtraction';
        foreach ($form['items'] as &$item) {
            foreach ($adjustment->items as $adjustmentItem) {
                if ($adjustmentItem->item_id == $item['item_id']) {
                    $item['quantity'] *= 2;
                    $item['id'] = $adjustmentItem->id;
                }
            }
        }
        $this->put('/adjustments/' . $adjustment->id, $form)->assertSessionHas('message');

        $adjustment->refresh();
        foreach ($adjustment->items as $adjustmentItem) {
            $stock = $adjustmentItem->item->stock()->ofWarehouse($adjustment->warehouse_id)->first();
            $this->assertEquals(0 - $adjustmentItem->quantity, $stock->quantity);
        }
    }
}
