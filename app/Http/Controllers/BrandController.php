<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $cacheKey = $this->cacheKey($request, 'brands:index');

        $brands = Cache::remember($cacheKey, $this->cacheTtl(), function () use ($request) {
            $query = Brand::query()->orderBy('name');

            if ($request->filled('name')) {
                $name = $request->query('name');
                $query->where('name', 'ilike', "%{$name}%");
            }

            return $query->get();
        });

        return response()->json([
            'brands' => $brands,
        ]);
    }
}
