<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        RateLimiter::clear('login');
        parent::tearDown();
    }

    public function test_tras_varios_intentos_fallidos_el_login_queda_bloqueado(): void
    {
        User::factory()->create(['email' => 'admin@test.com', 'password' => 'password123']);

        $payload = ['email' => 'admin@test.com', 'password' => 'incorrecta'];

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', $payload)->assertStatus(422);
        }

        $this->postJson('/api/login', $payload)->assertStatus(429);
    }
}
