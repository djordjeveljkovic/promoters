<?php // resources/lang/sr/admin_dashboard.php

return [
    'page_title' => 'Admin Kontrolna Tabla',
    'main_heading' => 'Admin Analitička Kontrolna Tabla',

    /* Stat card hints (used by the superadmin dashboard) */
    'stat_active_festivals'        => ':count aktivno',
    'stat_users_breakdown'         => ':admins admina · :promoters promotera',
    'stat_orders_completed'        => ':count završeno',
    'stat_revenue_hint'            => 'Od završenih porudžbina',

    'overall_performance' => [
        'heading' => 'Ukupni Performans',
        'total_revenue_all_time' => 'Ukupan Prihod (Sve vreme)',
        'total_orders_all_time' => 'Ukupno Porudžbina (Sve vreme)',
        'tickets_sold_completed_all_time' => 'Prodatih Ulaznica (Završene porudžbine)',
        'revenue_last_30_days' => 'Prihod (Poslednjih 30 dana)',
    ],

    'top_ticket_types' => [
        'heading' => 'Top Tipovi Ulaznica (Po prodatoj količini)',
        'no_data' => 'Nema podataka o prodaji ulaznica.',
        'table_header_type_name' => 'Naziv Tipa',
        'table_header_quantity_sold' => 'Prodata Količina',
        'table_header_est_revenue' => 'Proc. Prihod',
    ],

    'user_ticket_stats' => [
        'heading' => 'Statistika Korisnika i Ulaznica',
        'role_count_suffix' => 'i:', // e.g., "Admin" -> "Admini:", "Promoter" -> "Promoteri:"
        'active_tickets' => 'Aktivne Ulaznice:',
        'inactive_tickets' => 'Neaktivne Ulaznice:',
    ],

    'order_statuses' => [
        'heading' => 'Statusi Porudžbina',
    ],

    'top_promoter_performance' => [
        'heading' => 'Top Performanse Promotera (Završene porudžbine)',
        'no_data' => 'Nema podataka o performansama promotera.',
        'table_header_promoter' => 'Promoter',
        'table_header_email' => 'Email',
        'table_header_orders_generated' => 'Generisanih Porudžbina',
        'table_header_revenue_generated' => 'Generisan Prihod',
    ],

    'recent_orders' => [
        'heading' => 'Nedavne Porudžbine',
        'no_data' => 'Nema nedavnih porudžbina.',
        'table_header_order_id' => 'ID Porudžbine',
        'table_header_customer_email' => 'Email Kupca',
        'table_header_promoter' => 'Promoter',
        'table_header_items' => 'Stavke',
        'table_header_total' => 'Ukupno',
        'table_header_status' => 'Status',
        'table_header_date' => 'Datum',
    ],
];
