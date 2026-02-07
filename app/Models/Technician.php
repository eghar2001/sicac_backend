<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Technician extends Model
{
    protected $fillable = [
        'user_id',
        'availability_date'
    ];



    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function availableShifts()
    {
        return $this->hasMany(AvailableShift::class);
    }

    public function claims()
    {
        return $this->hasMany(Claim::class, 'assigned_technician_id');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
}
