<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Technician extends Model
{
    protected $fillable = [
        'user_id',
        'dni',
        'phone',
        'address',
        'city',
        'availability_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
