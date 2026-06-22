<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-[color:var(--ds-text)]">{{ __('login.title') }}</h1>
        <p class="text-sm text-[color:var(--ds-text-muted)] mt-1">{{ __('login.description') }}</p>
    </div>

    <x-auth-session-status class="text-sm text-center" :status="session('status')" />

    <form wire:submit="login" class="space-y-5">
        <x-ds.field :label="__('login.email_label')" name="email" :required="true" :error="$errors->first('email')">
            <input wire:model="email" type="email" required autofocus autocomplete="email" class="ds-input" placeholder="email@example.com">
        </x-ds.field>

        <div class="relative">
            <x-ds.field :label="__('login.password_label')" name="password" :required="true">
                <input wire:model="password" type="password" required autocomplete="current-password" class="ds-input" :placeholder="__('login.password_placeholder')">
            </x-ds.field>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" wire:navigate class="absolute right-0 top-0 text-xs text-indigo-600 hover:underline">
                    {{ __('login.forgot_password_link') }}
                </a>
            @endif
        </div>

        <label class="inline-flex items-center gap-2 text-sm text-[color:var(--ds-text)]">
            <input wire:model="remember" type="checkbox" class="ds-checkbox">
            {{ __('login.remember_me_label') }}
        </label>

        <button type="submit" class="ds-btn ds-btn-primary w-full">
            {{ __('login.submit_button') }}
        </button>
    </form>

    @if (Route::has('register'))
        <div class="text-center text-sm text-[color:var(--ds-text-muted)] pt-3 border-t border-[color:var(--ds-divider)]">
            {{ __('login.no_account_text') }}
            <a href="{{ route('register') }}" wire:navigate class="text-indigo-600 hover:underline font-medium">{{ __('login.sign_up_link') }}</a>
        </div>
    @endif
</div>
