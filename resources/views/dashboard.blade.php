<x-app-layout>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 font-sans">

        {{-- ====== TOP BAR ====== --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight flex items-center gap-2">
                    Tableau de bord <span class="text-indigo-600">EasyColoc</span>
                </h1>
                <p class="text-sm text-gray-500 mt-1 uppercase tracking-wider font-semibold italic">
                    {{ $colocation ? $colocation->name : 'Aucune colocation' }} • {{ ucfirst($user->role) }}
                </p>
            </div>
            
            @if($colocation)
                <div class="flex items-center gap-3">
                    <a href="{{ route('expenses.create') }}" class="inline-flex items-center px-5 py-2.5 bg-gray-900 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-all duration-200 shadow-lg shadow-gray-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Nouvelle Dépense
                    </a>
                </div>
            @endif
        </div>

        @if(!$colocation)
            {{-- ====== EMPTY STATE (MODIFIÉ POUR ÊTRE PRO) ====== --}}
            <div class="bg-white rounded-3xl p-12 text-center border border-gray-100 shadow-xl shadow-gray-100/50">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 mb-6">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Lancez votre colocation</h2>
                <p class="text-gray-500 max-w-sm mx-auto mb-8 text-sm leading-relaxed">
                    Centralisez vos comptes, suivez les dettes et gérez vos factures en toute simplicité avec vos colocataires.
                </p>
                <a href="{{ route('colocations.create') }}" class="inline-flex items-center px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-all">
                    Créer un espace maintenant
                </a>
            </div>
        @else

            {{-- ====== GRID STATS ====== --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                {{-- Card 1: Total Dépenses --}}
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-lg group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded">Ce mois</span>
                    </div>
                    <p class="text-gray-500 text-sm font-medium">Total Dépenses</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($colocation->expenses->sum('amount'), 2) }} €</h3>
                </div>

                {{-- Card 2: Votre Balance --}}
                @php 
                    $userBalance = collect($balances)->firstWhere('name', $user->name);
                @endphp
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-lg group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                    </div>
                    <p class="text-gray-500 text-sm font-medium">Votre état actuel</p>
                    <h3 class="text-2xl font-bold mt-1 {{ ($userBalance['balance'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($userBalance['balance'] ?? 0, 2) }} €
                    </h3>
                </div>

                {{-- Card 3: Membres --}}
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow group text-right">
                    <div class="flex justify-end mb-4">
                        <div class="flex -space-x-2 overflow-hidden">
                            @foreach($colocation->memberships->take(4) as $member)
                                <div class="inline-block h-8 w-8 rounded-full ring-2 ring-white bg-indigo-500 flex items-center justify-center text-[10px] text-white font-bold">
                                    {{ substr($member->user->name, 0, 2) }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <p class="text-gray-500 text-sm font-medium">Membres Actifs</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $colocation->memberships->where('status','active')->count() }}</h3>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- BALANCES (Col-span 2) --}}
                <div class="lg:col-span-2 bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-8 py-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                        <h3 class="font-bold text-gray-900 italic">Détails des soldes</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-xs text-gray-400 uppercase tracking-widest">
                                    <th class="px-8 py-4 font-bold">Membre</th>
                                    <th class="px-8 py-4 font-bold text-right">Investi</th>
                                    <th class="px-8 py-4 font-bold text-right">Balance</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                @foreach($balances as $b)
                                    <tr class="hover:bg-gray-50/80 transition">
                                        <td class="px-8 py-4">
                                            <div class="flex items-center gap-3 font-semibold text-gray-700">
                                                <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-500 text-xs font-bold uppercase">
                                                    {{ substr($b['name'], 0, 2) }}
                                                </div>
                                                {{ $b['name'] }}
                                            </div>
                                        </td>
                                        <td class="px-8 py-4 text-right text-gray-500 font-medium">
                                            {{ number_format($b['paid'], 2) }} €
                                        </td>
                                        <td class="px-8 py-4 text-right font-bold">
                                            <span class="{{ $b['balance'] >= 0 ? 'text-emerald-600' : 'text-rose-500' }}">
                                                {{ $b['balance'] > 0 ? '+' : '' }}{{ number_format($b['balance'], 2) }} €
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- SETTLEMENTS (Col-span 1) --}}
                <div class="bg-gray-900 rounded-3xl shadow-2xl p-8 text-white">
                    <h3 class="text-lg font-bold mb-6 flex items-center gap-2">
                        <span class="w-2 h-2 bg-indigo-500 rounded-full animate-pulse"></span>
                    Remboursements
                    </h3>
                    @if(count($settlements) > 0)
                        <div class="space-y-6">
                            @foreach($settlements as $s)
                                <div class="relative pl-6 border-l border-gray-700 py-1">
                                    <div class="absolute -left-[5px] top-0 w-2.5 h-2.5 bg-indigo-500 rounded-full"></div>
                                    <p class="text-xs text-gray-400 uppercase tracking-tighter">{{ $s['from'] }} doit à</p>
                                    <div class="flex justify-between items-center mt-1">
                                        <span class="font-bold text-gray-100 text-base">{{ $s['to'] }}</span>
                                        <span class="text-xl font-black text-indigo-400 font-mono">{{ number_format($s['amount'], 2) }}€</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-10">
                            <div class="bg-gray-800 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p class="text-gray-400 text-sm">Tout est en règle.</p>
                        </div>
                    @endif

                    @if($user->role === 'owner' || $user->role === 'admin')
                        <div class="mt-10">
                            <a href="{{ route('colocations.members', $colocation->id) }}" class="flex items-center justify-center gap-2 py-3 px-4 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-xs font-bold transition">
                                Gérer les colocataires
                            </a>
                        </div>
                    @endif
                </div>

            </div>
        @endif
    </div>
</x-app-layout>