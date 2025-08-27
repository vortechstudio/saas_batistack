<div>
    <div class="flex flex-col mb-10">
        <span class="font-black text-blue-800 text-3xl mb-10">Suivi de la commande N°{{ $order->order_number }}</span>
        <ul class="steps">
            <li class="step step-success">
                <span class="step-icon">@svg('heroicon-o-check')</span>En attente
            </li>
            <li class="step step-neutral">
                <span class="step-icon">@svg('heroicon-o-x-mark')</span>Confirmé
            </li>
            <li class="step step-neutral">
                <span class="step-icon">@svg('heroicon-o-x-mark')</span>En cours de traitement
            </li>
            <li class="step step-neutral">
                <span class="step-icon">@svg('heroicon-o-x-mark')</span>Livrée
            </li>
        </ul>
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
