<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\TransactionDetail;
use App\User;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // $transactions = TransactionDetail::with(['transaction.user', 'product.galleries'])
        //                 ->whereHas('product', function($product) {
        //                     $product->where('users_id', Auth::user()->id);
        //                 });
        $transactions = TransactionDetail::with(['transaction.user', 'product.galleries'])
                        ->whereHas('product', function($product) {
                            $product->where('users_id', Auth::user()->id);
                        });
        // $revenue = $transactions->get()->reduce(function($carry, $item){
        //     $carry + $item->price;
        // });
        $revenue = $transactions->get()->sum('price');
        // dd($transactions->get());
        // dd($transactions->get()->sum('price'));
        // dd($transactions->get()->reduce(function($carry, $item){
        //     $carry + $item->price;
        // }));
        $customer = User::count();

        return view('pages.dashboard', [
            'transaction_count' => $transactions->count(),
            'transaction_data' => $transactions->get(),
            'revenue' => $revenue,
            'customer' => $customer,
        ]);
    }
}
