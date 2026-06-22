<?php // resources/lang/sr/admin_orders.php

return [
    'page_title' => 'Admin Porudžbine Ulaznica',
    'main_heading' => 'Prodate Karte',
    'create_order_button' => 'Kreiraj Porudžbinu',

    'filters' => [
        'all_job_statuses_option' => 'Svi Statusi Posla',
        'search_placeholder' => 'Pretraži ID, Email, Promotera...',
        'search_button' => 'Pretraži',
        'clear_button' => 'Očisti',
    ],

    'table' => [
        'header_id' => 'ID',
        'header_customer' => 'Kupac',
        'header_promoter' => 'Promoter',
        'header_date' => 'Datum',
        'header_items' => 'Stavke',
        'header_total' => 'Ukupno',
        'header_paid' => 'Plaćeno',
        'header_commission' => 'Provizija',
        'header_job_status' => 'Status Posla',
        'header_actions' => 'Akcije',

        'promoter_not_available' => 'N/A',
        'commission_not_calculated' => '---',
        'status_tooltip_failure_prefix' => 'Kliknite da vidite grešku:',
        'action_view' => 'Pregledaj',
        'action_generate_images' => 'Generiši Slike',
        'action_resend_email' => 'Pošalji email ponovo',
        'action_generate_images_tooltip_failure_prefix' => 'Ponovno generisanje slika. Greška:',
        'action_send_mail' => 'Pošalji Email',
        'action_send_mail_tooltip_base' => 'Ponovo pošalji/Pokušaj email.',
        'action_send_mail_tooltip_additional_failure_prefix' => 'Greška:',
        'job_failure_reason_label' => 'Razlog Neuspeha Posla:',
        'no_orders_header' => 'Nema pronađenih porudžbina',
        'no_orders_message' => 'Nijedna porudžbina ne odgovara vašim kriterijumima ili još uvek nema porudžbina.',
    ],

    'statuses' => [
        'pending' => 'Na čekanju',
        'processing' => 'U obradi',
        'failed' => 'Neuspešno',
        'blocked' => 'Blokirano',
        'completed' => 'Završeno',
        'sent' => 'Poslato',
        'unknown' => 'N/A',
    ],
];
