<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition()
    {
        return [
            'name'       => $this->faker->unique()->name,
            'guard_name' => 'web',
            'account_id'     => 1,
        ];
    }
}
