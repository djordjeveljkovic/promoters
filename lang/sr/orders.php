<?php // resources/lang/sr/promoter_orders.php

return [
    'page_title' => 'Moje Porudžbine Ulaznica',
    'main_heading' => 'Moje Izvršene Porudžbine Ulaznica',
    'create_new_order_button' => 'Kreiraj Novu Porudžbinu',

    'table' => [
        'header_order_id' => 'ID Porudžbine',
        'header_customer_email' => 'Email Kupca',
        'header_order_date' => 'Datum Porudžbine',
        'header_items' => 'Stavke',
        'header_total_price' => 'Ukupna Cena',
        'header_commission_earned' => 'Zarađena Provizija',
        'header_job_status' => 'Status Posla',
        'header_actions' => 'Akcije',

        'commission_not_calculated' => 'Nije Obračunato',
        'status_error_tooltip_prefix' => 'Kliknite da vidite detalje greške:',
        'actions_retry_images_button' => 'Ponovi Slike',
        'actions_retry_images_tooltip_prefix' => 'Ponovo generisanje slika/QR kodova. Greška:',
        'actions_retry_email_button' => 'Ponovi Email',
        'actions_retry_email_tooltip_prefix' => 'Ponovno slanje email-a. Greška:',
        'actions_resend_email_button' => 'Ponovo Pošalji Email',
        'actions_resend_email_tooltip' => 'Ponovo pošalji email potvrde.',
        'job_failure_reason_label' => 'Razlog Neuspeha Posla:',
        'no_orders_message' => "Još uvek niste izvršili nijednu porudžbinu.",
    ],

    'create_page_title' => 'Kreiraj Novu Porudžbinu',
    'create_main_heading' => 'Kreiraj Novu Porudžbinu',
    'create_back_to_orders_link' => '&larr; Nazad na Porudžbine',
    'create_customer_email_label' => 'Email Kupca',

    'create_order_items_heading' => 'Stavke Porudžbine',
    'create_ticket_type_label' => 'Tip Ulaznice',
    'create_select_ticket_type_option' => 'Izaberite tip ulaznice...',
    'create_quantity_label' => 'Količina',
    'create_add_item_button' => 'Dodaj Stavku',

    'create_items_table_header_ticket' => 'Ulaznica',
    'create_items_table_header_quantity' => 'Količina',
    'create_items_table_header_unit_price' => 'Cena po komadu',
    'create_items_table_header_subtotal' => 'Međuzbir',
    'create_items_table_header_remove' => 'Ukloni',
    'create_no_items_message' => 'Nema dodatih stavki.',

    'create_total_label' => 'Ukupno',
    'create_cancel_button' => 'Otkaži',
    'create_submit_button' => 'Izvrši Porudžbinu i Pošalji Ulaznice',
    // Translatable job statuses
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
