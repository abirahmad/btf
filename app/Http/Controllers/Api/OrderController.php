<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Repositories\OrderRepository;
use App\Actions\GenerateInvoiceAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private OrderRepository $orderRepository
    ) {}

    public function index(Request $request)
    {
        if (auth()->user()->hasRole('customer')) {
            $orders = $this->orderRepository->getByUser(auth()->id());
        } else {
            $orders = $this->orderRepository->paginate($request->get('per_page', 15));
        }

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.variant' => 'nullable|array',
            'shipping_address' => 'required|array',
            'billing_address' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $order = $this->orderService->createOrder([
                ...$request->all(),
                'user_id' => auth()->id()
            ]);

            return response()->json($order, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);
        
        return response()->json($order->load('items.product'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $this->orderService->updateOrderStatus($order->id, $request->status);
            return response()->json($order->fresh());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function cancel(Order $order)
    {
        $this->authorize('update', $order);

        try {
            $this->orderService->cancelOrder($order->id);
            return response()->json(['message' => 'Order cancelled successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function invoice(Order $order, GenerateInvoiceAction $invoiceAction)
    {
        $this->authorize('view', $order);

        $path = $invoiceAction->execute($order);
        
        return response()->download($path);
    }
}