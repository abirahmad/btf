<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Actions\ImportProductsAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function __construct(
        private ProductRepository $productRepository
    ) {}

    public function index(Request $request)
    {
        if ($request->has('search')) {
            $products = $this->productRepository->search($request->search);
            return response()->json($products);
        }

        $products = $this->productRepository->paginate($request->get('per_page', 15));
        return response()->json($products);
    }

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
            ...$request->validated(),
            'user_id' => auth()->id()
        ]);

        return response()->json($product, 201);
    }

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

        $this->productRepository->update($product->id, $request->validated());

        return response()->json($product->fresh());
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        
        $this->productRepository->delete($product->id);
        
        return response()->json(['message' => 'Product deleted successfully']);
    }

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