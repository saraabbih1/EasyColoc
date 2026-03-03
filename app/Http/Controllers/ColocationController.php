<?php

namespace App\Http\Controllers;

use App\Http\Requests\InviteMemberRequest;
use App\Http\Requests\StoreColocationRequest;
use App\Models\Colocation;
use App\Models\Invitation;
use App\Models\Membership;
use App\Services\BalanceCalculatorService;
use App\Services\ColocationService;
use App\Services\InvitationService;
use Illuminate\Support\Facades\Auth;

class ColocationController extends Controller
{
    public function __construct(
        private readonly ColocationService $colocationService,
        private readonly InvitationService $invitationService
    ) {
        $this->middleware('auth');
        $this->middleware('not.banned');
    }

    public function create()
    {
        $this->authorize('create', Colocation::class);

        return view('colocations.create');
    }

    public function store(StoreColocationRequest $request)
    {
        $this->authorize('create', Colocation::class);

        $colocation = $this->colocationService->createColocation(Auth::user(), $request->validated());

        return redirect()->route('colocations.show', $colocation)
            ->with('success', 'Colocation creee avec succes.');
    }

    public function show(Colocation $colocation)
    {
        $this->authorize('view', $colocation);

        $user = auth()->user();

        $isOwner = ((int) $colocation->owner_id === (int) $user->id);
        $isMember = $colocation->memberships()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
        $isAdmin = $user->isGlobalAdmin();
        $canManage = $isOwner || $isAdmin;

        $members = $colocation->activeMembers()->get();
        $expenses = $colocation->expenses()->with(['category', 'payer', 'payments'])->latest()->get();
        $settlements = $colocation->settlements()->where('status', 'pending')->get();

        $balances = app(BalanceCalculatorService::class)->calculateBalances($colocation);

        return view('colocations.show', compact(
            'colocation',
            'balances',
            'members',
            'expenses',
            'settlements',
            'isOwner',
            'isMember',
            'canManage'
        ));
    }

    public function members(Colocation $colocation)
    {
        $this->authorize('manage', $colocation);

        $user = auth()->user();
        $isOwner = ((int) $colocation->owner_id === (int) $user->id);
        $isAdmin = $user->isGlobalAdmin();
        $canManage = $isOwner || $isAdmin;

        $memberships = $colocation->memberships()
            ->where('status', 'active')
            ->with('user')
            ->get();

        $invitations = $colocation->invitations()
            ->where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with('inviter')
            ->latest()
            ->get();

        return view('colocations.members', compact('colocation', 'memberships', 'invitations', 'isOwner', 'canManage'));
    }

    public function invite(InviteMemberRequest $request, Colocation $colocation)
    {
        $this->authorize('invite', $colocation);

        if ($colocation->activeMembers()->count() >= 10) {
            return back()->with('error', 'Le nombre maximum de membres est atteint (10).');
        }

        $email = mb_strtolower(trim($request->validated('email')));

        $isAlreadyMember = $colocation->memberships()
            ->where('status', 'active')
            ->whereHas('user', fn ($query) => $query->where('email', $email))
            ->exists();

        if ($isAlreadyMember) {
            return back()->with('error', 'Cet utilisateur est deja membre actif de la colocation.');
        }

        $existingInvitation = $this->invitationService->findPendingValid($colocation, $email);
        if ($existingInvitation) {
            return back()->with('error', 'Une invitation a deja ete envoyee a cette adresse.');
        }

        $invitation = $this->invitationService->createPending($colocation, $email, auth()->id());
        $emailSent = $this->invitationService->sendInvitationEmail($invitation);

        if (!$emailSent) {
            return back()->with('error', "L'invitation a ete creee, mais l'email n'a pas pu etre envoye. Verifiez la configuration SMTP.");
        }

        return back()->with('success', 'Invitation envoyee avec succes.');
    }

    public function destroyInvitation(Colocation $colocation, Invitation $invitation)
    {
        $this->authorize('manage', $colocation);

        if ((int) $invitation->colocation_id !== (int) $colocation->id) {
            abort(404);
        }

        $invitation->delete();

        return back()->with('success', 'Invitation supprimee avec succes.');
    }

    public function removeMember(Colocation $colocation, Membership $membership)
    {
        $this->authorize('removeMember', $colocation);

        if ($membership->isOwner()) {
            return back()->with('error', 'Vous ne pouvez pas retirer le proprietaire de la colocation.');
        }

        $this->colocationService->removeMember($colocation, $membership);

        return back()->with('success', 'Membre retire avec succes.');
    }

    public function leave(Colocation $colocation)
    {
        $this->authorize('leave', $colocation);

        $this->colocationService->leaveColocation($colocation, Auth::user());

        return redirect()->route('dashboard')
            ->with('success', 'Vous avez quitte la colocation.');
    }

    public function cancel(Colocation $colocation)
    {
        $this->authorize('cancel', $colocation);

        $this->colocationService->cancelColocation($colocation);

        return redirect()->route('dashboard')
            ->with('success', 'Colocation annulee avec succes.');
    }
}
