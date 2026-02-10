<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = [
        'technician_id',
        'user_id',
        'technician_request_id',
        'score',
        'description',
    ];

    public function technician()
    {
        return $this->belongsTo(Technician::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function technicianRequest()
    {
        return $this->belongsTo(TechnicianRequest::class, 'technician_request_id');
    }
}
