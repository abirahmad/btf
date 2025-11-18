<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use App\Repositories\ProductRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private InventoryService $inventoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inventoryService = new InventoryService(new ProductRepository(new Product()));
        $this->seed();
    }

    public function test_can_update_stock()
    {
        $product = Product::first();
        $initialStock = $product->stock_quantity;

        $result = $this->inventoryService->updateStock(
            $product->id, 
            10, 
            'Stock increase test'
        );

        $this->assertTrue($result);
        $this->assertEquals($initialStock + 10, $product->fresh()->stock_quantity);
    }

    public function test_cannot_reduce_stock_below_zero()
    {
        $product = Product::first();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient stock');

        $this->inventoryService->updateStock(
            $product->id, 
            -($product->stock_quantity + 1), 
            'Stock reduction test'
        );
    }

    public function test_can_reserve_stock()
    {
        $product = Product::first();
        $initialStock = $product->stock_quantity;

        $result = $this->inventoryService->reserveStock($product->id, 5);

        $this->assertTrue($result);
        $this->assertEquals($initialStock - 5, $product->fresh()->stock_quantity);
    }

    public function test_can_release_stock()
    {
        $product = Product::first();
        $initialStock = $product->stock_quantity;

        $result = $this->inventoryService->releaseStock($product->id, 5);

        $this->assertTrue($result);
        $this->assertEquals($initialStock + 5, $product->fresh()->stock_quantity);
    }
}