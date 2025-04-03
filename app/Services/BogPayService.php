<?php


namespace App\Services;

use App\Traits\FormatTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


class BogPayService 
{

    public function create($amount, $generatedOrderId)
    {
        $token = $this->getToken(config('bog.client_id'), config('bog.secret_key'));

        if (!$token) {
            return false;
        }

        // Prepare data according to the new API requirements
        $postfields = [
            // 'callback_url' => route('bog.payment.callback'), // HTTPS URL for callback
            'callback_url' => 'https://mytechnic.ge/bogpay/payment/callback',
            'external_order_id' => $generatedOrderId, // Internal order ID
            'purchase_units' => [
                'currency' => config('bog.currency'),
                'total_amount' => round($amount, 2),
                'basket' => [
                    [
                        'product_id' => '1',  // Replace with actual product ID
                        'quantity' => 1,
                        'unit_price' => round($amount, 2)
                    ]
                ]
            ],
            'redirect_urls' => [
                'fail' => route('home'),        // Failure redirect
                'success' => route('home')   // Success redirect
            ]
        ];
        // Make the order creation request
        $response = $this->makeOrder($token, $postfields);
        return $response;
    }




    public function getToken($client_id, $secret_key)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://oauth2.bog.ge/auth/realms/bog/protocol/openid-connect/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(['grant_type' => 'client_credentials']),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/x-www-form-urlencoded",
            ],
            CURLOPT_USERPWD => $client_id . ":" . $secret_key, // Use client_id and secret_key for basic auth
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        
        $response = json_decode($response, true);
        
        if (isset($response['access_token'])) {
            return $response['access_token'];
        } else {
            return false; // Return false if token request fails
        }
    }

    public function makeOrder($token, $postfields)
    {

        $curl = curl_init();
    
        curl_setopt_array($curl, array(
            CURLOPT_URL => config('bog.url') . "/payments/v1/ecommerce/orders",  // New API endpoint
            // CURLOPT_URL => "/payments/v1/ecommerce/orders",  // New API endpoint

            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($postfields),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer " . $token,
                "Accept-Language: ka"  // Set language to Georgian or other based on your preference
            ],
        ));
    
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }

    // public function refund($amount, $orderId)
    // {
    //     $token = $this->getToken(config('bog.client_id'), config('bog.secret_key'));

    //     if(!$token)
    //     {
    //         return $token;
    //     }

    //     $postfields = [

    //         // ENUM: CAPTURE, AUTHORIZE, LOAN
    //         'order_id' => $orderId,

    //         //URL of the page to which the payer will be redirected after a success/failure payment. Does not contain any data
    //         'amount' => $amount,
    //     ];

    //     $postFields = 'order_id='.$orderId.'&amount='.$amount;
    //         //  return urlencode($postFields);
    //     $curl = curl_init();

    //     curl_setopt_array($curl, array(

    //         //For production replace https://dev.ipay.ge/ to https://ipay.ge
    //         CURLOPT_URL => config('bog.url')."/checkout/refund",
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => "",
    //         CURLOPT_MAXREDIRS => 10,
    //         CURLOPT_TIMEOUT => 0,
    //         CURLOPT_FOLLOWLOCATION => true,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => "POST",
    //         CURLOPT_POSTFIELDS => $postFields,
    //         CURLOPT_HTTPHEADER => array(
    //             "Content-Type: application/x-www-form-urlencoded",

    //             //access token
    //             "Authorization: Bearer " . $token
    //         ),
    //     ));

    //     $response = curl_exec($curl);

    //     curl_close($curl);

    //     return $response;
    // }

    
    // public function orderStatus($order_id)
    // {
    //     $token = $this->getToken(config('bog.client_id'), config('bog.secret_key'));

    //     $ch = curl_init();

    //     curl_setopt($ch, CURLOPT_URL, config('bog.url')."/checkout/orders/status/".$order_id);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


    //     $headers = array();
    //     $headers[] = 'Accept: application/json';
    //     $headers[] = "Authorization: Bearer " . $token;

    //     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    //     $response = curl_exec($ch);
    //     curl_close($ch);

    //     $response = json_decode($response, true);
    //     return $response;
    // }


    // public function paymentDetails($order_id)
    // {
    //     $token = $this->getToken(config('bog.client_id'), config('bog.secret_key'));

    //     $ch = curl_init();

    //     curl_setopt($ch, CURLOPT_URL, config('bog.url')."/checkout/orders/".$order_id);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


    //     $headers = array();
    //     $headers[] = 'Accept: application/json';
    //     $headers[] = "Authorization: Bearer " . $token;

    //     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    //     $response = curl_exec($ch);
    //     curl_close($ch);

    //     $response = json_decode($response, true);
    //     return $response;
    // }

}