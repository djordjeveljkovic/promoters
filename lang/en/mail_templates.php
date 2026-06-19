<?php

return [
    'page_title'             => 'Mail templates',
    'page_intro'             => 'Edit the look and feel of every transactional email. Global defaults apply to every festival; festival-scoped templates override them.',

    'create_new'             => 'Create a new template',
    'as_global_default'      => 'As global default',
    'as_global_help'         => 'Applies to every festival.',
    'override_for'           => 'Override for…',

    'list' => [
        'header_template'    => 'Template',
        'header_festival'    => 'Festival',
        'header_subject'     => 'Subject',
        'header_version'     => 'Version',
        'header_updated'     => 'Updated',
        'header_actions'     => 'Actions',
        'global_badge'       => 'Global default',
        'disabled_badge'     => 'Disabled',
        'no_templates'       => 'No custom templates yet — every email uses the built-in Blade fallback.',
    ],

    'editor' => [
        'internal_name'      => 'Internal name',
        'subject'            => 'Mail subject',
        'from_section'       => 'From address (optional — falls back to global MAIL_FROM_ADDRESS)',
        'from_name_ph'       => 'REFEST Tim',
        'from_address_ph'    => 'tickets@refest.rs',
        'is_active'          => 'Active — use this template when sending',
        'template_key'       => 'Template key:',
        'festival_label'     => 'Festival:',
        'global_label'       => 'Global default',

        'html_body'          => 'HTML body',
        'blade'              => 'Blade',
        'css_optional'       => 'CSS (optional)',
        'css_help'           => 'Injected into <style> inside the email <head>',

        'save'               => 'Save template',
        'refresh_preview'    => 'Refresh preview',
        'copy_to_global'     => 'Copy to global default',
        'delete'             => 'Delete',
        'back_to_list'       => 'Back to list',
    ],

    'preview' => [
        'live_preview'       => 'Live preview',
        'rendered'           => 'Rendered',
        'error'              => 'Error',
    ],

    'variables' => [
        'title'              => 'Available variables',
        'intro'              => 'Reference these inside the template with',
        'copy_title'         => 'Click to copy',
    ],

    'confirm' => [
        'delete'             => 'Delete this template? Future sends will use the built-in fallback view.',
        'delete_in_editor'   => 'Delete this template? Future sends will fall back.',
    ],

    'flash' => [
        'saved_new'          => 'Template created.',
        'saved_existing'     => 'Template updated (v:version).',
        'deleted'            => 'Template removed (the next send will fall back to the built-in view or the next-most-specific override).',
        'copied_to_global'   => 'Copied to global defaults.',
    ],
];
