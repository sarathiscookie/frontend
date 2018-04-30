<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\PaymentRequest;
use App\Userlist;
use App\Booking;
use Auth;
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
        include(app_path() . '/function/Payone.php');

        if(isset($request->payment)) {
            $defaults = array(

                "aid" => '34801',//"your_account_id",

                "mid" => '33914',//"your_merchant_id",

                "portalid" => '2024367',

                "key" => hash("md5", "j75iq35Jg1MVMFC5"), // the key has to be hashed as md5

                "mode" => "test", // can be "live" for actual transactions

                "api_version" => "3.10",

                "encoding" => "UTF-8"

            );

            if($request->payment === 'payPal') {
                $personalData = array(

                    "salutation" => "Herr",

                    "title" => "Dr.",

                    "firstname" => "Jane",

                    "lastname" => "Doe",

                    "street" => "Nebelhornstraße 3",

                    "addressaddition" => "Waltenhofen",

                    "zip" => "87448",

                    "city" => "Regensburg",

                    "country" => "DE",

                    "email" => "sarath@cabin-holiday.com",

                    "telephonenumber" => "9562903203",

                    "birthday" => "19860307",

                    "language" => "de",

                    "gender" => "m",

                    "ip" => "8.8.8.8"

                );

                $parameters = array(

                    "request" => "authorization",

                    "clearingtype" => "wlt", // wallet clearing type

                    "wallettype" => "PPE", // PPE for Paypal

                    "amount" => "100000",

                    'currency' => 'EUR',

                    "reference" => uniqid(),

                    "narrative_text" => "Just an order",

                    "de[1]"  => "Cabin Name: SCW-18-100023",   // Item description

                    "document_date" => date('Ymd'),

                    "booking_date" => date('Ymd'),

                    "invoiceid" => "SCW-18-100023",

                    "invoice_deliverymode" => "P", //PDF

                    "invoice_deliverydate" => date('Ymd'), //PDF

                    "invoiceappendix" => "Cabin Name: SCW-18-100023", //Dynamic text on the invoice

                    "shipping_firstname" => "Jane",

                    "shipping_lastname" => "Doe",

                    "shipping_company" => "Huetten Holiday",

                    "shipping_street" => "Nebelhornstraße 3",

                    "shipping_zip" => "87448",

                    "shipping_city" => "Regensburg",

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

                /**

                 * This should return something like:

                 * Array

                 * (

                 *  [status] => REDIRECT

                 *  [redirecturl] => https://www.sandbox.paypal.com/webscr?useraction=commit&cmd=_express-checkout&token=EC-4XXX73XXXK03XXX1A

                 *  [txid] => 205387102

                 *  [userid] => 90737467

                 * )

                 */

                $response = Payone::sendRequest($request);

                //dd($response);
                if ($response["status"] == "REDIRECT") {
                    return redirect()->away($response["redirecturl"]);
                }
                else {
                    dd("Something went wrong. :(");
                }
            }
            else {
                dd('others');
            }
        }

        return redirect()->back()->with('choosePaymentResponse', 'Select a payment method');
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
