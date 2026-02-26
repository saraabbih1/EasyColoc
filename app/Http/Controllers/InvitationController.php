<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Membership;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        Invitation::create([
            'colocation_id' => $colocationId,
            'email' => $request->email,
            'token' => Str::random(32),
            'status' => 'pending'
        ]);

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
}