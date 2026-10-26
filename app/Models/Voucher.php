<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'package_id',
        'amount',
        'customer_name',
        'customer_phone',
        'status',
        'cashier',
        'batch_id',
        'activated_at',
        'expires_at',
        'mac_address',
        'notes',
    ];

    protected $casts = [
        'amount' => 'integer',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function activeSession(): HasOne
    {
        return $this->hasOne(ActiveSession::class);
    }

    public function activeSessions(): HasMany
    {
        return $this->hasMany(ActiveSession::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(VoucherDevice::class);
    }

    public function activeDevices(): HasMany
    {
        return $this->hasMany(VoucherDevice::class)->where('is_active', true);
    }

    public function getFormattedAmountAttribute(): string
    {
        $currency = $this->user ? ($this->user->currency ?? 'TZS') : 'TZS';

        return $currency.' '.number_format($this->amount);
    }

    public function isExpired(): bool
    {
        if ($this->status === 'expired') {
            return true;
        }

        if ($this->expires_at && Carbon::now()->greaterThan($this->expires_at)) {
            return true;
        }

        return false;
    }

    public function isRevoked(): bool
    {
        return $this->status === 'revoked';
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ! $this->isExpired();
    }

    public function getDeviceLimitAttribute(): int
    {
        return $this->package ? (int) $this->package->device_limit : 1;
    }

    public function getRegisteredDeviceCountAttribute(): int
    {
        return $this->devices()->count();
    }

    /**
     * Generate unique voucher code formatted like LH-XXXX-XXXX or PREFIX-XXXX-XXXX
     */
    public static function generateCode(?string $prefix = null): string
    {
        $prefix = $prefix ? strtoupper(trim($prefix)) : 'LH';
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        do {
            $part1 = '';
            $part2 = '';
            for ($i = 0; $i < 4; $i++) {
                $part1 .= $chars[random_int(0, strlen($chars) - 1)];
                $part2 .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $code = "{$prefix}-{$part1}-{$part2}";
        } while (static::where('code', $code)->exists());

        return $code;
    }
}
