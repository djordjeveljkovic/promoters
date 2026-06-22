<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <script>
            (function () {
                try {
                    var stored = localStorage.getItem('flux.appearance') || 'system';
                    var apply = function (mode) {
                        var dark = mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                        document.documentElement.classList.toggle('dark', dark);
                    };
                    apply(stored);
                    window.Flux = window.Flux || {};
                    window.Flux.applyAppearance = function (mode) {
                        if (mode === 'system') localStorage.removeItem('flux.appearance');
                        else localStorage.setItem('flux.appearance', mode);
                        apply(mode);
                    };
                } catch (_) {}
            })();
        </script>
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-2">
                <div class="flex flex-col gap-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
