<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailableShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'technician_id',
        'week_day',
        'shift_name',
    ];

    public function technician()
    {
        return $this->belongsTo(Technician::class);
    }
}
