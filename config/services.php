<?php

return [
    'whmcs' => [
        'url' => env('WHMCS_URL'),
        'identifier' => env('WHMCS_IDENTIFIER'),
        'secret' => env('WHMCS_SECRET'),
        'timeout' => env('WHMCS_TIMEOUT', 15),
        'support_dept_id' => env('WHMCS_SUPPORT_DEPT_ID', 1),
    ],
];
