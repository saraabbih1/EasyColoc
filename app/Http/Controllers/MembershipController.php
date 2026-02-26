<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Colocation;
use App\Models\Membership;
use App\Models\User;

class MembershipController extends Controller
{
    // affichage de tout les membre de collection
    public function index(Colocation $colocation)
    {
        $memberships = $colocation->memberships()->with('user')->get();
        return view('colocations.members', compact('colocation', 'memberships'));
    }
// addition de membre 
    public function store(Request $request, Colocation $colocation)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string',
        ]);

        Membership::create([
            'user_id' => $request->user_id,
            'colocation_id' => $colocation->id,
            'role' => $request->role,
            'status' => 'active',
        ]);

        return redirect()->back()->with('success', 'Membre ajouté avec succès!');
    }

    // supp un memb
    public function destroy(Membership $membership)
    {
        $membership->delete();
        return redirect()->back()->with('success', 'Membre supprimé!');
    }
}