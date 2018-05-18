<?php

namespace App\Http\Controllers;

use App\Mail\BookingSuccess;
use Illuminate\Http\Request;

use App\Http\Requests\PaymentRequest;
use App\Userlist;
use App\Booking;
use App\Order;
use App\Ordernumber;
use App\Payment;
use Auth;
use Validator;
use Payone;
use DateTime;
use Carbon\Carbon;
use Mail;
use Illuminate\Support\Facades\Schema;

class PaymentController extends Controller
{
    /**
     * Function for service fee
     *
     * @param  string  $sumPrepayAmount
     * @return \Illuminate\Http\Response
     */
    public function serviceFees($sumPrepayAmount)
    {
        $serviceTaxBook = 0;

        if($sumPrepayAmount <= 30) {
            $serviceTaxBook = env('SERVICE_TAX_ONE');
        }

        if($sumPrepayAmount > 30 && $sumPrepayAmount <= 100) {
            $serviceTaxBook = env('SERVICE_TAX_TWO');
        }

        if($sumPrepayAmount > 100) {
            $serviceTaxBook = env('SERVICE_TAX_THREE');
        }

        return $serviceTaxBook;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        /*if (session()->has('availableStatus') && session()->get('availableStatus') === 'success') {*/
            $prepayment_amount           = [];
            $moneyBalance                = 0;
            $payByBillPossible           = [];
            $cabin_code                  = '';
            $carts                       = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                ->where('status', "8")
                ->where('is_delete', 0)
                ->take(5)
                ->get();

            if(count($carts) > 0) {

                foreach ($carts as $key => $cart) {
                    $prepayment_amount[] = $cart->prepayment_amount;

                    /* Condition to check pay by bill possible begin */
                    // Pay by bill radio button in payment page will show when there is two weeks diff b/w current date and checking from date.
                    $checkingFrom        = $cart->checkin_from->format('Y-m-d');
                    $currentDate         = date('Y-m-d');
                    $d1                  = new DateTime($currentDate);
                    $d2                  = new DateTime($checkingFrom);
                    $dateDifference      = $d2->diff($d1);
                    if($dateDifference->days > 14) {
                        $payByBillPossible[] = 'yes';
                    }
                    else {
                        $payByBillPossible[] = 'no';
                    }
                    /* Condition to check pay by bill possible end */

                    $invoice_number      = explode('-', $cart->invoice_number, 2);
                    $cabin_code         .= $invoice_number[0].'-';
                }

                /* Get order number */
                $orderNumber             = Ordernumber::first();
                if( !empty ($orderNumber->number) ) {
                    $order_num           = (int)$orderNumber->number + 1;
                }
                else {
                    $order_num           = 100000;
                }

                $order_number            = 'ORDER'.'-'.date('y').'-'.$cabin_code.$order_num;
                $sum_prepayment_amount   = array_sum($prepayment_amount);
                $serviceTax              = $this->serviceFees($sum_prepayment_amount);
                $percentage              = ($serviceTax / 100) * $sum_prepayment_amount;
                $prepay_service_total    = $sum_prepayment_amount + $percentage;

                /* Getting money balance */
                $user                    = Userlist::select('money_balance')
                    ->where('is_delete', 0)
                    ->findOrFail(Auth::user()->_id);

                if($user) {
                    $moneyBalance        = $user->money_balance;
                }

                return view('payment', ['moneyBalance' => $moneyBalance, 'sumPrepaymentAmount' => $sum_prepayment_amount, 'prepayServiceTotal' => $prepay_service_total, 'serviceTax' => $serviceTax, 'payByBillPossible' => $payByBillPossible, 'order_number' => $order_number]);
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
        $prepayment_amount           = [];
        $cart_ids                    = [];
        $cabin_code                  = '';
        $payByBillPossible           = [];
        $user                        = Userlist::where('is_delete', 0)->where('usrActive', '1')->find(Auth::user()->_id);
        $carts                       = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
            ->where('status', "8")
            ->where('is_delete', 0)
            ->take(5)
            ->get();

        if($carts) {
            /* Loop for amount calculation,  */
            foreach ($carts as $key => $cart) {
                $prepayment_amount[] = $cart->prepayment_amount;
                $cart_ids[]          = $cart->_id;
                $invoice_number      = explode('-', $cart->invoice_number, 2);
                $cabin_code         .= $invoice_number[0].'-';

                /* Condition to check pay by bill possible begin */
                // Pay by bill condition works if there is two weeks diff b/w current date and checking from date.
                $checkingFrom        = $cart->checkin_from->format('Y-m-d');
                $currentDate         = date('Y-m-d');
                $d1                  = new DateTime($currentDate);
                $d2                  = new DateTime($checkingFrom);
                $dateDifference      = $d2->diff($d1);
                if($dateDifference->days > 14) {
                    $payByBillPossible[] = 'yes';
                }
                else {
                    $payByBillPossible[] = 'no';
                }
                /* Condition to check pay by bill possible end */
            }

            /* Create order number begin */
            $orderNumber = Ordernumber::first();
            if( !empty ($orderNumber->number) ) {
                $order_num = (int)$orderNumber->number + 1;
            }
            else {
                $order_num = 100000;
            }
            /* Create order number end */

            $order_number            = 'ORDER'.'-'.date('y').'-'.$cabin_code.$order_num;
            $sum_prepayment_amount   = array_sum($prepayment_amount);
            $total_prepayment_amount = round($sum_prepayment_amount, 2);

            if($request->has('moneyBalance') && $request->moneyBalance === '1') {
                if($user->money_balance >= $total_prepayment_amount) {

                    /* How much money user have in their account after used money balance */
                    $afterRedeemAmount = $user->money_balance - $total_prepayment_amount;

                    /* Storing order details */
                    $order                                = new Order;
                    $order->order_id                      = $order_number;
                    $order->auth_user                     = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                    $order->order_amount                  = $total_prepayment_amount;
                    $order->order_total_amount            = $total_prepayment_amount;
                    $order->order_money_balance_used      = round($total_prepayment_amount, 2);
                    $order->order_money_balance_used_date = Carbon::now();
                    $order->order_payment_method          = 1; // 1 => fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                    $order->order_delete                  = 0;
                    $order->save();

                    if($order) {
                        /* Updating user money balance */
                        $user->money_balance = round($afterRedeemAmount, 2);
                        $user->save();

                        /* Updating order number */
                        $orderNumber->number = $order_num;
                        $orderNumber->save();

                        /* Updating booking details */
                        foreach ($cart_ids as $cart_id) {
                            $cartUpdate                 = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                                ->where('status', "8")
                                ->where('is_delete', 0)
                                ->find($cart_id);
                            $cartUpdate->order_id       = new \MongoDB\BSON\ObjectID($order->_id);
                            $cartUpdate->status         = '1';
                            $cartUpdate->payment_status = '1';
                            $cartUpdate->save();
                        }

                        /* Send email to guest after successful booking */
                        Mail::to($user->usrEmail)->send(new BookingSuccess());

                        return redirect()->route('payment.success')->with('bookingSuccessStatus', 'Thank you very much for booking with Huetten-Holiday.de.');
                    }
                    else {
                        return redirect()->back()->with('bookingFailureStatus', 'There has been an error processing your request.');
                    }
                }
                else {
                    if(isset($request->payment)) {
                        // Function call for payment gateway section
                        $paymentGateway    = $this->paymentGateway($request->all(), $request->ip(), $total_prepayment_amount, $order_number);

                        /* How much money user have in their account after used money balance */
                        $afterRedeemAmount = $total_prepayment_amount - $user->money_balance;
                        $percentage        = ($this->serviceFees($afterRedeemAmount) / 100) * $afterRedeemAmount;
                        $total             = round($afterRedeemAmount + $percentage, 2);

                        if ($paymentGateway["status"] == "REDIRECT") {

                            /* Storing order details */
                            $order                        = new Order;
                            $order->order_id              = $order_number;
                            $order->auth_user             = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                            $order->order_status          = "REDIRECT";
                            $order->txid                  = $paymentGateway["txid"];
                            $order->userid                = $paymentGateway["userid"];
                            $order->order_payment_type    = $request->payment;
                            $order->order_payment_method  = 2; // 1 => fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                            $order->order_amount          = round($afterRedeemAmount, 2);
                            $order->order_total_amount    = $total;
                            $order->order_delete          = 0;
                            $order->save();
                            /* Storing order details end */

                            if($order) {
                                /* Updating user money balance */
                                $user->userid        = $paymentGateway["userid"];
                                $user->save();

                                /* Updating order number */
                                $orderNumber->number = $order_num;
                                $orderNumber->save();

                                /* Updating booking details */
                                foreach ($cart_ids as $cart_id) {
                                    $cartUpdate               = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                                        ->where('status', "8")
                                        ->where('is_delete', 0)
                                        ->find($cart_id);
                                    $cartUpdate->order_id     = new \MongoDB\BSON\ObjectID($order->_id);
                                    $cartUpdate->payment_type = $request->payment;
                                    $cartUpdate->txid         = $paymentGateway["txid"];
                                    $cartUpdate->userid       = $paymentGateway["userid"];
                                    $cartUpdate->save();
                                }

                                $request->session()->flash('bookingSuccessStatus', 'Thank you very much for booking with Huetten-Holiday.de.');
                                return redirect()->away($paymentGateway["redirecturl"]);
                            }
                            else {
                                return redirect()->back()->with('bookingFailureStatus', 'There has been an error processing your request.');
                            }
                        }
                        elseif ($paymentGateway["status"] == "APPROVED") { // If card is not 3d secure and prepayment(PayByBill) return status is APPROVED and "redirect url" will not return. We manually redirect to success page.
                            /* Storing order details */
                            $order                        = new Order;
                            $order->order_id              = $order_number;
                            $order->auth_user             = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                            $order->order_status          = "APPROVED";
                            $order->txid                  = $paymentGateway["txid"];
                            $order->userid                = $paymentGateway["userid"];
                            $order->order_payment_type    = $request->payment;
                            $order->order_payment_method  = 2; // 1 => fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                            $order->order_amount          = round($afterRedeemAmount, 2);
                            $order->order_total_amount    = $total;
                            $order->order_delete          = 0;

                            if($request->payment === 'payByBill') {
                                if(in_array('yes', $payByBillPossible)) {
                                    $order->clearing_bankaccount       = $paymentGateway["clearing_bankaccount"];
                                    $order->clearing_bankcode          = $paymentGateway["clearing_bankcode"];
                                    $order->clearing_bankcountry       = $paymentGateway["clearing_bankcountry"];
                                    $order->clearing_bankname          = $paymentGateway["clearing_bankname"];
                                    $order->clearing_bankaccountholder = $paymentGateway["clearing_bankaccountholder"];
                                    $order->clearing_bankiban          = $paymentGateway["clearing_bankiban"];
                                    $order->clearing_bankbic           = $paymentGateway["clearing_bankbic"];
                                    $order->save();
                                    $request->session()->flash('bookingSuccessStatusPrepayment', 'Thank you very much for booking with Huetten-Holiday.de.');
                                    return redirect()->route('payment.prepayment')->with('order', $order);
                                }
                                else {
                                    return redirect()->back()->with('bookingFailureStatus', 'There has been an error processing your request.');
                                }
                            }

                            $order->save();
                            /* Storing order details end */

                            if($order) {
                                /* Updating user money balance */
                                $user->userid        = $paymentGateway["userid"];
                                $user->save();

                                /* Updating order number */
                                $orderNumber->number = $order_num;
                                $orderNumber->save();

                                /* Updating booking details */
                                foreach ($cart_ids as $cart_id) {
                                    $cartUpdate               = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                                        ->where('status', "8")
                                        ->where('is_delete', 0)
                                        ->find($cart_id);
                                    $cartUpdate->order_id     = new \MongoDB\BSON\ObjectID($order->_id);
                                    $cartUpdate->payment_type = $request->payment;
                                    $cartUpdate->txid         = $paymentGateway["txid"];
                                    $cartUpdate->userid       = $paymentGateway["userid"];
                                    $cartUpdate->save();
                                }

                                return redirect()->route('payment.success')->with('bookingSuccessStatus', 'Thank you very much for booking with Huetten-Holiday.de.');
                            }
                            else {
                                return redirect()->back()->with('bookingFailureStatus', 'There has been an error processing your request.');
                            }
                        }
                        else {
                            /* Storing order details begin */
                            $order                       = new Order;
                            $order->order_id             = $order_number;
                            $order->auth_user            = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                            $order->order_status         = "ERROR / PENDING";
                            $order->txid                 = $paymentGateway["txid"];
                            $order->userid               = $paymentGateway["userid"];
                            $order->order_payment_type   = $request->payment;
                            $order->order_payment_method = 2; // 1 => fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                            $order->order_amount         = round($afterRedeemAmount, 2);
                            $order->order_total_amount   = $total;
                            $order->order_delete         = 0;
                            $order->save();
                            /* Storing order details end */

                            if($order) {
                                /* Updating user money balance */
                                $user->userid        = $paymentGateway["userid"];
                                $user->save();

                                /* Updating order number */
                                $orderNumber->number = $order_num;
                                $orderNumber->save();

                                /* Updating booking details */
                                foreach ($cart_ids as $cart_id) {
                                    $cartUpdate               = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                                        ->where('status', "8")
                                        ->where('is_delete', 0)
                                        ->find($cart_id);
                                    $cartUpdate->order_id     = new \MongoDB\BSON\ObjectID($order->_id);
                                    $cartUpdate->payment_type = $request->payment;
                                    $cartUpdate->txid         = $paymentGateway["txid"];
                                    $cartUpdate->userid       = $paymentGateway["userid"];
                                    $cartUpdate->save();
                                }

                                return redirect()->route('payment.error')->with('bookingErrorStatus', 'There has been an error or pending processing your request.');
                            }
                        }
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
                    $paymentGateway = $this->paymentGateway($request->all(), $request->ip(), $total_prepayment_amount, $order_number);
                    $percentage     = ($this->serviceFees($total_prepayment_amount) / 100) * $total_prepayment_amount;
                    $total          = round($total_prepayment_amount + $percentage, 2);

                    if ($paymentGateway["status"] == "REDIRECT") { // If card is 3d secure return status is REDIRECT and "redirect url" will return.

                        /* Storing order details */
                        $order                       = new Order;
                        $order->order_id             = $order_number;
                        $order->auth_user            = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                        $order->order_status         = "REDIRECT";
                        $order->txid                 = $paymentGateway["txid"];
                        $order->userid               = $paymentGateway["userid"];
                        $order->order_payment_type   = $request->payment;
                        $order->order_payment_method = 3; // 1 => fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                        $order->order_amount         = $total_prepayment_amount;
                        $order->order_total_amount   = $total;
                        $order->order_delete         = 0;
                        $order->save();
                        /* Storing order details end */

                        if($order) {
                            /* Updating user money balance */
                            $user->userid        = $paymentGateway["userid"];
                            $user->save();

                            /* Updating order number */
                            $orderNumber->number = $order_num;
                            $orderNumber->save();

                            /* Updating booking details */
                            foreach ($cart_ids as $cart_id) {
                                $cartUpdate               = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                                    ->where('status', "8")
                                    ->where('is_delete', 0)
                                    ->find($cart_id);
                                $cartUpdate->order_id     = new \MongoDB\BSON\ObjectID($order->_id);
                                $cartUpdate->payment_type = $request->payment;
                                $cartUpdate->txid         = $paymentGateway["txid"];
                                $cartUpdate->userid       = $paymentGateway["userid"];
                                $cartUpdate->save();
                            }

                            $request->session()->flash('bookingSuccessStatus', 'Thank you very much for booking with Huetten-Holiday.de.');
                            return redirect()->away($paymentGateway["redirecturl"]);
                        }
                        else {
                            return redirect()->back()->with('bookingFailureStatus', 'There has been an error processing your request.');
                        }
                    }
                    elseif ($paymentGateway["status"] == "APPROVED") { // If card is not 3d secure and prepayment(PayByBill) return status is APPROVED and "redirect url" will not return. We manually redirect to success page.
                        /* Storing order details begin */
                        $order                        = new Order;
                        $order->order_id              = $order_number;
                        $order->auth_user             = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                        $order->order_status          = "APPROVED";
                        $order->txid                  = $paymentGateway["txid"];
                        $order->userid                = $paymentGateway["userid"];
                        $order->order_payment_type    = $request->payment;
                        $order->order_payment_method  = 3; // 1 => fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                        $order->order_amount          = $total_prepayment_amount;
                        $order->order_total_amount    = $total;
                        $order->order_delete          = 0;

                        if($request->payment === 'payByBill') {
                            if(in_array('yes', $payByBillPossible)) {
                                $order->clearing_bankaccount       = $paymentGateway["clearing_bankaccount"];
                                $order->clearing_bankcode          = $paymentGateway["clearing_bankcode"];
                                $order->clearing_bankcountry       = $paymentGateway["clearing_bankcountry"];
                                $order->clearing_bankname          = $paymentGateway["clearing_bankname"];
                                $order->clearing_bankaccountholder = $paymentGateway["clearing_bankaccountholder"];
                                $order->clearing_bankiban          = $paymentGateway["clearing_bankiban"];
                                $order->clearing_bankbic           = $paymentGateway["clearing_bankbic"];
                                $order->save();
                                $request->session()->flash('bookingSuccessStatusPrepayment', 'Thank you very much for booking with Huetten-Holiday.de.');
                                return redirect()->route('payment.prepayment')->with('order', $order);
                            }
                            else {
                                return redirect()->back()->with('bookingFailureStatus', 'There has been an error processing your request.');
                            }
                        }

                        $order->save();
                        /* Storing order details end */

                        if($order) {
                            /* Updating user money balance */
                            $user->userid        = $paymentGateway["userid"];
                            $user->save();

                            /* Updating order number */
                            $orderNumber->number = $order_num;
                            $orderNumber->save();

                            /* Updating booking details */
                            foreach ($cart_ids as $cart_id) {
                                $cartUpdate               = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                                    ->where('status', "8")
                                    ->where('is_delete', 0)
                                    ->find($cart_id);
                                $cartUpdate->order_id     = new \MongoDB\BSON\ObjectID($order->_id);
                                $cartUpdate->payment_type = $request->payment;
                                $cartUpdate->txid         = $paymentGateway["txid"];
                                $cartUpdate->userid       = $paymentGateway["userid"];
                                $cartUpdate->save();
                            }

                            return redirect()->route('payment.success')->with('bookingSuccessStatus', 'Thank you very much for booking with Huetten-Holiday.de.');
                        }
                        else {
                            return redirect()->back()->with('bookingFailureStatus', 'There has been an error processing your request.');
                        }
                    }
                    else {
                        /* Storing order details begin */
                        $order                       = new Order;
                        $order->order_id             = $order_number;
                        $order->auth_user            = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                        $order->order_status         = "ERROR / PENDING";
                        $order->txid                 = $paymentGateway["txid"];
                        $order->userid               = $paymentGateway["userid"];
                        $order->order_payment_type   = $request->payment;
                        $order->order_payment_method = 3; // 1 => fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                        $order->order_amount         = $total_prepayment_amount;
                        $order->order_total_amount   = $total;
                        $order->order_delete         = 0;
                        $order->save();
                        /* Storing order details end */

                        if($order) {
                            /* Updating user money balance */
                            $user->userid        = $paymentGateway["userid"];
                            $user->save();

                            /* Updating order number */
                            $orderNumber->number = $order_num;
                            $orderNumber->save();

                            /* Updating booking details */
                            foreach ($cart_ids as $cart_id) {
                                $cartUpdate               = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                                    ->where('status', "8")
                                    ->where('is_delete', 0)
                                    ->find($cart_id);
                                $cartUpdate->order_id     = new \MongoDB\BSON\ObjectID($order->_id);
                                $cartUpdate->payment_type = $request->payment;
                                $cartUpdate->txid         = $paymentGateway["txid"];
                                $cartUpdate->userid       = $paymentGateway["userid"];
                                $cartUpdate->save();
                            }

                            return redirect()->route('payment.error')->with('bookingErrorStatus', 'There has been an error or pending processing your request.');
                        }
                    }
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
        return redirect()->back()->with('choosePaymentNullData', 'Something went wrong! Please check your cart.');
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $request
     * @param  string  $ip
     * @param  string  $amount
     * @param  string  $order_number
     * @return array
     */
    public function paymentGateway($request, $ip, $amount, $order_number)
    {
        /* Declaring variables */
        $walletType                   = "";

        $clearingType                 = "";

        $onlineBankTransferType       = "";

        $requestType                  = "";

        $pseudoCardPan                = "";

        $countryName                  = "";

        include(app_path() . '/Function/Payone.php');

        /* Condition for country*/
        if(Auth::user()->usrCountry === 'Deutschland') {
            $countryName              = "DE";
        }
        elseif(Auth::user()->usrCountry === 'Österreich') {
            $countryName              = "AT";
        }
        else {
            $countryName              = "DE";
        }

        /* Payment gateway details */
        $defaults                     = array(
            "aid"                     => env('AID'),

            "mid"                     => env('MID'),

            "portalid"                => env('PORTAL_ID'),

            "key"                     => hash("md5", env('KEY')), // The key has to be hashed as md5

            "mode"                    => env('MODE'), // Can be "live" for actual transactions

            "api_version"             => env('API_VERSION'),

            "encoding"                => env('ENCODING')
        );

        /* Personal details */
        $personalData                 = array(

            "salutation"              => Auth::user()->salutation,

            "title"                   => Auth::user()->title,

            "firstname"               => Auth::user()->usrFirstname,

            "lastname"                => Auth::user()->usrLastname,

            "company"                 => Auth::user()->company,

            "street"                  => Auth::user()->usrAddress,

            "zip"                     => Auth::user()->usrZip,

            "city"                    => Auth::user()->usrCity,

            "country"                 => $countryName,

            "email"                   => Auth::user()->usrEmail,

            "telephonenumber"         => Auth::user()->usrTelephone,

            "language"                => env('APP_LOCALE'),

            "gender"                  => Auth::user()->gender,

            "ip"                      => $ip
        );

        /* Condition for payment type */
        if($request['payment'] === 'payPal') {
            $clearingType             = "wlt";
            $walletType               = "PPE";
            $requestType              = "authorization";
        }
        elseif ($request['payment'] === 'payDirect') {
            $clearingType             = "wlt";
            $walletType               = "PDT";
            $requestType              = "authorization";
        }
        elseif ($request['payment'] === 'sofort') {
            $clearingType             = "sb";
            $onlineBankTransferType   = "PNT";
            $requestType              = "authorization";
        }
        elseif ($request['payment'] === 'creditCard') {
            $clearingType             = "cc";
            $requestType              = "authorization";
            $pseudoCardPan            = $request['pseudocardpan'];
        }
        elseif ($request['payment'] === 'payByBill') {
            $clearingType             = "vor";
            $requestType              = "preauthorization";
        }
        else{
            abort(404);
        }

        /* Parameters for payment gateway */
        $parameters                   = array(

            "request"                 => $requestType,

            "clearingtype"            => $clearingType,

            "wallettype"              => $walletType,

            "amount"                  => str_replace(".", "", $amount),

            "currency"                => "EUR",

            "reference"               => random_int(111, 99999).uniqid(),

            "pr[1]"                   => str_replace(".", "", $amount),

            "no[1]"                   => "1",

            "param"                   => $order_number,

            "onlinebanktransfertype"  => $onlineBankTransferType,

            "bankcountry"             => $countryName,

            "pseudocardpan"           => $pseudoCardPan,

            "narrative_text"          => $order_number,

            "va[1]"                   => env('VATRATE'),

            "sd[1]"                   => date('Ymd'),

            "ed[1]"                   => date('Ymd'),

            "vatid"                   => env('VATID'),

            "document_date"           => date('Ymd'),

            "booking_date"            => date('Ymd'),

            "due_time"                => mktime(0, 0, 0, date('n'), date('j') + 1),

            "id[1]"                   => random_int(111, 99999).uniqid(),

            "de[1]"                   => $order_number,

            "customerid"              => random_int(9999, 9999999999),

            "userid"                  => random_int(9999, 9999999999),

            "personalid"              => random_int(9999, 9999999999),

            "invoiceid"               => $order_number,

            "invoice_deliverydate"    => date('Ymd'),

            "invoice_deliveryenddate" => date('Ymd'),

            "invoice_deliverymode"    => "P", //PDF

            "invoiceappendix"         => $order_number, //Dynamic text on the invoice

            "shipping_firstname"      => Auth::user()->usrFirstname,

            "shipping_lastname"       => Auth::user()->usrLastname,

            "shipping_company"        => Auth::user()->company,

            "shipping_street"         => Auth::user()->usrAddress,

            "shipping_zip"            => Auth::user()->usrZip,

            "shipping_city"           => Auth::user()->usrCity,

            "shipping_country"        => $countryName,

            "successurl"              => env('SUCCESSURL'),

            "errorurl"                => env('ERRORURL')
        );

        $request                      = array_merge($defaults, $parameters, $personalData);

        ksort($request);

        $response                     = Payone::sendRequest($request);

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
        // you'll need to include the $defaults array somehow, or at least get the key from a secret configuration file
        if ($_POST["key"] == hash("md5", env('KEY'))) {
            // key is valid, this notification is for us

            if ($_POST["txaction"] == "appointed") {
                $payment        = new Payment;
                foreach($_POST as $key => $value) {
                    if(Schema::hasColumn($payment->getTable(), $key)){
                        if(is_array($value)) {
                            $payment->{$key} = $value[1];
                        } else {
                            $payment->{$key} = $value;
                        }
                    }
                }
                $payment->save();

                $order          = Order::where('userid', $_POST["userid"])->where('txid', $_POST["txid"])->first();
                $order->tsok    = 'appointed';
                echo "TSOK";
            }
            if ($_POST["txaction"] == "paid") {
                $payment        = new Payment;
                foreach($_POST as $key => $value) {
                    if(Schema::hasColumn($payment->getTable(), $key)){
                        if(is_array($value)) {
                            $payment->{$key} = $value[1];
                        } else {
                            $payment->{$key} = $value;
                        }
                    }
                }
                $payment->save();

                $order          = Order::where('userid', $_POST["userid"])->where('txid', $_POST["txid"])->first();
                $order->tsok    = 'paid';
                echo "TSOK";
                // update your transaction accordingly, e.g. by $_POST["reference"]
            }
        }
        else{
            abort(404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function success()
    {
        if(session()->has('bookingSuccessStatus')) {
            return view('paymentSuccess');
        }
        else {
            abort(404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function prepayment()
    {
        if(session()->has('bookingSuccessStatusPrepayment') && session()->has('order')) {
            return view('paymentPrepayment');
        }
        else {
            abort(404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function failure()
    {
        if(session()->has('bookingErrorStatus')) {
            return view('paymentError');
        }
        else {
            abort(404);
        }
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
