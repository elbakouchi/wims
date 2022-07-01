<?php

namespace Database\Factories;

use App\Models\CheckoutItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class CheckoutItemFactory extends Factory
{
    protected $model = CheckoutItem::class;

    public function definition()
    {
        return [
            'quantity'    => $q = mt_rand(10, 20),
            'weight'      => $q,
            'batch_no'    => $this->faker->ean8,
            'expiry_date' => $this->faker->dateTime(),
            'item_id'     => 1,
            'checkout_id' => 1,
            'account_id'  => 1,
            'unit_id'     => null,
        ];
    }
}
