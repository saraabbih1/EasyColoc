<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'colocation_id',
        'expense_id',
        'paid_at',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function colocation()
    {
        return $this->belongsTo(Colocation::class);
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }
}
