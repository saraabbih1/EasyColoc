<?php

namespace App\Http\Controllers;

use App\Models\Colocation;
use App\Models\Invitation;
use App\Models\Membership;
use App\Services\InvitationService;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
    public function __construct(private readonly InvitationService $invitationService)
    {
        $this->middleware(['auth', 'not.banned'])->except('accept');
    }

    public function accept(string $token)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $invitation = Invitation::query()
            ->where('token', $token)
            ->pending()
            ->notExpired()
            ->with('colocation')
            ->firstOrFail();

        $user = Auth::user();

        if (!$this->invitationService->assertInvitationEmailMatchesUser($invitation, $user)) {
            return redirect()->route('dashboard')
                ->with('error', "L'email du compte ne correspond pas a l'invitation.");
        }

        if ($user->hasActiveColocation()) {
            return redirect()->route('dashboard')
                ->with('error', 'Vous avez deja une colocation active.');
        }

        if ($invitation->colocation->members()->count() >= 10) {
            return redirect()->route('dashboard')
                ->with('error', 'Cette colocation a atteint le nombre maximum de membres.');
        }

        Membership::create([
            'user_id' => $user->id,
            'colocation_id' => $invitation->colocation_id,
            'status' => 'active',
        ]);

        $invitation->accept();

        return redirect()->route('colocations.show', $invitation->colocation)
            ->with('success', 'Vous avez rejoint la colocation avec succes.');
    }

    public function refuse(string $token)
    {
        $invitation = Invitation::query()
            ->where('token', $token)
            ->pending()
            ->notExpired()
            ->firstOrFail();

        $user = Auth::user();
        if (!$this->invitationService->assertInvitationEmailMatchesUser($invitation, $user)) {
            return redirect()->route('dashboard')
                ->with('error', "L'email du compte ne correspond pas a l'invitation.");
        }

        $invitation->refuse();

        return redirect()->route('dashboard')
            ->with('success', 'Invitation refusee.');
    }

    public function destroy(Colocation $colocation, Invitation $invitation)
    {
        $this->authorize('manage', $colocation);

        if ((int) $invitation->colocation_id !== (int) $colocation->id) {
            abort(404);
        }

        $invitation->delete();

        return back()->with('success', 'Invitation supprimee avec succes.');
    }
}
