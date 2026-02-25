<?php

namespace App\Http\Controllers;


use App\Models\Membership;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
 

class ExpenseController extends Controller
{
    // Afficher le formulaire pour créer une dépense
    public function create()
    {
        // Récupérer la colocation active de l'utilisateur
        $membership = Auth::user()->memberships()->where('status', 'active')->first();
        $colocation = $membership ? $membership->colocation : null;

        if(!$colocation) {
            return redirect()->route('dashboard')->with('error', 'Vous n’avez pas de colocation active.');
        }

        $categories = $colocation->categories;

        return view('expenses.create', compact('colocation', 'categories'));
    }

    // Enregistrer la dépense
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'expense_date' => 'required|date',
            'category_id' => 'required|exists:categories,id',
        ]);

        $membership = Auth::user()->memberships()->where('status', 'active')->first();
        $colocation = $membership->colocation;

        Expense::create([
            'title' => $request->title,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'user_id' => Auth::id(),
            'colocation_id' => $colocation->id,
            'category_id' => $request->category_id,
        ]);

        return redirect()->route('dashboard')->with('success', 'Dépense ajoutée !');
    }
}