<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * U-005 / M-001: regression tests for the Serbian (Cyrillic) translations
 * the app uses by default.
 *
 *   - `pagination.php` (newly added — was missing entirely)
 *   - `passwords.php` (newly added)
 *   - `promoters.avatar_label` (added for the avatar upload)
 *
 * If any of these keys disappear, the Laravel fallback returns the
 * English (or the raw key) which is jarring in production.
 */
class SerbianTranslationsTest extends TestCase
{
    public function test_pagination_serbian_keys_exist(): void
    {
        app()->setLocale('sr');
        $next = __('pagination.next');
        $previous = __('pagination.previous');

        $this->assertNotSame('pagination.next', $next, 'pagination.next translation is missing');
        $this->assertNotSame('pagination.previous', $previous, 'pagination.previous translation is missing');
        $this->assertStringContainsString('Следећа', $next);
        $this->assertStringContainsString('Претходна', $previous);
    }

    public function test_passwords_serbian_keys_exist(): void
    {
        app()->setLocale('sr');
        $reset = __('passwords.reset');
        $sent = __('passwords.sent');

        $this->assertNotSame('passwords.reset', $reset);
        $this->assertNotSame('passwords.sent', $sent);
        $this->assertStringContainsString('лозинка', $reset);
        $this->assertStringContainsString('лозинк', $sent);
    }

    public function test_promoter_avatar_keys_exist_in_both_locales(): void
    {
        app()->setLocale('en');
        $enLabel = __('promoters.edit_form.avatar_label');
        app()->setLocale('sr');
        $srLabel = __('promoters.edit_form.avatar_label');

        $this->assertNotSame('promoters.edit_form.avatar_label', $enLabel, 'EN: avatar_label key missing');
        $this->assertNotSame('promoters.edit_form.avatar_label', $srLabel, 'SR: avatar_label key missing');
        $this->assertStringContainsString('Avatar', $enLabel);
    }
}