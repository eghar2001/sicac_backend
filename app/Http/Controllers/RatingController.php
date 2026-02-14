<?php

namespace App\Http\Controllers;

use App\Http\Resources\RatingResource;
use App\Models\Rating;
use App\Models\Technician;
use App\Models\TechnicianRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function store(Request $request, Technician $technician)
    {
        $validated = $request->validate([
            'technician_request_id' => 'required|exists:technician_requests,id',
            'score' => 'required|integer|min:1|max:5',
            'description' => 'nullable|string',
        ]);

        $technicianRequest = TechnicianRequest::findOrFail($validated['technician_request_id']);
        $this->authorize('create', [Rating::class, $technician, $technicianRequest]);

        $rating = Rating::create([
            'technician_id' => $technician->id,
            'user_id' => Auth::id(),
            'technician_request_id' => $technicianRequest->id,
            'score' => $validated['score'],
            'description' => $validated['description'] ?? null,
        ]);

        return (new RatingResource($rating))->response()->setStatusCode(201);
    }
}
