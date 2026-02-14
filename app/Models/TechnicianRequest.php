<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechnicianRequest extends Model
{
    public const TYPE_TECHNICAL_SERVICE = 'technical_service';

    public const STATUS_PENDING = 'pending';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'requesting_user_id',
        'technician_id',
        'category_id',
        'claim_id',
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

    public function claim()
    {
        return $this->belongsTo(Claim::class);
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

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_ASSIGNED,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    public static function storeRules(): array
    {
        return [
            'technician_id' => 'nullable|exists:technicians,id',
            'category_id' => 'nullable|exists:categories,id',
            'claim_id' => 'nullable|exists:claims,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'wanted_date_start' => 'required|date',
            'wanted_date_end' => 'required|date|after_or_equal:wanted_date_start',
            'time_shift' => 'required|string',
        ];
    }

    public static function statusUpdateRules(): array
    {
        return [
            'status' => 'required|in:' . implode(',', self::statuses()),
        ];
    }

    public static function updateRules(): array
    {
        return [
            'subject' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'category_id' => 'nullable|exists:categories,id',
            'claim_id' => 'nullable|exists:claims,id',
            'status' => 'sometimes|in:' . implode(',', self::statuses()),
            'technician_id' => 'nullable|exists:technicians,id',
            'wanted_date_start' => 'nullable|date',
            'wanted_date_end' => 'nullable|date|after_or_equal:wanted_date_start',
            'time_shift' => 'nullable|string',
        ];
    }
}
