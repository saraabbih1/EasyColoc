<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Colocation;
use App\Models\Expense;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $membership = $user->memberships()->where('status', 'active')->with('colocation')->first();
        $colocation = $membership ? $membership->colocation : null;

        $balances = [];
        $settlements = [];

        if ($colocation) {
            $members = $colocation->memberships->where('status', 'active');
            $totalExpenses = $colocation->expenses->sum('amount');
            $membersCount = $members->count();
            $share = $membersCount > 0 ? $totalExpenses / $membersCount : 0;

            foreach ($members as $m) {
                $paid = $colocation->expenses->where('user_id', $m->user->id)->sum('amount');
                $balances[] = [
                    'name' => $m->user->name,
                    'paid' => $paid,
                    'balance' => $paid - $share
                ];
            }

            $debtors = collect($balances)->where('balance', '<', 0)->values();
            $creditors = collect($balances)->where('balance', '>', 0)->values();

            foreach ($debtors as $debtor) {
                foreach ($creditors as $creditor) {
                    if ($debtor['balance'] == 0) break;
                    $amount = min(abs($debtor['balance']), $creditor['balance']);
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

        return view('dashboard', compact('user', 'colocation', 'balances', 'settlements'));
    }
}