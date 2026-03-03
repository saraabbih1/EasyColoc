<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Colocation extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
        'owner_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    public function activeMemberships()
    {
        return $this->memberships()->where('status', 'active');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function pendingInvitations()
    {
        return $this->invitations()->where('status', 'pending');
    }

    public function settlements()
    {
        return $this->hasMany(Settlement::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function pendingSettlements()
    {
        return $this->settlements()->where('status', 'pending');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'memberships')
            ->withPivot(['status', 'left_at'])
            ->withTimestamps();
    }

    public function activeMembers()
    {
        return $this->belongsToMany(User::class, 'memberships')
            ->wherePivot('status', 'active')
            ->withPivot(['status', 'left_at'])
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', 'cancelled');
    }

    public function getTotalExpensesAttribute()
    {
        return $this->expenses()->sum('amount');
    }

    public function getMemberCountAttribute()
    {
        return $this->activeMemberships()->count();
    }
}
