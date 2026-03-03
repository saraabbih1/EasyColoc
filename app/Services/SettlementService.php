<?php

namespace App\Services;

use App\Models\Colocation;
use App\Models\Settlement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SettlementService
{
    public function __construct(
        private readonly BalanceCalculatorService $balanceCalculatorService,
        private readonly PaymentService $paymentService
    ) {
    }

    public function getPendingForColocation(Colocation $colocation): Collection
    {
        return $colocation->pendingSettlements()
            ->with(['debtor', 'creditor'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function buildSummary(Colocation $colocation): array
    {
        $balances = $this->balanceCalculatorService->calculateBalances($colocation);
        $summary = [];

        foreach ($balances as $userId => $balance) {
            if ((float) $balance === 0.0) {
                continue;
            }

            $user = $colocation->members()->firstWhere('id', $userId);
            if (!$user) {
                continue;
            }

            $summary[$userId] = [
                'user' => $user,
                'balance' => $balance,
                'formatted_balance' => number_format(abs((float) $balance), 2, ',', ' ') . ' MAD',
                'type' => $balance > 0 ? 'creditor' : 'debtor',
            ];
        }

        return $summary;
    }

    public function markAsPaid(Settlement $settlement): void
    {
        DB::transaction(function () use ($settlement) {
            $settlement->markAsPaid();
            $this->paymentService->recordFromSettlement($settlement);
        });
    }

    public function optimize(Colocation $colocation): void
    {
        $optimized = $this->balanceCalculatorService->optimizeSettlements($colocation);

        DB::transaction(function () use ($colocation, $optimized) {
            Settlement::where('colocation_id', $colocation->id)->delete();

            foreach ($optimized as $settlement) {
                Settlement::create([
                    'colocation_id' => $colocation->id,
                    'debtor_id' => $settlement['debtor_id'],
                    'creditor_id' => $settlement['creditor_id'],
                    'amount' => $settlement['amount'],
                    'status' => 'pending',
                ]);
            }
        });
    }
}
