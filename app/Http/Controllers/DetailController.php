<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Cart;
use Illuminate\Support\Facades\Auth;

class DetailController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request, $slug)
    {
        $product = Product::with(['galleries', 'user'])->where('slug', $slug)->firstOrFail();
        return view('pages.detail', [
            'product' => $product,
        ]);
    }

    public function add($id) {
        $data = [
            'products_id' => $id,
            'users_id' => Auth::user()->id,
        ];

        Cart::create($data);
        return redirect()->route('cart');
    }
} 
