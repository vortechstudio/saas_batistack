<div class="bg-slate-50">
    {{-- HERO HEADER --}}
    <div class="bg-ovh-deep text-white pt-20 pb-32 relative overflow-hidden">
        <!-- Pattern subtil de fond -->
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#444cf7 1px, transparent 1px); background-size: 30px 30px;"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-6 tracking-tight">
                Une tarification <span class="text-ovh-accent">transparente</span> et évolutive
            </h1>
            <p class="text-xl text-blue-100 max-w-2xl mx-auto mb-10">
                Pas de frais cachés, pas de frais d'installation. Changez d'offre à tout moment en fonction de la croissance de votre entreprise.
            </p>

            <!-- Billing Toggle -->
            <div class="flex justify-center items-center space-x-4">
                <span class="text-sm font-medium {{ $billingCycle === 'monthly' ? 'text-white' : 'text-blue-300' }}">Mensuel</span>
                <button
                    wire:click="setBillingCycle('{{ $billingCycle === 'monthly' ? 'yearly' : 'monthly' }}')"
                    class="relative inline-flex h-8 w-14 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-ovh-accent focus:ring-offset-2 focus:ring-offset-ovh-deep {{ $billingCycle === 'yearly' ? 'bg-ovh-primary' : 'bg-slate-600' }}"
                    role="switch"
                    aria-checked="{{ $billingCycle === 'yearly' }}">
                    <span aria-hidden="true" class="pointer-events-none inline-block h-7 w-7 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $billingCycle === 'yearly' ? 'translate-x-6' : 'translate-x-0' }}"></span>
                </button>
                <span class="text-sm font-medium flex items-center {{ $billingCycle === 'yearly' ? 'text-white' : 'text-blue-300' }}">
                    Annuel
                    <span class="ml-2 inline-flex items-center rounded-full bg-green-400/10 px-2 py-1 text-xs font-medium text-green-400 ring-1 ring-inset ring-green-400/20">-20%</span>
                </span>
            </div>
        </div>
    </div>

    {{-- PRICING CARDS --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-20 relative z-20 pb-24">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($plans as $key => $plan)
                <div class="flex flex-col bg-white rounded-xl shadow-xl border transition duration-300 hover:-translate-y-2 {{ $plan['highlight'] ? 'border-ovh-primary ring-2 ring-ovh-primary ring-opacity-50 relative' : 'border-slate-200' }}">

                    @if($plan['highlight'])
                        <div class="absolute top-0 inset-x-0 -mt-3 flex justify-center">
                            <span class="bg-ovh-primary text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow-sm">
                                Recommandé
                            </span>
                        </div>
                    @endif

                    <div class="p-8 border-b border-slate-100 flex-grow">
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ $plan['name'] }}</h3>
                        <p class="text-sm text-slate-500 mb-6 h-10">{{ $plan['description'] }}</p>

                        <div class="flex items-baseline mb-6">
                            <span class="text-4xl font-extrabold text-slate-900">
                                {{ $billingCycle === 'yearly' ? $plan['price_yearly'] : $plan['price_monthly'] }}€
                            </span>
                            <span class="text-slate-500 ml-2">/ mois</span>
                        </div>
                        @if($billingCycle === 'yearly')
                            <p class="text-xs text-green-600 font-semibold mb-6">Facturé {{ $plan['price_yearly'] * 12 }}€ / an</p>
                        @else
                            <p class="text-xs text-slate-400 mb-6">Sans engagement</p>
                        @endif

                        <ul class="space-y-4">
                            @foreach($plan['features'] as $feature)
                                <li class="flex items-start">
                                    <svg class="h-5 w-5 text-green-500 flex-shrink-0 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-sm text-slate-600">{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="p-8 bg-slate-50 rounded-b-xl">
                        <a href="{{ route('register') }}?plan={{ $key }}" class="block w-full py-3 px-6 text-center rounded-md font-semibold transition shadow-sm {{ $plan['highlight'] ? 'bg-ovh-primary text-white hover:bg-ovh-dark shadow-blue-500/30' : 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 hover:text-ovh-primary' }}">
                            {{ $plan['cta'] }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- COMPARISON TABLE (Tech Specs Style) --}}
    <div class="bg-white py-24 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-slate-900">Comparatif détaillé des fonctionnalités</h2>
                <p class="mt-4 text-slate-500">Tout ce qui est inclus dans votre abonnement Batistack.</p>
            </div>

            <div class="overflow-hidden border border-slate-200 rounded-lg shadow-sm">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-ovh-deep text-white">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider w-1/3">Fonctionnalités</th>
                        <th scope="col" class="px-6 py-4 text-center text-sm font-semibold uppercase tracking-wider w-1/5">Starter</th>
                        <th scope="col" class="px-6 py-4 text-center text-sm font-semibold uppercase tracking-wider w-1/5 bg-ovh-primary/20">Business</th>
                        <th scope="col" class="px-6 py-4 text-center text-sm font-semibold uppercase tracking-wider w-1/5">Enterprise</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200 text-sm text-slate-600">
                    <!-- Section Gestion -->
                    <tr class="bg-slate-50">
                        <td colspan="4" class="px-6 py-3 font-bold text-slate-900">Gestion Commerciale</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4">Devis & Factures</td>
                        <td class="px-6 py-4 text-center text-green-500"><svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></td>
                        <td class="px-6 py-4 text-center text-green-500 bg-blue-50/30"><svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></td>
                        <td class="px-6 py-4 text-center text-green-500"><svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4">Signature Électronique</td>
                        <td class="px-6 py-4 text-center text-slate-300 font-mono">-</td>
                        <td class="px-6 py-4 text-center text-green-500 bg-blue-50/30"><svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></td>
                        <td class="px-6 py-4 text-center text-green-500"><svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></td>
                    </tr>

                    <!-- Section Technique -->
                    <tr class="bg-slate-50">
                        <td colspan="4" class="px-6 py-3 font-bold text-slate-900">Technique & Chantiers</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4">Planning Gantt</td>
                        <td class="px-6 py-4 text-center text-slate-300 font-mono">-</td>
                        <td class="px-6 py-4 text-center text-green-500 bg-blue-50/30"><svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></td>
                        <td class="px-6 py-4 text-center text-green-500"><svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4">Stockage GED</td>
                        <td class="px-6 py-4 text-center font-medium">5 Go</td>
                        <td class="px-6 py-4 text-center font-medium bg-blue-50/30">50 Go</td>
                        <td class="px-6 py-4 text-center font-medium">Illimité</td>
                    </tr>

                    <!-- Section Support -->
                    <tr class="bg-slate-50">
                        <td colspan="4" class="px-6 py-3 font-bold text-slate-900">Service & Support</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4">SLA (Disponibilité)</td>
                        <td class="px-6 py-4 text-center font-medium">99.5%</td>
                        <td class="px-6 py-4 text-center font-medium bg-blue-50/30">99.9%</td>
                        <td class="px-6 py-4 text-center font-medium">99.99%</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4">Type de support</td>
                        <td class="px-6 py-4 text-center">Email</td>
                        <td class="px-6 py-4 text-center bg-blue-50/30">Chat + Email</td>
                        <td class="px-6 py-4 text-center">Téléphone dédié 24/7</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- FAQ SECTION --}}
    <div class="py-24 bg-ovh-light">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-slate-900 mb-8 text-center">Questions Fréquentes</h2>
            <div class="space-y-4">
                <div x-data="{ open: false }" class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                    <button @click="open = !open" class="w-full px-6 py-4 text-left flex justify-between items-center focus:outline-none">
                        <span class="font-semibold text-slate-800">Puis-je changer d'offre en cours de route ?</span>
                        <svg class="w-5 h-5 text-slate-400 transform transition-transform" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <div x-show="open" class="px-6 pb-4 text-slate-600">
                        Absolument. Vous pouvez passer à une offre supérieure à tout moment. Le montant sera calculé au prorata. Pour passer à une offre inférieure, le changement sera effectif à la fin de votre période d'engagement.
                    </div>
                </div>

                <div x-data="{ open: false }" class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                    <button @click="open = !open" class="w-full px-6 py-4 text-left flex justify-between items-center focus:outline-none">
                        <span class="font-semibold text-slate-800">Mes données sont-elles sécurisées ?</span>
                        <svg class="w-5 h-5 text-slate-400 transform transition-transform" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <div x-show="open" class="px-6 pb-4 text-slate-600">
                        Oui. Batistack est hébergé sur des infrastructures certifiées ISO 27001 en France. Vos données sont chiffrées et sauvegardées quotidiennement sur des serveurs distants sécurisés.
                    </div>
                </div>

                <div x-data="{ open: false }" class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                    <button @click="open = !open" class="w-full px-6 py-4 text-left flex justify-between items-center focus:outline-none">
                        <span class="font-semibold text-slate-800">Quels sont les moyens de paiement acceptés ?</span>
                        <svg class="w-5 h-5 text-slate-400 transform transition-transform" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <div x-show="open" class="px-6 pb-4 text-slate-600">
                        Nous acceptons les cartes bancaires (Visa, Mastercard, Amex) ainsi que les prélèvements SEPA pour les offres annuelles Business et Enterprise.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
