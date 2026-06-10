<?php

return [

    'title' => 'Resetovanje lozinke',

    'heading' => 'Resetujte lozinku',

    'form' => [

        'email' => [
            'label' => 'E-mail adresa',
        ],

        'password' => [
            'label' => 'Lozinka',
            'validation_attribute' => 'lozinka',
        ],

        'password_confirmation' => [
            'label' => 'Potvrdi lozinku',
        ],

        'actions' => [

            'reset' => [
                'label' => 'Resetuj lozinku',
            ],

        ],

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'Previše pokušaja resetovanja',
            'body' => 'Pokušajte ponovo za :seconds sekundi.',
        ],

    ],

];
