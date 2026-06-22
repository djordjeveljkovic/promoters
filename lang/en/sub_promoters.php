<?php

return [
    'page_title' => 'Sub-promoters',

    'list' => [
        'subtitle'       => 'Sub-promoters sell under your account. You decide how much of your commission they earn — the rest is yours.',
        'header_name'    => 'Name',
        'header_email'   => 'Email',
        'header_overrides' => 'Active overrides',
        'header_actions' => 'Actions',
        'set_button'     => 'Set commission',
        'no_subs_title'  => 'No sub-promoters yet',
        'no_subs_message'=> 'Create a sub-promoter to share your commission with them. Sub-promoters sell under your account.',
    ],

    'show' => [
        'subtitle'           => 'Choose how much of your commission each sub-promoter earns. Whatever you don\'t pay them, you keep.',
        'back'               => 'Back to sub-promoters',
        'header_ticket_type' => 'Ticket type',
        'header_your_commission' => 'Your commission (RSD)',
        'header_their_commission' => 'Their commission (RSD)',
        'their_payout_will_be' => 'Their payout will be',
        'your_payout_will_be' => 'Your payout will be',
        'save_button'        => 'Save commission rates',
        'cap_error'          => 'Cannot exceed your commission of :amount RSD for this ticket type.',
        'no_ticket_types'    => 'No ticket types',
        'no_ticket_types_message' => 'Ask the festival admin to create ticket types before setting commission rates.',
        'warning_title'      => 'Important',
        'warning_body'       => 'The sub-promoter\'s commission is deducted from your own commission. If you set a high rate, your own payout will be lower.',
    ],
];
