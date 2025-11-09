<div>
    <div class="flex flex-col mb-10">
        <span class="font-black text-blue-800 text-3xl mb-10">Suivi de la commande N°{{ $order->order_number }}</span>
        <ul class="steps" wire:poll.visible.2s>
            <li class="step {{ in_array($order->status->value, ['pending', 'confirmed', 'processing', 'delivered']) ? 'step-success' : 'step-neutral' }}">
                <span class="step-icon">@svg('heroicon-o-clock')</span>En attente
            </li>
            <li class="step {{ in_array($order->status->value, ['confirmed', 'processing', 'delivered']) ? 'step-success' : ($order->status->value === 'pending' ? 'step-neutral' : '') }}">
                <span class="step-icon">@svg('heroicon-o-check')</span>Confirmé
            </li>
            <li class="step {{ in_array($order->status->value, ['processing', 'delivered']) ? 'step-success' : (in_array($order->status->value, ['pending', 'confirmed']) ? 'step-neutral' : '') }}">
                <span class="step-icon">@svg('heroicon-o-cog')</span>En cours de traitement
            </li>
            <li class="step {{ $order->status->value === 'delivered' ? 'step-success' : (in_array($order->status->value, ['pending', 'confirmed', 'processing']) ? 'step-neutral' : '') }}">
                <span class="step-icon">@svg('heroicon-o-check-circle')</span>Livrée
            </li>
        </ul>
    </div>
    <div class="flex flex-col mb-10 bg-gray-200 rounded-2xl m-5 p-5">
        <span class="font-black text-blue-800 text-xl mb-4" wire:poll.visible.2s>
            Statut actuel : <span class="text-{{ $order->status->color() }}-600">{{ $order->status->label() }}</span>
        </span>
        <p class="text-gray-700 text-base" wire:poll.visible.2s>
            {{ $order->status->description() }}
        </p>
    </div>
    <div class="flex flex-row justify-around gap-2">
        <x-mary-card title="Historique de la commande" class="bg-gray-100 rounded-xl w-full">
            <table class="table" wire:poll.visible.2s>
                <tbody>
                    @foreach ($order->logs as $log)
                        <tr class="font-black text-blue-800">
                            <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $log->libelle }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-mary-card>
        <x-mary-card title="Détails de la commande" class="bg-gray-100 rounded-xl w-full">
            <table class="table">
                <tbody>
                    <tr class="font-black text-blue-800">
                        <td>Numéro de commande</td>
                        <td>{{ $order->order_number }}</td>
                    </tr>
                    <tr class="font-black text-blue-800">
                        <td>Date de commande</td>
                        <td>{{ $order->created_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    <tr class="font-black text-blue-800">
                        <td>Montant</td>
                        <td>{{ $order->total_amount }}</td>
                    </tr>
                </tbody>
            </table>
        </x-mary-card>
    </div>
</div>
