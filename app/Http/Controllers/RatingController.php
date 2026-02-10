<?php

namespace App\Http\Controllers;

use App\Http\Resources\RatingResource;
use App\Models\Rating;
use App\Models\TechnicianRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function store(Request $request, $technicianId)
    {
        $validated = $request->validate([
            'technician_request_id' => 'required|exists:technician_requests,id',
            'score' => 'required|integer|min:1|max:5',
            'description' => 'nullable|string',
        ]);

        $tr = TechnicianRequest::findOrFail($validated['technician_request_id']);

        // only the user who requested the work can post a review for that request
        if ($tr->requesting_user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // ensure the rating refers to the actual assigned technician for that request
        if ($tr->technician_id && $tr->technician_id != $technicianId) {
            return response()->json(['message' => 'Technician mismatch'], 400);
        }

        // avoid duplicate reviews for the same technician_request
        if (Rating::where('technician_request_id', $tr->id)->exists()) {
            return response()->json(['message' => 'Review already exists for this request'], 409);
        }

        $rating = Rating::create([
            'technician_id' => $technicianId,
            'user_id' => Auth::id(),
            'technician_request_id' => $tr->id,
            'score' => $validated['score'],
            'description' => $validated['description'] ?? null,
        ]);

        return (new RatingResource($rating))->response()->setStatusCode(201);
    }
}
