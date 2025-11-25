<div>
    <div class="card card-side bg-base-200 shadow-sm align-middle p-5 mb-10">
        <figure>
            <img
                src="{{ Gravatar::get($customer->user->email) }}"
                alt="Movie" class="h-[100px] w-[100px] rounded-full" />
        </figure>
        <div class="card-body">
            <div class="font-black text-indigo-500 text-lg">{{ $customer->entreprise ?? $customer->user->nom." ".$customer->user->prenom }}</div>
            <div class="text-gray-400">{{ $customer->user->fullname }}</div>
            <div class="align-middle">
                <x-mary-icon name="o-map-pin" class="w-4 h-4 mr-2" />
                <span class="text-gray-400">{{ $customer->adresse }}, {{ $customer->code_postal }} {{ $customer->ville }}, {{ $customer->pays }}</span>
            </div>
            <div class="align-middle">
                <x-mary-icon name="o-phone" class="w-4 h-4 mr-2" />
                <span class="text-gray-400">{{ $customer->tel ?? $customer->portable }}</span>
            </div>
            <div class="align-middle">
                <x-mary-icon name="o-inbox" class="w-4 h-4 mr-2" />
                <span class="text-gray-400">{{ $customer->user->email }}</span>
            </div>
        </div>
        <div class="p-5">
            <x-mary-badge class="badge-accent badge-xl" value="OK" />
        </div>
    </div>
</div>
