<div>
    <div class="flex flex-col mb-10">
        <span class="font-black text-blue-800 text-3xl mb-10">Suivi de la commande N°{{ $order->order_number }}</span>
        <ul class="steps">
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
        <span class="font-black text-blue-800 text-xl mb-4">
            Statut actuel : <span class="text-{{ $order->status->color() }}-600">{{ $order->status->label() }}</span>
        </span>
        <p class="text-gray-700 text-base">
            {{ $order->status->description() }}
        </p>
    </div>
    <div class="flex flex-row justify-around items-center gap-2">
        <x-mary-card title="Historique de la commande" class="bg-gray-100 rounded-xl">
            <table class="table">
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
    </div>
</div>
