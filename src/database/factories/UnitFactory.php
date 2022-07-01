<?php

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition()
    {
        return [
            'code'            => $this->faker->word,
            'name'            => $this->faker->word,
            'operator'        => null,
            'operation_value' => null,
            'base_unit_id'    => null,
            'account_id'     => 1,
        ];
    }
}
