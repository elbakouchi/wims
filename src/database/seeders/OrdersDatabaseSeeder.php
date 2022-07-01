<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Item;
use App\Models\Checkin;
use App\Models\Checkout;
use App\Models\Transfer;
use App\Models\Adjustment;
use App\Models\CheckinItem;
use App\Events\CheckinEvent;
use App\Models\CheckoutItem;
use App\Models\TransferItem;
use App\Events\CheckoutEvent;
use App\Events\TransferEvent;
use App\Models\AdjustmentItem;
use App\Events\AdjustmentEvent;
use Illuminate\Database\Seeder;

class OrdersDatabaseSeeder extends Seeder
{
    public function run()
    {
        for ($i = 1; $i <= 25; $i++) {
            $this->createCheckin($i);
        }

        for ($i = 1; $i <= 20; $i++) {
            $this->createCheckout($i);
        }

        for ($i = 1; $i <= 10; $i++) {
            $this->createTransfer($i);
        }

        for ($i = 1; $i <= 10; $i++) {
            $this->createAdjustment($i);
        }

        // Now
        Carbon::setTestNow();
        for ($i = 26; $i <= 28; $i++) {
            $this->createCheckin($i, true);
        }
        for ($i = 21; $i <= 22; $i++) {
            $this->createCheckout($i, true);
        }
        $this->createTransfer(11, true);
        $this->createAdjustment(11, true);
    }

    private function createAdjustment($i, $now = false)
    {
        $data = [
            'warehouse_id' => mt_rand(1, 4),
            'reference'    => 'TT' . ($i > 9 ? $i : '0' . $i),
        ];
        if ($now) {
            $data['date'] = now();
        }
        $data = Adjustment::factory()->make($data)->toArray();
        Carbon::setTestNow();
        Carbon::setTestNow(Carbon::parse($data['date']));
        $adjustment = Adjustment::factory()->create($data);

        $items = Item::inRandomOrder()->take(mt_rand(2, 3))->get();
        foreach ($items as $item) {
            if ($item->has_variants && $item->variations->isNotEmpty()) {
                $adjustmentItem = AdjustmentItem::factory()->create([
                    'item_id'       => $item->id,
                    'unit_id'       => $item->unit_id,
                    'adjustment_id' => $adjustment->id,
                    'type'          => $adjustment->type,
                    'warehouse_id'  => $adjustment->warehouse_id,
                ]);

                $variation = $item->variations()->inRandomOrder()->first();
                $adjustmentItem->variations()->sync([$variation->id => [
                    'weight'   => $adjustmentItem->weight,
                    'unit_id'  => $adjustmentItem->unit_id,
                    'quantity' => $adjustmentItem->quantity,
                ]]);
            } else {
                $adjustmentItem = $adjustment->items()->create(
                    AdjustmentItem::factory()->make([
                        'item_id'       => $item->id,
                        'unit_id'       => $item->unit_id,
                        'adjustment_id' => $adjustment->id,
                        'type'          => $adjustment->type,
                        'warehouse_id'  => $adjustment->warehouse_id,
                    ])->toArray()
                );
            }
        }
        event(new AdjustmentEvent($adjustment));
    }

    private function createCheckin($i, $now = false)
    {
        $data = [
            'warehouse_id' => mt_rand(1, 4),
            'contact_id'   => mt_rand(1, 20),
            'reference'    => 'TCI' . ($i > 9 ? $i : '0' . $i),
        ];
        if ($now) {
            $data['date'] = now();
        }
        $data = Checkin::factory()->make($data)->toArray();
        Carbon::setTestNow();
        Carbon::setTestNow(Carbon::parse($data['date']));
        $checkin = Checkin::factory()->create($data);

        $items = Item::inRandomOrder()->take(mt_rand(2, 3))->get();
        foreach ($items as $item) {
            if ($item->has_variants && $item->variations->isNotEmpty()) {
                $checkinItem = CheckinItem::factory()->create([
                    'item_id'      => $item->id,
                    'checkin_id'   => $checkin->id,
                    'unit_id'      => $item->unit_id,
                    'warehouse_id' => $checkin->warehouse_id,
                ]);

                $variation = $item->variations()->inRandomOrder()->first();
                $checkinItem->variations()->sync([$variation->id => [
                    'weight'   => $checkinItem->weight,
                    'unit_id'  => $checkinItem->unit_id,
                    'quantity' => $checkinItem->quantity,
                ]]);
            } else {
                $checkinItem = $checkin->items()->create(
                    CheckinItem::factory()->make([
                        'item_id'      => $item->id,
                        'checkin_id'   => $checkin->id,
                        'unit_id'      => $item->unit_id,
                        'warehouse_id' => $checkin->warehouse_id,
                    ])->toArray()
                );
            }
        }
        event(new CheckinEvent($checkin));
    }

    private function createCheckout($i, $now = false)
    {
        $data = [
            'warehouse_id' => mt_rand(1, 4),
            'contact_id'   => mt_rand(1, 20),
            'reference'    => 'TCO' . ($i > 9 ? $i : '0' . $i),
        ];
        if ($now) {
            $data['date'] = now();
        }
        $data = Checkout::factory()->make($data)->toArray();
        Carbon::setTestNow();
        Carbon::setTestNow(Carbon::parse($data['date']));
        $checkout = Checkout::factory()->create($data);

        $items = Item::inRandomOrder()->take(mt_rand(2, 3))->get();
        foreach ($items as $item) {
            if ($item->has_variants && $item->variations->isNotEmpty()) {
                $checkoutItem = CheckoutItem::factory()->create([
                    'item_id'      => $item->id,
                    'checkout_id'  => $checkout->id,
                    'unit_id'      => $item->unit_id,
                    'warehouse_id' => $checkout->warehouse_id,
                ]);

                $variation = $item->variations()->inRandomOrder()->first();
                $checkoutItem->variations()->sync([$variation->id => [
                    'weight'   => $checkoutItem->weight,
                    'unit_id'  => $checkoutItem->unit_id,
                    'quantity' => $checkoutItem->quantity,
                ]]);
            } else {
                $checkoutItem = $checkout->items()->create(
                    CheckoutItem::factory()->make([
                        'item_id'      => $item->id,
                        'checkout_id'  => $checkout->id,
                        'unit_id'      => $item->unit_id,
                        'warehouse_id' => $checkout->warehouse_id,
                    ])->toArray()
                );
            }
        }
        event(new CheckoutEvent($checkout));
    }

    private function createTransfer($i, $now = false)
    {
        $data = [
            'from_warehouse_id' => mt_rand(1, 4),
            'to_warehouse_id'   => mt_rand(1, 4),
            'reference'         => 'TT' . ($i > 9 ? $i : '0' . $i),
        ];
        if ($data['from_warehouse_id'] == $data['to_warehouse_id']) {
            $data['to_warehouse_id'] = $data['to_warehouse_id'] > 2 ? $data['to_warehouse_id'] - 1 : $data['to_warehouse_id'] + 1;
        }
        if ($now) {
            $data['date'] = now();
        }
        $data = Transfer::factory()->make($data)->toArray();
        Carbon::setTestNow();
        Carbon::setTestNow(Carbon::parse($data['date']));
        $transfer = Transfer::factory()->create($data);

        $items = Item::inRandomOrder()->take(mt_rand(2, 3))->get();
        foreach ($items as $item) {
            if ($item->has_variants && $item->variations->isNotEmpty()) {
                $transferItem = TransferItem::factory()->create([
                    'item_id'           => $item->id,
                    'transfer_id'       => $transfer->id,
                    'unit_id'           => $item->unit_id,
                    'to_warehouse_id'   => $transfer->to_warehouse_id,
                    'from_warehouse_id' => $transfer->from_warehouse_id,
                ]);

                $variation = $item->variations()->inRandomOrder()->first();
                $transferItem->variations()->sync([$variation->id => [
                    'weight'   => $transferItem->weight,
                    'unit_id'  => $transferItem->unit_id,
                    'quantity' => $transferItem->quantity,
                ]]);
            } else {
                $transferItem = $transfer->items()->create(
                    TransferItem::factory()->make([
                        'item_id'           => $item->id,
                        'transfer_id'       => $transfer->id,
                        'unit_id'           => $item->unit_id,
                        'to_warehouse_id'   => $transfer->to_warehouse_id,
                        'from_warehouse_id' => $transfer->from_warehouse_id,
                    ])->toArray()
                );
            }
        }
        event(new TransferEvent($transfer));
    }
}
