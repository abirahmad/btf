<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Actions\ImportProductsAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        private ProductRepository $productRepository
    ) {}

    /**
     * @OA\Get(
     *     path="/products",
     *     summary="Get list of products",
     *     tags={"Products"},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Products retrieved successfully")
     * )
     */
    public function index(Request $request)
    {
        if ($request->has('search')) {
            $products = $this->productRepository->search($request->search);
            return response()->json($products);
        }

        $products = $this->productRepository->paginate($request->get('per_page', 15));
        return response()->json($products);
    }

    /**
     * @OA\Post(
     *     path="/products",
     *     summary="Create a new product",
     *     security={{"bearerAuth":{}}},
     *     tags={"Products"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Laptop Pro 15"),
     *             @OA\Property(property="sku", type="string", example="LAP-PRO-15"),
     *             @OA\Property(property="price", type="number", example=1299.99),
     *             @OA\Property(property="stock_quantity", type="integer", example=50),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="variants", type="object")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Product created successfully"),
     *     @OA\Response(response=422, description="Validation errors")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'variants' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product = $this->productRepository->create([
            'name' => $request->name,
            'sku' => $request->sku,
            'price' => $request->price,
            'stock_quantity' => $request->stock_quantity,
            'description' => $request->description,
            'variants' => $request->variants,
            'user_id' => auth()->id()
        ]);

        return response()->json($product, 201);
    }

    /**
     * @OA\Get(
     *     path="/products/{id}",
     *     summary="Get a specific product",
     *     tags={"Products"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Product retrieved successfully"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function show(Product $product)
    {
        return response()->json($product);
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'price' => 'numeric|min:0',
            'stock_quantity' => 'integer|min:0',
            'description' => 'nullable|string',
            'variants' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $this->productRepository->update($product->id, $request->only([
            'name', 'price', 'stock_quantity', 'description', 'variants'
        ]));

        return response()->json($product->fresh());
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        
        $this->productRepository->delete($product->id);
        
        return response()->json(['message' => 'Product deleted successfully']);
    }

    /**
     * @OA\Post(
     *     path="/products/import",
     *     summary="Import products from CSV",
     *     security={{"bearerAuth":{}}},
     *     tags={"Products"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(@OA\Property(property="file", type="string", format="binary"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Products imported successfully"),
     *     @OA\Response(response=422, description="Validation errors")
     * )
     */
    public function import(Request $request, ImportProductsAction $importAction)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $file = $request->file('file');
        $results = $importAction->execute($file->getPathname(), auth()->id());

        return response()->json($results);
    }
}