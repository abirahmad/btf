<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $vendor = User::role('vendor')->first();

        $products = [
            [
                'name' => 'Laptop Pro 15"',
                'description' => 'High-performance laptop for professionals',
                'sku' => 'LAP-PRO-15',
                'price' => 1299.99,
                'stock_quantity' => 50,
                'low_stock_threshold' => 10,
                'user_id' => $vendor->id,
                'variants' => ['color' => ['Silver', 'Space Gray'], 'storage' => ['256GB', '512GB', '1TB']]
            ],
            [
                'name' => 'Wireless Headphones',
                'description' => 'Premium noise-cancelling headphones',
                'sku' => 'WH-NC-001',
                'price' => 299.99,
                'stock_quantity' => 100,
                'low_stock_threshold' => 20,
                'user_id' => $vendor->id,
                'variants' => ['color' => ['Black', 'White', 'Blue']]
            ],
            [
                'name' => 'Smartphone X',
                'description' => 'Latest flagship smartphone',
                'sku' => 'SP-X-128',
                'price' => 899.99,
                'stock_quantity' => 75,
                'low_stock_threshold' => 15,
                'user_id' => $vendor->id,
                'variants' => ['color' => ['Black', 'White', 'Red'], 'storage' => ['128GB', '256GB', '512GB']]
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}