<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Models\OrderInstallment;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\CredoInstallmentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class CheckInstallmentOrderStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $credoInstallmentService;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(CredoInstallmentService $credoInstallmentService)
    {
        $this->credoInstallmentService = $credoInstallmentService;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::channel('mylog')->info('CheckInstallmentOrderStatus job started.');

        $orders = OrderInstallment::where('status_id', 2)->get();

        foreach ($orders as $order) {
            $response = $this->credoInstallmentService->getOrderStatus($order->merchant_id, $order->order_code);
            
            // Assuming the response contains a status field
            if (isset($response['status'])) {
                $order->status_id = $response['data'];
                $order->save();

                // Optional: Handle additional logic based on status
                if ($response['status'] === 3) {
                    // send to courier or something 
                }
            } else {
                Log::error('Status not found in response for order code: ' . $order->order_code);
            }
        }
    }
}
