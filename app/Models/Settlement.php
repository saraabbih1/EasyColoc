<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Settlement extends Model
{
    protected $fillable = [
        'colocation_id',
        'debtor_id',
        'creditor_id',
        'amount',
        'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function colocation()
    {
        return $this->belongsTo(Colocation::class);
    }

    public function debtor()
    {
        return $this->belongsTo(User::class, 'debtor_id');
    }

    public function creditor()
    {
        return $this->belongsTo(User::class, 'creditor_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    public function scopeBetweenUsers(Builder $query, int $debtorId, int $creditorId): Builder
    {
        return $query->where('debtor_id', $debtorId)
                    ->where('creditor_id', $creditorId);
    }

    public function markAsPaid(): void
    {
        $this->update(['status' => 'paid']);
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2, ',', ' ') . ' €';
    }

    public function getDescriptionAttribute()
    {
        return "{$this->debtor->name} doit {$this->formatted_amount} à {$this->creditor->name}";
    }
}
