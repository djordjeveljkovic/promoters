<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MailTemplate;
use App\Support\Mail\MailTemplateRenderer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * Serves the rendered preview HTML for the editor iframe.
 *
 * The editor is a Livewire component that does the actual Blade render,
 * but iframes need a real URL to load `srcdoc` reliably in some clients
 * (Outlook web, certain mobile browsers, the Livewire devtools, …).
 *
 * This controller:
 *  - Auth-guards with the same role middleware the editor route uses
 *  - Reads a `?id=…` query param, loads the MailTemplate, renders it
 *    with a synthetic order, and returns the HTML body verbatim.
 */
class MailTemplatePreviewController extends Controller
{
    public function __invoke(Request $request, MailTemplateRenderer $renderer): Response
    {
        $id = (int) $request->query('id');
        $key = $request->query('key');

        if ($id) {
            $tpl = MailTemplate::findOrFail($id);
            $stub = $this->stubData($tpl);
            $html = $renderer->renderHtml($tpl, $stub);
            return response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
        }

        if ($key) {
            // No DB row yet — render the *fallback* (or starter HTML) for
            // this key so admins can preview the built-in shape before
            // they save their own version.
            $festivalId = $request->integer('festival') ?: null;
            $festival = $festivalId ? \App\Models\Festival::find($festivalId) : null;

            // Try the built-in view first.
            $view = 'emails.' . str_replace('.', '/', $key);
            if (view()->exists($view)) {
                $body = view($view, [
                    'order'    => $this->stubOrder($festival),
                    'festival' => $festival,
                ])->render();
                return response($body, 200, ['Content-Type' => 'text/html; charset=utf-8']);
            }

            // No built-in view (e.g. promoter.new_order) — fall back to
            // the editor's starter HTML so the preview pane has something.
            $stub = [
                'order' => $this->stubOrder($festival),
                'festival' => $festival,
                'promoter' => auth()->user(),
            ];
            $fake = new MailTemplate([
                'key'        => $key,
                'festival_id'=> $festival?->id,
                'name'       => Str::headline($key),
                'subject'    => $renderer->defaultSubject($key, $festival),
                'html_body'  => $this->starterHtml($key, $festival),
                'css'        => null,
            ]);
            $fake->exists = true;
            $html = $renderer->renderHtml($fake, $stub);
            return response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
        }

        abort(400, 'Missing ?id= or ?key= parameter');
    }

    /**
     * Mirror the editor's starterHtml() so the preview controller can
     * render an inline HTML body when the key has no built-in view.
     */
    private function starterHtml(string $key, ?\App\Models\Festival $festival): string
    {
        $fName = $festival?->displayName() ?? 'festival';
        $brand = $festival?->primary_color ?: '#ff2d92';

        return match ($key) {
            'promoter.new_order' => "<h1 style=\"color:{$brand};\">New order received</h1>"
                . "<p>Hi promoter,</p><p>Order #PREVIE on {$fName} has just been placed.</p>",
            'admin.daily_summary' => "<h1 style=\"color:{$brand};\">Daily summary</h1>"
                . "<p>Hi admin,</p><p>Here's the day at {$fName}.</p>",
            'admin.image_generation_failed' => "<h1 style=\"color:#dc2626;\">Image generation failed</h1>"
                . "<p>Order #PREVIE on {$fName} failed to render tickets.</p>",
            default => "<h1 style=\"color:{$brand};\">Template preview</h1>",
        };
    }

    private function stubData(MailTemplate $tpl): array
    {
        $festival = $tpl->festival;
        return [
            'order' => $this->stubOrder($festival),
            'festival' => $festival,
        ];
    }

    private function stubOrder(?\App\Models\Festival $festival): \App\Models\TicketOrder
    {
        $order = new \App\Models\TicketOrder([
            'order_number' => 'PREVIE',
            'email'        => 'kupac@example.com',
            'paid'         => 12000,
            'total'        => 12000,
            'job_status'   => 'completed',
        ]);
        $order->id = 12345;
        $order->created_at = now();
        $order->festival = $festival;
        $order->setRelation('items', collect());
        $order->setRelation('tickets', collect());
        return $order;
    }
}
