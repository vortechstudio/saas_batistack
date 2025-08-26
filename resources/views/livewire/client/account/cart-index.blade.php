<div>
    <div class="flex flex-col gap-2 mb-10">
        <span class="font-black text-blue-800 text-4xl">Souscrire à une offre</span>
        <span class="text-gray-300">Souscrivez à une offre pour accéder à nos services.</span>
    </div>

    <div class="flex flex-row justify-around items-center gap-2">
        <x-mary-button label="License Batistack" icon="o-key" class="btn-outline btn-wide mt-10 mx-2 w-[100%]" link="{{ route('client.account.cart.index') }}" />
        <x-mary-button label="Nos Modules" icon="o-cog" class="btn-outline btn-wide mt-10 mx-2 w-[100%]" link="{{ route('client.account.cart.index') }}" />
        <x-mary-button label="Nos Options" icon="o-adjustments-horizontal" class="btn-outline btn-wide mt-10 mx-2 w-[100%]" link="{{ route('client.account.cart.index') }}" />
    </div>
</div>
