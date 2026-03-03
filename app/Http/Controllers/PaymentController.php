<?php

namespace App\Http\Controllers;

use App\Models\Colocation;
use App\Models\Payment;
use App\Models\Settlement;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService)
    {
        $this->middleware('auth');
        $this->middleware('not.banned');
    }

    public function store(Request $request, Colocation $colocation)
    {
        $this->authorize('create', [Payment::class, $colocation]);

        $validated = $request->validate([
            'to_user_id' => ['required', 'integer', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
        ]);

        $isMember = $colocation->memberships()
            ->where('user_id', $validated['to_user_id'])
            ->where('status', 'active')
            ->exists();

        if (!$isMember) {
            return back()->withErrors(['to_user_id' => 'Le beneficiaire doit etre membre actif de la colocation.']);
        }

        $settlement = Settlement::query()
            ->where('colocation_id', $colocation->id)
            ->where('debtor_id', auth()->id())
            ->where('creditor_id', $validated['to_user_id'])
            ->where('status', 'pending')
            ->orderBy('amount', 'desc')
            ->first();

        if (!$settlement) {
            return back()->withErrors(['amount' => 'Aucune dette en attente pour ce paiement.']);
        }

        $amount = min((float) $validated['amount'], (float) $settlement->amount);

        DB::transaction(function () use ($colocation, $settlement, $amount): void {
            $this->paymentService->recordManual(
                $colocation,
                auth()->id(),
                (int) $settlement->creditor_id,
                $amount
            );

            $remaining = round((float) $settlement->amount - $amount, 2);
            if ($remaining <= 0.009) {
                $settlement->update(['status' => 'paid', 'amount' => 0]);
            } else {
                $settlement->update(['amount' => $remaining]);
            }
        });

        if ($settlement->fresh()?->status === 'paid') {
            return redirect()
                ->route('colocations.settlement.show', $colocation)
                ->with('success', 'Paiement enregistre et dette marquee comme payee.');
        }

        return redirect()
            ->route('colocations.settlement.show', $colocation)
            ->with('success', 'Paiement enregistre avec succes.');
    }
}
