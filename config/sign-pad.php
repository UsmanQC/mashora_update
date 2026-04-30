<?php

return [
    'disk_name' => env('SIGN_PAD_DISK', 'public'),

    'certify_documents' => env('SIGN_PAD_CERTIFY', false),

    /** Route name after saving signature (doctor onboarding continues with duration step). */
    'redirect_route_name' => env('SIGN_PAD_REDIRECT_ROUTE', 'filament.doctor.pages.onboarding-duration'),
];
