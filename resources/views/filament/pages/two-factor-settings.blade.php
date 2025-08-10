<x-filament-panels::page>
    @if(auth()->user()->two_factor_enabled)
        <div class="card w-96 bg-green-100 border-green-300 rounded-md card-lg shadow-sm">
            <div class="card-body">
                <h2 class="card-title">2FA Activé</h2>
                <p>L'authentification à deux facteurs est active sur votre compte.</p>
                @if (auth()->user()->two_factor_verified_at)
                    <p class="mt-1">Dernière vérification : {{ auth()->user()->two_factor_verified_at->diffForHumans() }}</p>
                @endif
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informations de sécurité</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">2FA Activée</p>
                                <p class="text-sm text-gray-500">Depuis {{ auth()->user()->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Codes de récupération</p>
                                <p class="text-sm text-gray-500">
                                    {{ auth()->user()->two_factor_recovery_codes ? count(json_decode(decrypt(auth()->user()->two_factor_recovery_codes), true)) : 0 }} codes disponibles
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    @else
        <div class="card w-96 bg-red-100 border-red-300 rounded-md card-lg shadow-sm">
            <div class="card-body">
                <h2 class="card-title">2FA Non Activé</h2>
                <p>Vous n'avez pas activé l'authentification à deux facteurs sur votre compte.</p>
            </div>
        </div>
    @endif

    <div class="card w-96 bg-red-100 border-red-300 rounded-md card-lg shadow-sm">
        <div class="card-body">
            <h2 class="card-title">Information sur l'autentification 2FA</h2>
            <div>
                <h4 class="text-sm font-medium text-gray-900 mb-2">Qu'est-ce que la 2FA ?</h4>
                <p class="text-sm text-gray-600">
                    L'authentification à deux facteurs ajoute une couche de sécurité supplémentaire à votre compte.
                    En plus de votre mot de passe, vous devrez entrer un code généré par votre application d'authentification.
                </p>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-900 mb-2">Applications recommandées :</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Google Authenticator</li>
                    <li>• Microsoft Authenticator</li>
                    <li>• Authy</li>
                    <li>• 1Password</li>
                </ul>
            </div>
            <a href="{{ route('two-factor.setup') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                @if(auth()->user()->two_factor_enabled)
                    Gérer 2FA
                @else
                    Activer 2FA
                @endif
            </a>
        </div>
    </div>
</x-filament-panels::page>
