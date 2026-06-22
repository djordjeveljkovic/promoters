<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-[color:var(--ds-text)]">{{ __('Forgot password') }}</h1>
        <p class="text-sm text-[color:var(--ds-text-muted)] mt-1">{{ __('Enter your email to receive a password reset link') }}</p>
    </div>

    <x-auth-session-status class="text-sm text-center" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="space-y-4">
        <x-ds.field :label="__('Email Address')" name="email" :required="true">
            <input wire:model="email" type="email" required autofocus class="ds-input" placeholder="email@example.com">
        </x-ds.field>

        <button type="submit" class="ds-btn ds-btn-primary w-full">
            {{ __('Email password reset link') }}
        </button>
    </form>

    <div class="text-center text-sm text-[color:var(--ds-text-muted)] pt-3 border-t border-[color:var(--ds-divider)]">
        {{ __('Or, return to') }}
        <a href="{{ route('login') }}" wire:navigate class="text-indigo-600 hover:underline font-medium">{{ __('log in') }}</a>
    </div>
</div>
