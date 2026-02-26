<x-app-layout>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 font-sans">

        {{-- ====== TOP BAR ====== --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight flex items-center gap-2">
                    Dashboard <span class="text-blue-600">EasyColoc</span>
                </h1>
                <p class="text-sm text-gray-500 mt-1 uppercase tracking-wide font-semibold italic">
                    {{ $colocation ? $colocation->name : 'Aucune colocation' }} • {{ ucfirst($user->role) }}
                </p>
            </div>
            
            @if($colocation)
                <div class="flex items-center gap-3">
                    <a href="{{ route('expenses.create') }}" class="inline-flex items-center px-5 py-2.5 bg-blue-700 hover:bg-blue-800 text-white text-sm font-bold rounded-xl shadow-lg transition duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nouvelle Dépense
                    </a>
                </div>
            @endif
        </div>

        @if(!$colocation)
        <form method="POST" action="{{ route('invitations.store', $colocation->id) }}">
    @csrf
    <input type="email" name="email" placeholder="Email" required>
    <button type="submit">Inviter</button>
</form>
            {{-- EMPTY STATE --}}
            <div class="bg-white rounded-3xl p-12 text-center border border-gray-200 shadow-xl">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 mb-6">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Lancez votre colocation</h2>
                <p class="text-gray-500 max-w-sm mx-auto mb-8 text-sm leading-relaxed">
                    Centralisez vos comptes, suivez les dettes et gérez vos factures facilement avec vos colocataires.
                </p>
                <a href="{{ route('colocations.create') }}" class="inline-flex items-center px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition">
                    Créer un espace maintenant
                </a>
            </div>
        @else
            {{-- GRID STATS --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                
                {{-- Total Dépenses --}}
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-2.5 bg-green-50 text-green-600 rounded-lg group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-1 rounded">Ce mois</span>
                    </div>
                    <p class="text-gray-500 text-sm font-medium">Total Dépenses</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($colocation->expenses->sum('amount'), 2) }} €</h3>
                </div>

                {{-- Votre Balance --}}
                @php 
                    $userBalance = collect($balances)->firstWhere('name', $user->name);
                @endphp
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-lg group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-gray-500 text-sm font-medium">Votre état actuel</p>
                    <h3 class="text-2xl font-bold mt-1 {{ ($userBalance['balance'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($userBalance['balance'] ?? 0, 2) }} €
                    </h3>
                </div>

                {{-- Membres --}}
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition group text-right">
                    <div class="flex justify-end mb-4">
                        <div class="flex -space-x-2 overflow-hidden">
                            @foreach($colocation->memberships->take(4) as $member)
                                <div class="inline-block h-8 w-8 rounded-full ring-2 ring-white bg-blue-500 flex items-center justify-center text-[10px] text-white font-bold">
                                    {{ substr($member->user->name, 0, 2) }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <p class="text-gray-500 text-sm font-medium">Membres Actifs</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $colocation->memberships->where('status','active')->count() }}</h3>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>