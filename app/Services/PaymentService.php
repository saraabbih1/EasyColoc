<?php

namespace App\Services;

use App\Models\Colocation;
use App\Models\Payment;
use App\Models\Settlement;

class PaymentService
{
    public function recordFromSettlement(Settlement $settlement): Payment
    {
        return Payment::create([
            'from_user_id' => $settlement->debtor_id,
            'to_user_id' => $settlement->creditor_id,
            'colocation_id' => $settlement->colocation_id,
            'paid_at' => now(),
            'amount' => $settlement->amount,
        ]);
    }

    public function recordManual(Colocation $colocation, int $fromUserId, int $toUserId, float $amount): Payment
    {
        return Payment::create([
            'from_user_id' => $fromUserId,
            'to_user_id' => $toUserId,
            'colocation_id' => $colocation->id,
            'paid_at' => now(),
            'amount' => $amount,
        ]);
    }
}
