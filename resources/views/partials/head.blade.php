<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

@if (!empty($description))
    <meta name="description" content="{{ $description }}">
@endif

@if (!empty($ogImage))
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
@endif
<meta property="og:title" content="{{ $title ?? config('app.name') }}">
<meta property="og:type" content="{{ $ogType ?? 'website' }}">
<meta property="og:url" content="{{ url()->current() }}">

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

{{-- Inter font (system fallback chain if CDN is blocked) --}}
<link rel="preconnect" href="https://fonts.bunny.net">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap">

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
