<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_id',
        'mac_address',
        'ip_address',
        'device_name',
        'is_active',
        'first_authorized_at',
        'last_seen_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'first_authorized_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }
}
