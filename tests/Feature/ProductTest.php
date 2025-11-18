<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_list_products()
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'name', 'sku', 'price', 'stock_quantity']
                    ]
                ]);
    }

    public function test_vendor_can_create_product()
    {
        $vendor = User::role('vendor')->first();
        $token = JWTAuth::fromUser($vendor);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/products', [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 99.99,
            'stock_quantity' => 50,
            'description' => 'Test product description'
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'id', 'name', 'sku', 'price', 'stock_quantity'
                ]);

        $this->assertDatabaseHas('products', [
            'sku' => 'TEST-001'
        ]);
    }

    public function test_customer_cannot_create_product()
    {
        $customer = User::role('customer')->first();
        $token = JWTAuth::fromUser($customer);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/products', [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 99.99,
            'stock_quantity' => 50
        ]);

        $response->assertStatus(403);
    }

    public function test_can_search_products()
    {
        $response = $this->getJson('/api/v1/products?search=Laptop');

        $response->assertStatus(200);
    }
}