<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MailTemplate;
use App\Support\Mail\MailTemplateRenderer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
        } elseif ($key) {
            // Render the *fallback* view for this key — used to show admins
            // what the built-in email looks like before they override it.
            $festivalId = $request->integer('festival') ?: null;
            $festival = $festivalId ? \App\Models\Festival::find($festivalId) : null;
            $view = 'emails.' . str_replace('.', '/', $key);
            $body = view($view, [
                'order' => $this->stubOrder($festival),
                'festival' => $festival,
            ])->render();
            return response($body, 200, ['Content-Type' => 'text/html; charset=utf-8']);
        } else {
            abort(400, 'Missing ?id= or ?key= parameter');
        }

        $stub = $this->stubData($tpl);
        $html = $renderer->renderHtml($tpl, $stub);

        return response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
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
