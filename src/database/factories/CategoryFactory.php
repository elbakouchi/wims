<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition()
    {
        static $number = 0;
        $number++;
        return [
            'account_id' => 1,
            'parent_id'  => null,
            'code'       => 'C' . $number,
            'name'       => $name = 'Category ' . $number,
        ];
    }
}
