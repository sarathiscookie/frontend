<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Booking;
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
        $cartCount     = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
            ->where('status', "8")
            ->where('is_delete', 0)
            ->take(5)
            ->count();
        if($cartCount) {
            return $cartCount;
        }
    }
}
