<?php

namespace Database\Factories;

use App\Models\Transfer;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransferFactory extends Factory
{
    protected $model = Transfer::class;

    public function definition()
    {
        return [
            'date'              => $this->faker->dateTimeBetween('-3 months'),
            'hash'              => $this->faker->randomKey(),
            'reference'         => $this->faker->ean13,
            'draft'             => $this->faker->boolean,
            'from_warehouse_id' => 1,
            'to_warehouse_id'   => 2,
            'user_id'           => 1,
            'recurring_id'      => null,
            'account_id'        => 1,
            'details'           => $this->faker->paragraph,
            'extra_attributes'  => null,
            'approved_at'       => null,
        ];
    }
}
