<?php

return [

    'title' => 'Prijava',

    'heading' => 'Prijavite se',

    'actions' => [

        'register' => [
            'before' => 'ili',
            'label' => 'kreirajte novi nalog',
        ],

        'request_password_reset' => [
            'label' => 'Zaboravljena lozinka?',
        ],

    ],

    'form' => [

        'email' => [
            'label' => 'E-mail adresa',
        ],

        'password' => [
            'label' => 'Lozinka',
        ],

        'remember' => [
            'label' => 'Zapamti me',
        ],

        'actions' => [

            'authenticate' => [
                'label' => 'Prijavi se',
            ],

        ],

    ],

    'messages' => [

        'failed' => 'Ovi podaci se ne podudaraju s našim zapisima.',

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'Previše pokušaja prijave',
            'body' => 'Pokušajte ponovo za :seconds sekundi.',
        ],

    ],

];
