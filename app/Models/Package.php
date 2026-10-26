<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'duration_hours',
        'price',
        'speed_limit',
        'device_limit',
        'badge_label',
        'icon',
        'description',
        'is_active',
    ];

    protected $casts = [
        'duration_hours' => 'integer',
        'price' => 'integer',
        'device_limit' => 'integer',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        $currency = $this->user ? ($this->user->currency ?? 'TZS') : 'TZS';

        return $currency.' '.number_format($this->price);
    }
}
