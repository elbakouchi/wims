<?php

namespace Database\Factories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition()
    {
        return [
            'account_id'     => 1,
            'name'    => $this->faker->name,
            'details' => $this->faker->sentence,
            'phone'   => $this->faker->phoneNumber,
            'email'   => $this->faker->unique()->safeEmail,
        ];
    }
}
