<?php

return [
    'title' => 'Potvrdite e-mail adresu',
    'heading' => 'Potvrdite e-mail adresu',
    'actions' => [
        'resend_notification' => [
            'label' => 'Pošalji ponovno',
        ],
    ],
    'messages' => [
        'notification_not_received' => 'Niste primili e-mail?',
        'notification_sent' => 'Poslali smo poruku na :email s uputama za potvrdu vaše e-mail adrese.',
    ],
    'notifications' => [
        'notification_resent' => [
            'title' => 'E-mail za potvrdu ponovno je poslan.',
        ],
        'notification_resend_throttled' => [
            'title' => 'Previše pokušaja ponovnog slanja',
            'body' => 'Pokušajte ponovno za :seconds sekundi.',
        ],
    ],
];
