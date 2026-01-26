<?php

namespace App\Http\Controllers;

use App\Models\Subfamily;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SubfamilyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $cacheKey = $this->cacheKey($request, 'subfamilies:index');

        $subfamilies = Cache::remember($cacheKey, $this->cacheTtl(), function () use ($request) {
            $query = Subfamily::with('family')->orderBy('name');

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
            'subfamilies' => $subfamilies,
        ]);
    }
}
