<?php

namespace App\Http\Controllers;

use App\Models\Colocation;
use App\Models\Membership;
use Illuminate\Http\Request;

class ColocationController extends Controller
{
    public function create()
    {
        return view('colocations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $user = auth()->user();

        // Vérifier si user a déjà une colocation active
        $activeMembership = $user->memberships()
            ->where('status', 'active')
            ->exists();

        if ($activeMembership) {
            return back()->withErrors([
                'error' => 'Vous avez déjà une colocation active.'
            ]);
        }

        // Créer colocation
        $colocation = Colocation::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => 'active'
        ]);

        // Créer membership owner
        Membership::create([
            'user_id' => $user->id,
            'colocation_id' => $colocation->id,
            'role' => 'owner',
            'status' => 'active',
            'joined_at' => now()
        ]);

        return redirect()->route('colocations.show', $colocation);
    }

    public function show(Colocation $colocation)
    {
        return view('colocations.show', compact('colocation'));
    }
    public function members($id)
    {
        $colocation = Colocation::with('memberships.user')
            ->findOrFail($id);

        return view('colocations.members', compact('colocation'));
    }

}