<?php

namespace Database\Factories;

use App\Modules\Contact\Domain\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        $domains = ['gmail.com', 'outlook.com', 'yahoo.com', 'hotmail.com', 'icloud.com'];

        $email = strtolower($this->faker->unique()->userName . '@' . $this->faker->randomElement($domains));

        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'title' => $this->faker->randomElement(['Gerente', 'Director', 'Coordinador', 'Analista', 'Asistente']),
            'email' => $email,
            'phone' => $this->faker->numerify('+591 7######'),
        ];
    }
}
