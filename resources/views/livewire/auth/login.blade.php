<div class="flex flex-col gap-6">
    <x-auth-header :title="__('login.title')" :description="__('login.description')" />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-6">
        <flux:input
            wire:model="email"
            :label="__('login.email_label')"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="email@example.com" {{-- Example format, usually not translated --}}
        />

        <div class="relative">
            <flux:input
                wire:model="password"
                :label="__('login.password_label')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('login.password_placeholder')"
                viewable
            />

            @if (Route::has('password.request'))
                <flux:link class="absolute end-0 top-0 text-sm" :href="route('password.request')" wire:navigate>
                    {{ __('login.forgot_password_link') }}
                </flux:link>
            @endif
        </div>

        <flux:checkbox wire:model="remember" :label="__('login.remember_me_label')" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">{{ __('login.submit_button') }}</flux:button>
        </div>
    </form>
</div>
