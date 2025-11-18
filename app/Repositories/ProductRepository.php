<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository extends BaseRepository
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function search(string $query): Collection
    {
        return $this->model->search($query)->get();
    }

    public function findBySku(string $sku): ?Product
    {
        return $this->model->where('sku', $sku)->first();
    }

    public function getLowStockProducts(): Collection
    {
        return $this->model->whereRaw('stock_quantity <= low_stock_threshold')->get();
    }

    public function getByVendor(int $vendorId): LengthAwarePaginator
    {
        return $this->model->where('user_id', $vendorId)->paginate();
    }

    public function updateStock(int $productId, int $quantity): bool
    {
        return $this->model->where('id', $productId)->update(['stock_quantity' => $quantity]);
    }
}