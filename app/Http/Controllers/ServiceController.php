<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Cart;
use Auth;

class ServiceController extends Controller
{
    /**
     * Count the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function cart()
    {
        $cartCount     = Cart::where('user_id', new \MongoDB\BSON\ObjectID(Auth::user()->_id))->count();
        if($cartCount) {
            return $cartCount;
        }
    }
}
