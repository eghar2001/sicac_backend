<?php

namespace App\Http\Controllers;

use App\Models\TechnicianRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TechnicianRequestController extends Controller
{
    public function startTechnicianRequest(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'wanted_date_start' => 'required|date',
            'wanted_date_end' => 'required|date|after_or_equal:wanted_date_start',
            'time_shift' => 'required|string',
        ]);

        $technicianRequest = TechnicianRequest::create([
            'requesting_user_id' => Auth::id(),
            'technician_id' => null,
            ...$validated,
        ]);

        return response()->json($technicianRequest, 201);
    }
}
