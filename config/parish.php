<?php

/**
 * Parish configuration — reads from environment variables.
 *
 * These values come from Render environment variables → start-apache.sh
 * patches them into .env at startup → config:cache freezes them.
 *
 * Admin-editable overrides (parish_name, parish_address, etc.) are stored
 * in the `settings` database table by SettingsController::update() and
 * read back by SettingsController::index() via Setting::get().
 *
 * The Blade views that use config('parish.*') show the .env defaults.
 * If the admin updates values through the Settings panel, those updated
 * values are stored in the DB and shown back in the Settings form.
 */

return [
    'name'    => env('PARISH_NAME',    'Mary Help of Christians Parish'),
    'address' => env('PARISH_ADDRESS', 'Southville 1, Niugan, Cabuyao, Laguna'),
    'phone'   => env('PARISH_PHONE',   '(049) 5668994'),
    'email'   => env('PARISH_EMAIL',   'mhcparish@gmail.com'),
    'priest'  => env('PARISH_PRIEST',  'Rev. Fr. Erwin S. Sanchez'),

    // Payment accounts for GCash and Maya (InstaPay)
    'gcash' => [
        'number' => env('GCASH_NUMBER', '09369454812'),
        'name'   => env('GCASH_NAME',   'Aries Cumpio'),
    ],
    'maya' => [
        'number' => env('MAYA_NUMBER', '09918276384'),
        'name'   => env('MAYA_NAME',   'Aries Cumpio'),
    ],
];
