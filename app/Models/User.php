<?php

namespace App\Models;
use App\Models\Membership;
use App\Models\Expense;
use App\Models\Settlement;
use App\Models\Payment;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_banned',
        'reputation'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'is_banned' => 'boolean',
        ];
    }

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    public function activeMembership()
    {
        return $this->memberships()->where('status', 'active')->first();
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function settlementsAsDebtor()
    {
        return $this->hasMany(Settlement::class, 'debtor_id');
    }

    public function settlementsAsCreditor()
    {
        return $this->hasMany(Settlement::class, 'creditor_id');
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class, 'invited_by');
    }

    public function paymentsSent()
    {
        return $this->hasMany(Payment::class, 'from_user_id');
    }

    public function paymentsReceived()
    {
        return $this->hasMany(Payment::class, 'to_user_id');
    }

    public function hasActiveColocation(): bool
    {
        return $this->memberships()->where('status', 'active')->exists();
    }

    public function isGlobalAdmin(): bool
    {
        return $this->hasRole('global_admin');
    }

    public function isBanned(): bool
    {
        return (bool) $this->is_banned;
    }

    public function ban(): void
    {
        $this->update(['is_banned' => true]);
    }

    public function unban(): void
    {
        $this->update(['is_banned' => false]);
    }

    public function updateReputation(int $change): void
    {
        $this->increment('reputation', $change);
    }

    public function getPendingDebtsAttribute()
    {
        return $this->settlementsAsDebtor()->pending()->sum('amount');
    }

    public function getPendingCreditsAttribute()
    {
        return $this->settlementsAsCreditor()->pending()->sum('amount');
    }
}
