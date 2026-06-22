<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body>
    <div class="ds-auth">
        {{ $slot }}
        {{ $side ?? '' }}
    </div>
</body>
</html>
