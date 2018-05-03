<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\PaymentRequest;
use App\Userlist;
use App\Booking;
use Auth;
use Validator;
use Payone;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        /*if (session()->has('availableStatus') && session()->get('availableStatus') === 'success') {*/
            $prepayment_amount           = [];
            $serviceTax                  = 0;
            $moneyBalance                = 0;
            $carts                       = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                ->where('status', "8")
                ->where('is_delete', 0)
                ->take(5)
                ->get();

            if(count($carts) > 0) {
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
                    $moneyBalance        = $user->money_balance;
                }

                return view('payment', ['moneyBalance' => $moneyBalance, 'sumPrepaymentAmount' => $sum_prepayment_amount, 'prepayServiceTotal' => $prepay_service_total, 'serviceTax' => $serviceTax]);
            }
            else {
                return redirect()->route('cart');
            }
        /*}
        else {
            return redirect()->route('cart');
        }*/

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
     * @param  \App\Http\Requests\PaymentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PaymentRequest $request)
    {
        $user                        = Userlist::where('is_delete', 0)->where('usrActive', '1')->find(Auth::user()->_id);

        $prepayment_amount           = [];
        $serviceTax                  = 0;

        $carts                       = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
            ->where('status', "8")
            ->where('is_delete', 0)
            ->take(5)
            ->get();

        if(count($carts) > 0) {
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

            $prepay_service_total    = round($sum_prepayment_amount + $percentage, 2);

            if(isset($request->moneyBalance) && $request->moneyBalance === '1') {
                if($user->money_balance >= $prepay_service_total) {
                    dd('money balance greater. not need payment choose validation');
                    // not need payment choose validation
                    // store order details
                    // update user->moneybalance
                    // redirect to success page
                }
                else {
                    if(isset($request->payment)) {
                        // Function call for payment gateway section
                        $paymentGateway = $this->paymentGateway($request->payment, $user, $request->ip(), str_replace(".", "", $prepay_service_total));
                        if ($paymentGateway["status"] == "REDIRECT") {
                            return redirect()->away($paymentGateway["redirecturl"]);
                        }
                        // store order details
                        // update user->moneybalance
                        // redirect to success page
                    }
                    else {
                        $validator = Validator::make($request->all(), [
                            'payment' => 'required'
                        ]);

                        if ($validator->fails()) {
                            return redirect()->back()->withErrors($validator)->withInput();
                        }
                    }
                }
            }
            else {
                if(isset($request->payment)) {
                    // Function call for payment gateway section
                    $paymentGateway = $this->paymentGateway($request->payment, $user, $request->ip(), str_replace(".", "", $prepay_service_total));
                    if ($paymentGateway["status"] == "REDIRECT") {
                        return redirect()->away($paymentGateway["redirecturl"]);
                    }
                    // store order details
                    // update user->moneybalance
                    // redirect to success page
                }
                else {
                    $validator = Validator::make($request->all(), [
                        'payment' => 'required'
                    ]);

                    if ($validator->fails()) {
                        return redirect()->back()->withErrors($validator)->withInput();
                    }
                }
            }
        }
        else {
            return redirect()->back()->with('choosePaymentNullData', 'Something went wrong! Please check your cart.');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $payment
     * @param  string  $user
     * @param  string  $ip
     * @param  string  $amount
     * @return array
     */
    public function paymentGateway($payment, $user, $ip, $amount)
    {
        $walletType   = "";

        $clearingType = "";

        $onlineBankTransferType = "";

        include(app_path() . '/function/Payone.php');

        /* Payment gateway details */
        $defaults     = array(
            "aid"         => env('AID'), // Account id

            "mid"         => env('MID'), // Merchant id

            "portalid"    => env('PORTAL_ID'),

            "key"         => hash("md5", env('KEY')), // The key has to be hashed as md5

            "mode"        => env('MODE'), // Can be "live" for actual transactions

            "api_version" => env('API_VERSION'),

            "encoding"    => env('ENCODING')
        );

        /* Personal details */
        $personalData = array(

            /*"salutation" => "Herr",

            "title" => "Dr.",*/

            "firstname" => $user->usrFirstname,

            "lastname" => $user->usrLastname,

            "street" => $user->usrAddress,

            "zip" => $user->usrZip,

            "city" => $user->usrCity,

            "country" => "DE",

            "email" => $user->usrEmail,

            "telephonenumber" => $user->usrTelephone,

            "language" => "de",

            /*"gender" => "m",*/

            "ip" => $ip
        );

        /* Condition for payment type */
        if($payment === 'payPal') {
            $clearingType = "wlt";
            $walletType   = "PPE";
        }
        elseif ($payment === 'payDirect') {
            $clearingType = "wlt";
            $walletType   = "PDT";
        }
        elseif ($payment === 'sofort') {
            $clearingType = "sb";
            $onlineBankTransferType = "PNT";
        }

        /* Parameters for payment gateway */
        $parameters = array(

            "request" => "authorization",

            "clearingtype" => $clearingType, // wallet clearing type

            "wallettype" => $walletType,

            "amount" => $amount,

            'currency' => 'EUR',

            "reference" => uniqid(),

            "onlinebanktransfertype" => $onlineBankTransferType,

            "bankcountry" => "DE",

            "narrative_text" => "Cabin room booked",

            "va[1]"  => "1900",   // Item description

            "vatid" => "DE310927476",

            "document_date" => date('Ymd'),

            "booking_date" => date('Ymd'),

            "invoice_deliverymode" => "P", //PDF

            "invoiceappendix" => "Cabin Name: SCW-18-100023", //Dynamic text on the invoice

            "shipping_firstname" => $user->usrFirstname,

            "shipping_lastname" => $user->usrLastname,

            "shipping_street" => $user->usrAddress,

            "shipping_zip" => $user->usrZip,

            "shipping_city" => $user->usrCity,

            "shipping_country" => "DE",

            "successurl" => "https://payone.test/success.php?reference=your_unique_reference",

            "errorurl" => "https://payone.test/cancelled.php?reference=your_unique_reference",

            "backurl" => "https://payone.test/back.php?reference=your_unique_reference",

            /*"successurl" => "https://yourshop.de/payment/success?reference=your_unique_reference",

            "errorurl" => "https://yourshop.de/payment/error?reference=your_unique_reference",

            "backurl" => "https://yourshop.de/payment/back?reference=your_unique_reference",*/

        );

        $request = array_merge($defaults, $parameters, $personalData);

        ksort($request);

        //print_r($request);

        $response = Payone::sendRequest($request);

        /**

         * This should return something like:

         * Array

         * (

         *  [status] => REDIRECT

         *  [redirecturl] => https://www.sandbox.paypal.com/webscr?useraction=commit&cmd=_express-checkout&token=EC-4XXX73XXXK03XXX1A

         *  [redirecturl] => https://www.sofort.com/payment/go/7904xxxxxxxxxxxxxxxxxxxxeeca29ec9d8c7912

         *  [redirecturl] => https://sandbox.paydirekt.de/checkout/#/checkout/fe012345-abcd-efef-1234-7d7d7d7d7d7d

         *  [txid] => 205387102

         *  [userid] => 90737467

         * )

         */

        return $response;
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
