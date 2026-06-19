<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Linije za validaciju
    |--------------------------------------------------------------------------
    |
    | Sledeće jezičke linije sadrže podrazumevane poruke o greškama koje koristi
    | klasa za validaciju. Neka od ovih pravila imaju više verzija,
    | kao što su pravila za veličinu. Slobodno prilagodite svaku od ovih poruka.
    |
    */

    'accepted' => 'Polje :attribute mora biti prihvaćeno.',
    'accepted_if' => 'Polje :attribute mora biti prihvaćeno kada :other ima vrednost :value.',
    'active_url' => 'Polje :attribute mora biti validan URL.',
    'after' => 'Polje :attribute mora biti datum nakon :date.',
    'after_or_equal' => 'Polje :attribute mora biti datum nakon ili jednak :date.',
    'alpha' => 'Polje :attribute sme sadržati samo slova.',
    'alpha_dash' => 'Polje :attribute sme sadržati samo slova, brojeve, crtice i donje crte.',
    'alpha_num' => 'Polje :attribute sme sadržati samo slova i brojeve.',
    'any_of' => 'Polje :attribute nije validno.',
    'array' => 'Polje :attribute mora biti niz.',
    'ascii' => 'Polje :attribute sme sadržati samo jednobajtne alfanumeričke znakove i simbole.',
    'before' => 'Polje :attribute mora biti datum pre :date.',
    'before_or_equal' => 'Polje :attribute mora biti datum pre ili jednak :date.',
    'between' => [
        'array' => 'Polje :attribute mora imati između :min i :max stavki.',
        'file' => 'Polje :attribute mora biti između :min i :max kilobajta.',
        'numeric' => 'Polje :attribute mora biti između :min i :max.',
        'string' => 'Polje :attribute mora imati između :min i :max znakova.',
    ],
    'boolean' => 'Polje :attribute mora biti tačno ili netačno (true or false).',
    'can' => 'Polje :attribute sadrži neovlašćenu vrednost.',
    'confirmed' => 'Potvrda polja :attribute se ne poklapa.',
    'contains' => 'Polju :attribute nedostaje obavezna vrednost.',
    'current_password' => 'Lozinka je netačna.',
    'date' => 'Polje :attribute mora biti validan datum.',
    'date_equals' => 'Polje :attribute mora biti datum jednak :date.',
    'date_format' => 'Polje :attribute se ne poklapa sa formatom :format.',
    'decimal' => 'Polje :attribute mora imati :decimal decimalnih mesta.',
    'declined' => 'Polje :attribute mora biti odbijeno.',
    'declined_if' => 'Polje :attribute mora biti odbijeno kada :other ima vrednost :value.',
    'different' => 'Polja :attribute i :other moraju biti različita.',
    'digits' => 'Polje :attribute mora imati :digits cifara.',
    'digits_between' => 'Polje :attribute mora imati između :min i :max cifara.',
    'dimensions' => 'Polje :attribute ima nevažeće dimenzije slike.',
    'distinct' => 'Polje :attribute ima dupliranu vrednost.',
    'doesnt_end_with' => 'Polje :attribute se ne sme završavati jednim od sledećih: :values.',
    'doesnt_start_with' => 'Polje :attribute ne sme počinjati jednim od sledećih: :values.',
    'email' => 'Polje :attribute mora biti validna email adresa.',
    'ends_with' => 'Polje :attribute se mora završavati jednim od sledećih: :values.',
    'enum' => 'Izabrana vrednost za :attribute nije validna.',
    'exists' => 'Izabrana vrednost za :attribute nije validna.',
    'extensions' => 'Polje :attribute mora imati jednu od sledećih ekstenzija: :values.',
    'file' => 'Polje :attribute mora biti datoteka.',
    'filled' => 'Polje :attribute mora imati vrednost.',
    'gt' => [
        'array' => 'Polje :attribute mora imati više od :value stavki.',
        'file' => 'Polje :attribute mora biti veće od :value kilobajta.',
        'numeric' => 'Polje :attribute mora biti veće od :value.',
        'string' => 'Polje :attribute mora imati više od :value znakova.',
    ],
    'gte' => [
        'array' => 'Polje :attribute mora imati :value stavki ili više.',
        'file' => 'Polje :attribute mora biti veće ili jednako :value kilobajta.',
        'numeric' => 'Polje :attribute mora biti veće ili jednako :value.',
        'string' => 'Polje :attribute mora imati :value znakova ili više.',
    ],
    'hex_color' => 'Polje :attribute mora biti validna heksadecimalna boja.',
    'image' => 'Polje :attribute mora biti slika.',
    'in' => 'Izabrana vrednost za :attribute nije validna.',
    'in_array' => 'Polje :attribute mora postojati u :other.',
    'integer' => 'Polje :attribute mora biti ceo broj.',
    'ip' => 'Polje :attribute mora biti validna IP adresa.',
    'ipv4' => 'Polje :attribute mora biti validna IPv4 adresa.',
    'ipv6' => 'Polje :attribute mora biti validna IPv6 adresa.',
    'json' => 'Polje :attribute mora biti validan JSON string.',
    'list' => 'Polje :attribute mora biti lista.',
    'lowercase' => 'Polje :attribute mora biti malim slovima.',
    'lt' => [
        'array' => 'Polje :attribute mora imati manje od :value stavki.',
        'file' => 'Polje :attribute mora biti manje od :value kilobajta.',
        'numeric' => 'Polje :attribute mora biti manje od :value.',
        'string' => 'Polje :attribute mora imati manje od :value znakova.',
    ],
    'lte' => [
        'array' => 'Polje :attribute ne sme imati više od :value stavki.',
        'file' => 'Polje :attribute mora biti manje ili jednako :value kilobajta.',
        'numeric' => 'Polje :attribute mora biti manje ili jednako :value.',
        'string' => 'Polje :attribute mora imati :value znakova ili manje.',
    ],
    'mac_address' => 'Polje :attribute mora biti validna MAC adresa.',
    'max' => [
        'array' => 'Polje :attribute ne sme imati više od :max stavki.',
        'file' => 'Polje :attribute ne sme biti veće od :max kilobajta.',
        'numeric' => 'Polje :attribute ne sme biti veće od :max.',
        'string' => 'Polje :attribute ne sme imati više od :max znakova.',
    ],
    'max_digits' => 'Polje :attribute ne sme imati više od :max cifara.',
    'mimes' => 'Polje :attribute mora biti datoteka tipa: :values.',
    'mimetypes' => 'Polje :attribute mora biti datoteka tipa: :values.',
    'min' => [
        'array' => 'Polje :attribute mora imati najmanje :min stavki.',
        'file' => 'Polje :attribute mora biti najmanje :min kilobajta.',
        'numeric' => 'Polje :attribute mora biti najmanje :min.',
        'string' => 'Polje :attribute mora imati najmanje :min znakova.',
    ],
    'min_digits' => 'Polje :attribute mora imati najmanje :min cifara.',
    'missing' => 'Polje :attribute mora nedostajati.',
    'missing_if' => 'Polje :attribute mora nedostajati kada :other ima vrednost :value.',
    'missing_unless' => 'Polje :attribute mora nedostajati osim ako :other ima vrednost :value.',
    'missing_with' => 'Polje :attribute mora nedostajati kada je :values prisutno.',
    'missing_with_all' => 'Polje :attribute mora nedostajati kada su :values prisutna.',
    'multiple_of' => 'Polje :attribute mora biti umnožak :value.',
    'not_in' => 'Izabrana vrednost za :attribute nije validna.',
    'not_regex' => 'Format polja :attribute nije validan.',
    'numeric' => 'Polje :attribute mora biti broj.',
    'password' => [
        'letters' => 'Polje :attribute mora sadržati najmanje jedno slovo.',
        'mixed' => 'Polje :attribute mora sadržati najmanje jedno veliko i jedno malo slovo.',
        'numbers' => 'Polje :attribute mora sadržati najmanje jedan broj.',
        'symbols' => 'Polje :attribute mora sadržati najmanje jedan simbol.',
        'uncompromised' => 'Data vrednost za :attribute se pojavila u curenju podataka. Molimo izaberite drugu vrednost za :attribute.',
    ],
    'present' => 'Polje :attribute mora biti prisutno.',
    'present_if' => 'Polje :attribute mora biti prisutno kada :other ima vrednost :value.',
    'present_unless' => 'Polje :attribute mora biti prisutno osim ako :other ima vrednost :value.',
    'present_with' => 'Polje :attribute mora biti prisutno kada je :values prisutno.',
    'present_with_all' => 'Polje :attribute mora biti prisutno kada su :values prisutna.',
    'prohibited' => 'Polje :attribute je zabranjeno.',
    'prohibited_if' => 'Polje :attribute je zabranjeno kada :other ima vrednost :value.',
    'prohibited_if_accepted' => 'Polje :attribute je zabranjeno kada je :other prihvaćeno.',
    'prohibited_if_declined' => 'Polje :attribute je zabranjeno kada je :other odbijeno.',
    'prohibited_unless' => 'Polje :attribute je zabranjeno osim ako :other nije u :values.',
    'prohibits' => 'Polje :attribute zabranjuje prisustvo :other.',
    'regex' => 'Format polja :attribute nije validan.',
    'required' => 'Polje :attribute je obavezno.',
    'required_array_keys' => 'Polje :attribute mora sadržati unose za: :values.',
    'required_if' => 'Polje :attribute je obavezno kada :other ima vrednost :value.',
    'required_if_accepted' => 'Polje :attribute je obavezno kada je :other prihvaćeno.',
    'required_if_declined' => 'Polje :attribute je obavezno kada je :other odbijeno.',
    'required_unless' => 'Polje :attribute je obavezno osim ako :other nije u :values.',
    'required_with' => 'Polje :attribute je obavezno kada je :values prisutno.',
    'required_with_all' => 'Polje :attribute je obavezno kada su :values prisutna.',
    'required_without' => 'Polje :attribute je obavezno kada :values nije prisutno.',
    'required_without_all' => 'Polje :attribute je obavezno kada nijedno od :values nije prisutno.',
    'same' => 'Polja :attribute i :other se moraju poklapati.',
    'size' => [
        'array' => 'Polje :attribute mora sadržati :size stavki.',
        'file' => 'Polje :attribute mora biti :size kilobajta.',
        'numeric' => 'Polje :attribute mora biti :size.',
        'string' => 'Polje :attribute mora imati :size znakova.',
    ],
    'starts_with' => 'Polje :attribute mora počinjati jednim od sledećih: :values.',
    'string' => 'Polje :attribute mora biti tekst (string).',
    'timezone' => 'Polje :attribute mora biti validna vremenska zona.',
    'unique' => 'Vrednost za polje :attribute već postoji.',
    'uploaded' => 'Postavljanje polja :attribute nije uspelo.',
    'uppercase' => 'Polje :attribute mora biti velikim slovima.',
    'url' => 'Polje :attribute mora biti validan URL.',
    'ulid' => 'Polje :attribute mora biti validan ULID.',
    'uuid' => 'Polje :attribute mora biti validan UUID.',

    /*
    |--------------------------------------------------------------------------
    | Prilagođene linije za validaciju
    |--------------------------------------------------------------------------
    |
    | Ovde možete navesti prilagođene poruke za validaciju atributa koristeći
    | konvenciju "attribute.rule" za imenovanje linija. Ovo omogućava brzo
    | specificiranje konkretne prilagođene jezičke linije za dato pravilo atributa.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message', // prilagođena-poruka
        ],
        // Primeri koje smo ranije definisali:
        // 'current_password' => [
        //     'required' => 'Molimo unesite svoju trenutnu lozinku.',
        //     'current_password' => 'Uneta trenutna lozinka se ne poklapa sa našom evidencijom.',
        // ],
        // 'password' => [ // Ovo se odnosi na polje 'nova lozinka'
        //     'required' => 'Molimo unesite novu lozinku.',
        //     'confirmed' => 'Potvrda nove lozinke se ne poklapa.',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Prilagođeni atributi za validaciju
    |--------------------------------------------------------------------------
    |
    | Sledeće jezičke linije se koriste za zamenu našeg placeholder-a atributa
    | nečim čitljivijim kao što je "Email Adresa" umesto "email".
    | Ovo nam jednostavno pomaže da našu poruku učinimo izražajnijom.
    |
    */

    'attributes' => [
        // Primeri koje smo ranije definisali:
        // 'current_password' => 'trenutna lozinka',
        // 'password' => 'nova lozinka',
        // 'password_confirmation' => 'potvrda nove lozinke',
    ],

];
