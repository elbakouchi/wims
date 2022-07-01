<?php

namespace Database\Factories;

use App\Models\TransferItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransferItemFactory extends Factory
{
    protected $model = TransferItem::class;

    public function definition()
    {
        return [
            'quantity'    => $q = mt_rand(1, 5),
            'weight'      => $q,
            'batch_no'    => $this->faker->ean8,
            'expiry_date' => $this->faker->dateTime(),
            'item_id'     => 1,
            'transfer_id' => 1,
            'account_id'  => 1,
            'unit_id'     => null,
        ];
    }
}
