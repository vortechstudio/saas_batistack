<div>
    <div class="flex flex-col gap-2 mb-10">
        <span class="font-black text-blue-800 text-4xl">Souscrire à une licence</span>
    </div>
    <form wire:submit="subscribe">
        {{ $this->form }}

        <div class="flex justify-end mt-5">
            <x-mary-button type="submit" label="Suivant" class="btn-outline btn-info" />
        </div>
    </form>

    <x-filament-actions::modals />
</div>
