<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RatingSummaryController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type', 'technicians');

        if ($type === 'technicians') {
            $aggregates = Rating::select('technician_id', DB::raw('AVG(score) as average'), DB::raw('COUNT(*) as total'), DB::raw('MAX(created_at) as last_created_at'))
                ->groupBy('technician_id')
                ->get();

            $data = $aggregates->map(function ($row) {
                $last = Rating::where('technician_id', $row->technician_id)->orderBy('created_at', 'desc')->first();

                $name = null;
                if ($last && $last->technician && $last->technician->user) {
                    $name = $last->technician->user->name;
                }

                return [
                    'technician_id' => $row->technician_id,
                    'name' => $name,
                    'average' => round((float) $row->average, 2),
                    'total' => (int) $row->total,
                    'last_review' => $last->description ?? null,
                    'last_client_rating' => $last->score ?? null,
                    'last_client_notes' => $last->description ?? null,
                ];
            });

            return response()->json(['data' => $data]);
        }

        // For other types (e.g. products) return empty array for now
        return response()->json(['data' => []]);
    }
}
