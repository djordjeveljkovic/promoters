<?php

namespace Tests\Feature;

use App\Models\Festival;
use App\Models\MailTemplate;
use App\Models\TicketOrder;
use App\Models\User;
use App\Support\Mail\MailTemplateRenderer;
use App\Support\Mail\ResolvedTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MailTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /* --------------- resolution --------------- */

    public function test_falls_back_to_built_in_view_when_no_template_is_seeded(): void
    {
        $r = app(MailTemplateRenderer::class);
        $resolved = $r->resolve('customer.tickets', null, ['order' => null]);

        $this->assertInstanceOf(ResolvedTemplate::class, $resolved);
        $this->assertSame('fallback', $resolved->source);
        $this->assertNotEmpty($resolved->body);
        $this->assertStringContainsString('<html', strtolower($resolved->body));
    }

    public function test_resolves_to_db_row_when_global_template_exists(): void
    {
        MailTemplate::create([
            'key'        => 'customer.tickets',
            'festival_id' => null,
            'name'       => 'Global tickets',
            'subject'    => 'Hello {{ $festival_name }}',
            'html_body'  => '<p>Body for {{ $festival_name }}</p>',
            'css'        => '.x { color: red; }',
            'is_active'  => true,
            'version'    => 1,
        ]);

        $r = app(MailTemplateRenderer::class);
        $festival = Festival::create(['name' => 'REFEST', 'year' => 2026, 'status' => 'active']);
        $resolved = $r->resolve('customer.tickets', $festival, ['order' => null]);

        $this->assertSame('db', $resolved->source);
        $this->assertSame('Hello REFEST 2026', $resolved->subject);
        $this->assertStringContainsString('Body for REFEST 2026', $resolved->body);
        // CSS should be inlined into a <style> block.
        $this->assertStringContainsString('.x { color: red; }', $resolved->body);
    }

    public function test_festival_specific_template_overrides_global_default(): void
    {
        MailTemplate::create([
            'key' => 'customer.tickets', 'festival_id' => null,
            'name' => 'Global', 'subject' => 'GLOBAL', 'html_body' => '<p>GLOBAL BODY</p>',
            'is_active' => true, 'version' => 1,
        ]);

        $f = Festival::create(['name' => 'Lovefest', 'year' => 2027, 'status' => 'active']);

        MailTemplate::create([
            'key' => 'customer.tickets', 'festival_id' => $f->id,
            'name' => 'Lovefest override', 'subject' => 'LOVEFEST', 'html_body' => '<p>LOVEFEST BODY</p>',
            'is_active' => true, 'version' => 1,
        ]);

        $r = app(MailTemplateRenderer::class);
        $resolved = $r->resolve('customer.tickets', $f, ['order' => null]);

        $this->assertSame('LOVEFEST', $resolved->subject);
        $this->assertStringContainsString('LOVEFEST BODY', $resolved->body);
    }

    public function test_inactive_template_is_skipped(): void
    {
        MailTemplate::create([
            'key' => 'customer.tickets', 'festival_id' => null,
            'name' => 'Inactive', 'subject' => 'INACTIVE', 'html_body' => '<p>INACTIVE</p>',
            'is_active' => false, 'version' => 1,
        ]);

        $r = app(MailTemplateRenderer::class);
        $resolved = $r->resolve('customer.tickets', null, ['order' => null]);
        $this->assertSame('fallback', $resolved->source);
    }

    public function test_disabled_festival_template_falls_back_to_global(): void
    {
        MailTemplate::create([
            'key' => 'customer.tickets', 'festival_id' => null,
            'name' => 'Global', 'subject' => 'GLOBAL', 'html_body' => '<p>GLOBAL</p>',
            'is_active' => true, 'version' => 1,
        ]);

        $f = Festival::create(['name' => 'Lovefest', 'year' => 2027, 'status' => 'active']);

        MailTemplate::create([
            'key' => 'customer.tickets', 'festival_id' => $f->id,
            'name' => 'Lovefest OFF', 'subject' => 'OFF', 'html_body' => '<p>OFF</p>',
            'is_active' => false, 'version' => 1,
        ]);

        $r = app(MailTemplateRenderer::class);
        $resolved = $r->resolve('customer.tickets', $f, ['order' => null]);
        $this->assertSame('GLOBAL', $resolved->subject);
    }

    /* --------------- rendering --------------- */

    public function test_renders_blade_placeholders_in_html_body(): void
    {
        MailTemplate::create([
            'key' => 'customer.tickets', 'festival_id' => null,
            'name' => 'Test', 'subject' => 'X',
            'html_body' => '<h1>Hi {{ $customer_name }}</h1><p>Order {{ $order_number }}</p>',
            'is_active' => true, 'version' => 1,
        ]);

        $r = app(MailTemplateRenderer::class);
        $resolved = $r->resolve('customer.tickets', null, [
            'order' => new TicketOrder(['order_number' => 'ABCD12', 'email' => 'jane@example.com']),
        ]);

        $this->assertStringContainsString('Hi jane', $resolved->body);
        $this->assertStringContainsString('Order ABCD12', $resolved->body);
    }

    public function test_fragments_are_wrapped_in_a_minimal_html_skeleton(): void
    {
        $tpl = new MailTemplate([
            'name' => 'Test',
            'html_body' => '<p>just a paragraph</p>',
        ]);
        $tpl->exists = true;

        $r = app(MailTemplateRenderer::class);
        $body = $r->renderHtml($tpl, ['order' => null]);

        $this->assertStringContainsString('<!DOCTYPE html>', $body);
        $this->assertStringContainsString('<body', $body);
        $this->assertStringContainsString('just a paragraph', $body);
    }

    public function test_full_documents_keep_their_existing_head(): void
    {
        $tpl = new MailTemplate([
            'name' => 'Test',
            'html_body' => '<!DOCTYPE html><html><head><title>My email</title></head><body><p>X</p></body></html>',
        ]);
        $tpl->exists = true;

        $r = app(MailTemplateRenderer::class);
        $body = $r->renderHtml($tpl, ['order' => null]);

        $this->assertStringContainsString('<title>My email</title>', $body);
        $this->assertStringNotContainsString('/* wrapper title */', $body);
    }

    public function test_css_is_injected_before_existing_head_close(): void
    {
        $tpl = new MailTemplate([
            'name' => 'Test',
            'css'   => '.hero { background: #000; }',
            'html_body' => '<html><head><title>X</title></head><body></body></html>',
        ]);
        $tpl->exists = true;

        $r = app(MailTemplateRenderer::class);
        $body = $r->renderHtml($tpl, ['order' => null]);

        $this->assertStringContainsString('<style>', $body);
        $this->assertStringContainsString('.hero { background: #000; }', $body);
    }

    public function test_render_string_handles_placeholder_with_or_without_dollar(): void
    {
        $r = app(MailTemplateRenderer::class);
        $this->assertSame('Hello world', $r->renderString('Hello {{ name }}', ['name' => 'world']));
        $this->assertSame('Hello world', $r->renderString('Hello {{ $name }}', ['name' => 'world']));
        // Missing keys are replaced with the empty string, so a space is
        // preserved (just like every other mail template engine).
        $this->assertSame('Hello ', $r->renderString('Hello {{ missing }}', ['name' => 'world']));
    }

    public function test_template_error_is_captured_and_inlined_as_comment(): void
    {
        $tpl = new MailTemplate([
            'name' => 'Bad',
            'html_body' => '<p>{{ $nonexistent->chainThatWillFail() }}</p>',
        ]);
        $tpl->exists = true;

        $r = app(MailTemplateRenderer::class);
        $body = $r->renderHtml($tpl, []);

        $this->assertStringContainsString('<!-- template error:', $body);
    }

    /* --------------- livewire / editor --------------- */

    public function test_superadmin_can_view_the_mail_template_index(): void
    {
        $super = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($super)
            ->get(route('superadmin.mail-templates.index'))
            ->assertOk();
    }

    public function test_promoter_cannot_view_the_mail_template_index(): void
    {
        $promoter = User::factory()->create(['role' => 'promoter']);
        $this->actingAs($promoter)
            ->get(route('superadmin.mail-templates.index'))
            ->assertStatus(403);
    }

    public function test_festival_admin_can_view_festival_scoped_mail_templates(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $f = Festival::create(['name' => 'X', 'year' => 2026, 'status' => 'active']);
        $admin->festivals()->attach($f->id, ['role_in_festival' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.mail-templates.index', $f->slug))
            ->assertOk();
    }

    public function test_admin_without_festival_access_is_blocked(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $f = Festival::create(['name' => 'X', 'year' => 2026, 'status' => 'active']);

        $this->actingAs($admin)
            ->get(route('admin.mail-templates.index', $f->slug))
            ->assertStatus(403);
    }

    public function test_superadmin_can_create_a_new_global_template(): void
    {
        $super = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($super);

        \Livewire\Livewire::test(\App\Livewire\Admin\MailTemplates\Editor::class)
            ->call('newGlobal', 'customer.tickets')
            ->set('name', 'My new template')
            ->set('html_body', '<p>Hello world! This is a long enough body to pass validation rules.</p>')
            ->set('subject', 'Hi {{ $festival_name }}')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('editing', fn ($v) => is_int($v));

        $this->assertDatabaseHas('mail_templates', [
            'key' => 'customer.tickets',
            'festival_id' => null,
            'name' => 'My new template',
        ]);
    }

    public function test_superadmin_can_create_a_festival_scoped_template(): void
    {
        $super = User::factory()->create(['role' => 'superadmin']);
        $f = Festival::create(['name' => 'Lovefest', 'year' => 2027, 'status' => 'active']);
        $this->actingAs($super);

        \Livewire\Livewire::test(\App\Livewire\Admin\MailTemplates\Editor::class)
            ->call('newForFestival', 'customer.tickets', $f->id)
            ->set('name', 'Lovefest special')
            ->set('html_body', '<p>Hello Lovefest! This is a long enough body to pass validation rules.</p>')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mail_templates', [
            'key' => 'customer.tickets',
            'festival_id' => $f->id,
        ]);
    }

    public function test_updating_a_template_bumps_its_version(): void
    {
        $super = User::factory()->create(['role' => 'superadmin']);
        $tpl = MailTemplate::create([
            'key' => 'customer.tickets', 'festival_id' => null,
            'name' => 'v1', 'subject' => 's', 'html_body' => str_repeat('a', 50),
            'is_active' => true, 'version' => 1,
        ]);

        \Livewire\Livewire::actingAs($super)
            ->test(\App\Livewire\Admin\MailTemplates\Editor::class, ['editing' => $tpl->id])
            ->set('name', 'v2')
            ->set('html_body', str_repeat('b', 50))
            ->call('save')
            ->assertHasNoErrors();

        $tpl->refresh();
        $this->assertSame(2, $tpl->version);
        $this->assertSame('v2', $tpl->name);
    }
}
