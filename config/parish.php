<?php

return [
    'name'    => env('PARISH_NAME', 'Mary Help of Christians Parish'),
    'address' => env('PARISH_ADDRESS', 'Southville 1, Niugan, Cabuyao, Laguna'),
    'phone'   => env('PARISH_PHONE', '+63 49 XXX XXXX'),
    'email'   => env('PARISH_EMAIL', 'mhcparish@gmail.com'),
    'priest'  => env('PARISH_PRIEST', 'Rev. Fr. Parish Priest'),

    // Payment accounts for GCash and Maya (InstaPay)
    'gcash' => [
        'number' => env('GCASH_NUMBER', '09369454812'),
        'name'   => env('GCASH_NAME', 'Aries Cumpio'),
    ],
    'maya' => [
        'number' => env('MAYA_NUMBER', '09918276384'),
        'name'   => env('MAYA_NAME', 'Aries Cumpio'),
    ],
];
