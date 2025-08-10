<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-blue-100">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Vérification à deux facteurs
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Entrez le code de votre application d'authentification
            </p>
        </div>

        @if (session()->has('message'))
            <div class="rounded-md bg-green-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">
                            {{ session('message') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">
                            {{ session('error') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <form class="mt-8 space-y-6" wire:submit="verify">
            <div class="rounded-md shadow-sm -space-y-px">
                @if (!$use_recovery_code)
                    <div>
                        <label for="code" class="sr-only">Code de vérification</label>
                        <input 
                            id="code" 
                            name="code" 
                            type="text" 
                            wire:model="code"
                            maxlength="6"
                            class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm text-center text-lg tracking-widest"
                            placeholder="000000"
                            required
                        >
                        @error('code') 
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    <div>
                        <label for="recovery_code" class="sr-only">Code de récupération</label>
                        <input 
                            id="recovery_code" 
                            name="recovery_code" 
                            type="text" 
                            wire:model="recovery_code"
                            class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm text-center font-mono"
                            placeholder="XXXXXXXX"
                            required
                        >
                        @error('recovery_code') 
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-between">
                <div class="text-sm">
                    <button 
                        type="button"
                        wire:click="toggleRecoveryCode"
                        class="font-medium text-blue-600 hover:text-blue-500"
                    >
                        @if (!$use_recovery_code)
                            Utiliser un code de récupération
                        @else
                            Utiliser l'authentificateur
                        @endif
                    </button>
                </div>

                <div class="text-sm">
                    <span class="text-gray-500">
                        Tentatives restantes: {{ $max_attempts - $attempts }}
                    </span>
                </div>
            </div>

            <div>
                <button 
                    type="submit" 
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-blue-500 group-hover:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                        </svg>
                    </span>
                    Vérifier
                </button>
            </div>

            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Vous n'avez pas accès à votre appareil d'authentification ?
                </p>
                <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">
                    Retour à la connexion
                </a>
            </div>
        </form>
    </div>
</div>
