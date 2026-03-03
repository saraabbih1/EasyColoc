@extends('layouts.app')

@section('content')
@php
    // Fallbacks de sécurité pour éviter les erreurs de variables non définies
    $isOwner = $isOwner ?? (auth()->check() && ((int) $colocation->owner_id === (int) auth()->id()));
    
    $isMember = $isMember ?? (auth()->check() && $colocation->memberships()
        ->where('user_id', auth()->id())
        ->where('status', 'active')
        ->exists());
    
    $canManage = $canManage ?? $isOwner;
    
    // S'assurer que les collections sont définies
    $members = $members ?? collect();
    $expenses = $expenses ?? collect();
    $settlements = $settlements ?? collect();
    $balances = $balances ?? [];
@endphp
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $colocation->name }}</h1>
                        @if($colocation->description)
                            <p class="mt-1 text-gray-600">{{ $colocation->description }}</p>
                        @endif
                        <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                {{ $colocation->member_count }} membres
                            </span>
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                                {{ number_format($colocation->total_expenses, 2, ',', ' ') }} MAD de dépenses
                            </span>
                        </div>
                    </div>
                    
                    @if($canManage)
                        <div class="flex space-x-2">
                            <a href="{{ route('colocations.members', $colocation) }}" 
                               class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                Gérer
                            </a>
                            <form action="{{ route('colocations.cancel', $colocation) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette colocation ? Cette action est irréversible.')">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Annuler
                                </button>
                            </form>
                        </div>
                    @else
                        <form action="{{ route('colocations.leave', $colocation) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir quitter cette colocation ?')">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Quitter
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Balance Summary -->
            <div class="lg:col-span-2">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Solde des membres</h2>
                        
                        @if(empty($balances))
                            <div class="text-center py-8 text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                                <p class="mt-2">Aucune dépense enregistrée</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($balances as $userId => $balance)
                                    @php
                                        $user = $members->firstWhere('id', $userId);
                                        if (!$user) {
                                            continue;
                                        }
                                        $isCurrentUser = $user->id === auth()->id();
                                    @endphp
                                    <div class="flex items-center justify-between p-3 rounded-lg @if($isCurrentUser) bg-indigo-50 @else bg-gray-50 @endif">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center text-white font-semibold">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $user->name }}
                                                    @if($isCurrentUser)
                                                        <span class="text-indigo-600">(Vous)</span>
                                                    @endif
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    @if($balance > 0)
                                                        <span class="text-green-600">Doit recevoir</span>
                                                    @elseif($balance < 0)
                                                        <span class="text-red-600">Doit payer</span>
                                                    @else
                                                        <span class="text-gray-600">À jour</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-lg font-semibold @if($balance > 0) text-green-600 @elseif($balance < 0) text-red-600 @else text-gray-600 @endif">
                                                @if($balance > 0) + @endif {{ number_format(abs($balance), 2, ',', ' ') }} MAD
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Expenses -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mt-6">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-medium text-gray-900">Dépenses récentes</h2>
                            <a href="{{ route('expenses.index', $colocation) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                Voir tout
                            </a>
                        </div>
                        
                        @if($colocation->expenses->isEmpty())
                            <div class="text-center py-8 text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                                <p class="mt-2">Aucune dépense enregistrée</p>
                                <a href="{{ route('expenses.create', $colocation) }}" class="mt-2 inline-flex items-center text-indigo-600 hover:text-indigo-900">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Ajouter une dépense
                                </a>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($colocation->expenses->take(5) as $expense)
                                    <div class="flex items-center justify-between p-3 border-b border-gray-200 last:border-0">
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 rounded-full" style="background-color: {{ $expense->category->color ?? '#6B7280' }}"></div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">{{ $expense->title }}</p>
                                                <p class="text-xs text-gray-500">
                                                    {{ $expense->user->name }} • {{ $expense->expense_date->format('d/m/Y') }}
                                                    @if($expense->category)
                                                        • {{ $expense->category->name }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-semibold text-gray-900">{{ $expense->formatted_amount }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Actions rapides</h2>
                        <div class="space-y-3">
                            <a href="{{ route('expenses.create', $colocation) }}" 
                               class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Ajouter une dépense
                            </a>
                            
                            <a href="{{ route('settlements.index', $colocation) }}" 
                               class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                Voir les dettes
                            </a>
                            
                            @if($canManage)
                                <a href="#categories" 
                                   class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                    Gérer les catégories
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Categories -->
                <div id="categories" class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Categories</h2>

                        @if($canManage)
                            <form action="{{ route('categories.store', $colocation) }}" method="POST" class="space-y-3 mb-4">
                                @csrf
                                <div>
                                    <label for="category_name" class="block text-sm font-medium text-gray-700">Nom</label>
                                    <input id="category_name" type="text" name="name" required maxlength="255"
                                        class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Nouvelle categorie">
                                </div>
                                <div>
                                    <label for="category_color" class="block text-sm font-medium text-gray-700">Couleur</label>
                                    <input id="category_color" type="color" name="color" value="#6366F1"
                                        class="mt-1 h-10 w-full rounded-md border-gray-300">
                                </div>
                                <button type="submit"
                                    class="w-full inline-flex justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                    Ajouter
                                </button>
                            </form>
                        @endif

                        @php
                            $categories = $colocation->categories()->orderBy('name')->get();
                        @endphp

                        @if($categories->isEmpty())
                            <p class="text-sm text-gray-500">Aucune categorie pour le moment.</p>
                        @else
                            <div class="space-y-2">
                                @foreach($categories as $category)
                                    <div class="flex items-center justify-between rounded-md border border-gray-200 px-3 py-2">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-block h-3 w-3 rounded-full" style="background-color: {{ $category->color ?? '#6B7280' }};"></span>
                                            <span class="text-sm text-gray-800">{{ $category->name }}</span>
                                        </div>
                                        @if($canManage)
                                            <form action="{{ route('categories.destroy', [$colocation, $category]) }}" method="POST" onsubmit="return confirm('Supprimer cette categorie ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-red-600 hover:text-red-700">Supprimer</button>
                                            </form>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Members -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Membres ({{ $colocation->member_count }})</h2>
                        <div class="space-y-3">
                            @foreach($members->sortBy('name') as $member)
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                                        <p class="text-xs text-gray-500">
                                            @if((int) $colocation->owner_id === (int) $member->id)
                                                <span class="text-indigo-600">Propriétaire</span>
                                            @else
                                                Membre
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
