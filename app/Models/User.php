<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function networkDevices(): HasMany
    {
        return $this->hasMany(NetworkDevice::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isVendor(): bool
    {
        return $this->role === 'vendor';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isVerified(): bool
    {
        return ! is_null($this->email_verified_at);
    }

    /**
     * Return the effective business owner ID (if staff, returns parent_id).
     */
    public function getEffectiveUserId(): int
    {
        if ($this->isStaff() && $this->parent_id) {
            return $this->parent_id;
        }

        return $this->id;
    }

    /**
     * Get effective business owner model.
     */
    public function getEffectiveUser(): self
    {
        if ($this->isStaff() && $this->parent_id && $this->parent) {
            return $this->parent;
        }

        return $this;
    }

    /**
     * Return the captive portal public URL for this user.
     */
    public function getPortalUrlAttribute(): string
    {
        $slug = $this->portal_slug ?: Str::slug($this->business_name ?: 'hotspot-'.$this->id);

        return url('/portal/'.$slug);
    }
}
