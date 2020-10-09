<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Cart;
use App\Transaction;
use App\TransactionDetail;

use Exception;

use Midtrans\Snap;
use Midtrans\Config;

class CheckoutController extends Controller
{
    public function process(Request $request)
    {
        // Save user data
        $user = Auth::user();
        $user->update($request->except('total_price'));

        //Process checkout
        $code = 'STORE-'. mt_rand(0000000, 9999999);
        $carts = Cart::with(['product', 'user'])
                        ->where('users_id', Auth::user()->id)
                        ->get();

        //Transaction Create
        $transaction = Transaction::create([
            'users_id' => Auth::user()->id,
            'inscurance_price' => 0,
            'shipping_price' => 0,
            'total_price' => $request->total_price,
            'transaction_status' => 'PENDING',
            'code' => $code,
        ]);

        foreach ($carts as $cart) {
            $trx = 'TRX-'. mt_rand(00000000, 99999999);

            TransactionDetail::create([
                'transactions_id' => $transaction->id,
                'products_id' => $cart->product->id,
                'price' => $cart->product->price,
                'resi' => '',
                'shipping_status' => 'PENDING',
                'code' => $trx,
            ]);
        };

        //Delete Cart
        Cart::where('users_id', Auth::user()->id)->delete();

        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('secvices.midtrans.isSanitized');
        Config::$is3ds = config('secvices.midtrans.is3ds');

        //Make Array for Midtrans
        $midtrans = [
            'transaction_details' => array(
                'order_id' => $code,
                'gross_amount' => (int) $request->total_price,
            ),
            'customer_details' => [
                'first_name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ],
            'enabled_payment' => [
                'gopay','bri_va','bank_transfer'
            ],
            'vt_web' => []
        ];
        // dd($midtrans);
        // dd($request->total_price);

        try {
            //Get Snap Payment Page Url
            $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;

            // Redirect To Payment Url
            return redirect($paymentUrl);
        }
        catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function callback(Request $request)
    {

    }
}
