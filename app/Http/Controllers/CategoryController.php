<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Colocation;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('not.banned');
    }

    public function index(Colocation $colocation)
    {
        $this->authorize('view', $colocation);

        $categories = $colocation->categories()
            ->withCount('expenses')
            ->withSum('expenses', 'amount')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('colocation', 'categories'));
    }

    public function create(Colocation $colocation)
    {
        return redirect()
            ->route('colocations.show', $colocation)
            ->with('info', 'Ajoutez les categories depuis la page de la colocation.');
    }

    public function store(Request $request, Colocation $colocation)
    {
        $this->authorize('manage', $colocation);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
        ]);

        $existingCategory = $colocation->categories()
            ->where('name', $validated['name'])
            ->first();

        if ($existingCategory) {
            return back()->withErrors([
                'name' => 'Cette categorie existe deja dans cette colocation.',
            ]);
        }

        $color = $validated['color'] ?? null;
        $color = is_string($color) && trim($color) === '' ? null : $color;

        Category::create([
            'name' => $validated['name'],
            'color' => $color,
            'colocation_id' => $colocation->id,
        ]);

        return redirect()->route('colocations.show', $colocation)
            ->with('success', 'Categorie creee avec succes !');
    }

    public function edit(Colocation $colocation, Category $category)
    {
        $this->authorize('manage', $colocation);

        if ($category->colocation_id !== $colocation->id) {
            abort(404);
        }

        return view('categories.edit', compact('colocation', 'category'));
    }

    public function update(Request $request, Colocation $colocation, Category $category)
    {
        $this->authorize('manage', $colocation);

        if ($category->colocation_id !== $colocation->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
        ]);

        $existingCategory = $colocation->categories()
            ->where('name', $validated['name'])
            ->where('id', '!=', $category->id)
            ->first();

        if ($existingCategory) {
            return back()->withErrors([
                'name' => 'Cette categorie existe deja dans cette colocation.',
            ]);
        }

        $color = $validated['color'] ?? null;
        $color = is_string($color) && trim($color) === '' ? null : $color;

        $category->update([
            'name' => $validated['name'],
            'color' => $color,
        ]);

        return redirect()->route('colocations.show', $colocation)
            ->with('success', 'Categorie modifiee avec succes !');
    }

    public function destroy(Colocation $colocation, Category $category)
    {
        $this->authorize('manage', $colocation);

        if ($category->colocation_id !== $colocation->id) {
            abort(404);
        }

        if ($category->expenses()->exists()) {
            return back()->with('error', 'Impossible de supprimer une categorie contenant des depenses.');
        }

        $category->delete();

        return back()->with('success', 'Categorie supprimee avec succes !');
    }
}
