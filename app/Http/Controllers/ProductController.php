<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $cacheKey = $this->cacheKey($request, 'products:index');

        $products = Cache::remember($cacheKey, $this->cacheTtl(), function () use ($request) {
            $query = Product::with(['brand', 'subfamily.family', 'category']);

            if ($request->filled('brand_id')) {
                $query->whereIn('brand_id', (array) $request->query('brand_id'));
            }

            if ($request->filled('family_id')) {
                $familyIds = (array) $request->query('family_id');
                $query->whereHas('subfamily', function ($subfamilyQuery) use ($familyIds) {
                    $subfamilyQuery->whereIn('family_id', $familyIds);
                });
            }

            if ($request->filled('subfamily_id')) {
                $query->whereIn('subfamily_id', (array) $request->query('subfamily_id'));
            }

            if ($request->filled('category_id')) {
                $query->whereIn('category_id', (array) $request->query('category_id'));
            }

            if ($request->filled('name')) {
                $name = $request->query('name');
                $query->where('name', 'ilike', "%{$name}%");
            }

            if ($request->filled('price_min')) {
                $query->where('price_ars', '>=', $request->query('price_min'));
            }

            if ($request->filled('price_max')) {
                $query->where('price_ars', '<=', $request->query('price_max'));
            }

            return $query->paginate(15);
        });

        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'external_id' => ['required', 'string', 'max:255', 'unique:products,external_id'],
            'name' => ['required', 'string', 'max:255'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'subfamily_id' => ['required', 'integer', 'exists:subfamilies,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'model_sku' => ['nullable', 'string', 'max:255'],
            'price_ars' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
            'technical_specs' => ['nullable', 'array'],
            'source_specs' => ['nullable', 'string'],
        ]);

        $product = Product::create($validated);

        return response()->json([
            'product' => new ProductResource(
                $product->load(['brand', 'subfamily.family', 'category'])
            ),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $cacheKey = 'products:show:' . $product->id;
        $cachedProduct = Cache::remember($cacheKey, $this->cacheTtl(), function () use ($product) {
            return $product->load(['brand', 'subfamily.family', 'category']);
        });

        return response()->json([
            'product' => new ProductResource($cachedProduct),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'external_id' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('products', 'external_id')->ignore($product->id),
            ],
            'brand_id' => ['sometimes', 'integer', 'exists:brands,id'],
            'subfamily_id' => ['sometimes', 'integer', 'exists:subfamilies,id'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'model_sku' => ['nullable', 'string', 'max:255'],
            'price_ars' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
            'technical_specs' => ['nullable', 'array'],
            'source_specs' => ['nullable', 'string'],
        ]);

        $product->update($validated);

        return response()->json([
            'product' => new ProductResource(
                $product->load(['brand', 'subfamily.family', 'category'])
            ),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(['ok' => true]);
    }
}
