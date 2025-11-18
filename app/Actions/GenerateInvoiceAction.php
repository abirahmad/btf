<?php

namespace App\Actions;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerateInvoiceAction
{
    public function execute(Order $order): string
    {
        $order->load(['items.product', 'user']);
        
        $pdf = Pdf::loadView('invoices.template', compact('order'));
        
        $filename = "invoice-{$order->order_number}.pdf";
        $path = storage_path("app/invoices/{$filename}");
        
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        
        $pdf->save($path);
        
        return $path;
    }
}