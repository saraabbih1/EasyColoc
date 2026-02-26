<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Colocation;
use App\Models\Expense;
use App\Models\Invitation;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $membership = $user->memberships()
            ->where('status', 'active')
            ->with('colocation.expenses', 'colocation.memberships.user')
            ->first();

        $colocation = $membership ? $membership->colocation : null;

        $balances = [];
        $invitations = collect();
        $settlements = [];

        if ($colocation) {

        //njibo invetation mra wahda
            $invitations = Invitation::where('colocation_id', $colocation->id)
                ->latest()
                ->get();

            $members = $colocation->memberships->where('status', 'active');
            $totalExpenses = $colocation->expenses->sum('amount');
            $membersCount = $members->count();
            $share = $membersCount > 0 ? $totalExpenses / $membersCount : 0;

            foreach ($members as $m) {
                $paid = $colocation->expenses
                    ->where('user_id', $m->user->id)
                    ->sum('amount');

                $balances[] = [
                    'name' => $m->user->name,
                    'paid' => $paid,
                    'balance' => $paid - $share
                ];
            }

            //  Settlement Algorithm 
            $debtors = collect($balances)->where('balance', '<', 0)->values();
            $creditors = collect($balances)->where('balance', '>', 0)->values();

            foreach ($debtors as &$debtor) {
                foreach ($creditors as &$creditor) {

                    if ($debtor['balance'] >= 0) break;

                    $amount = min(
                        abs($debtor['balance']),
                        $creditor['balance']
                    );

                    if ($amount > 0) {
                        $settlements[] = [
                            'from' => $debtor['name'],
                            'to' => $creditor['name'],
                            'amount' => $amount
                        ];

                        $debtor['balance'] += $amount;
                        $creditor['balance'] -= $amount;
                    }
                }
            }
        }

        return view('dashboard', compact(
            'user',
            'colocation',
            'balances',
            'invitations',
            'settlements'
        ));
    }
}