<?php

namespace App\Support\Mail;

use App\Models\MailTemplate;

/**
 * Result of {@see MailTemplateRenderer::resolve()}.
 *
 * A plain value object so the Mailable doesn't need to know whether the
 * template came from the database or the built-in fallback view.
 */
class ResolvedTemplate
{
    public function __construct(
        public readonly string $subject,
        public readonly string $body,
        public readonly ?string $fromAddress = null,
        public readonly ?string $fromName = null,
        public readonly string $source = 'fallback', // 'db' or 'fallback'
        public readonly ?MailTemplate $template = null,
    ) {}

    /**
     * "Database template" or "Built-in fallback" — used in the editor
     * badge so admins know what they're overriding.
     */
    public function sourceLabel(): string
    {
        return match ($this->source) {
            'db'       => 'Custom template',
            'fallback' => 'Built-in fallback view',
            default    => $this->source,
        };
    }
}
