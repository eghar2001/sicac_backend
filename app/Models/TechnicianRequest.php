<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechnicianRequest extends Model
{
    protected $fillable = [
        'requesting_user_id',
        'technician_id',
        'category_id',
        'type',
        'status',
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
        return $this->belongsTo(Technician::class);
    }

    public function rating()
    {
        return $this->hasOne(Rating::class, 'technician_request_id');
    }

    public function isUser(User $user): bool
    {
        return (int) $this->requesting_user_id === (int) $user->id;
    }

    public function isTechnician(Technician $technician): bool
    {
        return $this->technician_id !== null
            && (int) $this->technician_id === (int) $technician->id;
    }

    public function hasRating(): bool
    {
        return $this->rating()->exists();
    }
}
