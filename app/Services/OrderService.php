<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private ProductRepository $productRepository,
        private InventoryService $inventoryService
    ) {}

    public function createOrder(array $orderData): Order
    {
        return DB::transaction(function () use ($orderData) {
            $totalAmount = 0;
            
            // Validate products and calculate total
            foreach ($orderData['items'] as $item) {
                $product = $this->productRepository->find($item['product_id']);
                
                if (!$product || $product->stock_quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }
                
                $totalAmount += $product->price * $item['quantity'];
            }

            // Create order
            $order = $this->orderRepository->create([
                'user_id' => $orderData['user_id'],
                'total_amount' => $totalAmount,
                'shipping_address' => $orderData['shipping_address'],
                'billing_address' => $orderData['billing_address']
            ]);

            // Create order items and reserve stock
            foreach ($orderData['items'] as $item) {
                $product = $this->productRepository->find($item['product_id']);
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'total_price' => $product->price * $item['quantity'],
                    'product_variant' => $item['variant'] ?? null
                ]);

                $this->inventoryService->reserveStock($item['product_id'], $item['quantity']);
            }

            return $order->load('items.product');
        });
    }

    public function updateOrderStatus(int $orderId, string $status): bool
    {
        $order = $this->orderRepository->find($orderId);
        
        if (!$order) {
            throw new \Exception('Order not found');
        }

        return $this->orderRepository->updateStatus($orderId, $status);
    }

    public function cancelOrder(int $orderId): bool
    {
        return DB::transaction(function () use ($orderId) {
            $order = $this->orderRepository->find($orderId);
            
            if (!$order || !$order->canBeCancelled()) {
                throw new \Exception('Order cannot be cancelled');
            }

            // Release reserved stock
            foreach ($order->items as $item) {
                $this->inventoryService->releaseStock($item->product_id, $item->quantity);
            }

            return $this->orderRepository->updateStatus($orderId, 'cancelled');
        });
    }
}