<?php

namespace App\Http\Controllers;

use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FamilyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $cacheKey = $this->cacheKey($request, 'families:index');

        $families = Cache::remember($cacheKey, $this->cacheTtl(), function () use ($request) {
            $query = Family::query()->orderBy('name');

            if ($request->filled('name')) {
                $name = $request->query('name');
                $query->where('name', 'ilike', "%{$name}%");
            }

            return $query->get();
        });

        return response()->json([
            'families' => $families,
        ]);
    }
}
