<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Expense extends Model
{
    protected $fillable = [
        'title',
        'amount',
        'expense_date',
        'user_id',
        'colocation_id',
        'category_id'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function colocation()
    {
        return $this->belongsTo(Colocation::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeByMonth(Builder $query, string $month): Builder
    {
        return $query->whereMonth('expense_date', $month);
    }

    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeInColocation(Builder $query, int $colocationId): Builder
    {
        return $query->where('colocation_id', $colocationId);
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2, ',', ' ') . ' €';
    }
}
