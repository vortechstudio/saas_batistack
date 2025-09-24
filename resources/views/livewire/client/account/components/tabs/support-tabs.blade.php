<div>
    <div class="text-3xl font-bold pb-5">Mon niveau de support</div>
    <div class="text-xl pb-2">Quels sont les différents niveaux de support ?</div>
    <p class="pb-5">
        Pour répondre à chacune de vos spécificités, Batistack a mis en place 3 niveaux d’accompagnement*. Par défaut, nos solutions sont livrées avec le niveau Standard. Selon les solutions auxquelles vous souscrivez, nous vous recommandons un niveau de support qui vous fera bénéficier au mieux d’un accompagnement de nos experts. Vous avez néanmoins le choix de garder votre niveau de support actuel, ou de souscrire à un niveau autre que celui recommandé.
    </p>
    <div class="flex justify-around">
        @foreach ($products as $product)
            @foreach ($product->prices as $k => $price)
                <div class="card w-96 {{ Str::lower($price->info_stripe->nickname) === auth()->user()->customer->support_type->value ? 'bg-blue-500 text-white' : '' }} shadow-sm">
                    <div class="card-body">
                        @if($k === 1)
                            <span class="badge badge-xs badge-warning">Most Popular</span>
                        @endif
                        <div class="flex justify-between">
                            <h2 class="text-3xl font-bold">{{ $price->info_stripe->nickname }}</h2>
                            <span class="text-xl">{{ number_format($price->info_stripe->unit_amount / 100, 2, ',', ' ') }} € / an</span>
                        </div>
                        <p class="mt-6 flex flex-col gap-2 text-md">
                            {{ $price->info_stripe->metadata->description }}
                        </p>
                        @if(Str::lower($price->info_stripe->nickname) !== auth()->user()->customer->support_type->value)
                        <div class="mt-6">
                            <button wire:click="subscribe('{{ $price->id }}')" class="btn btn-primary btn-block">Souscrire</button>
                        </div>
                        @endif
                    </div>
                    </div>
            @endforeach
        @endforeach
    </div>
</div>
