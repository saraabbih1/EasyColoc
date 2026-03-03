@extends('layouts.app')

@section('content')
@php
    $isOwner = $isOwner ?? (auth()->check() && ((int) $colocation->owner_id === (int) auth()->id()));
    $canManage = $canManage ?? $isOwner;
    $memberships = $memberships ?? collect();
    $invitations = $invitations ?? collect();
@endphp
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Gestion des membres</h1>
                        <p class="mt-1 text-gray-600">{{ $colocation->name }}</p>
                    </div>
                    <a href="{{ route('colocations.show', $colocation) }}" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Members List -->
            <div class="lg:col-span-2">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Membres actifs ({{ $memberships->count() }}/10)</h2>
                        
                        <div class="space-y-4">
                            @foreach($memberships->sortBy(fn($membership) => $membership->user?->name ?? '') as $membership)
                                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center text-white font-semibold">
                                            {{ strtoupper(substr($membership->user?->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $membership->user?->name ?? 'Utilisateur supprimé' }}
                                                @if($membership->user?->id === auth()->id())
                                                    <span class="text-indigo-600">(Vous)</span>
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ $membership->user?->email ?? '-' }}
                                            </p>
                                            <div class="mt-1">
                                                @if(($membership->user?->id) === $colocation->owner_id)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                        Propriétaire
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        Membre
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if(($membership->user?->id !== null) && (($membership->user?->id) !== $colocation->owner_id) && (($membership->user?->id) !== auth()->id()))
                                        <form action="{{ route('colocations.remove-member', [$colocation, $membership]) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir retirer {{ $membership->user?->name ?? 'ce membre' }} de la colocation ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-red-300 text-xs font-medium rounded text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Retirer
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Pending Invitations -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mt-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Invitations en attente</h2>
                        
                        @if($invitations->isEmpty())
                            <div class="text-center py-8 text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <p class="mt-2">Aucune invitation en attente</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($invitations as $invitation)
                                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center text-white font-semibold">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">{{ $invitation->email }}</p>
                                                <p class="text-xs text-gray-500">
                                                    Invité par {{ $invitation->inviter?->name ?? 'Utilisateur supprimé' }} • 
                                                    @if($invitation->expires_at)
                                                        Expire le {{ $invitation->expires_at->format('d/m/Y') }}
                                                    @else
                                                        Pas d'expiration
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center space-x-2">
                                            @if($invitation->isExpired())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Expirée
                                                </span>
                                            @endif
                                            
                                            <form action="{{ route('colocations.invitations.destroy', [$colocation->id, $invitation->id]) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette invitation ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-2 py-1 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </form>
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
                <!-- Invite Members -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Inviter des membres</h2>
                        
                        <form action="{{ route('colocations.invite', $colocation) }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">
                                    Adresse email
                                </label>
                                <div class="mt-1">
                                    <input type="email"
                                           name="email"
                                           id="email"
                                           required
                                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                           placeholder="email@example.com">
                                </div>
                            </div>

                            @if($memberships->count() >= 10)
                                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3 text-sm text-yellow-800">
                                    Limite atteinte: 10 membres maximum.
                                </div>
                            @endif

                            <button type="submit"
                                    @if($memberships->count() >= 10) disabled @endif
                                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Envoyer une invitation
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Statistiques</h2>
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Membres actifs</span>
                                <span class="text-sm font-medium text-gray-900">{{ $memberships->count() }}/10</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Invitations envoyées</span>
                                <span class="text-sm font-medium text-gray-900">{{ $invitations->count() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Total des dépenses</span>
                                <span class="text-sm font-medium text-gray-900">{{ number_format($colocation->total_expenses, 2, ',', ' ') }} MAD</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Date de création</span>
                                <span class="text-sm font-medium text-gray-900">{{ $colocation->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Help -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Gestion des membres</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Invitez jusqu'à 9 autres membres</li>
                                    <li>Les invitations expirent après 7 jours</li>
                                    <li>Seul le propriétaire peut retirer des membres</li>
                                    <li>Le propriétaire ne peut pas être retiré</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


