<div>
    @if(session('error'))
        <x-mary-alert title="Erreur lors de la validation de la commande" icon="o-exclamation-triangle" class="alert-error" role="alert">
            {{ session('error') }}
        </x-mary-alert>
    @endif
    <form wire:submit="proceedToPayment">
        {{ $this->form }}
    </form>
</div>
