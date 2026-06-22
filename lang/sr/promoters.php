<?php // resources/lang/sr/admin_promoters.php

return [
    'page_title' => 'Upravljanje Promoterima',
    'main_heading' => 'Promoteri',
    'add_promoter_button' => 'Dodaj Promotera',

    'table' => [
        'header_name' => 'Promoter',
        'header_email' => 'Email',
        'header_joined_date' => 'Datum Pridruživanja',
        // New headers
        'header_tickets_sold' => 'Prodate Karte',
        'header_made_for_organizers' => 'Doprinos Organizatorima', // Or 'Zarada za Organizatore'
        'header_commission_earned' => 'Ostvarena Provizija',
        'header_paid_to_organizers' => 'Plaćeno Organizatorima',
        'header_owed_to_organizers' => 'Duguje Organizatorima',
        // End of new headers
        'header_actions' => 'Akcije',

        'action_edit' => 'Izmeni',
        'action_delete' => 'Obriši',
        'delete_confirm_message' => 'Da li ste sigurni da želite da obrišete ovog promotera? Ova akcija se ne može opozvati.',

        'no_promoters_header' => 'Nema pronađenih promotera',
        'no_promoters_message' => 'Započnite dodavanjem novog promotera.',
    ],
    'edit_form' => [
        'page_title'                => 'Izmeni Promotera',
        'main_heading'              => 'Izmeni Promotera',
        'name_label'                => 'Ime',
        'name_placeholder'          => 'Ime Promotera',
        'email_label'               => 'Email',
        'password_label'            => 'Lozinka',
        'password_placeholder_edit' => 'Ostavite prazno da zadržite trenutnu lozinku',
        'password_help_text'        => "Ostavite prazno ako ne želite da promenite lozinku.",
        'paid_label'                => 'Plaćeni iznos:', // Original was "Platio:"
        'paid_placeholder'          => 'Unesite plaćeni iznos', // Original was "Platio"
        'cancel_button'             => 'Otkaži',
        'update_button'             => 'Ažuriraj Promotera',
        // P-070: javni profil
        'public_profile'        => 'Javni profil',
        'make_profile_public'   => 'Učini profil ovog promotera javnim',
        'public_help_text'      => 'Kada je uključeno, svako može videti biografiju i festivale na /p/{id}.',
        'bio_label'             => 'Kratka biografija',
        'bio_help_text'         => 'Do 500 karaktera. Čist tekst.',
        'bio_placeholder'       => 'Kratka prezentacija — za koje vrste događaja prodajete karte?',
        // U-005: avatar upload
        'avatar_label'          => 'Profilna slika',
        'avatar_help_text'      => 'Kvadratni JPG/PNG/WebP do 1 MB. Prikazuje se na javnom profilu.',
    ],
    // Short keys used by the promoter edit page header.
    'edit' => [
        'page_title' => 'Izmeni promotera',
        'main_heading' => 'Izmeni promotera',
    ],

    'create_form' => [
        'page_title'                => 'Kreiraj Promotera',
        'main_heading'              => 'Kreiraj Promotera',
        'name_label'                => 'Ime',
        'name_placeholder'          => 'Unesite ime promotera',
        'email_label'               => 'Email',
        'password_label'            => 'Lozinka',
        'password_placeholder_create' => 'Unesite lozinku',
        'password_help_text_create' => 'Lozinka treba da bude jaka i bezbedna.',
        'cancel_button'             => 'Otkaži',
        'create_button'             => 'Kreiraj Promotera',
    ],

    // P-027: printable commission statement.
    'statement' => [
        'page_title'  => 'Izveštaj o proviziji — :name',
        'stat_orders'    => 'Narudžbine (plaćeno)',
        'stat_tickets'   => 'Prodatih ulaznica',
        'stat_gross'     => 'Bruto promet',
        'stat_commission'=> 'Zarađena provizija',
        'by_ticket_type' => 'Raspodela po tipu karte',
        'ledger'         => 'Dnevnik narudžbina',
        'no_orders'      => 'Nema narudžbina sa provizijom za ovog promotera.',
        'settlement'     => 'Završni račun',
        'settlement_total_commission' => 'Ukupna provizija',
        'settlement_paid'             => 'Već plaćeno organizatorima',
        'settlement_owed'             => 'Dug organizatorima',
    ],
    'statement_button' => 'Izveštaj o proviziji',
    'print_or_pdf'     => 'Štampaj / Sačuvaj kao PDF',
    'generated_at'     => 'Generisano',
    'role_on_this_festival' => 'Uloga na festivalu',
];
