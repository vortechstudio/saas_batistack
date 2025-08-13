<div>
    @if(session('error'))
        <x-mary-alert title="Erreur lors de la validation de la commande" icon="o-exclamation-triangle" class="alert-danger" role="alert">
            {{ session('error') }}
        </x-mary-alert>
    @endif
    {{ $this->form }}
</div>
