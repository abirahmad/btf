<?php

namespace App\Services;

use App\Models\Product;
use App\Models\InventoryLog;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function __construct(
        private ProductRepository $productRepository
    ) {}

    public function updateStock(int $productId, int $quantity, string $reason, ?int $userId = null): bool
    {
        return DB::transaction(function () use ($productId, $quantity, $reason, $userId) {
            $product = $this->productRepository->find($productId);
            
            if (!$product) {
                throw new \Exception('Product not found');
            }

            $previousStock = $product->stock_quantity;
            $newStock = $previousStock + $quantity;

            if ($newStock < 0) {
                throw new \Exception('Insufficient stock');
            }

            $this->productRepository->updateStock($productId, $newStock);

            InventoryLog::create([
                'product_id' => $productId,
                'type' => $quantity > 0 ? 'increase' : 'decrease',
                'quantity' => abs($quantity),
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reason' => $reason,
                'user_id' => $userId
            ]);

            return true;
        });
    }

    public function reserveStock(int $productId, int $quantity): bool
    {
        return $this->updateStock($productId, -$quantity, 'Order reservation');
    }

    public function releaseStock(int $productId, int $quantity): bool
    {
        return $this->updateStock($productId, $quantity, 'Order cancellation');
    }

    public function checkLowStock(): array
    {
        return $this->productRepository->getLowStockProducts()->toArray();
    }
}