<?php

namespace App\Services;

use App\Models\Product;
use App\Models\OrderInstallment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class CredoInstallmentService 
{
    public function createOrder($validatedData)
    {
        // Ensure unique order code
        do {
            $orderCode = uniqid();
        } while (OrderInstallment::where('order_code', $orderCode)->exists());
    
        // Get product details from the product_ids
        $productIds = $validatedData['product_ids']; // Product IDs as an array from request
    
        // Retrieve product details from the products table
        $products = Product::whereIn('id', $productIds)->get();
    
        // Map products to the required format for the external API
        $productData = $products->map(function($product) {
            return [
                "id" => (string)$product->id,
                "title" => $product->getTitleAttribute(),
                "amount" => (string)1, // Assuming 1 unit for each product
                "price" => (string)($product->price * 100), // Convert price to cents if required
                "type" => "0", // Assuming type 0 for products
            ];
        });
    
        // Generate a hash for Credo API call
        $check = $this->generateHash($productData, config('credo.secret_key'));
    
        // Prepare request payload for Credo API
        $requestData = [
            'merchantId' => config('credo.client_id'),
            'orderCode' => $orderCode,
            'check' => $check,
            'products' => $productData,
            'installmentLength' => 12,
            'clientFullName' => $validatedData['name'],
            'mobile' => $validatedData['phone_no'],
            'factAddress' => $validatedData['address'],
        ];
    
        // Make the API call to Credo
        $response = Http::post(config('credo.url'), $requestData);
    
        // Check if the response is successful
        if ($response->successful()) {
            // Save order details to the database
            OrderInstallment::create([
                'order_code' => $orderCode,
                'client_full_name' => $validatedData['name'],
                'personal_number' => $validatedData['personal_number'],
                'mobile' => $validatedData['phone_no'],
                'payment_type_id' => $validatedData['payment_type'], // Assuming payment_type corresponds to ID
                'fact_address' => $validatedData['address'],
                'product_ids' => $productIds, // Store as JSON or use $casts in the model
            ]);
    
            // Return the API response
            Log::channel('mylog')->info('response', $response->json());

            return $response->json();
        } else {
            // Log the error and return a response
            Log::error('Failed to create order: ' . $response->body());
            return response()->json(['error' => 'Failed to create order'], 500);
        }
    }
    
    private function generateHash($productData, $secret) {
        $checkString = $productData->flatMap(function ($item) {
            return [$item['id'], $item['title'], $item['amount'], $item['price']];
        })->implode('') . $secret;

        return md5($checkString);
    }

    public function getOrderStatus($merchantId, $orderCode) {
        $secret = config('credo.secret_key'); // Use the secret from config
        $hash = $this->generateHash([$merchantId, $orderCode], $secret); // Generate hash using the new method
    
        $apiUrl = config('credo.url'); // Use the URL from config for status check
    

        return [
            'status' => 'finished', // Set the status you want to test
            'message' => 'Fake response for testing',
        ];
        $response = Http::get($apiUrl, [
            'merchantId' => $merchantId,
            'orderCode' => $orderCode,
            'hash' => $hash,
        ]);
    
        if ($response->successful()) {
            return $response->json();
        } else {    
            Log::error('Failed to get order status: ' . $response->body()); // Improved logging
            return response()->json(['error' => 'Failed to get order status'], 500);
        }
    }
    
}
