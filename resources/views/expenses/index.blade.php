@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Dépenses</h1>
                        <p class="mt-1 text-gray-600">{{ $colocation->name }}</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('colocations.show', $colocation) }}" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </a>
                        @can('create', [\App\Models\Expense::class, $colocation])
                            <a href="{{ route('expenses.create', $colocation) }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Ajouter une dépense
                            </a>
                        @endcan
                        <a href="{{ route('colocations.settlement.show', $colocation) }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Remboursements
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Filters and Stats -->
            <div class="lg:col-span-1">
                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Filtres</h2>
                        
                        <form method="GET" class="space-y-4">
                            <div>
                                <label for="month" class="block text-sm font-medium text-gray-700">Mois</label>
                                <select name="month" id="month" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Tous les mois</option>
                                    @foreach($monthlyStats as $stat)
                                        <option value="{{ $stat->year }}-{{ str_pad($stat->month, 2, '0', STR_PAD_LEFT) }}" 
                                                {{ $monthFilter == $stat->year . '-' . str_pad($stat->month, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create($stat->year, $stat->month)->format('F Y') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700">Catégorie</label>
                                <select name="category" id="category" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Toutes les catégories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" 
                                                {{ $categoryFilter == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                </svg>
                                Filtrer
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Statistiques</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-600">Total des dépenses</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($filteredExpenses->sum('amount'), 2, ',', ' ') }} MAD</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-600">Nombre de dépenses</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $filteredExpenses->count() }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-600">Moyenne par dépense</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    {{ $filteredExpenses->count() > 0 ? number_format($filteredExpenses->sum('amount') / $filteredExpenses->count(), 2, ',', ' ') : '0,00' }} MAD
                                </p>
                            </div>
                        </div>
                        
                        @if($categoryStats->isNotEmpty())
                            <div class="mt-6">
                                <h3 class="text-sm font-medium text-gray-900 mb-3">Par catégorie</h3>
                                <div class="space-y-2">
                                    @foreach($categoryStats->take(5) as $stat)
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="w-2 h-2 rounded-full mr-2 bg-gray-400"></div>
                                                <span class="text-xs text-gray-600">{{ $stat->category->name }}</span>
                                            </div>
                                            <span class="text-xs font-medium text-gray-900">{{ number_format($stat->total, 2, ',', ' ') }} MAD</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Expenses List -->
            <div class="lg:col-span-3">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-medium text-gray-900">
                                Dépenses 
                                @if($monthFilter || $categoryFilter)
                                    <span class="text-sm text-gray-500">(filtrées)</span>
                                @endif
                            </h2>
                            <span class="text-sm text-gray-500">{{ $filteredExpenses->count() }} résultat(s)</span>
                        </div>
                        
                        @if($filteredExpenses->isEmpty())
                            <div class="text-center py-12 text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                                <p class="mt-2">Aucune dépense trouvée</p>
                                @can('create', [\App\Models\Expense::class, $colocation])
                                    <a href="{{ route('expenses.create', $colocation) }}" class="mt-2 inline-flex items-center text-indigo-600 hover:text-indigo-900">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Ajouter une dépense
                                    </a>
                                @endcan
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Dépense
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Payeur
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Montant
                                            </th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($filteredExpenses as $expense)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="w-2 h-2 rounded-full mr-3 bg-gray-400"></div>
                                                        <div>
                                                            <div class="text-sm font-medium text-gray-900">{{ $expense->title }}</div>
                                                            <div class="text-xs text-gray-500">{{ $expense->category->name ?? 'Non catégorisée' }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="w-6 h-6 bg-indigo-500 rounded-full flex items-center justify-center text-white text-xs font-semibold mr-2">
                                                            {{ strtoupper(substr($expense->user->name, 0, 1)) }}
                                                        </div>
                                                        <span class="text-sm text-gray-900">{{ $expense->user->name }}</span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $expense->expense_date->format('d/m/Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="text-sm font-semibold text-gray-900">{{ $expense->formatted_amount }}</span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <div class="flex items-center justify-end space-x-2">
                                                        @if($expense->isFullySettled())
                                                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-700">
                                                                Paid
                                                            </span>
                                                        @else
                                                            @can('pay', $expense)
                                                                <form method="POST" action="{{ route('colocations.payments.store', $colocation) }}">
                                                                    @csrf
                                                                    <input type="hidden" name="expense_id" value="{{ $expense->id }}">
                                                                    <button type="submit" class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-indigo-600 text-white hover:bg-indigo-700">
                                                                        Payer
                                                                    </button>
                                                                </form>
                                                            @endcan
                                                        @endif
                                                        @can('update', $expense)
                                                            <a href="{{ route('expenses.edit', [$colocation, $expense]) }}" 
                                                               class="text-indigo-600 hover:text-indigo-900">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                                </svg>
                                                            </a>
                                                        @endcan
                                                        @can('delete', $expense)
                                                            <form action="{{ route('expenses.destroy', [$colocation, $expense]) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette dépense ?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                                    </svg>
                                                                </button>
                                                            </form>
                                                        @endcan
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
