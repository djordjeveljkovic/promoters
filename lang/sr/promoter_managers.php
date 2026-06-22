<?php

return [
    'page_title' => 'Promoter menadžeri',
    'index_intro' => 'Promoteri koji mogu da kreiraju svoje sub-promotere i dele proviziju sa njima.',

    'list' => [
        'header_manager'     => 'Menadžer',
        'header_email'      => 'Email',
        'header_overrides'  => 'Podešenih overrid-ova',
        'header_default_count' => 'Podrazumevanih tipova karata',
        'header_actions'    => 'Akcije',
        'no_managers_title' => 'Još nema promoter menadžera',
        'no_managers_message' => 'Unapredite jednog od promotera u menadžera sa stranice Promoteri kako bi mogao da kreira sub-promotere i deli proviziju.',
        'go_to_promoters'   => 'Idi na promotere',
        'set_commissions'   => 'Podesi provizije',
    ],

    'show' => [
        'subtitle' => 'Preglasajte podrazumevanu proviziju po tipu karte za ovog promoter menadžera. Ostavite prazno da koristi podrazumevanu.',
        'back'     => 'Nazad na menadžere',
        'how_works_title' => 'Kako ovo radi',
        'how_works_body'  => 'Podrazumevana provizija dolazi iz lestvice provizija svakog tipa karte. Podesite override ovde da date ovom menadžeru drugu stopu — važi samo za njega, ne za druge menadžere.',
        'header_ticket_type' => 'Tip karte',
        'header_default'     => 'Podrazumevana (RSD)',
        'header_override'    => 'Override za ovog menadžera (RSD)',
        'save_button'        => 'Sačuvaj override-ove provizije',
        'no_ticket_types'    => 'Još nema tipova karata',
        'no_ticket_types_message' => 'Kreirajte tip karte za ovaj festival pre podešavanja provizija.',
    ],

    // P-025: inline role changer on the promoters index page.
    'role' => [
        'admin'            => 'Admin',
        'promoter'         => 'Promoter',
        'promoter_manager' => 'Promoter menadžer',
        'sub_promoter'     => 'Sub-promoter',
    ],
    'change_role_label'   => 'Uloga na festivalu',
    'change_role_button'  => 'Promeni ulogu',
    'change_role_confirm'  => 'Promeniti ulogu ovog korisnika na festivalu?',
];
