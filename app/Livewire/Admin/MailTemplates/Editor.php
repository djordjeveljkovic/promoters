<?php

namespace App\Livewire\Admin\MailTemplates;

use App\Models\Festival;
use App\Models\MailTemplate;
use App\Models\TicketOrder;
use App\Models\User;
use App\Support\Mail\MailTemplateRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Mail-template editor.
 *
 * Two modes:
 *  - **List**  (when no `editing` is set) — table of all templates,
 *               grouped by key, with a "New template" button per row.
 *  - **Edit**  (when `editing` is set)            — side-by-side HTML/CSS
 *               editor and live preview iframe, plus the variable helper
 *               panel on the right.
 *
 * The component supports *both* festival-scoped and global templates:
 *  - When constructed with a `?festival=` query param, all save/load
 *    actions target that festival's override.
 *  - Otherwise we're in superadmin mode and the editor can create global
 *    defaults (festival_id = null) or festival overrides.
 *
 *  Note: no `#[Layout]` attribute here — both routes that mount this
 *  component (superadmin and festival-scoped admin) already wrap it
 *  in `<x-layouts.app>`, so adding it here would render the sidebar
 *  twice.
 */
class Editor extends Component
{
    /* --------------- URL state --------------- */
    #[Url] public ?int $editing = null;            // MailTemplate id, or null for new
    #[Url] public ?string $key = null;             // template key, used for "new"
    #[Url] public ?string $festivalSlug = null;    // festival slug (URL) → resolved to id
    public mixed $festival = null;                 // festival id (DB), or a slug/object until mount() normalises it
    public ?int $festivalId = null;                // canonical festival id used in queries

    /* --------------- Form state --------------- */
    public string $name = '';
    public string $subject = '';
    public string $html_body = '';
    public ?string $css = null;
    public ?string $from_address = null;
    public ?string $from_name = null;
    public bool $is_active = true;

    /* --------------- Preview state --------------- */
    public string $previewSource = '';   // the live HTML the preview pane is showing
    public string $previewStatus = 'idle'; // 'idle' | 'rendered' | 'error'
    public ?string $previewError = null;

    /* --------------- Lifecycle --------------- */

    public function mount($festival = null): void
    {
        // The route's `festival` parameter can be a slug, an ID, or a
        // Festival model (handled by the EnsureFestivalAccess middleware).
        // Normalise to a private int id we use in queries; the public
        // `$festival` field stays as-is for display purposes.
        $resolved = $this->resolveFestivalId($festival);
        if ($resolved) {
            $this->festivalId = $resolved;
            $this->festival = $resolved;
        }

        // Also honour the dedicated `festivalSlug` URL query param so the
        // editor works inside the superadmin scope where there is no
        // route-model binding.
        if ($this->festivalSlug) {
            $this->festivalId = $this->resolveFestivalId($this->festivalSlug);
            $this->festival = $this->festivalId;
        }

        if ($this->editing) {
            $this->loadTemplate(MailTemplate::findOrFail($this->editing));
        } elseif ($this->key) {
            $this->prepareForNew($this->key, $this->festivalId);
        }
    }

    private function resolveFestivalId($value): ?int
    {
        if ($value === null || $value === '') return null;
        if ($value instanceof Festival) return (int) $value->id;
        if (is_numeric($value)) return (int) $value;
        return Festival::where('slug', (string) $value)->value('id');
    }

    public function loadTemplate(MailTemplate $tpl): void
    {
        $this->editing      = $tpl->id;
        $this->key          = $tpl->key;
        $this->festivalId   = $tpl->festival_id;
        $this->festival     = $tpl->festival_id;
        $this->name         = $tpl->name;
        $this->subject      = $tpl->subject ?? '';
        $this->html_body    = $tpl->html_body;
        $this->css          = $tpl->css;
        $this->from_address = $tpl->from_address;
        $this->from_name    = $tpl->from_name;
        $this->is_active    = $tpl->is_active;

        $this->renderPreview();
    }

    public function prepareForNew(string $key, ?int $festivalId = null): void
    {
        $this->editing      = null;
        $this->key          = $key;
        $this->festivalId   = $festivalId;
        $this->festival     = $festivalId;

        $festival = $festivalId ? Festival::find($festivalId) : null;

        // Pre-fill from an existing template (closest match) so the user
        // doesn't start from a blank canvas.
        $source = app(MailTemplateRenderer::class)->find($key, $festival);

        $fLabel = $festival?->displayName() ?? 'global default';
        $this->name = match ($key) {
            'customer.tickets' => "Tickets email — {$fLabel}",
            default            => Str::headline($key) . " — {$fLabel}",
        };
        $this->subject      = $source?->subject ?? app(MailTemplateRenderer::class)->defaultSubject($key, $festival);
        $this->html_body    = $source?->html_body ?? $this->starterHtml($key, $festival);
        $this->css          = $source?->css;
        $this->from_address = $source?->from_address;
        $this->from_name    = $source?->from_name;
        $this->is_active    = true;

        $this->renderPreview();
    }

    public function newGlobal(string $key): void
    {
        $this->prepareForNew($key, null);
    }

    public function newForFestival(string $key, int $festivalId): void
    {
        $this->prepareForNew($key, $festivalId);
    }

    public function edit(int $id): void
    {
        $this->loadTemplate(MailTemplate::findOrFail($id));
    }

    public function cancelEdit(): void
    {
        $this->editing      = null;
        $this->key          = null;
        $this->festivalId   = null;
        $this->festival     = null;
        $this->name         = '';
        $this->subject      = '';
        $this->html_body    = '';
        $this->css          = null;
        $this->previewSource = '';
        $this->previewStatus = 'idle';
        $this->previewError  = null;
    }

    /* --------------- Save / delete --------------- */

    public function save(): void
    {
        $this->validate([
            'key'         => ['required', 'string', 'max:100'],
            'name'        => ['required', 'string', 'max:160'],
            'subject'     => ['nullable', 'string', 'max:255'],
            'html_body'   => ['required', 'string', 'min:20'],
            'festival'    => ['nullable', 'integer', 'exists:festivals,id'],
            'from_address'=> ['nullable', 'email', 'max:255'],
            'from_name'   => ['nullable', 'string', 'max:160'],
        ]);

        $payload = [
            'key'           => $this->key,
            'festival_id'   => $this->festivalId,
            'name'          => $this->name,
            'subject'       => $this->subject,
            'html_body'     => $this->html_body,
            'css'           => $this->css,
            'from_address'  => $this->from_address,
            'from_name'     => $this->from_name,
            'is_active'     => $this->is_active,
            'last_edited_by'=> auth()->id(),
        ];

        if ($this->editing) {
            $tpl = MailTemplate::findOrFail($this->editing);
            $tpl->version = ($tpl->version ?? 1) + 1;
            $tpl->fill($payload)->save();
            session()->flash('success', "Template updated (v{$tpl->version}).");
        } else {
            $tpl = MailTemplate::create($payload + ['version' => 1]);
            $this->editing = $tpl->id;
            session()->flash('success', 'Template created.');
        }

        // Bust the runtime cache so the next send picks up the new body.
        Cache::forget('mail-template:resolved:' . $this->key . ':' . ($this->festivalId ?? 'global'));

        $this->renderPreview();
    }

    public function delete(int $id): void
    {
        $tpl = MailTemplate::findOrFail($id);
        $tpl->delete();
        Cache::forget('mail-template:resolved:' . $tpl->key . ':' . ($tpl->festival_id ?? 'global'));

        if ($this->editing === $id) {
            $this->cancelEdit();
        }

        session()->flash('success', 'Template removed (the next send will fall back to the built-in view or the next-most-specific override).');
    }

    public function duplicateAsGlobal(): void
    {
        if (!$this->editing) return;
        $tpl = MailTemplate::findOrFail($this->editing);
        $new = $tpl->replicate(['festival_id']);
        $new->festival_id = null;
        $new->name = $tpl->name . ' (global copy)';
        $new->version = 1;
        $new->save();
        $this->loadTemplate($new->fresh());
        session()->flash('success', 'Copied to global defaults.');
    }

    /* --------------- Preview --------------- */

    /**
     * Live re-render of the preview pane.  Wired to `wire:model.live` on
     * the html_body textarea, but throttled server-side by Livewire.
     */
    public function updatedHtmlBody(): void
    {
        $this->renderPreview();
    }
    public function updatedCss(): void
    {
        $this->renderPreview();
    }
    public function updatedSubject(): void { /* no-op, but kept for symmetry */ }

    public function renderPreview(): void
    {
        try {
            $stub = $this->makeStub($this->key ?? 'customer.tickets');
            $renderer = app(MailTemplateRenderer::class);

            // Render using a fake in-memory template so we can preview
            // unsaved changes without writing to the DB.
            $fakeTpl = new MailTemplate([
                'key'        => $this->key,
                'festival_id'=> $this->festivalId,
                'name'       => $this->name,
                'subject'    => $this->subject,
                'html_body'  => $this->html_body,
                'css'        => $this->css,
            ]);
            $fakeTpl->exists = true;

            $this->previewSource = $renderer->renderHtml($fakeTpl, $stub);
            $this->previewStatus = 'rendered';
            $this->previewError  = null;
        } catch (\Throwable $e) {
            $this->previewStatus = 'error';
            $this->previewError  = $e->getMessage();
        }
    }

    /**
     * Build a TicketOrder-shaped data array for the preview so the user
     * sees something representative.
     */
    private function makeStub(string $key): array
    {
        $festival = $this->festivalId
            ? Festival::with('ticketTypes')->find($this->festivalId)
            : Festival::with('ticketTypes')->orderByDesc('year')->first();

        // Build a synthetic order so the template can render items / tickets.
        $order = new TicketOrder([
            'order_number' => 'PREVIE',
            'email'        => 'kupac@example.com',
            'paid'         => 12000,
            'total'        => 12000,
            'job_status'   => 'completed',
        ]);
        $order->id = 12345;
        $order->created_at = now();
        $order->festival = $festival;
        $order->setRelation('items', collect([
            (object) ['quantity' => 2, 'ticketType' => $festival?->ticketTypes->first()],
        ]));
        $order->setRelation('tickets', collect([
            (object) [
                'code'        => 'PREVIEW-001',
                'image_path'  => null,
                'ticketType'  => $festival?->ticketTypes->first(),
            ],
            (object) [
                'code'        => 'PREVIEW-002',
                'image_path'  => null,
                'ticketType'  => $festival?->ticketTypes->first(),
            ],
        ]));

        $promoter = auth()->user() ?? User::first();

        return [
            'order'    => $order,
            'festival' => $festival,
            'promoter' => $promoter,
        ];
    }

    private function starterHtml(string $key, ?Festival $festival): string
    {
        $fName = $festival?->displayName() ?? 'festival';
        $brand = $this->brandColor($festival);

        return match ($key) {
            'promoter.new_order' => <<<HTML
<h1 style="font-family: -apple-system, BlinkMacSystemFont, sans-serif; color: {$brand};">
    New order received
</h1>
<p>Hi {{ \$promoter_name ?? 'promoter' }},</p>
<p>An order was just placed on <strong>{{ \$festival_name ?? '{$fName}' }}</strong>:</p>
<ul>
    <li>Order #{{ \$order_number ?? '—' }} ({{ \$order_total ?? '—' }})</li>
    <li>Customer: {{ \$customer_email ?? '—' }}</li>
    <li>Tickets: {{ \$ticket_count ?? 0 }}</li>
</ul>
<p>— {{ \$app_name ?? 'Promoteri' }}</p>
HTML,
            'admin.daily_summary' => <<<HTML
<h1 style="font-family: -apple-system, BlinkMacSystemFont, sans-serif; color: {$brand};">
    Daily summary
</h1>
<p>Hi admin,</p>
<p>Here's the day at a glance for <strong>{{ \$festival_name ?? '{$fName}' }}</strong>:</p>
<p>Orders: {{ \$order_count ?? 0 }} — Revenue: {{ \$order_total ?? '0.00' }}</p>
<p>— {{ \$app_name ?? 'Promoteri' }}</p>
HTML,
            'admin.image_generation_failed' => <<<HTML
<h1 style="font-family: -apple-system, BlinkMacSystemFont, sans-serif; color: #dc2626;">
    Image generation failed
</h1>
<p>An image-generation job failed for order #{{ \$order_number ?? '—' }} on <strong>{{ \$festival_name ?? '{$fName}' }}</strong>.</p>
<p>Reason: {{ \$error_message ?? 'unknown' }}</p>
<p>Open the order and click "Re-run images" to retry.</p>
HTML,
            default => <<<HTML
<h1 style="font-family: -apple-system, BlinkMacSystemFont, sans-serif; color: {$brand};">
    Hvala na kupovini!
</h1>
<p>Poštovani {{ \$customer_name ?? 'kupac' }},</p>
<p>Vaše ulaznice za <strong>{{ \$festival_name ?? '{$fName}' }}</strong> su spremne i priložene uz ovaj mejl.</p>

@if (!empty(\$tickets) && count(\$tickets) > 0)
    <p>Ukupno ulaznica: <strong>{{ count(\$tickets) }}</strong></p>
@endif

<p>— {{ \$app_name ?? 'REFEST Festival' }}</p>
HTML,
        };
    }

    private function brandColor(?Festival $f): string
    {
        return $f?->primary_color ?: '#ff2d92';
    }

    /* --------------- Computed --------------- */

    #[Computed]
    public function templates(): \Illuminate\Support\Collection
    {
        return MailTemplate::query()
            ->with('festival')
            ->orderBy('key')
            ->orderByRaw('festival_id IS NULL DESC')
            ->orderBy('festival_id')
            ->orderByDesc('updated_at')
            ->get();
    }

    #[Computed]
    public function templateKeys(): array
    {
        // P-051 / U-004: surface every kind of transactional email the
        // platform can send so admins can override any of them.  The
        // actual send paths may not exist for every key yet — the
        // editor will still let the admin save a template for future
        // use, and we seed sensible defaults via the renderer.
        return [
            'customer.tickets'            => 'Customer — Tickets delivery',
            'promoter.new_order'           => 'Promoter — New order notification',
            'admin.daily_summary'          => 'Admin — Daily summary',
            'admin.image_generation_failed' => 'Admin — Ticket-image generation failed',
        ];
    }

    #[Computed]
    public function festivals(): \Illuminate\Support\Collection
    {
        return Festival::orderByDesc('year')->orderBy('name')->get();
    }

    #[Computed]
    public function variables(): array
    {
        return MailTemplateRenderer::availableVariables();
    }

    public function render(): View
    {
        return view('livewire.admin.mail-templates.editor');
    }
}
