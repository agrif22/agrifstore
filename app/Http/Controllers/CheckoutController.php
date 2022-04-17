<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Cart;
use App\Models\Transaction;
use App\Models\TransactionDetail;

use Exception;

use Midtrans\Snap;
use Midtrans\Config;
use Midtrans\Notification;

class CheckoutController extends Controller
{
    public function process(Request $request){
        // save users data
        $user = Auth::user();
        $user->update($request->except('total_price'));

         // proses chackout
        $code = 'STORE-'. mt_rand(0000,9999);
        $carts = Cart::with(['product','user'])
                ->where('users_id', Auth::user()->id)
                ->get();

        // Membuat transaksi
        $transaction = Transaction::create([
            'users_id' => Auth::user()->id ,
            'insurance_price' => 0 ,
            'shipping_price' => 0 ,
            'total_price' => $request->total_price ,
            'transaction_status' => 'PENDING' ,
            'code' => $code
        ]);

        //membuat transaksi detail
        foreach ($carts as $cart ) {
            $trx = 'TRX-'. mt_rand(0000,9999);

            TransactionDetail::create([
            'transactions_id' => $transaction->id ,
            'products_id' => $cart->product->id ,
            'price' => $cart->product->price ,
            'shipping_status' => 'PENDING' ,
            'resi' => '',
            'code' => $trx
        ]);
        }

        //hapus cart data
        Cart::with(['product','user'])
                ->where('users_id', Auth::user()->id)->delete();


         //Konfigurasi Midtrans
         Config::$serverKey = config('services.midtrans.serverKey');
         Config::$isProduction = config('services.midtrans.isProduction');
         Config::$isSanitized = config('services.midtrans.isSanitized');
         Config::$is3ds = config('services.midtrans.is3ds');


         //Buat array dikirim ke Midtrans 
         $midtrans = [
             'transaction_details' => [
                 'order_id'=> $code,
                 'gross_amount' => (int) $request->total_price,
             ],
             'customers_details' => [
                 'first_name' => Auth::user()->name,
                 'email' => Auth::user()->email,
             ],
             'enabled_payments'=> [
                 'gopay',
                 'permata_va',
                 'bank_transfer'
             ],
             'vtweb' => []
        ];
        try {
                // Get Snap Payment Page URL
                $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;
                
                // Redirect to Snap Payment Page
                return redirect($paymentUrl);
                }
        catch (Exception $e) {
                echo $e->getMessage();
            }

    }

    public function callback(Request $request){

        // Set konfiguraasi midtrans
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        // Instance midtrans notifiction
        $notification = new Notification();

        // Assign ke variable untuk memudahkan coding

        $status = $notification->transaction_status;
        $type = $notification->payment_type;
        $fraud = $notification->fraud_status;
        $order_id = $notification->order_id;

        // Cari transaction by ID
        $transaction = Transaction::findOrFail($order_id);


        // Handle notification status
        if($status == 'capture'){
            if($type == 'credit_card'){
                if($fraud == 'challenge'){
                    $transaction->status = 'PENDING';
                }
                else {
                    $transaction->status = 'SUCCESS';
                }
            }
        }
        else if($status == 'settlement'){
            $transaction->status = 'SUCCESS';
        }

        else if($status == 'pending'){
            $transaction->status = 'PENDING';
        }

        else if($status == 'deny'){
            $transaction->status = 'CANCELLED';
        }
        else if($status == 'expire'){
            $transaction->status = 'CANCELLED';
        }
        else if($status == 'cancel'){
            $transaction->status = 'CANCELLED';
        }
        // Simpan transaction

        $transaction->save();
    }
}
