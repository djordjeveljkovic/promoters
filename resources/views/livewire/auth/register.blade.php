<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-[color:var(--ds-text)]">Create an account</h1>
        <p class="text-sm text-[color:var(--ds-text-muted)] mt-1">Enter your details below to create your account</p>
    </div>

    <x-auth-session-status class="text-sm text-center" :status="session('status')" />

    <form wire:submit="register" class="space-y-4">
        <x-ds.field :label="__('Name')" name="name" :required="true">
            <input wire:model="name" type="text" required autofocus autocomplete="name" class="ds-input" placeholder="Full name">
        </x-ds.field>

        <x-ds.field :label="__('Email address')" name="email" :required="true">
            <input wire:model="email" type="email" required autocomplete="email" class="ds-input" placeholder="email@example.com">
        </x-ds.field>

        <x-ds.field :label="__('Password')" name="password" :required="true">
            <input wire:model="password" type="password" required autocomplete="new-password" class="ds-input" placeholder="Password">
        </x-ds.field>

        <x-ds.field :label="__('Confirm password')" name="password_confirmation" :required="true">
            <input wire:model="password_confirmation" type="password" required autocomplete="new-password" class="ds-input" placeholder="Confirm password">
        </x-ds.field>

        <button type="submit" class="ds-btn ds-btn-primary w-full">
            Create account
        </button>
    </form>

    <div class="text-center text-sm text-[color:var(--ds-text-muted)] pt-3 border-t border-[color:var(--ds-divider)]">
        Already have an account?
        <a href="{{ route('login') }}" wire:navigate class="text-indigo-600 hover:underline font-medium">Log in</a>
    </div>
</div>
