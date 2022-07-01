<?php

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition()
    {
        static $number = 0;
        $number++;
        return [
            'account_id' => 1,
            'code'       => 'WH' . $number,
            'name'       => 'Warehouse ' . $number,
            'email'      => $this->faker->safeEmail,
            'phone'      => $this->faker->e164PhoneNumber,
            'address'    => $this->faker->address,
            'active'     => 1, //$this->faker->boolean(65),
        ];
    }
}
