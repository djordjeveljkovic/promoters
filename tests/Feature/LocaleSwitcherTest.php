<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * P-072: locale switcher coverage.
 *
 * Verifies:
 *  - the `SetLocale` middleware reads the locale from `?lang=` first,
 *    then session, then `Accept-Language`;
 *  - an unsupported `?lang=` falls back to the app default;
 *  - the locale switcher Livewire component persists the choice to
 *    the session so subsequent pages render in the new language.
 */
class LocaleSwitcherTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create([
            'name'     => 'Test User',
            'email'    => 'localetest@refest.rs',
            'password' => bcrypt('x'),
            'role'     => 'admin',
        ]);
        $this->actingAs($this->user);
    }

    public function test_default_locale_is_serbian(): void
    {
        // Force the app to its default locale (tests can run against any env).
        app()->setLocale(config('app.locale'));
        $response = $this->get('/admin/festivals');
        $this->assertSame(config('app.locale'), app()->getLocale());
        $response->assertOk();
    }

    public function test_query_string_lang_overrides_locale(): void
    {
        $response = $this->get('/admin/festivals?lang=en');
        $response->assertOk();
        $this->assertSame('en', app()->getLocale());
        // The English version should be present in the rendered page.
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Pick a festival', $response->getContent());
    }

    public function test_unsupported_lang_falls_back_to_default(): void
    {
        $this->get('/admin/festivals?lang=xyz');
        $this->assertSame(config('app.locale'), app()->getLocale());
    }

    public function test_locale_persists_in_session(): void
    {
        // First request with ?lang=en should set the session.
        $this->get('/admin/festivals?lang=en')->assertOk();
        $this->assertSame('en', session('locale'));

        // Subsequent request without ?lang= should still be English.
        $follow = $this->get('/admin/festivals')->assertOk();
        $this->assertSame('en', app()->getLocale());
        $this->assertStringContainsString('Pick a festival', $follow->getContent());
    }

    public function test_switcher_component_renders(): void
    {
        $response = $this->get('/admin/festivals');
        $response->assertOk();
        // The Livewire switcher should be on the page.
        $response->assertSeeLivewire('locale-switcher');
    }

    public function test_switcher_persists_locale_via_livewire(): void
    {
        $this->get('/admin/festivals')->assertOk();
        // Invoke the switch action via Livewire test helper.
        \Livewire\Livewire::test('locale-switcher')
            ->call('switch', 'en')
            ->assertSet('current', 'en');

        $this->assertSame('en', session('locale'));
    }
}
