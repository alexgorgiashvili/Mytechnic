<?php

return [

    /**
     * This value decides to log or not to log requests.
     */
    'debug' => env('BOG_PAYMENT_DEBUG', false),

    /**
     * Payment url from Bank of Georgia
     */
    'url' => env('BOG_PAYMENT_URL'),

    /**
     * Merchant ID from Bank of Georgia
     */
    'client_id' => env('BOG_PAYMENT_CLIENT_ID'),

    /**
     * Page ID from Bank of Georgia
     */
    'secret_key' => env('BOG_SECRET_KEY'),

    /**
     * Shop Name for Bank of Georgia payment
     */
    'shop_name' => env('BOG_PAYMENT_SHOP_NAME', env('APP_NAME')),

    /**
     * Success/Failure callback url for Bank of Georgia
     */
    'redirect_url' => '/bog/payment/callback',

    /**
     * Default currency for Bank of Georgia payment
     */
    'currency' => env('BOG_PAYMENT_CURRENCY', 'GEL'),

    /**
     * Default language for Bank of Georgia payment
     */
    'language' => env('BOG_PAYMENT_LANGUAGE', 'KA'),

];