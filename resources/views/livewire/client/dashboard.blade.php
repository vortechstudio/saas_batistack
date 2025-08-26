<div>
    <div class="w-[75%] mx-auto">
        <span class="text-blue-800 text-4xl font-black mb-4">Bienvenue {{ auth()->user()->fullname }} !</span>     
        <div class="flex flex-row my-5 gap-5">
            <div class="w-[50%]">
                @livewire('dashboard.latest_service_widget')
            </div>
            <div class="w-[50%]">
                @livewire('dashboard.total_invoice_amount_widget')
                <div class="card card-border bg-blue-100 rounded-lg mt-5">
                    <div class="card-body justify-center items-center gap-3">
                        <span class="font-black text-blue-600 text-xl">Dernière commande</span>
                        <span class="badge badge-info text-white">N° 12585632</span>
                        <span class="text-blue-600"><strong>25/08/2025</strong> Votre facture est disponible @svg('heroicon-o-check', "text-blue-600 w-1 h-1")</span>
                        <a href="#">Voir mes commandes</a>
                    </div>
                </div>
            </div>  
        </div>   
    </div>
</div>
