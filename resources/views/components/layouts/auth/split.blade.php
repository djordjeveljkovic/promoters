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
                    document.documentElement.dataset.theme = dark ? 'dark' : 'light';
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
<body>
    <div class="ds-auth">
        {{ $slot }}
        {{ $side ?? '' }}
    </div>
</body>
</html>
