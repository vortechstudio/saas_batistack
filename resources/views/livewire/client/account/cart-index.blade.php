<div>
    <div class="flex flex-col gap-2 mb-10">
        <span class="font-black text-blue-800 text-4xl">Souscrire à une offre</span>
        <span class="text-gray-300">Souscrivez à une offre pour accéder à nos services.</span>
    </div>

    @if(!$hasPaymentMethod)
        <x-mary-alert title="Aucun moyen de paiement n'est enregistré" icon="o-exclamation-triangle" class="alert-warning">
            <x-slot:actions>
                <x-mary-button label="Ajouter un moyen de paiement" link="{{ route('client.account.method-payment') }}" class="btn-ghost" />
            </x-slot:actions>
        </x-mary-alert>
    @else
        <div class="flex flex-row justify-around items-center gap-2">
            <x-mary-button label="License Batistack" icon="o-key" class="btn-outline btn-wide mt-10 mx-2 w-[100%]" link="{{ route('client.account.cart.license') }}"  />
            <x-mary-button label="Nos Modules" icon="o-cog" class="btn-outline btn-wide mt-10 mx-2 w-[100%]" link="{{ route('client.account.cart.module') }}" />
            <x-mary-button label="Nos Options" icon="o-adjustments-horizontal" class="btn-outline btn-wide mt-10 mx-2 w-[100%]" link="{{ route('client.account.cart.index') }}" />
        </div>
    @endif


</div>
