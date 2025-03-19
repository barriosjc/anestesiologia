<?php

namespace Database\Factories;

use App\Models\Centro;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Centro>
 */
class CentroFactory extends Factory
{
    protected $model = Centro::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->company,
            'cuit' => $this->faker->unique()->numerify('##-########-#'),
            'telefono' => $this->faker->phoneNumber,
            'contacto' => $this->faker->name,
        ];
    }
}
