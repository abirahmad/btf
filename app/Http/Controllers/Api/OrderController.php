<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Repositories\OrderRepository;
use App\Actions\GenerateInvoiceAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OrderController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        private OrderService $orderService,
        private OrderRepository $orderRepository
    ) {}

    /**
     * @OA\Get(
     *     path="/orders",
     *     summary="Get list of orders",
     *     security={{"bearerAuth":{}}},
     *     tags={"Orders"},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Orders retrieved successfully")
     * )
     */
    public function index(Request $request)
    {
        if (auth()->user()->hasRole('customer')) {
            $orders = $this->orderRepository->getByUser(auth()->id());
        } else {
            $orders = $this->orderRepository->paginate($request->get('per_page', 15));
        }

        return response()->json($orders);
    }

    /**
     * @OA\Post(
     *     path="/orders",
     *     summary="Create a new order",
     *     security={{"bearerAuth":{}}},
     *     tags={"Orders"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="product_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="integer", example=2),
     *                     @OA\Property(property="variant", type="object", example={"color": "Silver", "storage": "512GB"})
     *                 ),
     *                 example={{
     *                     "product_id": 1,
     *                     "quantity": 2,
     *                     "variant": {"color": "Silver", "storage": "512GB"}
     *                 }}
     *             ),
     *             @OA\Property(
     *                 property="shipping_address",
     *                 type="object",
     *                 example={
     *                     "name": "John Doe",
     *                     "street": "123 Main St",
     *                     "city": "New York",
     *                     "state": "NY",
     *                     "zip": "10001"
     *                 }
     *             ),
     *             @OA\Property(
     *                 property="billing_address",
     *                 type="object",
     *                 example={
     *                     "street": "123 Main St",
     *                     "city": "New York",
     *                     "state": "NY",
     *                     "zip": "10001"
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Order created successfully"),
     *     @OA\Response(response=422, description="Validation errors")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/orders/{id}",
     *     summary="Get a specific order",
     *     security={{"bearerAuth":{}}},
     *     tags={"Orders"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Order retrieved successfully"),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/orders/{id}/invoice",
     *     summary="Download order invoice",
     *     security={{"bearerAuth":{}}},
     *     tags={"Orders"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Invoice PDF file"),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function invoice(Order $order, GenerateInvoiceAction $invoiceAction)
    {
        $this->authorize('view', $order);

        $path = $invoiceAction->execute($order);
        
        return response()->download($path);
    }
}