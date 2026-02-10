<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TechnicianResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->user;
        
        // Attempt to split name into First/Last
        $parts = explode(' ', $user->name ?? '', 2);
        $firstName = $parts[0] ?? '';
        $lastName = $parts[1] ?? '';

        // compute average rating and count
        $average = $this->ratings()->avg('score') ?: 0;
        $count = $this->ratings()->count();

        return [
            'id' => $this->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $user->email ?? '',
            // Profile fields come from User model
            'dni' => $user->dni ?? '',
            'phone' => $user->phone ?? '',
            'address' => $user->address ?? '',
            'city' => $user->city ?? '',
            'availability_date' => $this->availability_date,
            'average_rating' => round($average, 1),
            'reviews_count' => $count,
        ];
    }
}
