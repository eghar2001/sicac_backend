<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Claim extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_ANSWERED = 'answered';

    protected $fillable = [
        'requesting_user_id',
        'category_id',
        'status',
        'subject',
        'description',
        'answer',
        'answered_at',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function requestingUser()
    {
        return $this->belongsTo(User::class, 'requesting_user_id');
    }

    public function isUser(User $user): bool
    {
        return (int) $this->requesting_user_id === (int) $user->id;
    }

    public function technicianRequests()
    {
        return $this->hasMany(TechnicianRequest::class);
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
            self::STATUS_ANSWERED,
        ];
    }

    public function setAnswer(?string $answer): bool
    {
        $this->answer = $answer;
        $this->answered_at = now();
        $this->status = self::STATUS_ANSWERED;

        return $this->save();
    }

    public static function storeRules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
        ];
    }

    public static function updateRules(): array
    {
        return [
            'subject' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'category_id' => 'sometimes|exists:categories,id',
            'status' => 'sometimes|in:' . implode(',', self::statuses()),
        ];
    }

    public static function statusUpdateRules(): array
    {
        return [
            'status' => 'required|in:' . implode(',', self::statuses()),
        ];
    }
}
