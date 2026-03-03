<?php

namespace App\Services;

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
            'amount' => $settlement->amount,
        ]);
    }
}
