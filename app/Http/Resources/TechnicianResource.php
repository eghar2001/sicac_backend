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
        ];
    }
}
