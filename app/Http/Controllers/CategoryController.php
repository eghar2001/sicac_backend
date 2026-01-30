<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $cacheKey = $this->cacheKey($request, 'categories:index');

        $categories = Cache::remember($cacheKey, $this->cacheTtl(), function () use ($request) {
            $query = Category::query()->orderBy('name');

            if ($request->filled('name')) {
                $name = $request->query('name');
                $query->where('name', 'ilike', "%{$name}%");
            }

            return $query->get();
        });

        return response()->json([
            'categories' => $categories,
        ]);
    }
}
