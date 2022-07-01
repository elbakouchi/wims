<?php

namespace Database\Factories;

use App\Models\CheckinItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class CheckinItemFactory extends Factory
{
    protected $model = CheckinItem::class;

    public function definition()
    {
        return [
            'quantity'    => $q = mt_rand(1, 10),
            'weight'      => $q,
            'batch_no'    => $this->faker->ean8,
            'expiry_date' => $this->faker->dateTime(),
            'item_id'     => 1,
            'checkin_id'  => 1,
            'account_id'  => 1,
            'unit_id'     => null,
        ];
    }
}
