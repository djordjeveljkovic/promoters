<?php

namespace Database\Seeders;

use App\Models\MailTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Seeds the global defaults for every mail template.
 *
 * Each row is a *global* template (festival_id IS NULL) that festival
 * admins can override later.  The HTML body is read straight from the
 * existing Blade view so an admin sees the same content the system
 * always sent — and can tweak it from there.
 */
class MailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $editor = User::where('role', 'superadmin')->first()?->id;

        $templates = [
            [
                'key'      => MailTemplate::where('key', 'customer.tickets')->exists() ? null : 'customer.tickets',
                'name'     => 'Customer — Tickets delivery',
                'subject'  => 'Vaše ulaznice za {{ $festival_name }}',
                'view'     => 'emails.customer.tickets',
                'css'      => null,
            ],
        ];

        foreach ($templates as $tpl) {
            if (!$tpl['key']) continue;

            $path = resource_path('views/' . str_replace('.', '/', $tpl['view']) . '.blade.php');
            if (!File::exists($path)) {
                $this->command?->warn("Skipping seed for {$tpl['key']}: view not found at {$path}");
                continue;
            }
            $html = File::get($path);

            MailTemplate::updateOrCreate(
                ['key' => $tpl['key'], 'festival_id' => null],
                [
                    'name'           => $tpl['name'],
                    'subject'        => $tpl['subject'],
                    'html_body'      => $html,
                    'css'            => $tpl['css'],
                    'is_active'      => true,
                    'last_edited_by' => $editor,
                    'version'        => 1,
                ],
            );
        }
    }
}
