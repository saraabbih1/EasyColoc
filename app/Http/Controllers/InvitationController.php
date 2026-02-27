<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Membership;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Mail\InvitationMail;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ColocationController;


class InvitationController extends Controller
{
    //envoi l'inv
    public function store(Request $request, $colocationId)
{
    $request->validate([
        'email' => 'required|email'
    ]);

    $user = \App\Models\User::where('email', $request->email)->first();

    if ($user) {
        $alreadyMember = \App\Models\Membership::where('user_id', $user->id)
            ->where('colocation_id', $colocationId)
            ->where('status', 'active')
            ->exists();

        if ($alreadyMember) {
            return back()->with('error', 'Cet utilisateur est déjà membre.');
        }
    }

    // Create invitation
    Invitation::create([
        'email' => $request->email,
        'colocation_id' => $colocationId,
        'token' => Str::random(40),
        'expires_at' => now()->addDays(2),
        'status' => 'pending',
        'expires_at' => now()->addDays(2),
    ]);
   $invitation = Invitation::create([
    'email' => $request->email,
    'colocation_id' => $colocationId,
    'token' => Str::random(40),
    'expires_at' => now()->addDays(2),
    'status' => 'pending',
]);

// Envoyer l’email
Mail::to($request->email)->send(new InvitationMail($invitation));
    return back()->with('success', 'Invitation envoyée');
}

    //accpte
    public function accept($token)
    {
        $invitation = Invitation::where('token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        Membership::create([
            'user_id' => auth()->id(),
            'colocation_id' => $invitation->colocation_id,
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now()
        ]);

        $invitation->update([
            'status' => 'accepted'
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Invitation acceptée !');
    }
    public function refuse($token)
    {
        $invitation = Invitation::where('token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        if ($invitation->email !== auth()->user()->email) {
            abort(403);
        }

        $invitation->update(['status' => 'refused']);

        return redirect()->route('dashboard')
            ->with('success', 'Invitation refusée.');
    }
}
