<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActiveSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_id',
        'ip_address',
        'mac_address',
        'device_name',
        'bytes_down',
        'bytes_up',
        'connected_at',
        'status',
    ];

    protected $casts = [
        'connected_at' => 'datetime',
        'bytes_down' => 'integer',
        'bytes_up' => 'integer',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }
}
