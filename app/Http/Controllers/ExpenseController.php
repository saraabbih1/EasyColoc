<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Models\Expense;
use App\Models\Category;
use App\Models\Colocation;
use App\Services\BalanceCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('not.banned');
    }

    public function index(Colocation $colocation)
    {
        $this->authorize('view', $colocation);

        $expenses = $colocation->expenses()
            ->with(['category', 'user'])
            ->orderBy('expense_date', 'desc')
            ->paginate(20);

        $categories = $colocation->categories()->orderBy('name')->get();
        
        $monthFilter = request('month', now()->format('Y-m'));
        $categoryFilter = request('category');

        $query = $colocation->expenses()->with(['category', 'user']);

        if ($monthFilter) {
            $query->whereMonth('expense_date', substr($monthFilter, 5, 2))
                  ->whereYear('expense_date', substr($monthFilter, 0, 4));
        }

        if ($categoryFilter) {
            $query->where('category_id', $categoryFilter);
        }

        $filteredExpenses = $query->orderBy('expense_date', 'desc')->get();

        $monthlyStats = $colocation->expenses()
            ->selectRaw('YEAR(expense_date) as year, MONTH(expense_date) as month, SUM(amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $categoryStats = $colocation->expenses()
            ->selectRaw('category_id, SUM(amount) as total, COUNT(*) as count')
            ->with('category')
            ->groupBy('category_id')
            ->orderBy('total', 'desc')
            ->get();

        return view('expenses.index', compact(
            'colocation',
            'expenses',
            'categories',
            'filteredExpenses',
            'monthlyStats',
            'categoryStats',
            'monthFilter',
            'categoryFilter'
        ));
    }

    public function create(Colocation $colocation)
    {
        $this->authorize('create', [Expense::class, $colocation]);

        $categories = $colocation->categories()->orderBy('name')->get();

        return view('expenses.create', compact('colocation', 'categories'));
    }

    public function store(StoreExpenseRequest $request, Colocation $colocation)
    {
        $this->authorize('create', [Expense::class, $colocation]);

        $validated = $request->validated();

        $expense = Expense::create([
            'title' => $validated['title'],
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'colocation_id' => $colocation->id,
            'user_id' => Auth::id(),
            'category_id' => $validated['category_id']
        ]);

        $balanceCalculator = new BalanceCalculatorService();
        $balanceCalculator->recalculateBalances($colocation);

        return redirect()->route('expenses.index', $colocation)
            ->with('success', 'Dépense ajoutée avec succès !');
    }

    public function edit(Colocation $colocation, Expense $expense)
    {
        $this->authorize('update', $expense);

        $categories = $colocation->categories()->orderBy('name')->get();

        return view('expenses.edit', compact('colocation', 'expense', 'categories'));
    }

    public function update(Request $request, Colocation $colocation, Expense $expense)
    {
        $this->authorize('update', $expense);

        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01|max:99999.99',
            'expense_date' => 'required|date|before_or_equal:today',
            'category_id' => 'required|exists:categories,id'
        ]);

        $expense->update([
            'title' => $request->title,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'category_id' => $request->category_id
        ]);

        $balanceCalculator = new BalanceCalculatorService();
        $balanceCalculator->recalculateBalances($colocation);

        return redirect()->route('expenses.index', $colocation)
            ->with('success', 'Dépense modifiée avec succès !');
    }

    public function destroy(Colocation $colocation, Expense $expense)
    {
        $this->authorize('delete', $expense);

        $expense->delete();

        $balanceCalculator = new BalanceCalculatorService();
        $balanceCalculator->recalculateBalances($colocation);

        return redirect()->route('expenses.index', $colocation)
            ->with('success', 'Dépense supprimée avec succès !');
    }
}
