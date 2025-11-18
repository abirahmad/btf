<?php

namespace App\Jobs;

use App\Events\LowStockAlert;
use App\Services\InventoryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckLowStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(InventoryService $inventoryService): void
    {
        $lowStockProducts = $inventoryService->checkLowStock();
        
        foreach ($lowStockProducts as $product) {
            LowStockAlert::dispatch($product);
        }
    }
}