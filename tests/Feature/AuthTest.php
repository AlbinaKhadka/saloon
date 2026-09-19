<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_sanctum_csrf_cookie_endpoint_returns_success(): void
    {
        $response = $this->get('/sanctum/csrf-cookie');

        $response->assertStatus(204);
    }

    public function test_unauthenticated_user_endpoint_returns_401(): void
    {
        $response = $this->getJson('/user');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);

        $apiResponse = $this->getJson('/api/user');

        $apiResponse->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }
}
