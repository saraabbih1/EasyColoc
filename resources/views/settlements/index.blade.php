@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Dettes et remboursements</h1>
                        <p class="mt-1 text-gray-600">{{ $colocation->name }}</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('colocations.show', $colocation) }}" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </a>
                        <form action="{{ route('settlements.optimize', $colocation) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Optimiser les dettes
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Balance Summary -->
            <div class="lg:col-span-2">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Résumé des soldes</h2>
                        
                        @if(empty($summary))
                            <div class="text-center py-8 text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                <p class="mt-2">Tous les comptes sont équilibrés</p>
                                <p class="text-sm text-gray-400">Aucune dette en cours</p>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach($summary as $item)
                                    <div class="flex items-center justify-between p-4 rounded-lg @if($item['type'] == 'creditor') bg-green-50 @else bg-red-50 @endif">
                                        <div class="flex items-center">
                                            <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center text-white font-semibold">
                                                {{ strtoupper(substr($item['user']->name, 0, 1)) }}
                                            </div>
                                            <div class="ml-4">
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $item['user']->name }}
                                                    @if($item['user']->id === auth()->id())
                                                        <span class="text-indigo-600">(Vous)</span>
                                                    @endif
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    @if($item['type'] == 'creditor')
                                                        <span class="text-green-600">Doit recevoir</span>
                                                    @else
                                                        <span class="text-red-600">Doit payer</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xl font-bold @if($item['type'] == 'creditor') text-green-600 @else text-red-600 @endif">
                                                {{ $item['formatted_amount'] }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Detailed Settlements -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mt-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Détails des remboursements</h2>
                        
                        @if($settlements->isEmpty())
                            <div class="text-center py-8 text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <p class="mt-2">Aucun remboursement en attente</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($settlements as $settlement)
                                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="flex items-center mr-4">
                                                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center text-white text-xs font-semibold mr-2">
                                                    {{ strtoupper(substr($settlement->debtor->name, 0, 1)) }}
                                                </div>
                                                <span class="text-sm font-medium text-gray-900">{{ $settlement->debtor->name }}</span>
                                            </div>
                                            
                                            <svg class="w-5 h-5 text-gray-400 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                            </svg>
                                            
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white text-xs font-semibold mr-2">
                                                    {{ strtoupper(substr($settlement->creditor->name, 0, 1)) }}
                                                </div>
                                                <span class="text-sm font-medium text-gray-900">{{ $settlement->creditor->name }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center space-x-3">
                                            <div class="text-right">
                                                <p class="text-lg font-semibold text-gray-900">{{ $settlement->formatted_amount }}</p>
                                                <p class="text-xs text-gray-500">En attente</p>
                                            </div>
                                            
                                            @can('markAsPaid', $settlement)
                                                <form action="{{ route('settlements.mark-as-paid', [$colocation, $settlement]) }}" method="POST" onsubmit="return confirm('Marquer ce paiement comme effectue ?')">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="inline-flex items-center px-3 py-1.5 border border-green-300 text-xs font-medium rounded text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        Payer
                                                    </button>
                                                </form>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-700">
                                                    Paid
                                                </span>
                                            @endcan
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
                <!-- Quick Stats -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Statistiques</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-600">Total des dettes</p>
                                <p class="text-2xl font-bold text-red-600">
                                    {{ number_format($settlements->where('status', 'pending')->sum('amount'), 2, ',', ' ') }} MAD
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-600">Nombre de remboursements</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $settlements->where('status', 'pending')->count() }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-600">Moyenne par remboursement</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    {{ $settlements->where('status', 'pending')->count() > 0 ? number_format($settlements->where('status', 'pending')->sum('amount') / $settlements->where('status', 'pending')->count(), 2, ',', ' ') : '0,00' }} MAD
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Optimization Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Optimisation des dettes</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>L'optimisation réduit le nombre de transactions nécessaires pour équilibrer les comptes.</p>
                                <ul class="list-disc list-inside mt-2 space-y-1">
                                    <li>Moins de virements à effectuer</li>
                                    <li>Calcul automatique des montants optimaux</li>
                                    <li>Gagnez du temps dans vos remboursements</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Your Balance -->
                @php
                    $userBalance = 0;
                    foreach($summary as $item) {
                        if ($item['user']->id === auth()->id()) {
                            $userBalance = $item['balance'];
                            break;
                        }
                    }
                @endphp
                
                @if($userBalance != 0)
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">Votre solde</h2>
                            
                            <div class="text-center">
                                <p class="text-sm text-gray-600 mb-2">
                                    @if($userBalance > 0)
                                        Vous devez recevoir
                                    @else
                                        Vous devez payer
                                    @endif
                                </p>
                                <p class="text-3xl font-bold @if($userBalance > 0) text-green-600 @else text-red-600 @endif">
                                    {{ number_format(abs($userBalance), 2, ',', ' ') }} MAD
                                </p>
                                
                                @if($userBalance > 0)
                                    <p class="text-xs text-gray-500 mt-2">
                                        {{ $settlements->where('creditor_id', auth()->id())->where('status', 'pending')->count() }} personne(s) vous doivent de l'argent
                                    </p>
                                @else
                                    <p class="text-xs text-gray-500 mt-2">
                                        Vous devez de l'argent à {{ $settlements->where('debtor_id', auth()->id())->where('status', 'pending')->count() }} personne(s)
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

