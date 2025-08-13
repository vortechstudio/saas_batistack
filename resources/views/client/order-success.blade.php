@extends('components.layouts.app')

@section('content')
<div class="max-w-2xl mx-auto p-6">
    <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
        <div class="text-green-600 text-6xl mb-4">✓</div>
        <h1 class="text-2xl font-bold text-green-800 mb-2">Paiement réussi !</h1>
        <p class="text-green-700 mb-4">
            Votre commande a été traitée avec succès. Votre licence sera activée sous peu.
        </p>
        <div class="bg-white rounded-lg p-4 mb-4">
            <p><strong>Facture:</strong> {{ $invoice->invoice_number }}</p>
            <p><strong>Montant:</strong> {{ number_format($invoice->total, 2) }}€</p>
        </div>
        <a href="{{ route('client.dashboard') }}"
           class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
            Retour au tableau de bord
        </a>
    </div>
</div>
@endsection
