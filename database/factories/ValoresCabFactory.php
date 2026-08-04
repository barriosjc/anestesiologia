<?php

namespace Database\Factories;

use App\Models\ValoresCab;
use App\Models\Gerenciadora;
use App\Models\Cobertura;
use App\Models\Centro;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ValoresCab>
 */
class ValoresCabFactory extends Factory
{
    protected $model = ValoresCab::class;

    public function definition(): array
    {
        return [
            'gerenciadora_id' => Gerenciadora::factory(),
            'cobertura_id' => Cobertura::factory(),
            'centro_id' => Centro::factory(),
            'periodo' => '2025/01',
            'grupo' => $this->faker->randomElement(['A', 'B', 'C']),
        ];
    }
}
