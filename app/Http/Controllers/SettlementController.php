<?php

namespace App\Http\Controllers;

use App\Models\Colocation;
use App\Models\Settlement;
use App\Services\SettlementService;

class SettlementController extends Controller
{
    public function __construct(private readonly SettlementService $settlementService)
    {
        $this->middleware('auth');
        $this->middleware('not.banned');
    }

    public function index(Colocation $colocation)
    {
        $this->authorize('view', $colocation);

        $settlements = $this->settlementService->getPendingForColocation($colocation);
        $summary = $this->settlementService->buildSummary($colocation);

        return view('settlements.index', compact('colocation', 'settlements', 'summary'));
    }

    public function markAsPaid(Colocation $colocation, Settlement $settlement)
    {
        $this->authorize('markAsPaid', $settlement);

        if ((int) $settlement->colocation_id !== (int) $colocation->id) {
            abort(404);
        }

        $this->settlementService->markAsPaid($settlement);

        return back()->with('success', 'Paiement marque comme effectue.');
    }

    public function optimize(Colocation $colocation)
    {
        $this->authorize('manage', $colocation);

        $this->settlementService->optimize($colocation);

        return back()->with('success', 'Dettes optimisees avec succes.');
    }
}
