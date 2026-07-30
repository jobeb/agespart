<?php

namespace Database\Factories;

use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Incidencia>
 */
class IncidenciaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid_cliente' => (string) Str::uuid(),
            'tipo' => $this->faker->randomElement(['reparacion', 'instalacion']),
            'descripcion' => $this->faker->sentence(),
            'estado' => 'pendiente',
            'lat' => $this->faker->latitude(36, 43),
            'lng' => $this->faker->longitude(-9, 3),
            'direccion' => $this->faker->address(),
            'empleado_id' => User::factory()->state(['rol' => 'empleado']),
            'creado_por' => User::factory()->state(['rol' => 'admin']),
            'version' => 1,
        ];
    }
}
