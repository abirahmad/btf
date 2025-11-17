<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiTest extends TestCase
{
    public function test_api_returns_welcome_message(): void
    {
        $response = $this->get('/api/api');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Welcome to the API']);
    }
}