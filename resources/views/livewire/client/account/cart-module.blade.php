<div>
    @if($hasServices)
        <x-mary-alert title="Achat de service non autorisé" description='Aucun service de disponible. Veuillez souscrire à une offre "Batistack" avant de continuer.' icon="o-x-mark" class="alert-error" />
    @else
        <div class="card">
            <div class="card-body">
                <form wire:submit="subscribeModule">
                    {{ $this->form }}

                    <div class="flex justify-end mt-5">
                        <button type="submit" class="btn btn-primary" wire:loading.class="opacity-50">Valider</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
