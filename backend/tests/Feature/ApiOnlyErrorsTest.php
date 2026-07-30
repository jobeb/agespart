<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiOnlyErrorsTest extends TestCase
{
    public function test_una_peticion_sin_sesion_sin_cabecera_accept_devuelve_401_json_y_no_500(): void
    {
        // Sin ->getJson()/->postJson(): simula un cliente que no manda Accept: application/json,
        // el escenario que antes disparaba un intento de redirect a route('login') inexistente.
        $response = $this->get('/api/me');

        $response->assertStatus(401);
        $response->assertJson(['message' => 'Unauthenticated.']);
    }
}
