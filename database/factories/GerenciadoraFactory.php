<?php

namespace Database\Factories;

use App\Models\Gerenciadora;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Gerenciadora>
 */
class GerenciadoraFactory extends Factory
{
    protected $model = Gerenciadora::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->company,
            'cuit' => $this->faker->unique()->numerify('##-########-#'),
        ];
    }
}
