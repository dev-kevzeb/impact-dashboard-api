<?php

namespace Database\Factories;

use App\Modules\Agency\Domain\Agency;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgencyFactory extends Factory
{
    protected $model = Agency::class;

    public function definition()
    {
        return [
            'name'        => $this->faker->company(),
            'url'         => $this->faker->url(),
            'is_approved' => $this->faker->boolean(),
        ];
    }

    public function approved()
    {
        return $this->state(fn () => [
            'is_approved' => true,
        ]);
    }

    public function notApproved()
    {
        return $this->state(fn () => [
            'is_approved' => false,
        ]);
    }
}
