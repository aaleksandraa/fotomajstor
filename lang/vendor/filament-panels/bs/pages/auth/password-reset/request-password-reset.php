<?php

return [

    'title' => 'Resetovanje lozinke',

    'heading' => 'Zaboravljena lozinka?',

    'actions' => [

        'login' => [
            'label' => 'nazad na prijavu',
        ],

    ],

    'form' => [

        'email' => [
            'label' => 'E-mail adresa',
        ],

        'actions' => [

            'request' => [
                'label' => 'Pošalji e-mail',
            ],

        ],

    ],

    'notifications' => [

        'sent' => [
            'body' => 'Ako vaš nalog ne postoji, nećete primiti e-mail.',
        ],

        'throttled' => [
            'title' => 'Previše zahtjeva',
            'body' => 'Pokušajte ponovo za :seconds sekundi.',
        ],

    ],

];
