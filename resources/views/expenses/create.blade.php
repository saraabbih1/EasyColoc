@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Ajouter une dépense</h1>
                    <a href="{{ route('expenses.index', $colocation) }}" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </a>
                </div>

                <form action="{{ route('expenses.store', $colocation) }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">
                            Titre de la dépense <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1">
                            <input type="text" 
                                   name="title" 
                                   id="title" 
                                   required
                                   maxlength="255"
                                   value="{{ old('title') }}"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('title') border-red-500 @enderror"
                                   placeholder="Courses, Loyer, Factures...">
                            @error('title')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700">
                                Montant <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">MAD</span>
                                </div>
                                <input type="number" 
                                       name="amount" 
                                       id="amount" 
                                       required
                                       min="0.01"
                                       max="99999.99"
                                       step="0.01"
                                       value="{{ old('amount') }}"
                                       class="block w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('amount') border-red-500 @enderror"
                                       placeholder="0,00">
                            </div>
                            @error('amount')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="expense_date" class="block text-sm font-medium text-gray-700">
                                Date de la dépense <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1">
                                <input type="date" 
                                       name="expense_date" 
                                       id="expense_date" 
                                       required
                                       max="{{ now()->format('Y-m-d') }}"
                                       value="{{ old('expense_date', now()->format('Y-m-d')) }}"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('expense_date') border-red-500 @enderror">
                            </div>
                            @error('expense_date')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700">
                            Catégorie <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1">
                            <select name="category_id" 
                                    id="category_id" 
                                    required
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('category_id') border-red-500 @enderror">
                                <option value="">Sélectionner une catégorie</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" 
                                            {{ old('category_id') == $category->id ? 'selected' : '' }}
                                            data-color="{{ $category->color }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        @if($categories->isEmpty())
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Aucune catégorie disponible. 
                                    @can('manage', $colocation)
                                        <a href="{{ route('colocations.show', $colocation) }}#categories" class="text-indigo-600 hover:text-indigo-900">
                                            Créer une catégorie
                                        </a>
                                    @endcan
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Payeur Info -->
                    <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center text-white font-semibold mr-3">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Payeur : {{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500">Cette dépense sera enregistrée comme payée par vous</p>
                            </div>
                        </div>
                    </div>

                    <!-- Share Calculation -->
                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Répartition automatique</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>Cette dépense sera répartie équitablement entre les {{ $colocation->member_count }} membres de la colocation.</p>
                                    <p class="mt-1">Chacun devra : <span id="share-amount" class="font-semibold">0,00 MAD</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('expenses.index', $colocation) }}" 
                           class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                            Annuler
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                            Ajouter la dépense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amount');
    const shareAmountSpan = document.getElementById('share-amount');
    const memberCount = {{ $colocation->member_count }};

    function updateShareAmount() {
        const amount = parseFloat(amountInput.value) || 0;
        const share = amount / memberCount;
        shareAmountSpan.textContent = share.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' MAD';
    }

    amountInput.addEventListener('input', updateShareAmount);
    updateShareAmount();
});
</script>
@endsection



