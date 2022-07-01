<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Checkin;
use App\Models\Checkout;
use App\Models\Transfer;
use App\Models\Adjustment;

trait HelperTrait
{
    public function adjustmentform($active = false, $count = 1)
    {
        return $this->orderform(new Adjustment, $active, $count);
    }

    public function checkinform($active = false, $count = 1)
    {
        return $this->orderform(new Checkin, $active, $count);
    }

    public function checkoutform($active = false, $count = 1)
    {
        return $this->orderform(new Checkout, $active, $count);
    }

    public function orderform($model, $active, $count)
    {
        $form = $model::factory()->make()->toArray();
        if ($active) {
            $form['draft'] = 0;
        }
        $form['items'] = Item::inRandomOrder()->take(mt_rand(2, 3))->get()->transform(function ($item) {
            $item->weight = 1;
            $selected_serials = [];
            $selected_variations = [];
            $item->item_id = $item->id;
            $item->quantity = mt_rand(1, 3);
            if ($item->has_variants && $item->variations->isNotEmpty()) {
                $variation = $item->variations->random();
                $selected_variations[] = [
                    'variation_id' => $variation->id,
                    'weight'       => $item->weight,
                    'quantity'     => $item->quantity,
                    'unit_id'      => $item->unit_id,
                ];
            }
            $item->selected = ['variations' => $selected_variations, 'serials' => $selected_serials];
            unset($item->variations);
            unset($item->variants);
            unset($item->details);
            unset($item->id);
            return $item;
        })->toArray();
        return $form;
    }

    public function transferform($active = false, $count = 1)
    {
        return $this->orderform(new Transfer, $active, $count);
    }
}
