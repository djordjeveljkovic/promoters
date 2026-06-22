<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', ['title' => __('REFEST Festival — Karte')])

    <style>
        /* ---------- Landing page styles ---------- */
        .landing {
            position: relative;
            min-height: 100vh;
            width: 100%;
            overflow: hidden;
            color: #f8f4ff;
            background:
                radial-gradient(ellipse at top, #2a0942 0%, transparent 55%),
                radial-gradient(ellipse at bottom right, #4d0a5e 0%, transparent 55%),
                radial-gradient(ellipse at bottom left, #1a0a3e 0%, transparent 50%),
                linear-gradient(180deg, #060010 0%, #0a0218 100%);
        }

        .landing__canvas {
            position: absolute;
            inset: 0;
            width: 100% !important;
            height: 100% !important;
            display: block;
        }

        .landing__overlay {
            position: relative;
            z-index: 2;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.25rem;
            text-align: center;
            background:
                radial-gradient(ellipse at center, rgba(0,0,0,0) 0%, rgba(0,0,0,0.55) 80%);
        }

        .landing__nav {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 3;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
        }

        .landing__brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            font-size: 0.85rem;
            text-transform: uppercase;
            color: #fff;
        }

        .landing__brand-mark {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            background: linear-gradient(135deg, #ff2d92 0%, #5ce1ff 100%);
            box-shadow: 0 0 18px rgba(255, 45, 146, 0.6);
        }

        .landing__lang {
            font-size: 0.75rem;
            letter-spacing: 0.1em;
            color: rgba(255,255,255,0.55);
            text-transform: uppercase;
        }

        .landing__eyebrow {
            display: inline-block;
            padding: 0.4rem 0.9rem;
            border-radius: 999px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.18);
            backdrop-filter: blur(8px);
            font-size: 0.7rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: #ffd1ec;
            margin-bottom: 1.25rem;
        }

        .landing__title {
            font-size: clamp(2.4rem, 8vw, 5.5rem);
            line-height: 1;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin: 0 0 1rem;
            background: linear-gradient(135deg, #fff 0%, #ff2d92 45%, #5ce1ff 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 60px rgba(255, 45, 146, 0.25);
        }

        .landing__subtitle {
            font-size: clamp(1rem, 2.4vw, 1.25rem);
            color: rgba(255,255,255,0.78);
            max-width: 38ch;
            margin: 0 auto 2.5rem;
            line-height: 1.5;
        }

        .landing__cta-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 3rem;
        }

        .landing__cta {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.95rem 1.6rem;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
            border: 1px solid transparent;
        }

        .landing__cta--primary {
            background: linear-gradient(135deg, #ff2d92 0%, #ff5fb1 100%);
            color: #fff;
            box-shadow: 0 8px 32px rgba(255, 45, 146, 0.45);
        }

        .landing__cta--primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(255, 45, 146, 0.6);
        }

        .landing__cta--ghost {
            background: rgba(255,255,255,0.06);
            border-color: rgba(255,255,255,0.22);
            color: #fff;
            backdrop-filter: blur(6px);
        }

        .landing__cta--ghost:hover {
            background: rgba(255,255,255,0.12);
            transform: translateY(-2px);
        }

        .landing__stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1.25rem;
            max-width: 540px;
            width: 100%;
            margin-top: 0.5rem;
        }

        .landing__stat {
            padding: 1rem;
            border-radius: 1rem;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(8px);
        }

        .landing__stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff;
            display: block;
        }

        .landing__stat-label {
            font-size: 0.7rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.55);
            margin-top: 0.25rem;
        }

        .landing__footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 3;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: rgba(255,255,255,0.45);
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        /* Fallback when WebGL is unavailable */
        .landing--fallback {
            background: linear-gradient(180deg, #060010 0%, #2a0942 100%);
        }

        .landing--fallback .landing__canvas { display: none; }

        @media (prefers-reduced-motion: reduce) {
            .landing__cta { transition: none; }
        }

        @media (max-width: 540px) {
            .landing__stats { grid-template-columns: 1fr; }
            .landing__footer { flex-direction: column; gap: 0.4rem; }
        }
    </style>
</head>

<body class="landing">

    {{-- WebGL container. Filled by resources/js/landing/scene.js --}}
    <div id="landing-scene" class="landing__scene-host" aria-hidden="true"></div>

    {{-- Top navigation strip --}}
    <nav class="landing__nav" aria-label="Primary">
        <div class="landing__brand">
            <span class="landing__brand-mark"></span>
            <span>{{ config('app.name', 'Promoteri') }}</span>
        </div>
        <div class="landing__lang">{{ strtoupper(app()->getLocale()) }}</div>
    </nav>

    {{-- Main hero --}}
    <main class="landing__overlay">
        @php
            // M-010: drive the welcome-page hero from config + a tiny
            // runtime discovery of the active festival, so the page
            // reflects whatever the superadmin has configured.
            $appName      = config('app.name', 'Promoteri');
            $currentYear  = (int) date('Y');
            $activeFests  = \App\Models\Festival::query()
                ->where('status', 'active')
                ->where('is_public', true)
                ->orderByDesc('year')
                ->limit(1)
                ->get();
            $heroFestival = $activeFests->first();
            $heroName     = $heroFestival?->name ?? $appName;
            $heroYear     = $heroFestival?->year ?? $currentYear;
        @endphp
        <span class="landing__eyebrow">{{ $heroYear }} · {{ __('Ulaznice u prodaji') }}</span>

        <h1 class="landing__title">{{ $heroName }} {{ $heroFestival ? $heroYear : '' }}</h1>

        <p class="landing__subtitle">
            {{ __('Tvoj sledeći letnji beg počinje ovde.') }}
            {{ __('Rezerviši karte, prati program i podeli trenutak sa prijateljima — sve na jednom mestu.') }}
        </p>

        <div class="landing__cta-row">
            @auth
                <a href="{{ route('dashboard') }}" class="landing__cta landing__cta--primary">
                    Otvori kontrolnu tablu →
                </a>
            @else
                <a href="{{ route('login') }}" class="landing__cta landing__cta--primary">
                    Prijavi se
                </a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="landing__cta landing__cta--ghost">
                        Registruj se
                    </a>
                @endif
            @endauth
        </div>

        <div class="landing__stats">
            <div class="landing__stat">
                <span class="landing__stat-value">12k+</span>
                <span class="landing__stat-label">Prodatih karata</span>
            </div>
            <div class="landing__stat">
                <span class="landing__stat-value">40+</span>
                <span class="landing__stat-label">Izvođača</span>
            </div>
            <div class="landing__stat">
                <span class="landing__stat-value">3</span>
                <span class="landing__stat-label">Dane muzike</span>
            </div>
        </div>
    </main>

    <footer class="landing__footer">
        <span>© {{ date('Y') }} REFEST Festival</span>
        <span>
            <a href="{{ route('public.festival', $publicSlug ?? 'refest-2026') }}" class="hover:underline">Public festival page</a>
            · Built with Laravel &amp; WebGL
        </span>
    </footer>

</body>
</html>