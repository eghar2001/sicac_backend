<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subfamily extends Model
{
    protected $guarded = [];

    protected $casts = [
        'work_cost_points' => 'decimal:2',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
