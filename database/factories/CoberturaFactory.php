<?php

namespace Database\Factories;

use App\Models\Cobertura;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cobertura>
 */
class CoberturaFactory extends Factory
{
    protected $model = Cobertura::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->company,
            'cuit' => $this->faker->unique()->numerify('##-########-#'),
            'porcentaje_adic' => $this->faker->randomFloat(2, 0, 100),
            'edad_desde' => $this->faker->numberBetween(0, 50),
            'edad_hasta' => $this->faker->numberBetween(51, 100),
        ];
    }
}

