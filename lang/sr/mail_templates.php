<?php

return [
    'page_title'             => 'Šabloni mejlova',
    'page_intro'             => 'Menjajte izgled svakog transakcionog mejla. Globalni šabloni važe za sve festivale; šabloni vezani za festival ih preglasavaju.',

    'create_new'             => 'Kreiraj novi šablon',
    'as_global_default'      => 'Kao globalni podrazumevani',
    'as_global_help'         => 'Primenjuje se na svaki festival.',
    'override_for'           => 'Preglasaj za…',

    'list' => [
        'header_template'    => 'Šablon',
        'header_festival'    => 'Festival',
        'header_subject'     => 'Naslov',
        'header_version'     => 'Verzija',
        'header_updated'     => 'Ažurirano',
        'header_actions'     => 'Akcije',
        'global_badge'       => 'Globalni podrazumevani',
        'disabled_badge'     => 'Onemogućen',
        'no_templates'       => 'Još nema prilagođenih šablona — svi mejlovi koriste ugrađeni Blade prikaz.',
    ],

    'editor' => [
        'internal_name'      => 'Interni naziv',
        'subject'            => 'Naslov mejla',
        'from_section'       => 'Adresa pošiljaoca (opciono — koristi globalni MAIL_FROM_ADDRESS ako je prazno)',
        'from_name_ph'       => 'REFEST Tim',
        'from_address_ph'    => 'tickets@refest.rs',
        'is_active'          => 'Aktivan — koristi ovaj šablon pri slanju',
        'template_key'       => 'Ključ šablona:',
        'festival_label'     => 'Festival:',
        'global_label'       => 'Globalni podrazumevani',

        'html_body'          => 'HTML telo',
        'blade'              => 'Blade',
        'css_optional'       => 'CSS (opciono)',
        'css_help'           => 'Ubacuje se u <style> unutar <head> mejla',

        'save'               => 'Sačuvaj šablon',
        'refresh_preview'    => 'Osveži pregled',
        'copy_to_global'     => 'Kopiraj u globalni podrazumevani',
        'delete'             => 'Obriši',
        'back_to_list'       => 'Nazad na listu',
    ],

    'preview' => [
        'live_preview'       => 'Pregled uživo',
        'rendered'           => 'Renderovano',
        'error'              => 'Greška',
    ],

    'variables' => [
        'title'              => 'Dostupne promenljive',
        'intro'              => 'Koristite ih u šablonu sa',
        'copy_title'         => 'Kliknite da kopirate',
    ],

    'confirm' => [
        'delete'             => 'Obriši ovaj šablon? Buduća slanja će koristiti ugrađeni prikaz.',
        'delete_in_editor'   => 'Obriši ovaj šablon? Buduća slanja će se vratiti na podrazumevani.',
    ],

    'flash' => [
        'saved_new'          => 'Šablon je kreiran.',
        'saved_existing'     => 'Šablon je ažuriran (v:version).',
        'deleted'            => 'Šablon je uklonjen (sledeće slanje koristi ugrađeni prikaz ili naredni najspecifičniji prepis).',
        'copied_to_global'   => 'Kopirano u globalne podrazumevane.',
    ],
];
