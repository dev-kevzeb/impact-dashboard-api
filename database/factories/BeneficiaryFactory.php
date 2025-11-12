<?php

namespace Database\Factories;

use App\Modules\Beneficiary\Domain\Beneficiary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para generar datos fake de Beneficiary
 * 
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\Beneficiary\Domain\Beneficiary>
 */
class BeneficiaryFactory extends Factory
{
    /**
     * El nombre del modelo correspondiente a la factory.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Beneficiary::class;

    /**
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company(), // Genera nombres de empresas únicos
        ];
    }
}
