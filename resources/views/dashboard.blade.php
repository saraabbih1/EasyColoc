<x-app-layout>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 font-sans">

        {{-- ===== Alerts ===== --}}
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-xl border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-xl border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        {{-- ===== TOP BAR ===== --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900">
                    Dashboard <span class="text-blue-600">EasyColoc</span>
                </h1>
                <p class="text-sm text-gray-500 mt-1 font-medium">
                    {{ $colocation ? $colocation->name : 'Aucune colocation' }}
                </p>
            </div>

            
        </div>

        {{-- ===== EMPTY STATE ===== --}}
        <div class="container">
    <h2>Créer une colocation</h2>

    <form method="POST" action="{{ route('colocations.store') }}">
        @csrf

        <div class="mb-3">
            <label>Nom de la colocation</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">
            Créer
        </button>
    </form>
</div>
        @if(!$colocation)

            <div class="bg-white rounded-3xl p-12 text-center border border-gray-200 shadow-sm">
                <h2 class="text-2xl font-bold text-gray-900 mb-3">
                    Lancez votre colocation
                </h2>
                <p class="text-gray-500 mb-6">
                    Centralisez vos comptes et gérez vos dépenses facilement.
                </p>
                <a href="{{ route('colocations.create') }}"
                   class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold">
                    Créer une colocation
                </a>
            </div>

        @else

            {{-- ===== STATS GRID ===== --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

                {{-- Total Dépenses --}}
                <div class="bg-white p-6 rounded-2xl border shadow-sm">
                    <p class="text-gray-500 text-sm">Total Dépenses</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-2">
                        {{ number_format($colocation->expenses->sum('amount'), 2) }} MAD
                    </h3>
                </div>

                {{-- Votre Balance --}}
                @php
                    $userBalance = collect($balances)->firstWhere('name', auth()->user()->name);
                @endphp
                <div class="bg-white p-6 rounded-2xl border shadow-sm">
                    <p class="text-gray-500 text-sm">Votre Balance</p>
                    <h3 class="text-2xl font-bold mt-2 
                        {{ ($userBalance['balance'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($userBalance['balance'] ?? 0, 2) }} €
                    </h3>
                </div>

                {{-- Membres --}}
                <div class="bg-white p-6 rounded-2xl border shadow-sm">
                    <p class="text-gray-500 text-sm">Membres Actifs</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-2">
                        {{ $colocation->memberships->where('status','active')->count() }}
                    </h3>
                </div>
            </div>

            {{-- ===== INVITATION FORM ===== --}}
            @if(in_array(auth()->user()->role, ['owner','admin']))
            <div class="bg-white p-6 rounded-2xl border shadow-sm mb-8">
                <h2 class="text-lg font-bold mb-4">Inviter un colocataire</h2>

                <form method="POST" action="{{ route('invitations.store', $colocation->id) }}"
                      class="flex flex-col md:flex-row gap-4">
                    @csrf
                    <input type="email" name="email" required
                           placeholder="Email du colocataire"
                           class="flex-1 px-4 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <button type="submit"
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold">
                        Envoyer
                    </button>
                </form>
            </div>
            @endif

            {{-- ===== INVITATIONS LIST ===== --}}
            @if($invitations->count())
            <div class="bg-white p-6 rounded-2xl border shadow-sm">
                <h2 class="text-lg font-bold mb-4">Invitations envoyées</h2>

                <div class="space-y-3">
                    @foreach($invitations as $inv)
                        <div class="flex justify-between items-center p-4 border rounded-xl">

                            <div>
                                <p class="font-semibold text-gray-800">
                                    {{ $inv->email }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    Expire le:
                                    {{ $inv->expires_at?->format('d M Y') }}
                                </p>
                            </div>

                            <span class="px-3 py-1 text-xs rounded-full
                                @if($inv->status == 'pending') bg-yellow-100 text-yellow-700
                                @elseif($inv->status == 'accepted') bg-green-100 text-green-700
                                @else bg-red-100 text-red-700
                                @endif">
                                {{ ucfirst($inv->status) }}
                            </span>

                        </div>
                    @endforeach
                </div>
            </div>
            @endif
{{-- ===== MEMBERS LIST ===== --}}
@if($colocation && $colocation->memberships->count())
    <div class="bg-white p-6 rounded-2xl border shadow-sm mt-8">
        <h2 class="text-lg font-bold mb-4">Membres de la colocation</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($colocation->memberships as $membership)
                @php
                    $member = $membership->user;
                    $memberBalance = collect($balances)->firstWhere('name', $member->name);
                @endphp
                <div class="flex flex-col justify-between p-4 border rounded-xl hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold">
                                {{ strtoupper(substr($member->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">{{ $member->name }}</p>
                                <p class="text-xs text-gray-500">{{ ucfirst($membership->role) }}</p>
                            </div>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full
                            @if($membership->status == 'active') bg-green-100 text-green-700
                            @elseif($membership->status == 'pending') bg-yellow-100 text-yellow-700
                            @else bg-red-100 text-red-700
                            @endif">
                            {{ ucfirst($membership->status) }}
                        </span>
                    </div>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">Balance:</p>
                        <h3 class="text-lg font-bold {{ ($memberBalance['balance'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($memberBalance['balance'] ?? 0, 2) }} €
                        </h3>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
{{-- ===== FORMULAIRE NOUVELLE DÉPENSE ===== --}}
@if($colocation)
<div class="bg-white p-6 rounded-2xl border shadow-sm mb-6">
    <h2 class="text-lg font-bold mb-4">Ajouter une nouvelle dépense</h2>

    <form action="{{ route('expenses.store') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700">Titre</label>
            <input type="text" name="title" required
                   class="mt-1 block w-full px-4 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Montant (€)</label>
            <input type="number" name="amount" step="0.01" required
                   class="mt-1 block w-full px-4 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Catégorie</label>
            <input type="text" name="category"
                   class="mt-1 block w-full px-4 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <button type="submit"
                class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold">
            Ajouter Dépense
        </button>
    </form>
</div>
@endif

        @endif

    </div>
</x-app-layout>