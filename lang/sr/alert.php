<?php // resources/lang/sr/alert.php

return [
    // Poruke za Tipove Ulaznica
    'ticket_type_created_success' => 'Tip ulaznice je uspešno kreiran!',
    'ticket_type_create_failed'   => 'Kreiranje tipa ulaznice nije uspelo. Molimo pokušajte ponovo. Greška: :error',

    'ticket_type_updated_success' => 'Tip ulaznice je uspešno ažuriran!',
    'ticket_type_update_failed'   => 'Ažuriranje tipa ulaznice nije uspelo. Molimo pokušajte ponovo. Greška: :error',

    'ticket_type_deleted_success' => 'Tip ulaznice je uspešno obrisan!',
    'ticket_type_delete_failed'   => 'Brisanje tipa ulaznice nije uspelo. Greška: :error',

    'failed_to_create_directory' => 'Kreiranje direktorijuma nije uspelo: :error',
    'failed_to_move_photo'       => 'Premeštanje otpremljene fotografije nije uspelo: :error',
    'update_failed_create_directory' => 'Ažuriranje nije uspelo: Kreiranje direktorijuma nije moguće: :error',
    'update_failed_move_photo'       => 'Ažuriranje nije uspelo: Premeštanje nove fotografije nije moguće: :error',

    'order_created_success' => 'Narudžbina je uspešno kreirana! Procesiranje je pokrenuto za narudžbinu.',
    'order_created_failure' => 'Kreiranje narudžbine nije uspelo usled interne greške: :message',
    'image_generation_requeued' => 'Generisanje slike za narudžbinu je ponovo stavljeno u red.',
    'image_generation_cannot_rerun' => 'Generisanje slike za narudžbinu ne može biti ponovo pokrenuto iz trenutnog stanja (:status).',

    'email_requeued_success' => 'Email za narudžbinu je ponovo stavljen u red za slanje.',
    'email_cannot_resent' => 'Email za narudžbinu ne može biti ponovo poslat iz trenutnog stanja (:status).',

    'payment_amount_updated' => 'Iznos uplate je ažuriran.',
    'ticket_codes_not_found' => 'Nijedan od izabranih kodova ulaznica nije pronađen za ovu narudžbinu.',
    'no_tickets_to_process' => 'Nema dostupnih ulaznica za obradu za ovu narudžbinu.',
    'no_qr_codes_found' => 'Nisu pronađene slike QR kodova za navedene ulaznice.',
    'zip_creation_failed' => 'Kreiranje ZIP datoteke nije uspelo. Proverite dozvole servera ili logove.',

    'promoter_updated_success' => 'Promoter je uspešno ažuriran!',
    'auth_required' => 'Potrebna je autentifikacija.',
    'ticket_type_created_success' => 'Tip ulaznice je uspešno kreiran!',
    'ticket_type_create_failed' => 'Kreiranje tipa ulaznice nije uspelo. Molimo pokušajte ponovo. Greška: :message',
    'ticket_type_updated_success' => 'Tip ulaznice je uspešno ažuriran!',
    'ticket_type_update_failed' => 'Ažuriranje tipa ulaznice nije uspelo. Greška: :message',
    'ticket_type_deleted_success' => 'Tip ulaznice je uspešno obrisan!',
    'ticket_type_delete_failed' => 'Brisanje tipa ulaznice nije uspelo. Greška: :message',

    'password_update_success' => 'Lozinka je uspešno ažurirana!',
    'password_update_failed' => 'Ažuriranje lozinke nije uspelo. Molimo pokušajte ponovo.',
    'validation_failed_check_fields' => 'Validacija nije uspela. Molimo proverite polja za unos radi grešaka.',

    /* ---- Upravljanje festivalima ---- */
    'festival_created'              => 'Festival je uspešno kreiran.',
    'festival_updated'              => 'Festival je uspešno ažuriran.',
    'festival_deleted'              => 'Festival je obrisan.',
    'festival_cannot_delete_active' => 'Samo festivalsi u statusu "nacrt" mogu biti obrisani. Prvo ga arhivirajte.',
    'assignment_added'              => 'Korisnik je dodeljen festivalu.',
    'assignment_removed'            => 'Korisnik je uklonjen sa festivala.',

    /* ---- Promoter menadžer / provizije sub-promotera ---- */
    'sub_promoter_created'                  => 'Sub-promoter :name je kreiran. Sada možete da podesite provizije.',
    'commissions_saved'                    => 'Provizije su sačuvane.',
    'sub_commissions_saved'                => 'Provizije sub-promotera su sačuvane.',
    'sub_commission_cannot_exceed_manager'  => 'Provizija sub-promotera ne može biti veća od vaše provizije (:manager RSD) za ovaj tip karte.',
    'promoter_promoted_to_manager'          => ':name je sada promoter menadžer. Može da kreira svoje sub-promotere.',
    'promoter_demoted'                      => ':name je vraćen na običnog promotera.',

    /* ---- Upravljanje korisnicima ---- */
    'user_created'           => 'Korisnik je uspešno kreiran.',
    'user_updated'           => 'Korisnik je uspešno ažuriran.',
    'user_deleted'           => 'Korisnik je obrisan.',
    'user_cannot_delete_self'=> 'Ne možete obrisati sopstveni nalog.',

    /* ---- Autorizacija ---- */
    'no_festival_access'     => 'Nemate pristup ovom festivalu.',
    'role_unauthorized'      => 'Niste ovlašćeni za ovu akciju.',
];
