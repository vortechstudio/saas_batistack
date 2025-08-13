@extends('components.layouts.app')

@section('content')
<div class="max-w-2xl mx-auto p-6">
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
        <div class="text-yellow-600 text-6xl mb-4">⚠</div>
        <h1 class="text-2xl font-bold text-yellow-800 mb-2">Paiement annulé</h1>
        <p class="text-yellow-700 mb-4">
            Votre paiement a été annulé. Vous pouvez reprendre votre commande à tout moment.
        </p>
        <div class="space-x-4">
            <a href="{{ route('client.order') }}"
               class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                Reprendre la commande
            </a>
            <a href="{{ route('client.dashboard') }}"
               class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400">
                Retour au tableau de bord
            </a>
        </div>
    </div>
</div>
@endsection
