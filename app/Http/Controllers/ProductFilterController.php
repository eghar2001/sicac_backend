<?php

namespace App\Http\Controllers;

use App\Http\Resources\BrandResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\FamilyResource;
use App\Http\Resources\SubfamilyResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Family;
use App\Models\Subfamily;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductFilterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function brands(Request $request)
    {
        $cacheKey = $this->cacheKey($request, 'products:filters:brands');

        $brands = Cache::remember($cacheKey, $this->cacheTtl(), function () use ($request) {
            $query = Brand::query()->orderBy('name')->whereHas('products');

            if ($request->filled('name')) {
                $name = $request->query('name');
                $query->where('name', 'ilike', "%{$name}%");
            }

            return $query->get();
        });

        return response()->json([
            'brands' => BrandResource::collection($brands),
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function categories(Request $request)
    {
        $cacheKey = $this->cacheKey($request, 'products:filters:categories');

        $categories = Cache::remember($cacheKey, $this->cacheTtl(), function () use ($request) {
            $query = Category::query()->orderBy('name')->whereHas('products');

            if ($request->filled('name')) {
                $name = $request->query('name');
                $query->where('name', 'ilike', "%{$name}%");
            }

            return $query->get();
        });

        return response()->json([
            'categories' => CategoryResource::collection($categories),
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function families(Request $request)
    {
        $cacheKey = $this->cacheKey($request, 'products:filters:families');

        $families = Cache::remember($cacheKey, $this->cacheTtl(), function () use ($request) {
            $query = Family::query()->orderBy('name')->whereHas('subfamilies.products');

            if ($request->filled('name')) {
                $name = $request->query('name');
                $query->where('name', 'ilike', "%{$name}%");
            }

            return $query->get();
        });

        return response()->json([
            'families' => FamilyResource::collection($families),
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function subfamilies(Request $request)
    {
        $cacheKey = $this->cacheKey($request, 'products:filters:subfamilies');

        $subfamilies = Cache::remember($cacheKey, $this->cacheTtl(), function () use ($request) {
            $query = Subfamily::with('family')->orderBy('name')->whereHas('products');

            if ($request->filled('family_id')) {
                $query->whereIn('family_id', (array) $request->query('family_id'));
            }

            if ($request->filled('name')) {
                $name = $request->query('name');
                $query->where('name', 'ilike', "%{$name}%");
            }

            return $query->get();
        });

        return response()->json([
            'subfamilies' => SubfamilyResource::collection($subfamilies),
        ]);
    }
}
