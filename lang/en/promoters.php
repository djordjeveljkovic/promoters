<?php // resources/lang/en/admin_promoters.php

return [
    'page_title' => 'Manage Promoters',
    'main_heading' => 'Promoters',
    'add_promoter_button' => 'Add Promoter',

    'table' => [
        'header_name' => 'Name',
        'header_email' => 'Email',
        'header_joined_date' => 'Joined Date',
        'header_actions' => 'Actions',

        'action_edit' => 'Edit',
        'action_delete' => 'Delete',
        'delete_confirm_message' => 'Are you sure you want to delete this promoter? This action cannot be undone.',

        'no_promoters_header' => 'No promoters found',
        'no_promoters_message' => 'Get started by adding a new promoter.',
    ],
    // New keys for Edit Promoter page
    'edit_form' => [
        'page_title'                => 'Edit Promoter',
        'main_heading'              => 'Edit Promoter',
        'name_label'                => 'Name',
        'name_placeholder'          => 'Promoter Name',
        'email_label'               => 'Email',
        // 'email_placeholder'      => 'you@example.com', // Usually not translated if it's a format example
        'password_label'            => 'Password',
        'password_placeholder_edit' => 'Leave blank to keep current password',
        'password_help_text'        => "Leave blank if you don't want to change the password.",
        'paid_label'                => 'Amount Paid',
        'paid_placeholder'          => 'Enter amount paid',
        'cancel_button'             => 'Cancel',
        'update_button'             => 'Update Promoter',
        // P-070: public profile
        'public_profile'        => 'Public profile',
        'make_profile_public'   => 'Make this promoter’s profile public',
        'public_help_text'      => 'When enabled, anyone can view their bio and festivals at /p/{id}.',
        'bio_label'             => 'Short bio',
        'bio_help_text'         => 'Up to 500 characters. Plain text only.',
        'bio_placeholder'       => 'A short introduction — what kind of events do you sell for?',
    ],
    // Short keys used by the promoter edit page header.
    'edit' => [
        'page_title' => 'Edit promoter',
        'main_heading' => 'Edit promoter',
    ],
    // New keys for Create Promoter page
    'create_form' => [
        'page_title'                => 'Create Promoter',
        'main_heading'              => 'Create Promoter',
        'name_label'                => 'Name',
        'name_placeholder'          => 'Enter promoter name',
        'email_label'               => 'Email',
        // 'email_placeholder'      => 'you@example.com', // Usually not translated if it's a format example
        'password_label'            => 'Password',
        'password_placeholder_create' => 'Enter password', // Placeholder for creating new password
        'password_help_text_create' => 'The password should be strong and secure.', // More appropriate help text for creation
        // The original help text "Leave blank if you don't want to change the password." is for an update form.
        // If password is required on creation, that help text is misleading.
        // If password is NOT required and can be set later, then a text like:
        // 'password_help_text_optional_create' => 'Leave blank to generate a password or set it later.',

        'cancel_button'             => 'Cancel',    // Common key, can be reused
        'create_button'             => 'Create Promoter',
    ],

    // P-027: printable commission statement.
    'statement' => [
        'page_title'  => 'Commission statement — :name',
        'stat_orders'    => 'Orders (paid)',
        'stat_tickets'   => 'Tickets sold',
        'stat_gross'     => 'Gross revenue',
        'stat_commission'=> 'Commission earned',
        'by_ticket_type' => 'Breakdown by ticket type',
        'ledger'         => 'Order-by-order ledger',
        'no_orders'      => 'No commission-earning orders for this promoter yet.',
        'settlement'     => 'Settlement',
        'settlement_total_commission' => 'Total commission',
        'settlement_paid'             => 'Already paid to organisers',
        'settlement_owed'             => 'Still owed to organisers',
    ],
    // Reusable button labels
    'statement_button' => 'Commission statement',
    'print_or_pdf'     => 'Print / Save as PDF',
    'generated_at'     => 'Generated',
    'role_on_this_festival' => 'Role on this festival',
];
