<?php

namespace App\Services;

use App\Models\Colocation;
use App\Models\Membership;
use App\Models\Settlement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ColocationService
{
    public function createColocation(User $owner, array $attributes): Colocation
    {
        return DB::transaction(function () use ($owner, $attributes) {
            $colocation = Colocation::create([
                'name' => $attributes['name'],
                'description' => $attributes['description'] ?? null,
                'status' => 'active',
                'owner_id' => $owner->id,
            ]);

            Membership::create([
                'user_id' => $owner->id,
                'colocation_id' => $colocation->id,
                'status' => 'active',
            ]);

            return $colocation;
        });
    }

    public function removeMember(Colocation $colocation, Membership $membership): void
    {
        DB::transaction(function () use ($colocation, $membership) {
            $member = $membership->user;

            $pendingDebts = Settlement::query()
                ->where('colocation_id', $colocation->id)
                ->where('debtor_id', $member->id)
                ->where('status', 'pending')
                ->sum('amount');

            if ($pendingDebts > 0) {
                $member->updateReputation(-1);

                // Regle speciale: si owner retire un debiteur, la dette est imputee a l'owner.
                Settlement::query()
                    ->where('colocation_id', $colocation->id)
                    ->where('debtor_id', $member->id)
                    ->where('status', 'pending')
                    ->update(['debtor_id' => $colocation->owner_id]);
            } else {
                $member->updateReputation(1);
            }

            $membership->leave();
        });
    }

    public function leaveColocation(Colocation $colocation, User $user): void
    {
        DB::transaction(function () use ($colocation, $user) {
            $membership = $user->memberships()
                ->where('colocation_id', $colocation->id)
                ->where('status', 'active')
                ->firstOrFail();

            $pendingDebts = $user->settlementsAsDebtor()
                ->where('colocation_id', $colocation->id)
                ->pending()
                ->sum('amount');

            $user->updateReputation($pendingDebts > 0 ? -1 : 1);
            $membership->leave();
        });
    }

    public function cancelColocation(Colocation $colocation): void
    {
        DB::transaction(function () use ($colocation) {
            $membersWithDebts = $colocation->activeMemberships()
                ->whereHas('user.settlementsAsDebtor', function ($query) use ($colocation) {
                    $query->where('colocation_id', $colocation->id)
                        ->where('status', 'pending');
                })
                ->with('user')
                ->get();

            foreach ($membersWithDebts as $membership) {
                $membership->user->updateReputation(-1);
            }

            $colocation->update(['status' => 'cancelled']);
            $colocation->memberships()->update([
                'status' => 'cancelled',
                'left_at' => now(),
            ]);
        });
    }
}
