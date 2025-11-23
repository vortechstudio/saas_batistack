<div class="">
    <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

    <form wire:submit="register">
        {{ $this->form }}
    </form>
</div>
