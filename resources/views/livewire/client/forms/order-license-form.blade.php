<div>
    @if(session('error'))
        <x-mary-alert title="Erreur lors de la validation de la commande" :description="session('error')" icon="o-exclamation-triangle" class="alert-danger" />
    @endif
    {{ $this->form }}
</div>
