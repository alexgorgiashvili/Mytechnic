<?php

namespace App\Http\Controllers\GG;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class ApiGgController extends Controller
{
    public function getGGstatus(Request $request){

        $order = Order::where('gg_order_id',$request->order_id)->first();
        if($order){
            $order->gg_status = $request->status;
            $order->save();
        }
        Log::info($request->all());

        return 'ok';
    }
}
