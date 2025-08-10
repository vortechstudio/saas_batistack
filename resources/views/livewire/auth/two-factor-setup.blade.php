<div class="max-w-2xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Authentification à deux facteurs</h2>
        
        @if (session()->has('message'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('message') }}
            </div>
        @endif

        @if ($step === 'password')
            <div class="space-y-6">
                <div>
                    <p class="text-gray-600 mb-4">
                        Pour configurer l'authentification à deux facteurs, veuillez d'abord confirmer votre mot de passe.
                    </p>
                </div>
                
                <form wire:submit="verifyPassword">
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Mot de passe actuel
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            wire:model="password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required
                        >
                        @error('password') 
                            <span class="text-red-500 text-sm">{{ $message }}</span> 
                        @enderror
                    </div>
                    
                    <button 
                        type="submit" 
                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        Continuer
                    </button>
                </form>
            </div>
        @endif

        @if ($step === 'setup')
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Configuration de l'authentificateur</h3>
                    <p class="text-gray-600 mb-4">
                        Scannez ce code QR avec votre application d'authentification (Google Authenticator, Authy, etc.)
                    </p>
                </div>
                
                <div class="text-center">
                    <div class="inline-block p-4 bg-white border-2 border-gray-300 rounded-lg">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qr_code) }}" 
                             alt="QR Code" 
                             class="w-48 h-48">
                    </div>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-600 mb-2">Ou entrez manuellement cette clé :</p>
                    <code class="text-sm bg-gray-200 px-2 py-1 rounded">{{ $secret_key }}</code>
                </div>
                
                <form wire:submit="verifySetup">
                    <div class="mb-4">
                        <label for="verification_code" class="block text-sm font-medium text-gray-700 mb-2">
                            Code de vérification (6 chiffres)
                        </label>
                        <input 
                            type="text" 
                            id="verification_code" 
                            wire:model="verification_code"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-center text-lg tracking-widest"
                            maxlength="6"
                            placeholder="000000"
                            required
                        >
                        @error('verification_code') 
                            <span class="text-red-500 text-sm">{{ $message }}</span> 
                        @enderror
                    </div>
                    
                    <button 
                        type="submit" 
                        class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                        Activer la 2FA
                    </button>
                </form>
            </div>
        @endif

        @if ($step === 'complete')
            <div class="space-y-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">2FA Activée</h3>
                    <p class="text-gray-600">
                        L'authentification à deux facteurs est maintenant active sur votre compte.
                    </p>
                </div>
                
                @if (!empty($recovery_codes))
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <h4 class="font-semibold text-yellow-800 mb-2">Codes de récupération</h4>
                        <p class="text-sm text-yellow-700 mb-3">
                            Sauvegardez ces codes dans un endroit sûr. Ils vous permettront d'accéder à votre compte si vous perdez votre appareil d'authentification.
                        </p>
                        <div class="grid grid-cols-2 gap-2 font-mono text-sm">
                            @foreach ($recovery_codes as $code)
                                <div class="bg-white p-2 rounded border">{{ $code }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                <div class="flex space-x-4">
                    @if (!$show_recovery_codes && Auth::user()->two_factor_recovery_codes)
                        <button 
                            wire:click="showRecoveryCodes" 
                            class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            Voir les codes de récupération
                        </button>
                    @endif
                    
                    <button 
                        wire:click="disable2FA" 
                        class="flex-1 bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                        onclick="return confirm('Êtes-vous sûr de vouloir désactiver la 2FA ?')"
                    >
                        Désactiver la 2FA
                    </button>
                </div>
                
                @if ($show_recovery_codes && Auth::user()->two_factor_recovery_codes)
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-800 mb-2">Vos codes de récupération actuels</h4>
                        <div class="grid grid-cols-2 gap-2 font-mono text-sm">
                            @foreach (json_decode(decrypt(Auth::user()->two_factor_recovery_codes), true) as $code)
                                <div class="bg-white p-2 rounded border">{{ $code }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
