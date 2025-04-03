<?php

namespace App\Http\Controllers\Site;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\CharicxvaLog;
use Illuminate\Http\Request;
use App\Services\BogPayService;
use App\Models\OrderInstallment;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\InstallmentRequest;
use App\Services\CredoInstallmentService;



class GeoPaymentController extends Controller
{

    protected $credoInstallmentService;
    protected $bogPayService;

    public function __construct(CredoInstallmentService $credoInstallmentService, BogPayService $bogPayService) {
        $this->credoInstallmentService = $credoInstallmentService;
        $this->bogPayService = $bogPayService;
    }

    public function validatePaymentOrder(Request $request)
    {
 
        try {
            // Laravel automatically validates the request using the InstallmentRequest class.
            
            // Log the validated request data to check structure.
            Log::channel('mylog')->info('Validated Data:', $request->all());
            // Process the request after validation.
            $paymentType = $request->input('payment_type');
    
            // Route to appropriate service based on payment type.
            switch ($paymentType) {
                case 1: // BOG payment
                    return $this->bogPayRedirect($request);
                
                case 2: // Credo installment
                    return $this->credoInstallmentRequest($request);
    
                case 3: // BOG installment
                    return $this->bogInstallmentRequest($request);
    
                case 4: // TBC installment
                    return $this->tbcInstallmentRequest($request);
    
                default:
                    return response()->json(['error' => 'Invalid payment type'], 400);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // If validation fails, capture the validation error messages
            Log::channel('mylog')->error('Validation Error:', [
                'errors' => $e->errors(), // Array of validation errors
                'request' => $request->all() // Log the entire request for debugging
            ]);
    
            // Return a 422 response with validation errors.
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            // If any other error occurs, log the exception message and stack trace
            Log::channel('mylog')->error('Exception occurred:', [
                'message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);
    
            // Return a general error response
            return response()->json(['error' => 'An error occurred', 'message' => $e->getMessage()], 500);
        }
    }
    

    public function bogPayRedirect(Request $request)
    {
        $generatedOrderId = 'IPAY-' . Str::random();  // Generate unique order ID
        $amount = $request->total ?? 0;  // Use the amount for testing
    
        Log::channel('mylog')->info('Payment request received', ['request' => $request->all()]);
    
        // Create the payment order using the BogService
        if($amount > 0){

            $response = $this->bogPayService->create($amount, $generatedOrderId);
        }
    
        if (!$response) {
            Log::channel('mylog')->error('Failed to create payment order: Token not found');
            return response()->json(['error' => 'Token not found'], 500);
        }
    
        if (isset($response['id'])) {
            // Extract the order ID and redirect URL
            $order_id = $response['id'];
            $redirect_url = $response['_links']['redirect']['href'];
    
            // Save the payment order to the database
            try {
                $charicxvaLogCreate = new CharicxvaLog();
                $charicxvaLogCreate->generated_order_id = $generatedOrderId;
                $charicxvaLogCreate->payment_hash = $order_id;
                $charicxvaLogCreate->order_id = $order_id;
                $charicxvaLogCreate->user_id = '111';  // Add user ID dynamically
                $charicxvaLogCreate->amount = $amount;
                $charicxvaLogCreate->chveni_statusi = "dawyeba";
                $charicxvaLogCreate->status = 'CREATED';
                $charicxvaLogCreate->date = Carbon::now();
                $charicxvaLogCreate->save();


            $validatedData = $request->all();

           OrderInstallment::create([
                'order_code' => $generatedOrderId,  // Use the generated order code
                'client_full_name' => $validatedData['name'],
                'status_id' => 100,
                'personal_number' => $validatedData['personal_number'],
                'mobile' => $validatedData['phone_no'],
                'payment_type_id' => $validatedData['payment_type'], // Assuming payment_type corresponds to ID
                'fact_address' => $validatedData['address'],
                'product_ids' => $validatedData['product_ids'], // Store product IDs as JSON or another appropriate format
            ]);
    
                Log::channel('mylog')->info('Payment order created', ['orderId' => $order_id, 'redirectUrl' => $redirect_url]);
    
                // Return the redirect URL to the front end
                return response()->json(['redirect_url' => $redirect_url], 200);
    
            } catch (\Throwable $th) {
                Log::channel('mylog')->error('Error saving payment order log', ['error' => $th->getMessage()]);
                return response()->json(['error' => 'Error saving payment log'], 500);
            }
    
        } else {
            Log::channel('mylog')->error('Invalid payment response', ['response' => $response]);
            return response()->json(['error' => 'Invalid response from payment gateway'], 500);
        }
    }
    

    public function bogPaymentCallback(Request $request){

         // Log the callback request
            Log::channel('mylog')->info('Payment callback request received', [
                'request_data' => $request->all()
            ]);
        
            // Extract necessary data from the callback
            $body = $request->input('body');
            $order_id = $body['order_id'] ?? null;
            $external_order_id = $body['external_order_id'] ?? null;
            $status_key = $body['order_status']['key'] ?? null;
            $transaction_id = $body['payment_detail']['transaction_id'] ?? 'N/A';
            $payer_identifier = $body['payment_detail']['payer_identifier'] ?? 'N/A';
            $payment_method = $body['payment_detail']['transfer_method']['value'] ?? 'N/A';
            $card_type = $body['payment_detail']['card_type'] ?? 'N/A';
        
            // Log to make sure all required data is present
            if (!$order_id || !$external_order_id || !$status_key) {
                Log::channel('mylog')->error('Missing critical callback data', [
                    'order_id' => $order_id,
                    'external_order_id' => $external_order_id,
                    'status_key' => $status_key
                ]);
                return response(['message' => 'Missing required data'], 400);
            }
        
            // Check the status of the payment
            if ($status_key == 'completed') {
                // Fetch the record from the database using the external_order_id
                $charicxvaLogGet = CharicxvaLog::where('order_id', $order_id)
                                                ->where('status', 'CREATED')
                                                ->first();
        
                if (!$charicxvaLogGet) {
                    Log::channel('mylog')->error('No CharicxvaLog record found for order_id', ['order_id' => $order_id]);
                    return response(['message' => 'Order not found'], 404);
                }
        
                try {
                    \DB::transaction(function () use ($charicxvaLogGet, $transaction_id, $payer_identifier, $payment_method, $card_type) {
                        // Create a new CharicxvaLog record for successful payment
                        $newCharicxvaLog = new CharicxvaLog();
                        $newCharicxvaLog->generated_order_id = $charicxvaLogGet->generated_order_id;
                        $newCharicxvaLog->payment_hash = $charicxvaLogGet->payment_hash;
                        $newCharicxvaLog->order_id = $charicxvaLogGet->order_id;
                        $newCharicxvaLog->user_id = '111';
                        $newCharicxvaLog->amount = $charicxvaLogGet->amount;
                        $newCharicxvaLog->transaction_id = $transaction_id;
                        $newCharicxvaLog->pan = $payer_identifier;  // Masked card number
                        $newCharicxvaLog->payment_method = $payment_method;
                        $newCharicxvaLog->card_type = $card_type;
                        $newCharicxvaLog->chveni_statusi = "warmatebuli gadaxda";
                        $newCharicxvaLog->status = "PERFORMED";
                        $newCharicxvaLog->date = Carbon::now();
                        $newCharicxvaLog->date_dge = Carbon::now()->format('Y-m-d');
                        $newCharicxvaLog->date_tve = Carbon::now()->format('Y-m');
                        $newCharicxvaLog->date_weli = Carbon::now()->format('Y');
                        $newCharicxvaLog->save();
        
                        // Update the user's balance
            
                    });
        
                    Log::channel('mylog')->info('Payment performed successfully', ['order_id' => $order_id, 'user_id' => $charicxvaLogGet->user_id]);
        
                } catch (\Throwable $th) {
                    Log::channel('mylog')->error('Error during transaction', [
                        'order_id' => $order_id,
                        'error' => $th->getMessage()
                    ]);
                }
        
            } elseif ($status_key == 'rejected') {
                // Handle rejected payment
                Log::channel('mylog')->warning('Payment rejected', ['order_id' => $order_id]);
        
                // Fetch the record from the database using the external_order_id
                $charicxvaLogGet = CharicxvaLog::where('order_id', $order_id)
                                                ->where('status', 'CREATED')
                                                ->first();
        
                if ($charicxvaLogGet) {
                    try {
                        \DB::transaction(function () use ($charicxvaLogGet, $transaction_id, $payer_identifier, $payment_method, $card_type) {
                            // Create a new CharicxvaLog record for rejected payment
                            $newCharicxvaLog = new CharicxvaLog();
                            $newCharicxvaLog->generated_order_id = $charicxvaLogGet->generated_order_id;
                            $newCharicxvaLog->payment_hash = $charicxvaLogGet->payment_hash;
                            $newCharicxvaLog->order_id = $charicxvaLogGet->order_id;
                            $newCharicxvaLog->user_id = $charicxvaLogGet->user_id;
                            $newCharicxvaLog->amount = $charicxvaLogGet->amount;
                            $newCharicxvaLog->transaction_id = $transaction_id;
                            $newCharicxvaLog->pan = $payer_identifier;
                            $newCharicxvaLog->payment_method = $payment_method;
                            $newCharicxvaLog->card_type = $card_type;
                            $newCharicxvaLog->chveni_statusi = "gadaxda ver moxerxda"; // Rejected status message
                            $newCharicxvaLog->status = "REJECTED";
                            $newCharicxvaLog->date = Carbon::now();
                            $newCharicxvaLog->date_dge = Carbon::now()->format('Y-m-d');
                            $newCharicxvaLog->date_tve = Carbon::now()->format('Y-m');
                            $newCharicxvaLog->date_weli = Carbon::now()->format('Y');
                            $newCharicxvaLog->save();
                        });
        
                        Log::channel('mylog')->info('Payment rejected and logged', ['order_id' => $order_id, 'user_id' => $charicxvaLogGet->user_id]);
        
                    } catch (\Throwable $th) {
                        Log::channel('mylog')->error('Error during rejection process', [
                            'order_id' => $order_id,
                            'error' => $th->getMessage()
                        ]);
                    }
                } else {
                    Log::channel('mylog')->error('No CharicxvaLog record found for rejected payment', ['order_id' => $order_id]);
                }
            }
        
            // Respond to the callback
            return response(['message' => 'HTTP/1.1 200 Ok'], 200);
        

    }

    public function credoInstallmentRequest(Request $request)
    {
        // Pass validated data to the service
        return $this->credoInstallmentService->createOrder($request);
    }

    public function credoInstallmentStatus(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'orderCode' => 'required|string',
        ]);

        $merchantId = config('payment.client_id'); // Retrieve merchant ID from the config
        $orderCode = $request->input('orderCode'); // Get order code from the request

        // Fetch the order status using the service
        return $this->credoInstallmentService->getOrderStatus($merchantId, $orderCode);
    }

    public function priceFormat($price)
    {

        $price = str_replace(" ", "", $price);
        $price = str_replace(",", ".", $price);

        if (is_double($price) || is_numeric($price)) {
            return $price;
        }
        return 0;
    }
}
