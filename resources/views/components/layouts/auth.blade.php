@php
    $isAuthView = in_array(request()->route()?->getName(), ['login', 'register', 'password.request', 'password.reset', 'verification.notice']);
@endphp

<x-layouts.auth.split :title="$title ?? null">
    <div class="ds-auth-form">
        <div class="ds-auth-form-inner">
            <a href="{{ url('/') }}" wire:navigate class="inline-flex items-center gap-2 mb-8">
                <div class="w-8 h-8 rounded-md bg-indigo-600 text-white flex items-center justify-center font-semibold">P</div>
                <span class="text-sm font-semibold text-[color:var(--ds-text)]">{{ config('app.name', 'Promoteri') }}</span>
            </a>

            <x-flash-messages />

            {{ $slot }}

            <div class="mt-6 text-xs text-[color:var(--ds-text-subtle)] text-center">
                &copy; {{ date('Y') }} {{ config('app.name', 'Promoteri') }}
            </div>
        </div>
    </div>

    <x-slot:side>
        <div class="ds-auth-side">
            <div>
                <div class="w-10 h-10 rounded-md bg-indigo-600 text-white flex items-center justify-center font-semibold text-lg">P</div>
                <div class="mt-8 text-3xl font-semibold leading-tight">
                    Festival ticket sales,<br>made simple.
                </div>
                <p class="mt-3 text-sm text-neutral-400 max-w-sm">
                    Sell tickets, manage promoters, track commissions and deliver QR-coded tickets — all from one place.
                </p>
            </div>
            <div class="text-xs text-neutral-500">
                Secure · Multi-festival · Built for promoters
            </div>
        </div>
    </x-slot:side>
</x-layouts.auth.split>
