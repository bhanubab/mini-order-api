<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    // Cache key & duration
    const CACHE_KEY = 'products_all';
    const CACHE_TTL = 60 * 60; // 1 hour in seconds

    public function index(Request $request)
    {
        // Build a unique cache key based on filters/search
        $cacheKey = 'products_' . md5(json_encode($request->all()));

        $products = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($request) {
            $query = Product::query();

            if ($request->has('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            }

            if ($request->has('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }

            if ($request->has('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }

            if ($request->has('in_stock')) {
                $query->where('stock', '>', 0);
            }

            $sortBy       = $request->get('sort_by', 'created_at');
            $sortDir      = $request->get('sort_dir', 'desc');
            $allowedSorts = ['name', 'price', 'stock', 'created_at'];

            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
            }

            return $query->get();
        });

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        // Clear all product caches when new product added
        $this->clearProductCache();

        return new ProductResource($product);
    }

    public function show(Product $product)
    {
        // Cache individual product by ID
        $cacheKey = 'product_' . $product->id;

        $cachedProduct = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($product) {
            return $product;
        });

        return new ProductResource($cachedProduct);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        // Clear this product's cache + list cache
        Cache::forget('product_' . $product->id);
        $this->clearProductCache();

        return new ProductResource($product);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        // Clear cache on delete
        Cache::forget('product_' . $product->id);
        $this->clearProductCache();

        return response()->json(['message' => 'Product deleted successfully']);
    }

    // Helper — clears all product list caches
    private function clearProductCache(): void
    {
        Cache::tags !== null
            ? Cache::tags(['products'])->flush()
            : Cache::forget(self::CACHE_KEY);
    }
}