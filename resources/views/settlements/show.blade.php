@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                Optimiser les dettes
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Resume des soldes</h2>

                        @if(empty($summary))
                            <p class="text-sm text-gray-500">Tous les comptes sont equilibres.</p>
                        @else
                            <div class="space-y-3">
                                @foreach($summary as $item)
                                    <div class="flex items-center justify-between p-3 rounded-lg {{ $item['type'] === 'creditor' ? 'bg-green-50' : 'bg-red-50' }}">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $item['user']->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $item['type'] === 'creditor' ? 'Doit recevoir' : 'Doit payer' }}</p>
                                        </div>
                                        <p class="text-sm font-semibold {{ $item['type'] === 'creditor' ? 'text-green-700' : 'text-red-700' }}">
                                            {{ $item['formatted_balance'] }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mt-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Qui doit quoi a qui</h2>

                        @if($settlements->isEmpty())
                            <p class="text-sm text-gray-500">Aucun remboursement en attente.</p>
                        @else
                            <div class="space-y-3">
                                @foreach($settlements as $settlement)
                                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                        <div>
                                            <p class="text-sm text-gray-900">
                                                <span class="font-semibold">{{ $settlement->debtor->name }}</span>
                                                <span class="text-gray-500">doit</span>
                                                <span class="font-semibold">{{ $settlement->formatted_amount }}</span>
                                                <span class="text-gray-500">a</span>
                                                <span class="font-semibold">{{ $settlement->creditor->name }}</span>
                                            </p>
                                        </div>

                                        <div>
                                            @if((int) $settlement->debtor_id === (int) auth()->id())
                                                @can('create', [\App\Models\Payment::class, $colocation])
                                                    <form method="POST" action="{{ route('colocations.payments.store', $colocation) }}">
                                                        @csrf
                                                        <input type="hidden" name="to_user_id" value="{{ $settlement->creditor_id }}">
                                                        <input type="hidden" name="amount" value="{{ $settlement->amount }}">
                                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700">
                                                            Payer
                                                        </button>
                                                    </form>
                                                @endcan
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-700">
                                                    Paid
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Aide</h2>
                        <p class="text-sm text-gray-600">Le bouton <strong>Payer</strong> apparait uniquement pour le debiteur concerne.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
