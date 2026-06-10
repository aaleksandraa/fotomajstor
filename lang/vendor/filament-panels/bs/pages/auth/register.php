<?php

return [

    'title' => 'Registracija',

    'heading' => 'Kreirajte nalog',

    'actions' => [

        'login' => [
            'before' => 'ili',
            'label' => 'prijavite se na postojeći nalog',
        ],

    ],

    'form' => [

        'email' => [
            'label' => 'E-mail adresa',
        ],

        'name' => [
            'label' => 'Ime i prezime',
        ],

        'password' => [
            'label' => 'Lozinka',
            'validation_attribute' => 'lozinka',
        ],

        'password_confirmation' => [
            'label' => 'Potvrdi lozinku',
        ],

        'actions' => [

            'register' => [
                'label' => 'Registruj se',
            ],

        ],

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'Previše pokušaja registracije',
            'body' => 'Pokušajte ponovo za :seconds sekundi.',
        ],

    ],

];
