<?php

namespace App\Services;

use App\Models\Colocation;
use App\Models\Expense;
use App\Models\Settlement;
use App\Models\Membership;

class BalanceCalculatorService
{
    public function calculateBalances(Colocation $colocation): array
    {
        $members = $colocation->activeMemberships()->with('user')->get();
        $balances = [];

        foreach ($members as $membership) {
            $balances[$membership->user_id] = 0;
        }

        $expenses = $colocation->expenses()->with('user')->get();
        $memberCount = count($members);

        foreach ($expenses as $expense) {
            $share = $expense->amount / $memberCount;
            $balances[$expense->user_id] += $expense->amount - $share;

            foreach ($members as $membership) {
                if ($membership->user_id !== $expense->user_id) {
                    $balances[$membership->user_id] -= $share;
                }
            }
        }

        return $balances;
    }

    public function recalculateBalances(Colocation $colocation): void
    {
        Settlement::where('colocation_id', $colocation->id)->delete();

        $balances = $this->calculateBalances($colocation);
        $this->createSettlements($colocation, $balances);
    }

    private function createSettlements(Colocation $colocation, array $balances): void
    {
        $debtors = [];
        $creditors = [];

        foreach ($balances as $userId => $balance) {
            if ($balance < -0.01) {
                $debtors[$userId] = abs($balance);
            } elseif ($balance > 0.01) {
                $creditors[$userId] = $balance;
            }
        }

        arsort($creditors);
        arsort($debtors);

        foreach ($creditors as $creditorId => $creditorAmount) {
            foreach ($debtors as $debtorId => $debtorAmount) {
                if ($creditorAmount < 0.01 || $debtorAmount < 0.01) {
                    continue;
                }

                $settlementAmount = min($creditorAmount, $debtorAmount);

                if ($settlementAmount > 0.01) {
                    Settlement::create([
                        'colocation_id' => $colocation->id,
                        'debtor_id' => $debtorId,
                        'creditor_id' => $creditorId,
                        'amount' => round($settlementAmount, 2),
                        'status' => 'pending'
                    ]);

                    $creditorAmount -= $settlementAmount;
                    $debtorAmount -= $settlementAmount;

                    $creditors[$creditorId] = $creditorAmount;
                    $debtors[$debtorId] = $debtorAmount;
                }
            }
        }
    }

    public function optimizeSettlements(Colocation $colocation): array
    {
        $balances = $this->calculateBalances($colocation);
        $optimizedSettlements = [];

        $debtors = [];
        $creditors = [];

        foreach ($balances as $userId => $balance) {
            if ($balance < -0.01) {
                $debtors[$userId] = abs($balance);
            } elseif ($balance > 0.01) {
                $creditors[$userId] = $balance;
            }
        }

        arsort($creditors);
        arsort($debtors);

        foreach ($creditors as $creditorId => $creditorAmount) {
            foreach ($debtors as $debtorId => $debtorAmount) {
                if ($creditorAmount < 0.01 || $debtorAmount < 0.01) {
                    continue;
                }

                $settlementAmount = min($creditorAmount, $debtorAmount);

                if ($settlementAmount > 0.01) {
                    $optimizedSettlements[] = [
                        'debtor_id' => $debtorId,
                        'creditor_id' => $creditorId,
                        'amount' => round($settlementAmount, 2)
                    ];

                    $creditorAmount -= $settlementAmount;
                    $debtorAmount -= $settlementAmount;
                }
            }
        }

        return $optimizedSettlements;
    }

    public function getUserBalance(Colocation $colocation, int $userId): float
    {
        $balances = $this->calculateBalances($colocation);
        return $balances[$userId] ?? 0;
    }

    public function getBalanceSummary(Colocation $colocation): array
    {
        $balances = $this->calculateBalances($colocation);
        $summary = [];

        foreach ($balances as $userId => $balance) {
            if (abs($balance) > 0.01) {
                $user = $colocation->members()
                    ->where('users.id', $userId)
                    ->first();
                $summary[] = [
                    'user' => $user,
                    'balance' => $balance,
                    'type' => $balance > 0 ? 'creditor' : 'debtor',
                    'formatted_amount' => number_format(abs($balance), 2, ',', ' ') . ' MAD'
                ];
            }
        }

        usort($summary, function ($a, $b) {
            return abs($b['balance']) - abs($a['balance']);
        });

        return $summary;
    }
}
