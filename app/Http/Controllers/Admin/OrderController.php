<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Http;
use App\Repositories\Interfaces\UserInterface;
use App\Repositories\Interfaces\Admin\OrderInterface;
use App\Repositories\Interfaces\Admin\SellerInterface;
use App\Repositories\Interfaces\Admin\LanguageInterface;
use App\Repositories\Interfaces\Admin\DeliveryHero\DeliveryHeroInterface;

class OrderController extends Controller
{
    protected $order;
    protected $lang;
    protected $user;
    protected $seller;

    public function __construct(OrderInterface $order, LanguageInterface $lang, UserInterface $user,SellerInterface $seller){
        $this->order    = $order;
        $this->lang     = $lang;
        $this->user     = $user;
        $this->seller   = $seller;
    }

  
  

    public function sendTrackingToGG(Request $request)
    {
        try {
            $tracking = $request->input('tracking');
            $productId = $request->input('productId');
            $orderId = $request->input('orderId');
            $order = Order::find($orderId);
            // return $order->user;
            if (!$order) {
                return response()->json(['error' => 'Order not found'], 404);
            }
            
            $orderDetail = OrderDetail::where('product_id', $productId)->first();
    
            if (!$orderDetail) {
                return response()->json(['error' => 'Order detail not found'], 404);
            }
    
            $orderDetail->tr_tracking = $tracking;
            $orderDetail->save();
    
            $ggOrderId = $order->gg_order_id;
            if ($request->hasFile('invoiceFile') && $request->file('invoiceFile')->isValid()) {
                $invoiceFile = $request->file('invoiceFile');
                $requestData = [
                    'tracking' => $tracking,
                    'address' => $request->input('order')
                ];
                if ($ggOrderId !== null) {
                    $requestData['gg_order_id'] = $ggOrderId;
                }
                
                $response =  Http::attach('invoiceFile', file_get_contents($invoiceFile), 'invoice.jpg')
                    ->post('https://giogroup.ge/serviceProj', $requestData);
            }
    
            if ($response->successful()) {
                $responseData = $response->json();
                if ($ggOrderId === null && isset($responseData['courier_address_id'])) {
                    $order->gg_order_id = $responseData['courier_address_id'];
                    $order->save();
                }
                return $responseData;
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'API request failed: ' . $e->getMessage()], 500);
        }
    }



    
    
        
    
    
    
    
    
    
    
    
    
    
    public function index(Request $request){
        try{
            $orders             = $this->order->paginate($request, get_pagination('pagination'));
            $selected_seller    = isset($request->sl) ? $this->seller->getSeller($request->sl) : null;
            return view('admin.orders.orders',compact('orders','selected_seller'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }
    public function view($id){
        try{
            $order = $this->order->get($id);
            /*if(settingHelper('seller_system') != 1 && $order->seller_id != 1):
                Toastr::error(__('Seller module is inactive.'));
                return back();
            endif;*/
            $delivery_heroes    = $this->user->allTypeUser()->whereHas('deliveryHero')->where('user_type','delivery_hero')->where('status',1)->where('is_user_banned',0)->get();
            return view('admin.orders.order-details', compact('order','delivery_heroes'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }
    public function sellerOrders(Request $request){
        try{
            if(settingHelper('seller_system') != 1):
                Toastr::error(__('Seller module is inactive.'));
                return back();
            endif;
            $orders             = $this->order->sellerOrder($request, get_pagination('pagination'));
            $selected_seller    = isset($request->sl) ? $this->seller->getSeller($request->sl) : null;
            return view('admin.orders.seller-order',compact('orders','selected_seller'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function adminOrder(Request $request){
        try{
            $orders             = $this->order->adminOrder($request, get_pagination('pagination'));
            return view('admin.orders.admin-orders',compact('orders'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function pickupHubOrder(Request $request){
        try{
            $orders             = $this->order->pickupHubOrder($request, get_pagination('pagination'));
            $selected_seller    = isset($request->sl) ? $this->seller->getSeller($request->sl) : null;
            return view('admin.orders.pickup-hub-orders',compact('orders','selected_seller'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

 

    public function invoiceDownload($id)
    {
        try {

            $this->order->invoiceDownload($id);

            return back();
        } catch (\Exception $e) {

            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function assignDeliveryHero(Request $request){

        DB::beginTransaction();
        try {
            $this->order->assignDeliveryHero($request);
            Toastr::success(__('Delivery Hero Assigned Successfully'));
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function deliveryStatusChange(Request $request)
    {
        $order = $this->order->get($request['id']);
        if ($order->delivery_status != 'delivered'):
            if ($order->delivery_status == $request['delivery_status']):
                Toastr::error(__('Delivery status has been already updated to :status', ['status' => $request['delivery_status']]));
                return back();
            else:
                if (($order->payment_status == 'unpaid' || $order->payment_status == 'refunded_to_wallet')  && $request['delivery_status'] == 'delivered'):
                    Toastr::error(__('Unpaid order can not get delivered'));
                    return back();
                else:
                    $status = $this->order->deliveryStatusChange($request);
                    if ($status === 'product_not_available'):
                        Toastr::error(__('Product stock not available'));
                        return redirect()->back();
                    elseif ($status == true):
                        Toastr::success(__('Updated Successfully'));
                        return redirect()->back();
                    else:
                        Toastr::error(__('Something went wrong, Please Check your Email Settings'));
                        return back();
                    endif;
                endif;
            endif;
        else:
            Toastr::error(__('Delivered order can not get updated'));
            return back();
        endif;
    }

    public function paymentStatusChange(Request $request){
        $order = $this->order->get($request['id']);
        if ($order->delivery_status != 'delivered'):
            if ($order->payment_status == 'refunded_to_wallet' && $request['payment_status'] == 'Unpaid'):
                Toastr::error(__('Refunded payment can not get unpaid'));
                return back();
            endif;
            if ($order->payment_status == $request['payment_status']):
                Toastr::error(__('Payment status already been :status', ['status' => $request['payment_status']]));
                return back()->withInput();
            else:
                if ($request['payment_type'] == 'wallet' && $order->user->balance < $order->total_payable):
                    Toastr::error(__('Customer does not have enough wallet balance'));
                    return back();
                endif;

                DB::beginTransaction();
                try {
                    $this->order->paymentStatusChange($request);
                    Toastr::success(__('Updated Successfully'));
                    DB::commit();
                    return back();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Toastr::error($e->getMessage());
                    return redirect()->back();
                }
            endif;
        else:
            Toastr::error(__('Delivered order can not get updated'));
            return back();
            endif;
    }

    public function approveOfflinePayment(Request $request){
        $order = $this->order->get($request['id']);

        $request['payment_status'] = 'offline_payment';

        if ($order->payment_status == $request['payment_status']):

            $response['message'] = __('Payment status already been :status', ['status' => $request['payment_status']]);
            $response['status']  = 'error';
            $response['title']   = __('Opps');
        else:
            DB::beginTransaction();
            try {
                $this->order->paymentStatusChange($request);
                $response['message'] = __('Updated Successfully');
                $response['status']  = 'success';
                $response['title']   = __('Approved');
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Toastr::error($e->getMessage());
                return redirect()->back();
            }
        endif;
        return $response;
    }
}
