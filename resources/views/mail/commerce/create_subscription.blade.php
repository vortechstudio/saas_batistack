<x-mail::message>
# Confirmation de votre abonnement

Bonjour,

Nous vous confirmons la création de votre abonnement suite à votre commande N°{{ $order->order_number }}.

## Détails de votre commande
- **Numéro de commande :** {{ $order->order_number }}
- **Date :** {{ $order->created_at->format('d/m/Y à H:i') }}
- **Montant total :** {{ number_format($order->total_amount / 100, 2, ',', ' ') }} €
- **Statut :** {{ $order->status->label() }}

## Détails de votre abonnement
- **ID Stripe :** {{ $subscription->id }}
- **Statut :** {{ ucfirst($subscription->status) }}
- **Période de facturation :** {{ $subscription->items->data[0]->price->recurring->interval === 'month' ? 'Mensuel' : 'Annuel' }}
- **Prochaine facturation :** {{ \Carbon\Carbon::createFromTimestamp($subscription->current_period_end)->format('d/m/Y') }}

## Articles inclus
@foreach($order->items as $item)
- {{ $item->product->name }} ({{ $item->quantity }}x) - {{ number_format($item->unit_price / 100, 2, ',', ' ') }} €
@endforeach

<x-mail::button :url="route('client.account.orders.show', $order)">
Voir ma commande
</x-mail::button>

Votre abonnement est maintenant actif et vous bénéficiez de tous les avantages inclus dans votre formule.

Si vous avez des questions, n'hésitez pas à nous contacter.

Merci pour votre confiance,<br>
{{ config('app.name') }}
</x-mail::message>
