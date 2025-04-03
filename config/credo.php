<?php

return [

    /**
     * This value decides to log or not to log requests.
     */
    'debug' => env('CREDO_INSTALLMENT_DEBUG', false),

    /**
     * Payment url from Credo Installment
     */
    'url' => env('CREDO_INSTALLMENT_URL'),

    /**
     * Merchant ID from Credo Installment
     */
    'client_id' => env('CREDO_INSTALLMENT_CLIENT_ID'),

    /**
     * Secret key from Credo Installment
     */
    'secret_key' => env('CREDO_INSTALLMENT_SECRET_KEY'),

    /**
     * Default currency for Credo Installment
     */
    'currency' => env('CREDO_INSTALLMENT_CURRENCY', 'GEL'),

    /**
     * Default language for Credo Installment
     */
    'language' => env('CREDO_INSTALLMENT_LANGUAGE', 'KA'),

];
