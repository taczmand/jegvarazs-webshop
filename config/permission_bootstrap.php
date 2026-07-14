<?php

return [
    'token' => env('PERMISSION_BOOTSTRAP_TOKEN'),

    'excluded_permissions' => [
        'view-own-worksheets',
        'view-own-contracts',
    ],

    'users' => [
        'emails' => [
            'info@jegvarazsbolt.hu',
            'norbert@jegvarazsbolt.hu',
            'david.taczman@gmail.com'
        ],
    ],
];
