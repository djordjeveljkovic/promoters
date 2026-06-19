<?php // resources/lang/sr/order_details.php

return [
    // Header Section
    'header' => [
        'order_prefix' => 'Porudžbina #',
        'total_label'  => 'Ukupno:',
    ],

    // Payment Section
    'payment' => [
        'paid_amount_label'   => 'Plaćeni iznos:',
        'update_button'       => 'Ažuriraj',
        'cancel_button'       => 'Otkaži',
        'paid_label'          => 'Plaćeno:',
        'edit_paid_button'    => 'Izmeni plaćeni iznos',
        'error_paid_message'  => 'Polje za plaćeni iznos je obavezno.',
    ],

    // Filter Section
    'filter' => [
        'label'              => 'Filtriraj po tipu ulaznice:',
        'all_types_option'   => 'Svi tipovi',
    ],

    // Tickets Section
    'tickets' => [
        'none_found_header'      => 'Nema pronađenih ulaznica',
        'none_found_message'     => 'Nema ulaznica povezanih sa ovom porudžbinom.',
        'qr_not_available'       => "QR Kod\nNije Dostupan",
        'card_title_prefix'      => 'Ulaznica #',
        'unknown_type'           => 'Nepoznat tip',
        'status_label'           => 'Status:',
        'status_active'          => 'Aktivna',
        'status_inactive'        => 'Neaktivna',
        'select_checkbox_checked'   => 'Selektovano',
        'select_checkbox_unchecked' => 'Selektuj',
        'none_match_filter'      => 'Nijedna ulaznica ne odgovara izabranom filteru.',
        'all_hidden_by_filter'   => 'Sve dostupne ulaznice su trenutno sakrivene filterom.',
        'image_alt_prefix'       => 'QR Kod za Ulaznicu',
        'image_not_embedded'     => 'Slika ulaznice nije mogla biti ugrađena.',
        'image_not_found'        => 'Slika ulaznice nije mogla biti generisana ili pronađena.',
        'title'                  => 'Vaše ulaznice:',
    ],

    // Order Actions Section
    'actions' => [
        'group_title'               => 'Akcije porudžbine',
        'download_selected_button'  => 'Preuzmi selektovane',
        'download_all_button'       => 'Preuzmi sve',
        'activate_selected_button'  => 'Aktiviraj selektovane',
        'deactivate_selected_button'=> 'Deaktiviraj selektovane',
    ],
];
