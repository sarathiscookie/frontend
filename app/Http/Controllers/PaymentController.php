<?php

namespace App\Http\Controllers;

use App\Mail\BookingFailed;
use App\Mail\BookingSuccess;
use Illuminate\Http\Request;

use App\Http\Requests\PaymentRequest;
use App\Userlist;
use App\Booking;
use App\Order;
use App\Ordernumber;
use App\Payment;
use App\Cabin;
use Auth;
use Validator;
use Payone;
use DateTime;
use Carbon\Carbon;
use Mail;
use App\Mail\SendVoucher;
use PDF;
use Illuminate\Support\Facades\Schema;

class PaymentController extends Controller
{
    /**
     * To generate date format as mongo.
     *
     * @param  string  $date
     * @return \Illuminate\Http\Response
     */
    protected function getDateUtc($date)
    {
        $dateFormatChange = DateTime::createFromFormat("d.m.y", $date)->format('Y-m-d');
        $dateTime         = new DateTime($dateFormatChange);
        $timeStamp        = $dateTime->getTimestamp();
        $utcDateTime      = new \MongoDB\BSON\UTCDateTime($timeStamp * 1000);
        return $utcDateTime;
    }

    /**
     * Function for service fee
     *
     * @param  string  $sumPrepayAmount
     * @param  string  $paymentMethod
     * @return \Illuminate\Http\Response
     */
    public function serviceFees($sumPrepayAmount, $paymentMethod = null)
    {
        $serviceTaxBook = 0;

        if($paymentMethod === 'payByBill'){
            if($sumPrepayAmount <= 30) {
                $serviceTaxBook = env('SERVICE_TAX_PAYBYBILL_ONE');
            }

            if($sumPrepayAmount > 30 && $sumPrepayAmount <= 100) {
                $serviceTaxBook = env('SERVICE_TAX_PAYBYBILL_TWO');
            }

            if($sumPrepayAmount > 100) {
                $serviceTaxBook = env('SERVICE_TAX_PAYBYBILL_THREE');
            }
        }
        elseif($paymentMethod === 'sofort'){
            if($sumPrepayAmount <= 30) {
                $serviceTaxBook = env('SERVICE_TAX_SOFORT_ONE');
            }

            if($sumPrepayAmount > 30 && $sumPrepayAmount <= 100) {
                $serviceTaxBook = env('SERVICE_TAX_SOFORT_TWO');
            }

            if($sumPrepayAmount > 100) {
                $serviceTaxBook = env('SERVICE_TAX_SOFORT_THREE');
            }
        }
        elseif($paymentMethod === 'payDirect'){
            if($sumPrepayAmount <= 30) {
                $serviceTaxBook = env('SERVICE_TAX_PAYDIRECT_ONE');
            }

            if($sumPrepayAmount > 30 && $sumPrepayAmount <= 100) {
                $serviceTaxBook = env('SERVICE_TAX_PAYDIRECT_TWO');
            }

            if($sumPrepayAmount > 100) {
                $serviceTaxBook = env('SERVICE_TAX_PAYDIRECT_THREE');
            }
        }
        elseif($paymentMethod === 'payPal'){
            if($sumPrepayAmount <= 30) {
                $serviceTaxBook = env('SERVICE_TAX_PAYPAL_ONE');
            }

            if($sumPrepayAmount > 30 && $sumPrepayAmount <= 100) {
                $serviceTaxBook = env('SERVICE_TAX_PAYPAL_TWO');
            }

            if($sumPrepayAmount > 100) {
                $serviceTaxBook = env('SERVICE_TAX_PAYPAL_THREE');
            }
        }
        elseif($paymentMethod === 'creditCard'){
            if($sumPrepayAmount <= 30) {
                $serviceTaxBook = env('SERVICE_TAX_CREDITCARD_ONE');
            }

            if($sumPrepayAmount > 30 && $sumPrepayAmount <= 100) {
                $serviceTaxBook = env('SERVICE_TAX_CREDITCARD_TWO');
            }

            if($sumPrepayAmount > 100) {
                $serviceTaxBook = env('SERVICE_TAX_CREDITCARD_THREE');
            }
        }
        else {
            if($sumPrepayAmount <= 30) {
                $serviceTaxBook = env('SERVICE_TAX_ONE');
            }

            if($sumPrepayAmount > 30 && $sumPrepayAmount <= 100) {
                $serviceTaxBook = env('SERVICE_TAX_TWO');
            }

            if($sumPrepayAmount > 100) {
                $serviceTaxBook = env('SERVICE_TAX_THREE');
            }
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
        $prepayment_amount         = [];
        $payByBillPossible         = '';
        $carts                     = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
            ->where('status', "8")
            ->where('is_delete', 0)
            ->take(5)
            ->get();

        if(count($carts) > 0 ) {

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
                    $payByBillPossible = 'yes';
                }
                else {
                    $payByBillPossible = 'no';
                }
                /* Condition to check pay by bill possible end */
            }

            /* Get order number */
            $orderNumber             = Ordernumber::first();
            if( !empty ($orderNumber->number) ) {
                $order_num           = (int)$orderNumber->number + 1;
            }
            else {
                $order_num           = 100000;
            }

            $order_number            = 'ORDER'.'-'.date('y').'-'.$order_num;
            $sum_prepayment_amount   = array_sum($prepayment_amount);
            $serviceTax              = $this->serviceFees($sum_prepayment_amount, $paymentMethod = null);
            $percentage              = ($serviceTax / 100) * $sum_prepayment_amount;
            $prepay_service_total    = $sum_prepayment_amount + $percentage;

            return view('payment', ['moneyBalance' => Auth::user()->money_balance, 'sumPrepaymentAmount' => $sum_prepayment_amount, 'prepayServiceTotal' => $prepay_service_total, 'serviceTax' => $serviceTax, 'payByBillPossible' => $payByBillPossible, 'order_number' => $order_number]);

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
        $payByBillPossible           = '';
        $user                        = Userlist::where('is_delete', 0)->where('usrActive', '1')->find(Auth::user()->_id);
        $carts                       = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
            ->where('status', "8")
            ->where('is_delete', 0)
            ->take(5)
            ->get();

        if(!empty($carts)) {
            $countCarts              = count($carts);
            /* Loop for amount calculation, pay by bill date difference and explode cabin code */
            foreach ($carts as $key => $cart) {
                $prepayment_amount[] = $cart->prepayment_amount;
                $cart_ids[]          = $cart->_id;

                // Pay by bill condition works if there is two weeks diff b/w current date and checking from date.
                $checkingFrom        = $cart->checkin_from->format('Y-m-d');
                $currentDate         = date('Y-m-d');
                $d1                  = new DateTime($currentDate);
                $d2                  = new DateTime($checkingFrom);
                $dateDifference      = $d2->diff($d1);
                if($dateDifference->days > 14) {
                    $payByBillPossible = 'yes';
                }
                else {
                    $payByBillPossible = 'no';
                }
            }

            /* Generate order number begin */
            $orderNumber = Ordernumber::first();
            if( !empty ($orderNumber->number) ) {
                $order_num = (int)$orderNumber->number + 1;
            }
            else {
                $order_num = 100000;
            }
            /* Generate order number end */

            $order_number            = 'ORDER'.'-'.date('y').'-'.$order_num;
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
                    $order->order_money_balance_used      = $total_prepayment_amount;
                    $order->order_money_balance_used_date = date('Y-m-d H:i:s');
                    $order->order_payment_method          = 1; // 1 => fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                    $order->order_delete                  = 0;
                    $order->save();

                    if($order) {
                        /* Updating money balance */
                        $user->money_balance = round($afterRedeemAmount, 2);
                        $user->save();

                        /* Updating order number in ordernumber collection */
                        $orderNumber->number = $order_num;
                        $orderNumber->save();

                        /* Updating booking details */
                        foreach ($cart_ids as $cart_id) {
                            $cartUpdate                    = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                                ->where('status', "8")
                                ->where('is_delete', 0)
                                ->find($cart_id);
                            $cartUpdate->order_id          = new \MongoDB\BSON\ObjectID($order->_id);
                            $cartUpdate->status            = '1';
                            $cartUpdate->payment_status    = '1';
                            $cartUpdate->moneybalance_used = round($cartUpdate->prepayment_amount, 2);
                            $cartUpdate->save();
                        }

                        /* Send email to guest after successful booking */
                        Mail::to($user->usrEmail)->send(new BookingSuccess());

                        return redirect()->route('payment.success')->with('bookingSuccessStatus', __('payment.bookingSuccessStatus'));
                    }
                    else {
                        return redirect()->back()->with('bookingFailureStatus', __('payment.bookingFailureStatus'));
                    }
                }
                else {
                    if(isset($request->payment)) {
                        /* How much money user have in their account after used money balance */
                        $afterRedeemAmount = $total_prepayment_amount - $user->money_balance;
                        $percentage        = ($this->serviceFees($afterRedeemAmount, $request->payment) / 100) * $afterRedeemAmount;
                        $total             = round($afterRedeemAmount + $percentage, 2);
                        $cartMoneyBalance  = $user->money_balance / $countCarts; // To store how much money balance used for each booking

                        // Function call for payment gateway section
                        $paymentGateway    = $this->paymentGateway($request->all(), $request->ip(), $total, $order_number);

                        if ($paymentGateway["status"] === "REDIRECT") {

                            /* Storing order details */
                            $order                                = new Order;
                            $order->order_id                      = $order_number;
                            $order->auth_user                     = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                            $order->order_status                  = "REDIRECT";
                            $order->txid                          = $paymentGateway["txid"];
                            $order->userid                        = $paymentGateway["userid"];
                            $order->order_payment_type            = $request->payment;
                            $order->order_payment_method          = 2; // 1 => fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                            $order->order_amount                  = round($afterRedeemAmount, 2);
                            $order->order_total_amount            = $total;
                            $order->order_money_balance_used      = round($user->money_balance, 2);
                            $order->order_money_balance_used_date = date('Y-m-d H:i:s');
                            $order->order_delete                  = 1;
                            $order->save();
                            /* Storing order details end */

                            if($order) {
                                /* Updating booking details */
                                foreach ($cart_ids as $cart_id) {
                                    $cartUpdate                    = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                                        ->where('status', "8")
                                        ->where('is_delete', 0)
                                        ->find($cart_id);
                                    $cartUpdate->order_id          = new \MongoDB\BSON\ObjectID($order->_id);
                                    $cartUpdate->payment_type      = $request->payment;
                                    $cartUpdate->txid              = $paymentGateway["txid"];
                                    $cartUpdate->userid            = $paymentGateway["userid"];
                                    $cartUpdate->moneybalance_used = round($cartMoneyBalance, 2);
                                    $cartUpdate->save();
                                }

                                /* Storing userid in user collection */
                                $user->userid        = $paymentGateway["userid"];
                                $user->save();

                                /* Updating order number in ordernumber collection */
                                $orderNumber->number = $order_num;
                                $orderNumber->save();

                                $request->session()->flash('txid', $paymentGateway["txid"]);
                                $request->session()->flash('userid', $paymentGateway["userid"]);
                                $request->session()->flash('bookingSuccessStatus', __('payment.bookingSuccessStatus'));
                                return redirect()->away($paymentGateway["redirecturl"]);
                            }
                            else {
                                return redirect()->back()->with('bookingFailureStatus', __('payment.bookingFailureStatus'));
                            }
                        }
                        elseif ($paymentGateway["status"] === "APPROVED") { // If card is not 3d secure and prepayment(PayByBill) return status is APPROVED and "redirect url" will not return. We manually redirect to success page.
                            /* Storing order details */
                            $order                                = new Order;
                            $order->order_id                      = $order_number;
                            $order->auth_user                     = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                            $order->order_status                  = "APPROVED";
                            $order->txid                          = $paymentGateway["txid"];
                            $order->userid                        = $paymentGateway["userid"];
                            $order->order_payment_type            = $request->payment;
                            $order->order_payment_method          = 2; // 1 => fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                            $order->order_amount                  = round($afterRedeemAmount, 2);
                            $order->order_total_amount            = $total;
                            $order->order_money_balance_used      = round($user->money_balance, 2);
                            $order->order_money_balance_used_date = date('Y-m-d H:i:s');
                            $order->order_delete                  = 1;

                            /* If guest paid using payByBill we need to store bank details. Condition begin */
                            if($request->payment === 'payByBill' && $payByBillPossible === 'yes') {
                                $order->clearing_bankaccount       = '';
                                $order->clearing_bankcode          = '';
                                $order->clearing_bankcountry       = '';
                                $order->clearing_bankname          = env('BANKNAME');
                                $order->clearing_bankaccountholder = env('ACCOUNTHOLDER');
                                $order->clearing_bankiban          = env('IBAN');
                                $order->clearing_bankbic           = env('BIC');
                            }
                            /* If guest paid using payByBill we need to store bank details. Condition end */

                            $order->save();
                            /* Storing order details end */

                            if($order) {
                                /* Updating booking details */
                                foreach ($cart_ids as $cart_id) {
                                    $cartUpdate                     = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                                        ->where('status', "8")
                                        ->where('is_delete', 0)
                                        ->find($cart_id);
                                    $cartUpdate->order_id           = new \MongoDB\BSON\ObjectID($order->_id);
                                    $cartUpdate->payment_type       = $request->payment;
                                    $cartUpdate->txid               = $paymentGateway["txid"];
                                    $cartUpdate->userid             = $paymentGateway["userid"];
                                    $cartUpdate->moneybalance_used  = round($cartMoneyBalance, 2);
                                    $cartUpdate->save();
                                }

                                /* Storing userid in user collection */
                                $user->userid        = $paymentGateway["userid"];
                                $user->save();

                                /* Updating order number in ordernumber collection */
                                $orderNumber->number = $order_num;
                                $orderNumber->save();

                                /* If guest paid using payByBill it will redirect to bank details listing page. Condition begin*/
                                if($request->payment === 'payByBill') {
                                    if($payByBillPossible === 'yes') {
                                        $request->session()->flash('txid', $paymentGateway["txid"]);
                                        $request->session()->flash('userid', $paymentGateway["userid"]);
                                        $request->session()->flash('payByBillPossible', $payByBillPossible);
                                        $request->session()->flash('bookingSuccessStatusPrepayment', __('payment.bookingSuccessStatus'));
                                        return redirect()->route('payment.prepayment')->with('order', $order);
                                    }
                                    else {
                                        return redirect()->back()->with('bookingFailureStatus', __('payment.bookingFailureStatus'));
                                    }
                                }
                                /* If guest paid using payByBill it will redirect to bank details listing page. Condition end*/

                                $request->session()->flash('txid', $paymentGateway["txid"]);
                                $request->session()->flash('userid', $paymentGateway["userid"]);
                                return redirect()->route('payment.success')->with('bookingSuccessStatus', __('payment.bookingSuccessStatus'));
                            }
                            else {
                                return redirect()->back()->with('bookingFailureStatus', __('payment.bookingFailureStatus'));
                            }
                        }
                        else {
                            return redirect()->route('payment.error')->with('bookingErrorStatus', __('payment.bookingErrorStatus'));
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
                    $percentage     = ($this->serviceFees($total_prepayment_amount, $request->payment) / 100) * $total_prepayment_amount;
                    $total          = round($total_prepayment_amount + $percentage, 2);
                    // Function call for payment gateway section
                    $paymentGateway = $this->paymentGateway($request->all(), $request->ip(), $total, $order_number);

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
                        $order->order_delete         = 1;
                        $order->save();
                        /* Storing order details end */

                        if($order) {
                            /* Storing userid from payment response in to user collection */
                            $user->userid        = $paymentGateway["userid"];
                            $user->save();

                            /* Updating order number in ordernumber collection */
                            $orderNumber->number = $order_num;
                            $orderNumber->save();

                            /* Updating booking details */
                            foreach ($cart_ids as $cart_id) {
                                $cartUpdate                 = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                                    ->where('status', "8")
                                    ->where('is_delete', 0)
                                    ->find($cart_id);
                                $cartUpdate->order_id          = new \MongoDB\BSON\ObjectID($order->_id);
                                $cartUpdate->payment_type      = $request->payment;
                                $cartUpdate->txid              = $paymentGateway["txid"];
                                $cartUpdate->userid            = $paymentGateway["userid"];
                                $cartUpdate->moneybalance_used = 0;
                                $cartUpdate->save();
                            }

                            $request->session()->flash('txid', $paymentGateway["txid"]);
                            $request->session()->flash('userid', $paymentGateway["userid"]);
                            $request->session()->flash('bookingSuccessStatus', __('payment.bookingSuccessStatus'));
                            return redirect()->away($paymentGateway["redirecturl"]);
                        }
                        else {
                            return redirect()->back()->with('bookingFailureStatus', __('payment.bookingFailureStatus'));
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
                        $order->order_delete          = 1;

                        /* If guest paid using payByBill we need to store bank details. Condition begin */
                        if($request->payment === 'payByBill' && $payByBillPossible === 'yes') {
                            $order->clearing_bankaccount       = '';
                            $order->clearing_bankcode          = '';
                            $order->clearing_bankcountry       = '';
                            $order->clearing_bankname          = env('BANKNAME');
                            $order->clearing_bankaccountholder = env('ACCOUNTHOLDER');
                            $order->clearing_bankiban          = env('IBAN');
                            $order->clearing_bankbic           = env('BIC');
                        }
                        /* If guest paid using payByBill we need to store bank details. Condition end */

                        $order->save();
                        /* Storing order details end */

                        if($order) {
                            /* Storing userid from payment response in to user collection */
                            $user->userid        = $paymentGateway["userid"];
                            $user->save();

                            /* Updating order number in ordernumber collection */
                            $orderNumber->number = $order_num;
                            $orderNumber->save();

                            /* Updating booking details */
                            foreach ($cart_ids as $cart_id) {
                                $cartUpdate               = Booking::where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                                    ->where('status', "8")
                                    ->where('is_delete', 0)
                                    ->find($cart_id);
                                $cartUpdate->order_id          = new \MongoDB\BSON\ObjectID($order->_id);
                                $cartUpdate->payment_type      = $request->payment;
                                $cartUpdate->txid              = $paymentGateway["txid"];
                                $cartUpdate->userid            = $paymentGateway["userid"];
                                $cartUpdate->moneybalance_used = 0;
                                $cartUpdate->save();
                            }

                            /* If guest paid using payByBill it will redirect to bank details listing page. Condition begin*/
                            if($request->payment === 'payByBill') {
                                if($payByBillPossible === 'yes') {
                                    $request->session()->flash('txid', $paymentGateway["txid"]);
                                    $request->session()->flash('userid', $paymentGateway["userid"]);
                                    $request->session()->flash('payByBillPossible', $payByBillPossible);
                                    $request->session()->flash('bookingSuccessStatusPrepayment', __('payment.bookingSuccessStatus'));
                                    return redirect()->route('payment.prepayment')->with('order', $order);
                                }
                                else {
                                    return redirect()->back()->with('bookingFailureStatus', __('payment.bookingFailureStatus'));
                                }
                            }
                            /* If guest paid using payByBill it will redirect to bank details listing page. Condition end*/

                            $request->session()->flash('txid', $paymentGateway["txid"]);
                            $request->session()->flash('userid', $paymentGateway["userid"]);
                            return redirect()->route('payment.success')->with('bookingSuccessStatus', __('payment.bookingSuccessStatus'));
                        }
                        else {
                            return redirect()->back()->with('bookingFailureStatus', __('payment.bookingFailureStatus'));
                        }
                    }
                    else {
                        return redirect()->route('payment.error')->with('bookingErrorStatus', __('payment.bookingErrorStatus'));
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
            return redirect()->route('cart');
        }
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
        setlocale(LC_ALL, 'de_DE');
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

        //dd(iconv('UTF-8', 'ASCII//TRANSLIT', Auth::user()->usrAddress));
        /* Personal details */
        $personalData                 = array(

            "salutation"              => iconv('UTF-8', 'ASCII//TRANSLIT', Auth::user()->salutation),

            "title"                   => iconv('UTF-8', 'ASCII//TRANSLIT', Auth::user()->title),

            "firstname"               => iconv('UTF-8', 'ASCII//TRANSLIT', Auth::user()->usrFirstname),

            "lastname"                => iconv('UTF-8', 'ASCII//TRANSLIT', Auth::user()->usrLastname),

            "company"                 => iconv('UTF-8', 'ASCII//TRANSLIT', Auth::user()->company),

            "street"                  => iconv('UTF-8', 'ASCII//TRANSLIT', Auth::user()->usrAddress),

            "zip"                     => iconv('UTF-8', 'ASCII//TRANSLIT', Auth::user()->usrZip),

            "city"                    => iconv('UTF-8', 'ASCII//TRANSLIT', Auth::user()->usrCity),

            "country"                 => $countryName,

            "email"                   => iconv('UTF-8', 'ASCII//TRANSLIT', Auth::user()->usrEmail),

            "telephonenumber"         => iconv('UTF-8', 'ASCII//TRANSLIT', Auth::user()->usrTelephone),

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

            "amount"                  => $amount * 100,

            "currency"                => "EUR",

            "reference"               => random_int(111, 99999).uniqid(),

            "pr[1]"                   => $amount * 100,

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

            "shipping_firstname"      => iconv('UTF-8', 'ASCII//TRANSLIT', Auth::user()->usrFirstname),

            "shipping_lastname"       => iconv('UTF-8', 'ASCII//TRANSLIT', Auth::user()->usrLastname),

            "shipping_company"        => iconv('UTF-8', 'ASCII//TRANSLIT', Auth::user()->company),

            "shipping_street"         => iconv('UTF-8', 'ASCII//TRANSLIT', Auth::user()->usrAddress),

            "shipping_zip"            => iconv('UTF-8', 'ASCII//TRANSLIT', Auth::user()->usrZip),

            "shipping_city"           => iconv('UTF-8', 'ASCII//TRANSLIT', Auth::user()->usrCity),

            "shipping_country"        => $countryName,

            "successurl"              => env('SUCCESSURL'),

            "errorurl"                => env('ERRORURL'),

            "backurl"                 => env('BACKURL'),
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
        if ($_POST["key"] == hash("md5", env('KEY'))) {

            // If key is valid, TSOK notification is for PAYONE

            $user  = Userlist::where('is_delete', 0)
                ->where('usrActive', '1')
                ->where('userid', $_POST["userid"])
                ->first();

            if ($_POST["txaction"] === "appointed") {
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
                echo "TSOK";

                $order               = Order::where('userid', $_POST["userid"])->where('txid', $_POST["txid"])->first();
                $order->tsok         = 'appointed';
                $order->reference    = $_POST["reference"];
                $order->clearingtype = $_POST["clearingtype"];
                $order->customerid   = $_POST["customerid"];
                $order->save();

                /* Send email to guest after successful booking */
                if($_POST["clearingtype"] === 'vor') {
                    if($user) {
                        Mail::to($user->usrEmail)->send(new BookingSuccess());
                    }
                }
            }
            else if ($_POST["txaction"] === "paid") {
                $payment        = Payment::where('userid', $_POST["userid"])->where('txid', $_POST["txid"])->first();
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
                echo "TSOK";

                $order               = Order::where('userid', $_POST["userid"])->where('txid', $_POST["txid"])->first();
                $order->tsok         = 'paid';
                $order->reference    = $_POST["reference"];
                $order->clearingtype = $_POST["clearingtype"];
                $order->customerid   = $_POST["customerid"];
                $order->save();

                /* Send email to guest after successful booking */
                if($user) {
                    Mail::to($user->usrEmail)->send(new BookingSuccess());
                }
            }
            else {
                /* Payment failure functionality begin */
                $order               = Order::where('userid', $_POST["userid"])->where('txid', $_POST["txid"])->first();
                $order->tsok         = 'failed';
                $order->save();
                /* Payment failure functionality end */

                /* Send notification email to admin if $_POST["txaction"] is not appointed or paid */
                if($user) {
                    Mail::to(env('ADMIN_EMAIL'))->send(new BookingFailed($_POST["txid"], $_POST["userid"]));
                }
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
        if(session()->has('bookingSuccessStatus')) {  // Success condition for cart payment

            if(session()->has('txid') && session()->has('userid')) {
                $carts              = Booking::select('_id', 'status', 'payment_status')
                    ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                    ->where('status', "8")
                    ->where('is_delete', 0)
                    ->where('txid', session()->get('txid'))
                    ->where('userid', session()->get('userid'))
                    ->get();

                if(!empty($carts)) {
                    foreach ($carts as $cart) {
                        $cartUpdate                 = Booking::find($cart->_id);
                        $cartUpdate->status         = '1';
                        $cartUpdate->payment_status = '1';
                        $cartUpdate->save();
                    }

                    $order          = Order::where('userid', session()->get('userid'))
                        ->where('txid', session()->get('txid'))
                        ->where('auth_user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                        ->first();

                    if(!empty($order)) {
                        $order->order_delete = 0;
                        $order->save();

                        if($order->order_payment_method === 2) { // 1 => Fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                            $user                = Userlist::where('is_delete', 0)->where('usrActive', '1')->find(Auth::user()->_id);
                            $user->money_balance = 0.00;
                            $user->save();
                        }
                    }
                }
                else {
                    abort(404);
                }
            }

            return view('paymentSuccess');
        }
        elseif(session()->has('editBookingSuccessStatus')) { // Success condition for edit payment
            if(session()->has('editTxId') && session()->has('editUserId')) {

                if( session()->has('updateBookingPayment') && session()->get('updateBookingPayment') === 'updateBookingPayment' && session()->has('availableStatus') && session()->get('availableStatus') === 'success' ) {

                    $bookingOld = Booking::where('status', '1')
                        ->where('payment_status', '1')
                        ->where('is_delete', 0)
                        ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                        ->find(session()->get('bookingIdRequest'));

                    if(!empty($bookingOld)) {
                        /* Update status of old booking */
                        $bookingOld->booking_update = date('Y-m-d H:i:s');
                        $bookingOld->status         = "9"; //9 => Old (Booking Updated)
                        $bookingOld->is_delete      = 1;
                        $bookingOld->save();

                        /* Update status of old orders */
                        $order  = Order::where('auth_user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                            ->find($bookingOld->order_id);

                        if(!empty($order)) {
                            $order->order_update_date  = date('Y-m-d H:i:s');
                            $order->save();
                        }

                        /* Update new booking status and payment status */
                        $newBooking = Booking::where('status', '10')
                            ->where('userid', session()->get('editUserId'))
                            ->where('txid', session()->get('editTxId'))
                            ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                            ->find(session()->get('newBooking'));

                        $newBooking->status         = '1';
                        $newBooking->payment_status = '1';
                        $newBooking->save();

                        /* Update money balance */
                        $newOrder = Order::where('userid', session()->get('editUserId'))
                            ->where('txid', session()->get('editTxId'))
                            ->where('auth_user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                            ->first();

                        if(!empty($newOrder) && $newOrder->order_payment_method === 2) { // 1 => fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                            $user = Userlist::where('is_delete', 0)->where('usrActive', '1')->find(Auth::user()->_id);
                            $user->money_balance = 0.00;
                            $user->save();
                        }
                    }
                }
                else {
                    abort(404);
                }
            }

            /* Delete sessions */
            session()->forget('bookingIdRequest');
            session()->forget('dateFromRequest');
            session()->forget('dateToRequest');
            session()->forget('bedRequest');
            session()->forget('dormRequest');
            session()->forget('sleepRequest');
            session()->forget('halfBoardRequest');
            session()->forget('commentsRequest');
            session()->forget('sleepingPlaceRequest');
            session()->forget('prepaymentAmountRequest');
            session()->forget('availableStatus');

            return view('paymentSuccess');
        }
        elseif(session()->has('inquiryBookingSuccessStatus')) { // Success condition for inquiry payment

            if(session()->has('inquiryTxId') && session()->has('inquiryUserId')) {

                if( session()->has('updateInquiryPayment') && session()->get('updateInquiryPayment') === 'updateInquiryPayment' && session()->has('newBooking') ) {

                    $bookingData            = Booking::where('status', '5')
                        ->where('inquirystatus', 1)
                        ->where('typeofbooking', 1)
                        ->where('userid', session()->get('inquiryUserId'))
                        ->where('txid', session()->get('inquiryTxId'))
                        ->where('is_delete', 0)
                        ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                        ->find(session()->get('newBooking'));

                    if(!empty($bookingData)) {

                        /* Update booking status and payment status */
                        $bookingData->status         = '1';
                        $bookingData->payment_status = '1';
                        $bookingData->save();

                        /* Update money balance */
                        $order = Order::where('userid', session()->get('inquiryUserId'))
                            ->where('txid', session()->get('inquiryTxId'))
                            ->where('auth_user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                            ->first();

                        if(!empty($order)) {
                            $order->order_delete = 0;
                            $order->save();

                            if($order->order_payment_method === 2) { // 1 => Fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                                $user = Userlist::where('is_delete', 0)->where('usrActive', '1')->find(Auth::user()->_id);
                                $user->money_balance = 0.00;
                                $user->save();
                            }
                        }
                    }
                }
                else {
                    abort(404);
                }
            }

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
        if(session()->has('bookingSuccessStatusPrepayment') && session()->has('order')) { // Success condition for cart prepayment

            if(session()->has('txid') && session()->has('userid') && session()->has('payByBillPossible')) {
                $carts              = Booking::select('_id', 'status', 'payment_status')
                    ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                    ->where('status', "8")
                    ->where('is_delete', 0)
                    ->where('txid', session()->get('txid'))
                    ->where('userid', session()->get('userid'))
                    ->get();

                if(!empty($carts) && session()->get('payByBillPossible') === 'yes') {
                    foreach ($carts as $cart) {
                        $cartUpdate                 = Booking::find($cart->_id);
                        $cartUpdate->status         = '5';
                        $cartUpdate->payment_status = '3';
                        $cartUpdate->save();
                    }

                    $order          = Order::where('userid', session()->get('userid'))
                        ->where('txid', session()->get('txid'))
                        ->where('auth_user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                        ->first();

                    if(!empty($order)) {
                        $order->order_delete = 0;
                        $order->save();

                        if($order->order_payment_method === 2) { // 1 => Fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                            $user = Userlist::where('is_delete', 0)->where('usrActive', '1')->find(Auth::user()->_id);
                            $user->money_balance = 0.00;
                            $user->save();
                        }
                    }

                }
                else {
                    abort(404);
                }
            }

            return view('paymentPrepayment');
        }
        else if(session()->has('editBookingSuccessStatusPrepayment') && session()->has('editBookOrder')) { // Success condition for edit prepayment

            if(session()->has('editTxId') && session()->has('editUserId') && session()->has('editPayByBillPossible') && session()->get('editPayByBillPossible') === 'yes') {

                if( session()->has('updateBookingPayment') && session()->get('updateBookingPayment') === 'updateBookingPayment' && session()->has('availableStatus') && session()->get('availableStatus') === 'success' ) {

                    $bookingOld = Booking::where('status', '1')
                        ->where('payment_status', '1')
                        ->where('is_delete', 0)
                        ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                        ->find(session()->get('bookingIdRequest'));

                    if(!empty($bookingOld)) {

                        /* Update status of old booking */
                        $bookingOld->booking_update = date('Y-m-d H:i:s');
                        $bookingOld->status         = "9"; //9 => Old (Booking Updated)
                        $bookingOld->is_delete      = 1;
                        $bookingOld->save();

                        /* Update status of old orders */
                        $order  = Order::where('auth_user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                            ->find($bookingOld->order_id);

                        if(!empty($order)) {
                            $order->order_update_date  = date('Y-m-d H:i:s');
                            $order->save();
                        }

                        /* Update new booking status and payment status */
                        $newBooking = Booking::where('status', '10')
                            ->where('userid', session()->get('editUserId'))
                            ->where('txid', session()->get('editTxId'))
                            ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                            ->find(session()->get('newBooking'));

                        $newBooking->status         = '5';
                        $newBooking->payment_status = '3';
                        $newBooking->save();

                        /* Update money balance */
                        $newOrder = Order::where('userid', session()->get('editUserId'))
                            ->where('txid', session()->get('editTxId'))
                            ->where('auth_user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                            ->first();

                        if(!empty($newOrder) && $newOrder->order_payment_method === 2) { // 1 => fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway
                            $user = Userlist::where('is_delete', 0)->where('usrActive', '1')->find(Auth::user()->_id);
                            $user->money_balance = 0.00;
                            $user->save();
                        }
                    }
                }
                else {
                    abort(404);
                }
            }

            /* Delete sessions */
            session()->forget('bookingIdRequest');
            session()->forget('dateFromRequest');
            session()->forget('dateToRequest');
            session()->forget('bedRequest');
            session()->forget('dormRequest');
            session()->forget('sleepRequest');
            session()->forget('halfBoardRequest');
            session()->forget('commentsRequest');
            session()->forget('sleepingPlaceRequest');
            session()->forget('prepaymentAmountRequest');
            session()->forget('availableStatus');

            return view('paymentPrepayment');
        }
        else if(session()->has('inquiryBookingSuccessStatusPrepayment') && session()->has('inquiryBookOrder')) { // Success condition for inquiry prepayment

            if(session()->has('inquiryTxId') && session()->has('inquiryUserId') && session()->has('inquiryPayByBillPossible') && session()->get('inquiryPayByBillPossible') === 'yes') {

                if( session()->has('updateInquiryPayment') && session()->get('updateInquiryPayment') === 'updateInquiryPayment' && session()->has('newBooking') ) {

                    $bookingData            = Booking::where('status', '5')
                        ->where('inquirystatus', 1)
                        ->where('typeofbooking', 1)
                        ->where('userid', session()->get('inquiryUserId'))
                        ->where('txid', session()->get('inquiryTxId'))
                        ->where('is_delete', 0)
                        ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                        ->find(session()->get('newBooking'));

                    if(!empty($bookingData)) {

                        /* Updating new booking status and payment status */
                        $bookingData->status         = '5';
                        $bookingData->payment_status = '3';
                        $bookingData->typeofbooking  = 0; // 1=> Inquiry, 0=> Not inquiry // Updating inquiry booking to normal booking
                        $bookingData->save();

                        $order = Order::where('userid', session()->get('inquiryUserId'))
                            ->where('txid', session()->get('inquiryTxId'))
                            ->where('auth_user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                            ->first();

                        if(!empty($order)) {
                            /*Updating order status*/
                            $order->order_delete = 0;
                            $order->save();

                            if($order->order_payment_method === 2) { // 1 => fully paid using money balance, 2 => Partially paid using money balance, 3 => Paid using payment gateway

                                /* Updating money balance */
                                $user = Userlist::where('is_delete', 0)->where('usrActive', '1')->find(Auth::user()->_id);
                                $user->money_balance = 0.00;
                                $user->save();
                            }
                        }
                    }
                }
                else {
                    abort(404);
                }
            }

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
     * Download prepayment bill.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function download(Request $request)
    {
        $order = Order::where('auth_user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))->where('order_delete', 0)->find($request->order_id);

        if($order) {
            $carts = Booking::select('invoice_number')
                ->where('user', new \MongoDB\BSON\ObjectID(Auth::user()->_id))
                ->where('status', '5')
                ->where('payment_status', '3')
                ->where('is_delete', 0)
                ->where('order_id', new \MongoDB\BSON\ObjectID($order->_id))
                ->get();

            /* Generating PDF */
            $html  = view('paymentPrepaymentPDF', ['order' => $order, 'carts' => $carts]);

            $pdf   = PDF::loadHTML($html);

            return $pdf->download($order->order_id. ".pdf");
        }
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
