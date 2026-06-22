<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-[color:var(--ds-text)]">{{ __('Reset password') }}</h1>
        <p class="text-sm text-[color:var(--ds-text-muted)] mt-1">{{ __('Please enter your new password below') }}</p>
    </div>

    <x-auth-session-status class="text-sm text-center" :status="session('status')" />

    <form wire:submit="resetPassword" class="space-y-4">
        <x-ds.field :label="__('Email')" name="email" :required="true">
            <input wire:model="email" type="email" required autocomplete="email" class="ds-input">
        </x-ds.field>

        <x-ds.field :label="__('Password')" name="password" :required="true">
            <input wire:model="password" type="password" required autocomplete="new-password" class="ds-input" placeholder="Password">
        </x-ds.field>

        <x-ds.field :label="__('Confirm password')" name="password_confirmation" :required="true">
            <input wire:model="password_confirmation" type="password" required autocomplete="new-password" class="ds-input" placeholder="Confirm password">
        </x-ds.field>

        <button type="submit" class="ds-btn ds-btn-primary w-full">
            {{ __('Reset password') }}
        </button>
    </form>
</div>
