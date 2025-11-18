<?php

namespace App\Actions;

use App\Models\Product;
use League\Csv\Reader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ImportProductsAction
{
    public function execute(string $filePath, int $userId): array
    {
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);
        
        $results = ['success' => 0, 'errors' => []];
        
        DB::transaction(function () use ($csv, $userId, &$results) {
            foreach ($csv as $offset => $record) {
                try {
                    $validator = Validator::make($record, [
                        'name' => 'required|string|max:255',
                        'sku' => 'required|string|unique:products,sku',
                        'price' => 'required|numeric|min:0',
                        'stock_quantity' => 'required|integer|min:0',
                        'description' => 'nullable|string'
                    ]);

                    if ($validator->fails()) {
                        $results['errors'][] = "Row {$offset}: " . implode(', ', $validator->errors()->all());
                        continue;
                    }

                    Product::create([
                        'name' => $record['name'],
                        'sku' => $record['sku'],
                        'price' => $record['price'],
                        'stock_quantity' => $record['stock_quantity'],
                        'description' => $record['description'] ?? null,
                        'user_id' => $userId
                    ]);

                    $results['success']++;
                } catch (\Exception $e) {
                    $results['errors'][] = "Row {$offset}: " . $e->getMessage();
                }
            }
        });

        return $results;
    }
}