<?php

namespace App\Support\Mail;

use App\Models\Festival;
use App\Models\MailTemplate;
use App\Models\TicketOrder;
use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Throwable;

/**
 * Resolves and renders customisable mail templates.
 *
 * Resolution order (first hit wins):
 *   1. festival-scoped row where key matches AND is_active = true
 *   2. global row (festival_id IS NULL) where key matches AND is_active = true
 *   3. built-in fallback view: `emails.<key>` (e.g. `emails.customer.tickets`)
 *
 * Use {@see resolve()} to get the {@see ResolvedTemplate} value object —
 * it carries the subject, from address, body and CSS so the mailable
 * doesn't need to know about the database.
 */
class MailTemplateRenderer
{
    /**
     * Variable catalogue surfaced in the editor UI.  Order matters —
     * this is the order the editor renders them in.
     *
     * @return array<string, string>
     */
    public static function availableVariables(): array
    {
        return [
            // Order data
            'order'                  => 'The TicketOrder model (with items.ticketType, tickets.ticketType, festival, customer, requestedBy).',
            'order_number'           => 'Short order code, e.g. ABCDEF.',
            'order_id'               => 'Numeric order ID.',
            'order_total'            => 'Total amount, formatted with currency (e.g. "12,500.00 RSD").',
            'order_paid'             => 'Amount already paid.',
            'order_status'           => 'job_status (pending|processing|completed|sent|failed|blocked).',
            'order_date'             => 'Order creation date (Y-m-d H:i).',
            'customer_email'         => 'Email of the customer who bought the tickets.',
            'customer_name'          => 'Customer name (best effort, falls back to email local-part).',

            // Festival data
            'festival'               => 'The Festival model (name, year, location, logo_path, primary_color, secondary_color, …).',
            'festival_name'          => 'e.g. "REFEST 2026".',
            'festival_year'          => 'Year of the festival.',
            'festival_location'      => 'Location string.',
            'festival_logo_url'      => 'Absolute URL to the festival logo (or empty string).',
            'festival_primary_color' => 'Primary brand color (e.g. "#ff2d92").',
            'festival_secondary_color' => 'Secondary brand color.',
            'festival_dates'         => 'Pre-formatted date range (e.g. "24-26 July 2026") or empty.',

            // Tickets
            'tickets'                => 'Collection of Ticket models (each with ticketType, code, image_path).',
            'ticket_count'           => 'Total number of tickets in this order.',
            'first_ticket_image_url' => 'URL of the first ticket image (or empty).',

            // Promoter / requester
            'promoter'               => 'The User model who placed the order (requestedBy).',
            'promoter_name'          => 'Promoter display name.',
            'promoter_email'         => 'Promoter email.',

            // App-wide
            'app_name'               => 'APP_NAME from config.',
            'support_email'          => 'A reasonable support address (falls back to MAIL_FROM_ADDRESS).',
            'unsubscribe_url'        => 'Stubbed anchor "#" — placeholder for future self-service.',
            'year'                   => 'Current calendar year.',
        ];
    }

    /**
     * Pick the best template for a given key + festival (or null for the
     * global default), and return a ResolvedTemplate ready to send.
     *
     * The returned object's body is already rendered with the supplied data.
     */
    public function resolve(string $key, ?Festival $festival, array $data): ResolvedTemplate
    {
        // Always expose the festival to the data so `{{ $festival_name }}`
        // works even if the caller didn't pass it explicitly.  This is
        // important for the subject-line renderer.
        if ($festival && !array_key_exists('festival', $data)) {
            $data['festival'] = $festival;
        }
        if ($festival && !array_key_exists('order', $data)) {
            $data['order'] = null;
        }

        $template = $this->find($key, $festival);

        if ($template) {
            return new ResolvedTemplate(
                subject: $this->renderString($template->subject, $data) ?: $this->defaultSubject($key, $festival),
                body:    $this->renderHtml($template, $data),
                fromAddress: $template->from_address ?: null,
                fromName:    $template->from_name ?: null,
                source:  'db',
                template: $template,
            );
        }

        // Fallback: built-in view.  Same shape, no DB row.
        $view = 'emails.' . str_replace('.', '/', $key);

        // The legacy views assume the order already has its relations
        // loaded and that `created_at` is a Carbon instance.  We don't
        // always have a real order in tests, so wrap null with a stub
        // the same way renderHtml() does — keeps the fallback safe.
        $data = $this->hydrate($data);
        if (!($data['order'] instanceof TicketOrder)) {
            $stub = new TicketOrder([
                'order_number' => $data['order_number'] ?? 'PREVIEW',
                'email'        => $data['customer_email'] ?? 'preview@example.com',
                'total'        => 0,
                'paid'         => 0,
            ]);
            $stub->id = 0;
            $stub->created_at = now();
            $stub->setRelation('items', collect());
            $stub->setRelation('tickets', collect());
            $data['order'] = $stub;
        }

        try {
            $body = view($view, $data + ['festival' => $festival])->render();
        } catch (\Throwable $e) {
            $body = '<!-- fallback view error: ' . e($e->getMessage()) . ' -->';
        }

        return new ResolvedTemplate(
            subject: $this->defaultSubject($key, $festival),
            body: $body,
            fromAddress: null,
            fromName: null,
            source: 'fallback',
            template: null,
        );
    }

    /**
     * Render an arbitrary {@see MailTemplate} (e.g. for the preview pane)
     * without touching the resolution logic.
     */
    public function renderHtml(MailTemplate $template, array $data): string
    {
        // Make festival/order objects available by name even if the caller
        // only passed loose scalars.
        $data = $this->hydrate($data);

        // If we don't have a real order, fake one whose relations are
        // empty collections so `$order->tickets` / `$order->items` don't
        // error out in the template.
        if (!($data['order'] instanceof TicketOrder)) {
            $stub = new TicketOrder([
                'order_number' => $data['order_number'] ?? 'PREVIEW',
                'email'        => $data['customer_email'] ?? 'preview@example.com',
                'total'        => 0,
                'paid'         => 0,
                'job_status'   => 'completed',
            ]);
            $stub->id = 0;
            $stub->created_at = now();
            $stub->setRelation('items', collect());
            $stub->setRelation('tickets', collect());
            $data['order'] = $stub;
        } else {
            // Make sure the relations are always at least empty collections
            // (handy when the caller passed a freshly-loaded model without
            // eager-loading these). Also patch up `created_at` if missing
            // so legacy templates like `{{ $order->created_at->format(...) }}`
            // don't blow up on a freshly-built in-memory order.
            if ($data['order']->relationLoaded('items') && $data['order']->items === null) {
                $data['order']->setRelation('items', collect());
            }
            if ($data['order']->relationLoaded('tickets') && $data['order']->tickets === null) {
                $data['order']->setRelation('tickets', collect());
            }
            if ($data['order']->created_at === null) {
                $data['order']->created_at = now();
            }
        }

        $css = trim((string) $template->css);
        $cssBlock = $css !== '' ? "<style>\n{$css}\n</style>" : '';

        // Build an inline render: the html_body is a complete document OR a
        // fragment. If it doesn't contain <html>, wrap it.
        $raw = (string) $template->html_body;
        $compiled = $this->compile($raw, $data);

        if (!str_contains(strtolower($compiled), '<html')) {
            $compiled = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$template->name}</title>
    {$cssBlock}
</head>
<body style="margin:0;padding:24px;background:#f5f5f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;color:#222;">
    {$compiled}
</body>
</html>
HTML;
        } elseif ($cssBlock !== '') {
            // Inject the CSS right before </head>
            $compiled = preg_replace('/<\/head>/i', $cssBlock . "\n</head>", $compiled, 1);
        }

        return $compiled;
    }

    /**
     * Compile a Blade string with error handling — invalid templates return
     * a useful error message instead of throwing 500s.
     */
    public function compile(string $template, array $data): string
    {
        $data = $this->hydrate($data);
        try {
            return Blade::render($template, $data);
        } catch (Throwable $e) {
            // Surface the error in the rendered output so the editor can
            // see it inline without a 500.
            return '<!-- template error: ' . e($e->getMessage()) . ' -->';
        }
    }

    /**
     * Render a string with placeholder substitution (no Blade, no HTML).
     * Used for subjects.
     */
    public function renderString(?string $template, array $data): string
    {
        if ($template === null || $template === '') {
            return '';
        }
        $data = $this->hydrate($data);

        // Accept both `{{ $foo.bar }}` and `{{ foo.bar }}` so authors can
        // copy/paste Blade snippets without the dollar sign.
        return preg_replace_callback('/\{\{\s*\$?([\w.]+)\s*\}\}/', function ($m) use ($data) {
            $v = data_get($data, $m[1]);
            return $v === null ? '' : (string) $v;
        }, $template);
    }

    /**
     * Build the canonical fallback subject when the template has none.
     */
    public function defaultSubject(string $key, ?Festival $festival): string
    {
        $name = $festival?->displayName() ?? 'festival';
        return match ($key) {
            'customer.tickets'             => "Vaše ulaznice za {$name}",
            'promoter.new_order'            => "New order received for {$name}",
            'admin.daily_summary'           => "Daily summary for {$name}",
            'admin.image_generation_failed' => "Image-generation failed on {$name}",
            'order.completed'               => "Porudžbina #" . ($festival?->id ?? '') . " je završena",
            default                        => Str::headline($key),
        };
    }

    /**
     * Look up the best template (festival override → global).
     */
    public function find(string $key, ?Festival $festival): ?MailTemplate
    {
        if ($festival) {
            $row = MailTemplate::query()
                ->forKey($key)
                ->where('festival_id', $festival->id)
                ->active()
                ->latest('updated_at')
                ->first();
            if ($row) return $row;
        }

        return MailTemplate::query()
            ->forKey($key)
            ->global()
            ->active()
            ->latest('updated_at')
            ->first();
    }

    /**
     * Expand scalar data (order, festival, …) into the friendly variables
     * listed in {@see availableVariables()}.
     */
    public function hydrate(array $data): array
    {
        $order    = $data['order']    ?? null;
        $festival = $data['festival'] ?? ($order?->festival ?? null);
        $promoter = $data['promoter'] ?? ($order?->requestedBy ?? null);
        $tickets  = $data['tickets']  ?? ($order?->tickets ?? collect());

        // Make sure the named variables are always present so Blade
        // templates that reference $order->foo don't blow up on missing
        // data.  We keep the model instance if we have one, otherwise an
        // empty stdClass with the methods the templates usually call.
        $data['order']    ??= $order ?? new \stdClass;
        $data['festival'] ??= $festival;
        $data['promoter'] ??= $promoter;
        $data['tickets']  ??= $tickets;

        $data['order_number']    ??= $order?->order_number;
        $data['order_id']        ??= $order?->id;
        $data['order_total']     ??= $order ? number_format((float) $order->total, 2) . ' RSD' : '';
        $data['order_paid']      ??= $order ? number_format((float) $order->paid, 2) . ' RSD' : '0.00 RSD';
        $data['order_status']    ??= $order?->job_status;
        $data['order_date']      ??= $order?->created_at?->format('Y-m-d H:i');

        $data['customer_email']  ??= $order?->email ?? $promoter?->email ?? '';
        $data['customer_name']   ??= $order?->orderedBy?->name
            ?? ($data['customer_email'] ? Str::before($data['customer_email'], '@') : '');

        $data['festival_name']           ??= $festival?->displayName();
        $data['festival_year']           ??= $festival?->year;
        $data['festival_location']       ??= $festival?->location;
        $data['festival_logo_url']       ??= $festival?->logo_path ? asset('storage/' . ltrim($festival->logo_path, '/')) : '';
        $data['festival_primary_color']  ??= $festival?->primary_color;
        $data['festival_secondary_color']??= $festival?->secondary_color;
        $data['festival_dates']          ??= $this->formatFestivalDates($festival);

        $data['ticket_count']           ??= is_countable($tickets) ? count($tickets) : 0;
        $data['first_ticket_image_url'] ??= $tickets instanceof \Illuminate\Support\Collection
            ? ($tickets->first()?->image_path ? asset('storage/' . ltrim($tickets->first()->image_path, '/')) : '')
            : '';

        $data['promoter_name']   ??= $promoter?->name;
        $data['promoter_email']  ??= $promoter?->email;

        $data['app_name']       ??= config('app.name');
        $data['support_email']  ??= config('mail.from.address', 'support@example.com');
        $data['unsubscribe_url']??= '#';
        $data['year']           ??= date('Y');

        return $data;
    }

    private function formatFestivalDates(?Festival $festival): string
    {
        if (!$festival) return '';
        $s = $festival->start_date;
        $e = $festival->end_date;
        if ($s && $e && $s->format('Y-m-d') !== $e->format('Y-m-d')) {
            return $s->format('d') . '-' . $e->format('d M Y');
        }
        return $s?->format('d M Y') ?? '';
    }
}
