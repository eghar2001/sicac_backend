<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechnicianRequest extends Model
{
    protected $fillable = [
        'requesting_user_id',
        'technician_id',
        'category_id',
        'subject',
        'description',
        'wanted_date_start',
        'wanted_date_end',
        'time_shift',
    ];

    use Concerns\UseCachedRelations;

    protected $cachedRelationships = [
        'timeShift' => [
            'class' => TimeShift::class,
            'foreign_key' => 'time_shift',
        ],
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function requestingUser()
    {
        return $this->belongsTo(User::class, 'requesting_user_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
