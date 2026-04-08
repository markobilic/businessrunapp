<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines (Serbian)
    |--------------------------------------------------------------------------
    */

    'accepted'             => 'Morate označiti ovo polje kao prihvaćeno.',
    'accepted_if'          => 'Morate označiti ovo polje kao prihvaćeno kada je :other :value.',
    'active_url'           => 'Uneti URL nije važeći.',
    'after'                => 'Uneti datum mora biti nakon :date.',
    'after_or_equal'       => 'Uneti datum mora biti :date ili kasniji.',
    'alpha'                => 'Ovo polje može sadržati samo slova.',
    'alpha_dash'           => 'Ovo polje može sadržati samo slova, brojeve, crtice i donje crte.',
    'alpha_num'            => 'Ovo polje može sadržati samo slova i brojeve.',
    'array'                => 'Ovo polje mora biti niz.',
    'ascii'                => 'Ovo polje može sadržati samo jednobajtne alfanumeričke znakove i simbole.',
    'before'               => 'Uneti datum mora biti pre :date.',
    'before_or_equal'      => 'Uneti datum mora biti :date ili raniji.',

    'between'              => [
        'numeric' => 'Vrednost mora biti između :min i :max.',
        'file'    => 'Fajl mora imati između :min i :max KB.',
        'string'  => 'Tekst mora imati između :min i :max karaktera.',
        'array'   => 'Niz mora sadržati između :min i :max stavki.',
    ],

    'boolean'              => 'Ovo polje mora biti tačno ili netačno.',
    'confirmed'            => 'Potvrda se ne poklapa sa originalnom vrednošću.',
    'current_password'     => 'Lozinka nije tačna.',
    'date'                 => 'Ovo polje mora biti važeći datum.',
    'date_equals'          => 'Uneti datum mora biti jednak :date.',
    'date_format'          => 'Datum se ne poklapa sa formatom :format.',
    'decimal'              => 'Ovo polje mora imati :decimal decimalnih mesta.',
    'declined'             => 'Ovo polje mora biti odbijeno.',
    'declined_if'          => 'Ovo polje mora biti odbijeno kada je :other :value.',
    'different'            => 'Vrednosti moraju biti različite.',
    'digits'               => 'Ovo polje mora imati tačno :digits cifara.',
    'digits_between'       => 'Ovo polje mora imati između :min i :max cifara.',
    'dimensions'           => 'Slika ima nevažeće dimenzije.',
    'distinct'             => 'Ovo polje ima duplikat vrednost.',
    'doesnt_end_with'      => 'Ovo polje ne sme završavati sa jednom od sledećih vrednosti: :values.',
    'doesnt_start_with'    => 'Ovo polje ne sme počinjati sa jednom od sledećih vrednosti: :values.',
    'email'                => 'Ovo polje mora biti važeća email adresa.',
    'ends_with'            => 'Ovo polje mora se završiti sa jednom od sledećih vrednosti: :values.',
    'enum'                 => 'Izabrana vrednost nije dozvoljena.',
    'exists'               => 'Izabrana vrednost ne postoji.',
    'file'                 => 'Ovo polje mora biti fajl.',
    'filled'               => 'Ovo polje mora imati vrednost.',
    'gt'                   => [
        'numeric' => 'Vrednost mora biti veća od :value.',
        'file'    => 'Fajl mora biti veći od :value KB.',
        'string'  => 'Tekst mora imati više od :value karaktera.',
        'array'   => 'Niz mora imati više od :value stavki.',
    ],
    'gte'                  => [
        'numeric' => 'Vrednost mora biti najmanje :value.',
        'file'    => 'Fajl mora biti najmanje :value KB.',
        'string'  => 'Tekst mora imati najmanje :value karaktera.',
        'array'   => 'Niz mora imati najmanje :value stavki.',
    ],
    'image'                => 'Ovo polje mora biti slika.',
    'in'                   => 'Izabrana vrednost nije važeća.',
    'in_array'             => 'Ovo polje mora postojati u :other.',
    'integer'              => 'Ovo polje mora biti ceo broj.',
    'ip'                   => 'Ovo polje mora biti važeća IP adresa.',
    'ipv4'                 => 'Ovo polje mora biti važeća IPv4 adresa.',
    'ipv6'                 => 'Ovo polje mora biti važeća IPv6 adresa.',
    'json'                 => 'Ovo polje mora biti važeći JSON string.',

    'list'                 => 'Ovo polje mora biti lista vrednosti.',
    'lowercase'            => 'Ovo polje mora biti napisano malim slovima.',
    'lt'                   => [
        'numeric' => 'Vrednost mora biti manja od :value.',
        'file'    => 'Fajl mora biti manji od :value KB.',
        'string'  => 'Tekst mora imati manje od :value karaktera.',
        'array'   => 'Niz mora imati manje od :value stavki.',
    ],
    'lte'                  => [
        'numeric' => 'Vrednost ne sme prelaziti :value.',
        'file'    => 'Fajl ne sme biti veći od :value KB.',
        'string'  => 'Tekst ne sme imati više od :value karaktera.',
        'array'   => 'Niz ne sme imati više od :value stavki.',
    ],
    'mac_address'          => 'Ovo polje mora biti važeća MAC adresa.',
    'max_digits'           => 'Ovo polje ne sme imati više od :max cifara.',
    'mimes'                => 'Ovo polje mora biti tipa: :values.',
    'mimetypes'            => 'Ovo polje mora biti tipa: :values.',
    'min' => [
        'array' => 'Ovo polje mora imati najmanje :min stavki.',
        'file' => 'Ovo polje mora imati najmanje :min kilobajta.',
        'numeric' => 'Ovo polje mora biti najmanje :min.',
        'string' => 'Ovo polje mora imati najmanje :min znakova.',
    ],
    'min_digits'           => 'Ovo polje mora imati najmanje :min cifara.',
    'multiple_of'          => 'Vrednost mora biti deljiva sa :value.',
    'not_in'               => 'Izabrana vrednost nije dozvoljena.',
    'not_regex'            => 'Format nije dozvoljen.',
    'numeric'              => 'Ovo polje mora biti broj.',
    'password'             => [
        'letters'       => 'Morate uneti bar jedno slovo.',
        'mixed'         => 'Morate uneti bar jedno veliko i jedno malo slovo.',
        'numbers'       => 'Morate uneti bar jednu cifru.',
        'symbols'       => 'Morate uneti bar jedan simbol.',
        'uncompromised' => 'Ova lozinka je procenjena kao nesigurna. Molimo izaberite drugu.',
    ],
    'present'              => 'Ovo polje mora biti prisutno.',
    'present_if'           => 'Ovo polje mora biti prisutno kada je :other :value.',
    'present_unless'       => 'Ovo polje mora biti prisutno osim ako je :other :values.',
    'present_with'         => 'Ovo polje mora biti prisutno kada je :values prisutno.',
    'present_with_all'     => 'Ovo polje mora biti prisutno kada su svi :values prisutni.',
    'prohibited'           => 'Ovo polje je zabranjeno.',
    'prohibited_if'        => 'Ovo polje je zabranjeno kada je :other :value.',
    'prohibited_unless'    => 'Ovo polje je zabranjeno osim ako je :other u :values.',
    'prohibits'            => 'Ovo polje zabranjuje prisustvo :other.',
    'regex'                => 'Format nije validan.',
    'required'             => 'Ovo polje je obavezno.',
    'required_array_keys'  => 'Ovaj niz mora sadržati ključeve: :values.',
    'required_if'          => 'Ovo polje je obavezno kada je :other :value.',
    'required_if_accepted' => 'Ovo polje je obavezno kada je :other prihvaćen.',
    'required_if_declined' => 'Ovo polje je obavezno kada je :other odbijen.',
    'required_unless'      => 'Ovo polje je obavezno osim ako je :other u :values.',
    'required_with'        => 'Ovo polje je obavezno kada je :values prisutno.',
    'required_with_all'    => 'Ovo polje je obavezno kada su svi :values prisutni.',
    'required_without'     => 'Ovo polje je obavezno kada :values nije prisutno.',
    'required_without_all' => 'Ovo polje je obavezno kada nijedno od :values nije prisutno.',
    'same'                 => 'Ovo polje i :other se moraju poklapati.',
    'size'                 => [
        'numeric' => 'Vrednost mora biti :size.',
        'file'    => 'Fajl mora biti :size KB.',
        'string'  => 'Tekst mora imati :size karaktera.',
        'array'   => 'Niz mora sadržati :size stavki.',
    ],
    'starts_with'          => 'Ovo polje mora počinjati sa jednom od: :values.',
    'string'               => 'Ovo polje mora biti tekst.',
    'timezone'             => 'Ovo polje mora biti važeća vremenska zona.',
    'unique'               => 'Ova vrednost je već zauzeta.',
    'uploaded'             => 'Otpremanje nije uspelo.',
    'uppercase'            => 'Ovo polje mora biti napisano velikim slovima.',
    'url'                  => 'Format URL-a nije validan.',
    'ulid'                 => 'Ovo polje mora biti važeći ULID.',
    'uuid'                 => 'Ovo polje mora biti važeći UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'Prilagođena poruka za :attribute i pravilo :rule-name.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | Ovde možete dati “ljudska” imena vašim poljima:
    |
    |   'email' => 'email adresa',
    |   'password' => 'lozinka',
    |
    */

    'attributes' => [],

];
