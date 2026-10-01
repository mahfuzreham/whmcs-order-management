<?php

return [
    'admin' => ['master_email'=>env('ADMIN_MASTER_EMAIL'), 'master_password'=>env('ADMIN_MASTER_PASSWORD')],
    'refund' => ['window_minutes'=>env('REFUND_WINDOW_MINUTES',5)],
    'bkash' => ['enabled'=>env('BKASH_ENABLED',false),'base_url'=>env('BKASH_BASE_URL','https://tokenized.pay.bka.sh/v1.2.0-beta'),'app_key'=>env('BKASH_APP_KEY'),'app_secret'=>env('BKASH_APP_SECRET'),'username'=>env('BKASH_USERNAME'),'password'=>env('BKASH_PASSWORD'),'callback_url'=>env('BKASH_CALLBACK_URL'),'timeout'=>env('BKASH_TIMEOUT',20)],
    'whmcs' => [
        'url' => env('WHMCS_URL'),
        'identifier' => env('WHMCS_IDENTIFIER'),
        'secret' => env('WHMCS_SECRET'),
        'timeout' => env('WHMCS_TIMEOUT', 15),
        'support_dept_id' => env('WHMCS_SUPPORT_DEPT_ID', 1),
    ],
];
