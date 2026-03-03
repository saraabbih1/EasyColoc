<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Membership extends Model
{
    protected $table = 'memberships';

    protected $fillable = [
        'user_id',
        'colocation_id',
        'status',
        'left_at'
    ];

    protected $casts = [
        'left_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function colocation()
    {
        return $this->belongsTo(Colocation::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeOwner(Builder $query): Builder
    {
        return $query->whereHas('colocation', function (Builder $query) {
            $query->whereColumn('colocations.owner_id', 'memberships.user_id');
        });
    }

    public function scopeMember(Builder $query): Builder
    {
        return $query->whereHas('colocation', function (Builder $query) {
            $query->whereColumn('colocations.owner_id', '!=', 'memberships.user_id');
        });
    }

    public function isOwner(): bool
    {
        return (int) $this->colocation?->owner_id === (int) $this->user_id;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function leave(): void
    {
        $this->update([
            'status' => 'left',
            'left_at' => now()
        ]);
    }
}
