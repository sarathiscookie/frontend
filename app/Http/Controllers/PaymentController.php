<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Userlist;
use App\Booking;
use Auth;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $prepayment_amount           = [];
        $sum_prepayment_amount       = 0;
        $prepay_service_total        = 0;
        $serviceTax                  = 0;
        $moneyBalance                = 0;
        $carts                       = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
            ->where('status', "8")
            ->where('is_delete', 0)
            ->take(5)
            ->get();

        if($carts) {
            /* Amount calculation */
            foreach ($carts as $key => $cart) {
                $prepayment_amount[] = $cart->prepayment_amount;
            }

            $sum_prepayment_amount   = array_sum($prepayment_amount);

            if($sum_prepayment_amount <= 30) {
                $serviceTax          = env('SERVICE_TAX_ONE');
            }

            if($sum_prepayment_amount > 30 && $sum_prepayment_amount <= 100) {
                $serviceTax          = env('SERVICE_TAX_TWO');
            }

            if($sum_prepayment_amount > 100) {
                $serviceTax          = env('SERVICE_TAX_THREE');
            }

            $percentage              = ($serviceTax / 100) * $sum_prepayment_amount;
            $prepay_service_total    = $sum_prepayment_amount + $percentage;

            /* Getting money balance */
            $user                    = Userlist::select('money_balance')
                ->where('is_delete', 0)
                ->findOrFail(Auth::user()->_id);

            if($user) {
                $moneyBalance = $user->money_balance;
            }
        }

        return view('payment', ['moneyBalance' => $moneyBalance, 'sumPrepaymentAmount' => $sum_prepayment_amount, 'prepayServiceTotal' => $prepay_service_total, 'serviceTax' => $serviceTax]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
