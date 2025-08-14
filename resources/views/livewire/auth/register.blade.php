<div class="flex flex-col gap-6">
    <x-auth-header
        :title="__('Create an account')"
        :description="__('Enter your details below to create your account and company profile')"
    />

    <!-- Barre de progression -->
    <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
        <div class="bg-blue-600 h-2 rounded-full transition-all duration-300"
             style="width: {{ $this->getProgressPercentage() }}%"></div>
    </div>

    <div class="text-center text-sm text-gray-600 dark:text-gray-400">
        Étape {{ $currentStep }} sur {{ $totalSteps }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-6">
        @if($currentStep === 1)
            <!-- Étape 1: Informations utilisateur -->
            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('Personal Information') }}
                </h3>

                <!-- Name -->
                <flux:input
                    wire:model="name"
                    :label="__('Full Name')"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    :placeholder="__('Your full name')"
                />

                <!-- Email Address -->
                <flux:input
                    wire:model="email"
                    :label="__('Email Address')"
                    type="email"
                    required
                    autocomplete="email"
                    placeholder="email@example.com"
                />

                <!-- Password -->
                <flux:input
                    wire:model="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="__('Password')"
                    viewable
                />

                <!-- Confirm Password -->
                <flux:input
                    wire:model="password_confirmation"
                    :label="__('Confirm Password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="__('Confirm password')"
                    viewable
                />

                <div class="flex justify-end">
                    <flux:button type="button" wire:click="nextStep" variant="primary" class="w-full">
                        {{ __('Next Step') }} →
                    </flux:button>
                </div>
            </div>
        @endif

        @if($currentStep === 2)
            <!-- Étape 2: Informations entreprise -->
            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('Company Information') }}
                </h3>

                <!-- Company Name -->
                <flux:input
                    wire:model="company_name"
                    :label="__('Company Name')"
                    type="text"
                    required
                    :placeholder="__('Your company name')"
                />

                <!-- Contact Name -->
                <flux:input
                    wire:model="contact_name"
                    :label="__('Contact Name')"
                    type="text"
                    required
                    :placeholder="__('Primary contact person')"
                />

                <!-- Phone -->
                <flux:input
                    wire:model="phone"
                    :label="__('Phone Number')"
                    type="tel"
                    required
                    :placeholder="__('Company phone number')"
                />

                <!-- Address -->
                <flux:input
                    wire:model="address"
                    :label="__('Address')"
                    type="text"
                    required
                    :placeholder="__('Street address')"
                />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- City -->
                    <flux:input
                        wire:model="city"
                        :label="__('City')"
                        type="text"
                        required
                        :placeholder="__('City')"
                    />

                    <!-- Postal Code -->
                    <flux:input
                        wire:model="postal_code"
                        :label="__('Postal Code')"
                        type="text"
                        required
                        :placeholder="__('Postal code')"
                    />
                </div>

                <!-- Country -->
                <flux:select
                    wire:model="country"
                    :label="__('Country')"
                    required
                >
                    <option value="FR">France</option>
                    <option value="BE">Belgique</option>
                    <option value="CH">Suisse</option>
                    <option value="CA">Canada</option>
                    <option value="LU">Luxembourg</option>
                </flux:select>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- SIRET -->
                    <flux:input
                        wire:model="siret"
                        :label="__('SIRET (Optional)')"
                        type="text"
                        :placeholder="__('Company SIRET number')"
                    />

                    <!-- VAT Number -->
                    <flux:input
                        wire:model="vat_number"
                        :label="__('VAT Number (Optional)')"
                        type="text"
                        :placeholder="__('EU VAT number')"
                    />
                </div>

                <div class="flex gap-4">
                    <flux:button type="button" wire:click="previousStep" variant="ghost" class="flex-1">
                        ← {{ __('Previous') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary" class="flex-1">
                        {{ __('Create Account') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        <span>{{ __('Already have an account?') }}</span>
        <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
    </div>
</div>
