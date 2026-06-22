<?php

namespace App\Livewire;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * P-072: locale switcher.
 *
 * Tiny Livewire component that lives in the topbar / auth pages.
 * Clicking a flag pushes the new locale into the session and reloads.
 *
 * Why Livewire and not a plain `<form action="?lang=en">`?
 *  - the redirect target is the current URL (so a promoter mid-flow
 *    can switch languages without losing their place), and Livewire
 *    gives us `redirect()->to(url()->current())` cleanly;
 *  - a single component makes the dropdown open/close state
 *    Alpine-driven without needing extra JS.
 */
class LocaleSwitcher extends Component
{
    public string $current = '';

    public function mount(): void
    {
        $this->current = App::getLocale();
    }

    /**
     * Switch to `$locale` (must be in `app.available_locales`) and
     * redirect back to where the user was. The `SetLocale` middleware
     * will pick up the new value via the URL query string on the
     * next request and persist it into the session.
     */
    public function switch(string $locale): void
    {
        $available = array_keys((array) config('app.available_locales', []));
        if (!in_array($locale, $available, true)) {
            return;
        }

        // Persist in the session immediately so the very next request
        // picks the new language even if the user clicks around with
        // Livewire navigate before the redirect.
        Session::put('locale', $locale);
        App::setLocale($locale);
        $this->current = $locale;

        $this->dispatch('locale-changed', locale: $locale);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $available = (array) config('app.available_locales', []);
        return view('livewire.locale-switcher', [
            'available' => $available,
        ]);
    }
}
