<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository extends BaseRepository
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return $this->model->where('order_number', $orderNumber)->first();
    }

    public function getByUser(int $userId): LengthAwarePaginator
    {
        return $this->model->where('user_id', $userId)->with('items.product')->paginate();
    }

    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }

    public function updateStatus(int $orderId, string $status): bool
    {
        $updateData = ['status' => $status];
        
        if ($status === 'shipped') {
            $updateData['shipped_at'] = now();
        } elseif ($status === 'delivered') {
            $updateData['delivered_at'] = now();
        }
        
        return $this->update($orderId, $updateData);
    }
}