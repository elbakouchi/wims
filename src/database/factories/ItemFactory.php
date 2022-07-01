<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function configure()
    {
        return $this->afterCreating(function (Item $item) {
            $item->setStock();
            $item->addVariations();
        });
    }

    public function definition()
    {
        static $number = 0;
        $number++;
        return [
            'account_id'     => 1,
            'track_quantity' => 1,
            'unit_id'        => null,
            'symbology'      => 'code128',
            // 'sku'            => $this->faker->uuid,
            'track_weight'   => $this->faker->boolean,
            'has_serials'    => $this->faker->boolean,
            'has_variants'   => $this->faker->boolean,
            'alert_quantity' => [10, 15, 20, 25][mt_rand(0, 3)],
            'details'        => $this->faker->paragraph(mt_rand(3, 6)),
            'code'           => 'TI' . ($number < 10 ? '0' . $number : $number),
            'name'           => 'Test Item ' . ($number < 10 ? '0' . $number : $number),
            'variants'       => json_decode('[{"name":"Color","option":["Red", "Green"]}, {"name":"Size","option":["S","M","L"]}]'),
        ];
    }
}
