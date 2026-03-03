<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class Invitation extends Model
{
    protected static ?bool $hasExpiresAtColumn = null;

    protected $fillable = [
        'colocation_id',
        'email',
        'token',
        'invited_by',
        'status',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invitation) {
            $invitation->token = Str::random(32);
            if (self::hasExpiresAtColumn()) {
                $invitation->expires_at = now()->addDays(7);
            }
        });
    }

    public function colocation()
    {
        return $this->belongsTo(Colocation::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeNotExpired(Builder $query): Builder
    {
        if (!self::hasExpiresAtColumn()) {
            return $query;
        }

        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function isExpired(): bool
    {
        if (!self::hasExpiresAtColumn()) {
            return false;
        }

        return $this->expires_at && $this->expires_at->isPast();
    }

    protected static function hasExpiresAtColumn(): bool
    {
        if (self::$hasExpiresAtColumn === null) {
            self::$hasExpiresAtColumn = Schema::hasColumn('invitations', 'expires_at');
        }

        return self::$hasExpiresAtColumn;
    }

    public function accept(): void
    {
        $this->update(['status' => 'accepted']);
    }

    public function refuse(): void
    {
        $this->update(['status' => 'refused']);
    }
}
