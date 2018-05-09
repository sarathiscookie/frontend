<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\PaymentRequest;
use App\Userlist;
use App\Booking;
use App\Order;
use Auth;
use Validator;
use Payone;
use DateTime;

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
            $payByBillPossible           = [];
            $carts                       = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                ->where('status', "8")
                ->where('is_delete', 0)
                ->take(5)
                ->get();

            if(count($carts) > 0) {

                foreach ($carts as $key => $cart) {
                    $prepayment_amount[] = $cart->prepayment_amount;

                    /* Condition to check pay by bill possible begin */
                    // Pay by bill radio button in payment page will show when there is three weeks diff b/w current date and checking from date.
                    $checkingFrom        = $cart->checkin_from->format('Y-m-d');
                    $currentDate         = date('Y-m-d');
                    $d1                  = new DateTime($currentDate);
                    $d2                  = new DateTime($checkingFrom);
                    $dateDifference      = $d2->diff($d1);
                    if($dateDifference->days > 20) {
                        $payByBillPossible[] = 'yes';
                    }
                    else {
                        $payByBillPossible[] = 'no';
                    }
                    /* Condition to check pay by bill possible end */
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

                return view('payment', ['moneyBalance' => $moneyBalance, 'sumPrepaymentAmount' => $sum_prepayment_amount, 'prepayServiceTotal' => $prepay_service_total, 'serviceTax' => $serviceTax, 'payByBillPossible' => $payByBillPossible]);
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

            $total_prepayment_amount = round($sum_prepayment_amount, 2);

            if(isset($request->moneyBalance) && $request->moneyBalance === '1') {
                if($user->money_balance >= $total_prepayment_amount) {
                    dd('money balance is greater');
                    /*$order                           = Order::new();
                    $order->order_number             = 'order_number';
                    $order->order_amount             = 'order_number';
                    $order->order_total_amount       = 'order_number';
                    $order->order_money_balance_used = 'order_number';
                    $order->created_at               = 'order_number';
                    $order->updated_at               = 'order_number';
                    $order->order_delete             = 'order_number';
                    $order->save();*/
                    // not need payment choose validation
                    // store order details -> order_id, order_number, order_date, order_amount, order_total_amount, order_money_balance_used, created_at, updated_at, order_delete
                    // update booking details -> order_id, status, payment_status, prepayment amount, total prepayment amount
                    // update user->moneybalance
                    // redirect to success page
                }
                else {
                    if(isset($request->payment)) {
                        // Function call for payment gateway section
                        $paymentGateway = $this->paymentGateway($request->payment, $user, $request->ip(), str_replace(".", "", $total_prepayment_amount), $request->pseudocardpan);
                        if ($paymentGateway["status"] == "REDIRECT") {
                            return redirect()->away($paymentGateway["redirecturl"]);
                        }
                        elseif ($paymentGateway["status"] == "APPROVED") { // no 3d secure verification required, transaction went through
                            echo "Thank you for your purchase."; // redirect to success url
                            //return redirect()->route('payment.success');
                        }
                        else {
                            echo "There has been an error processing your request.";
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
                    $paymentGateway = $this->paymentGateway($request->payment, $user, $request->ip(), str_replace(".", "", $total_prepayment_amount), $request->pseudocardpan);
                    if ($paymentGateway["status"] == "REDIRECT") { // If card is 3d secure return status is REDIRECT and "redirect url" will return.
                        return redirect()->away($paymentGateway["redirecturl"]);
                    }
                    elseif ($paymentGateway["status"] == "APPROVED") { // If card is not 3d secure return status is APPROVED and "redirect url" will not return. We manually redirect to success page.
                        echo "Thank you for your purchase."; // redirect to success page
                        //return redirect()->route('payment.success');
                    }
                    else {
                        echo "There has been an error processing your request."; // redirect to error page
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
    public function paymentGateway($payment, $user, $ip, $amount, $pseudocardpan)
    {
        $walletType             = "";

        $clearingType           = "";

        $onlineBankTransferType = "";

        $requestType            = "";

        $pseudoCardPan          = "";

        $countryName            = "";

        include(app_path() . '/Function/Payone.php');

        /* Condition for country*/
        if(Auth::user()->usrCountry === 'Deutschland') {
            $countryName       = "DE";
        }
        elseif(Auth::user()->usrCountry === 'Österreich') {
            $countryName       = "AT";
        }
        else {
            $countryName       = "IT";
        }

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

            "salutation" => $user->salutation,

            "title" => $user->title,

            "firstname" => $user->usrFirstname,

            "lastname" => $user->usrLastname,

            "street" => $user->usrAddress,

            "zip" => $user->usrZip,

            "city" => $user->usrCity,

            "country" => $countryName,

            "email" => $user->usrEmail,

            "telephonenumber" => $user->usrTelephone,

            "language" => "de",

            "gender" => $user->gender,

            "ip" => $ip
        );

        /* Condition for payment type */
        if($payment === 'payPal') {
            $clearingType = "wlt";
            $walletType   = "PPE";
            $requestType = "authorization";
        }
        elseif ($payment === 'payDirect') {
            $clearingType = "wlt";
            $walletType   = "PDT";
            $requestType = "authorization";
        }
        elseif ($payment === 'sofort') {
            $clearingType = "sb";
            $onlineBankTransferType = "PNT";
            $requestType = "authorization";
        }
        elseif ($payment === 'creditCard') {
            $clearingType = "cc";
            $requestType = "capture";
            $pseudoCardPan = $pseudocardpan;
        }
        elseif ($payment === 'payByBill') {
            $clearingType = "vor";
            $requestType = "preauthorization";
            $pseudoCardPan = $pseudocardpan;
        }
        else{
            abort(404);
        }

        /* Parameters for payment gateway */
        $parameters = array(

            "request" => $requestType,

            "clearingtype" => $clearingType, // wallet clearing type

            "wallettype" => $walletType,

            "amount" => $amount,

            'currency' => 'EUR',

            "reference" => uniqid(),

            "onlinebanktransfertype" => $onlineBankTransferType,

            "bankcountry" => "DE",

            "pseudocardpan" => $pseudoCardPan,

            "narrative_text" => "Cabin room booked",

            "va[1]"  => "1900",   // Item description

            "vatid" => "DE310927476",

            "document_date" => date('Ymd'),

            "booking_date" => date('Ymd'),

            "invoice_deliverymode" => "P", //PDF

            "invoiceappendix" => "Dynamic text on the invoice", //Dynamic text on the invoice

            "shipping_firstname" => $user->usrFirstname,

            "shipping_lastname" => $user->usrLastname,

            "shipping_street" => $user->usrAddress,

            "shipping_zip" => $user->usrZip,

            "shipping_city" => $user->usrCity,

            "shipping_country" => "DE",

            "successurl" => env('SUCCESSURL'),

            "errorurl" => env('ERRORURL')
        );

        $request = array_merge($defaults, $parameters, $personalData);

        ksort($request);

        $response = Payone::sendRequest($request);

        //dd($response);
        /**

         * This should return something like:

         * Array

         * (

         *  [status] => REDIRECT OR APPROVED

         *  [redirecturl] => https://www.sandbox.paypal.com/webscr?useraction=commit&cmd=_express-checkout&token=EC-4XXX73XXXK03XXX1A

         *  [redirecturl] => https://www.sofort.com/payment/go/7904xxxxxxxxxxxxxxxxxxxxeeca29ec9d8c7912

         *  [redirecturl] => https://sandbox.paydirekt.de/checkout/#/checkout/fe012345-abcd-efef-1234-7d7d7d7d7d7d
         *
         *  [redirecturl] => https://secure.pay1.de/3ds/redirect.php?md=21775143&txid=271387899

         *  [txid] => 271612813

         *  [userid] => 128309888

         * )

         */

        return $response;
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function response(Request $request)
    {
        dd($_POST);
        // you'll need to include the $defaults array somehow, or at least get the key from a secret configuration file
        if ($_POST["key"] == hash("md5", env('KEY'))) {
            // key is valid, this notification is for us
            echo "TSOK";
            if ($_POST["txaction"] == "appointed") {
                dd($_POST);
                // a freshly created transaction has been marked successfully initiated
                // update that transaction accordingly, e.g. by $_POST["reference"]
            }
            if ($_POST["txaction"] == "paid") {
                dd($_POST);
                // update your transaction accordingly, e.g. by $_POST["reference"]
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function success()
    {
        //dd($_REQUEST);
        dd('success');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function failure()
    {
        dd('failure');
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
