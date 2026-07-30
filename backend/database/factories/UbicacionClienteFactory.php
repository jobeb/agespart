<?php

namespace Database\Factories;

use App\Models\UbicacionCliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UbicacionCliente>
 */
class UbicacionClienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->company(),
            'direccion' => $this->faker->address(),
            'lat' => $this->faker->latitude(36, 43),
            'lng' => $this->faker->longitude(-9, 3),
            'contacto' => $this->faker->phoneNumber(),
            'activo' => true,
        ];
    }
}
