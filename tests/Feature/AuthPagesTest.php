<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Covers BUG-AUDIT-006 fix.
 *
 * Verifies that the auth pages (login, register, forgot-password,
 * reset-password) render 200 for unauthenticated guests. They used to
 * 500 on the `route('home')` call inside the shared auth layout.
 */
class AuthPagesTest extends TestCase
{
    public function test_login_page_renders_for_guest(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_register_page_renders_for_guest(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_forgot_password_page_renders_for_guest(): void
    {
        $this->get('/forgot-password')->assertOk();
    }

    public function test_reset_password_page_renders_for_guest(): void
    {
        $this->get('/reset-password/abc-token')->assertOk();
    }
}
