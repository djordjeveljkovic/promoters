<?php

namespace Tests\Unit;

use App\Support\Format;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * T-005-adjacent: the Format helper is the single source of truth
 * for how dates render in the UI.  If anyone refactors it, the
 * tests below catch locale regressions immediately.
 */
class FormatTest extends TestCase
{
    public function test_date_in_english(): void
    {
        app()->setLocale('en');
        $carbon = Carbon::create(2026, 6, 22, 14, 30, 0);

        $this->assertSame('2026-06-22', Format::date($carbon));
    }

    public function test_date_in_serbian(): void
    {
        app()->setLocale('sr');
        $carbon = Carbon::create(2026, 6, 22, 14, 30, 0);

        $this->assertSame('22.06.2026.', Format::date($carbon));
    }

    public function test_datetime_in_english(): void
    {
        app()->setLocale('en');
        $carbon = Carbon::create(2026, 6, 22, 14, 30, 0);

        $this->assertSame('2026-06-22 14:30', Format::datetime($carbon));
    }

    public function test_datetime_in_serbian(): void
    {
        app()->setLocale('sr');
        $carbon = Carbon::create(2026, 6, 22, 14, 30, 0);

        $this->assertSame('22.06.2026. 14:30', Format::datetime($carbon));
    }

    public function test_string_input_is_parsed(): void
    {
        app()->setLocale('en');
        $this->assertSame('2026-06-22', Format::date('2026-06-22 12:00:00'));
    }

    public function test_money_uses_serbian_grouping(): void
    {
        $this->assertSame('12,500.00 RSD', Format::money(12500.0));
    }
}