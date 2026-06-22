<?php

return [
    'page_title' => 'Promoter managers',
    'index_intro' => 'Promoters who can create their own sub-promoters and split their commission with them.',

    'list' => [
        'header_manager'     => 'Manager',
        'header_email'      => 'Email',
        'header_overrides'  => 'Overrides set',
        'header_default_count' => 'Default ticket types',
        'header_actions'    => 'Actions',
        'no_managers_title' => 'No promoter managers yet',
        'no_managers_message' => 'Promote one of your promoters to manager from the Promoters page so they can create sub-promoters and split their commission.',
        'go_to_promoters'   => 'Go to promoters',
        'set_commissions'   => 'Set commissions',
    ],

    'show' => [
        'subtitle' => 'Override the default commission per ticket type for this promoter manager. Leave blank to use the default.',
        'back'     => 'Back to managers',
        'how_works_title' => 'How this works',
        'how_works_body'  => 'The default commission comes from each ticket type\'s own commission ladder. Set an override here to give this manager a different rate — it will apply only to them, not to other managers.',
        'header_ticket_type' => 'Ticket type',
        'header_default'     => 'Default (RSD)',
        'header_override'    => 'Override for this manager (RSD)',
        'save_button'        => 'Save commission overrides',
        'no_ticket_types'    => 'No ticket types yet',
        'no_ticket_types_message' => 'Create a ticket type for this festival before setting commission rates.',
    ],
];
