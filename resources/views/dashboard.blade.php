@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
            <p class="text-sm text-gray-600">Vue globale de votre colocation.</p>
        </div>
        <a href="{{ route('colocations.create') }}" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            Nouvelle colocation
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
            <p class="text-sm text-gray-500">Reputation</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900">{{ auth()->user()->reputation ?? 0 }}</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
            <p class="text-sm text-gray-500">Depenses totales</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format((float) ($totalExpenses ?? 0), 2, ',', ' ') }} MAD</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
            <p class="text-sm text-gray-500">Depenses du mois</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format((float) ($monthlyExpenses ?? 0), 2, ',', ' ') }} MAD</p>
        </div>
    </div>

    <div class="mt-6 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Depenses recentes</h2>
            @if(isset($colocation))
                <a href="{{ route('expenses.index', $colocation) }}" class="text-sm font-medium text-indigo-700 hover:text-indigo-800">Voir tout</a>
            @endif
        </div>

        @if(($recentExpenses ?? collect())->isEmpty())
            <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center">
                <p class="text-sm text-gray-600">Aucune depense enregistree pour le moment.</p>
                @if(isset($colocation))
                    <a href="{{ route('expenses.create', $colocation) }}" class="mt-3 inline-flex items-center text-sm font-medium text-indigo-700 hover:text-indigo-800">
                        Ajouter une depense
                    </a>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th class="pb-2 font-medium">Titre</th>
                            <th class="pb-2 font-medium">Payeur</th>
                            <th class="pb-2 font-medium">Date</th>
                            <th class="pb-2 font-medium">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($recentExpenses as $expense)
                            <tr>
                                <td class="py-3 text-gray-900">{{ $expense->title }}</td>
                                <td class="py-3 text-gray-700">{{ $expense->user->name }}</td>
                                <td class="py-3 text-gray-700">{{ $expense->expense_date->format('d/m/Y') }}</td>
                                <td class="py-3 font-medium text-gray-900">{{ number_format((float) $expense->amount, 2, ',', ' ') }} MAD</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
