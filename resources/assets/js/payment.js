/* Js for payment module */
$(function() {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    /* Function for german number formatter. */
    var formatterPayment = new Intl.NumberFormat('de-DE', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 2
    });

    /* Helping objects for env variables */
    function variables() {
        var envBook = {
            tax_one: window.environment.service_tax_one,
            tax_two: window.environment.service_tax_two,
            tax_three: window.environment.service_tax_three,
            service_tax_paybybill_one: window.environment.service_tax_paybybill_one,
            service_tax_paybybill_two: window.environment.service_tax_paybybill_two,
            service_tax_paybybill_three: window.environment.service_tax_paybybill_three,
            service_tax_sofort_one: window.environment.service_tax_sofort_one,
            service_tax_sofort_two: window.environment.service_tax_sofort_two,
            service_tax_sofort_three: window.environment.service_tax_sofort_three,
            service_tax_paydirect_one: window.environment.service_tax_paydirect_one,
            service_tax_paydirect_two: window.environment.service_tax_paydirect_two,
            service_tax_paydirect_three: window.environment.service_tax_paydirect_three,
            service_tax_paypal_one: window.environment.service_tax_paypal_one,
            service_tax_paypal_two: window.environment.service_tax_paypal_two,
            service_tax_paypal_three: window.environment.service_tax_paypal_three,
            service_tax_creditcard_one: window.environment.service_tax_creditcard_one,
            service_tax_creditcard_two: window.environment.service_tax_creditcard_two,
            service_tax_creditcard_three: window.environment.service_tax_creditcard_three,
            redeemedAmountPayment: window.environment.redeemedAmountPayment,
            moneyBalancePayment: window.environment.moneyBalancePayment,
            serviceFeePayment: window.environment.serviceFeePayment,
            amountPayment: window.environment.amountPayment
        };

        return envBook;
    }

    var paymentChoosePassAmount           = $( ".sumPrepayAmount" ).data('sumprepayamount'); // If click on any payment method, pass amount to function to calculate service fee.
    var paymentChoosePassAmountDeductDays = $( ".sumPrepayAmount" ).data('amountafterdeductdays'); // If click on any payment method, pass amount to function to calculate service fee.

    // Function for calculating amount after click or property true of money balance checkbox
    function checkingPropertyCheckbox()
    {
        if($(".moneyBalanceCheckbox").is(":checked")) {
            $( ".afterRedeem" ).show();

            var redeemAmount              = $(".moneyBalanceCheckbox").parents().eq(2).data('redeem'); // We can use parent().parent().parent() also but it is slower.
            var sumPrepayAmount           = $( ".sumPrepayAmount" ).data('sumprepayamount');

            if (redeemAmount >= sumPrepayAmount) {
                var afterRedeemAmount     = redeemAmount - sumPrepayAmount;
                $( ".redeemAmount" ).html('<p class="info-listing-booking2">'+variables().redeemedAmountPayment+':</p><p class="info-listing-price-booking2">'+formatterPayment.format(redeemAmount)+'</p>');
                $( ".moneyBalance" ).html('<p class="info-listing-booking2">'+variables().moneyBalancePayment+':</p><p class="info-listing-price-booking2">'+formatterPayment.format(afterRedeemAmount)+'</p>');
                $( ".afterRedeemAmount" ).html();
                $( ".sumPrepayServiceTotal" ).html();
                $( ".serviceFee" ).hide();
                $( ".totalPrepayAmount" ).hide();
            }
            else {
                $( ".serviceFee" ).show();
                var afterRedeemAmount             = sumPrepayAmount - redeemAmount;
                var deductedDays                  = $( ".sumPrepayAmount" ).data('deducteddays');
                var afterRedeemAmountWithoutDays  = afterRedeemAmount / deductedDays;
                var paymentMethod                 = $( ".serviceFee" ).attr('data-paymentmethod');
                var serviceTaxBook                = serviceFees(afterRedeemAmountWithoutDays, paymentMethod);
                paymentChoosePassAmount           = afterRedeemAmount; // If click on any payment method, pass amount to function to calculate service fee.
                paymentChoosePassAmountDeductDays = afterRedeemAmountWithoutDays; // If click on any payment method, pass amount to function to calculate service fee.
                var sumPrepayPercentage           = (serviceTaxBook / 100) * afterRedeemAmountWithoutDays;
                var sumPrepayServiceTotal         = afterRedeemAmount + sumPrepayPercentage;
                $( ".redeemAmount" ).html('<p class="info-listing-booking2">'+variables().redeemedAmountPayment+':</p><p class="info-listing-price-booking2">'+formatterPayment.format(redeemAmount)+'</p>');
                $( ".moneyBalance" ).html();
                $( ".afterRedeemAmount" ).html('<p class="info-listing-booking2">'+variables().amountPayment+':</p><p class="info-listing-price-booking2">'+formatterPayment.format(afterRedeemAmount)+'</p>');
                $( ".sumPrepayServiceTotal" ).html(formatterPayment.format(sumPrepayServiceTotal));
                $( ".serviceFee" ).html('<p class="info-listing-booking2">'+variables().serviceFeePayment+':</p><p class="info-listing-price-booking2">'+formatterPayment.format(sumPrepayPercentage)+'</p>');
                $( ".totalPrepayAmount" ).show();
            }
        }
        else {
            $( ".afterRedeem" ).hide();
            $( ".serviceFee" ).show();
            var sumPrepaymentAmount           = $( ".sumPrepayAmount" ).data('sumprepayamount');
            var amountAfterDeductDays         = $( ".sumPrepayAmount" ).data('amountafterdeductdays');
            var paymentMethod                 = $( ".serviceFee" ).attr('data-paymentmethod');
            var serviceFee                    = serviceFees(amountAfterDeductDays, paymentMethod);
            paymentChoosePassAmount           = sumPrepaymentAmount; // If click on any payment method, pass amount to function to calculate service fee.
            paymentChoosePassAmountDeductDays = amountAfterDeductDays; // If click on any payment method, pass amount to function to calculate service fee.
            var sumPrepaymentPerc             = (serviceFee / 100) * amountAfterDeductDays;
            var sumPrepaymentServTotal        = sumPrepaymentAmount + sumPrepaymentPerc;

            $( ".sumPrepayServiceTotal" ).html(formatterPayment.format(sumPrepaymentServTotal));
            $( ".serviceFee" ).html('<p class="info-listing-booking2">'+variables().serviceFeePayment+':</p><p class="info-listing-price-booking2">'+formatterPayment.format(sumPrepaymentPerc)+'</p>');
            $( ".totalPrepayAmount" ).show();
        }
    }

    // Redeem amount functionality works when user clicked on checkbox
    $(".moneyBalanceCheckbox").click(function(){
        checkingPropertyCheckbox();
    });

    // If money balance checkbox property is selected on page load then amount will calculate and show.
    if($(".moneyBalanceCheckbox").prop("checked") === true){
        checkingPropertyCheckbox();
    }

    /* When click on payment method pass amount and calculate service fee. Open hide credit card iframe */
    $("input[name='payment']").on("click", function(){
        checkingPropertyRadiobutton($(this).val())
    });

    // If payment type radio button property is selected on page load then amount will calculate and show.
    if($("input[name='payment']:checked").val()){
        checkingPropertyRadiobutton($("input[name='payment']:checked").val())
    }

    // Function for calculating amount after click or property true of payment type radio button
    function checkingPropertyRadiobutton(val)
    {
        $( ".serviceFee" ).attr('data-paymentmethod', val);

        if(val === 'creditCard') {
            $("#creditcard").show();
        }
        else {
            $("#creditcard").hide();
        }

        var serviceFeePayMethod   = serviceFees(paymentChoosePassAmountDeductDays, val);
        var sumPayMethodPerc      = (serviceFeePayMethod / 100) * paymentChoosePassAmountDeductDays;
        var sumPayMethodServTotal = paymentChoosePassAmount + sumPayMethodPerc;
        $( ".serviceFee" ).html('<p class="info-listing-booking2">'+variables().serviceFeePayment+':</p><p class="info-listing-price-booking2">'+formatterPayment.format(sumPayMethodPerc)+'</p>');
        $( ".sumPrepayServiceTotal" ).html(formatterPayment.format(sumPayMethodServTotal));
    }

    /* Function to service tax calculation */
    function serviceFees(sumPrepayAmount, paymentMethod)
    {
        paymentMethod = paymentMethod || '';

        var serviceTaxBook = 0;

        if(paymentMethod === 'payByBill') {
            if(sumPrepayAmount <= 30) {
                serviceTaxBook = variables().service_tax_paybybill_one;
            }

            if(sumPrepayAmount > 30 && sumPrepayAmount <= 100) {
                serviceTaxBook = variables().service_tax_paybybill_two;
            }

            if(sumPrepayAmount > 100) {
                serviceTaxBook = variables().service_tax_paybybill_three;
            }
        }
        else if(paymentMethod === 'sofort') {
            if(sumPrepayAmount <= 30) {
                serviceTaxBook = variables().service_tax_sofort_one;
            }

            if(sumPrepayAmount > 30 && sumPrepayAmount <= 100) {
                serviceTaxBook = variables().service_tax_sofort_two;
            }

            if(sumPrepayAmount > 100) {
                serviceTaxBook = variables().service_tax_sofort_three;
            }
        }
        else if(paymentMethod === 'payDirect') {
            if(sumPrepayAmount <= 30) {
                serviceTaxBook = variables().service_tax_paydirect_one;
            }

            if(sumPrepayAmount > 30 && sumPrepayAmount <= 100) {
                serviceTaxBook = variables().service_tax_paydirect_two;
            }

            if(sumPrepayAmount > 100) {
                serviceTaxBook = variables().service_tax_paydirect_three;
            }
        }
        else if(paymentMethod === 'payPal') {
            if(sumPrepayAmount <= 30) {
                serviceTaxBook = variables().service_tax_paypal_one;
            }

            if(sumPrepayAmount > 30 && sumPrepayAmount <= 100) {
                serviceTaxBook = variables().service_tax_paypal_two;
            }

            if(sumPrepayAmount > 100) {
                serviceTaxBook = variables().service_tax_paypal_three;
            }
        }
        else if(paymentMethod === 'creditCard') {
            if(sumPrepayAmount <= 30) {
                serviceTaxBook = variables().service_tax_creditcard_one;
            }

            if(sumPrepayAmount > 30 && sumPrepayAmount <= 100) {
                serviceTaxBook = variables().service_tax_creditcard_two;
            }

            if(sumPrepayAmount > 100) {
                serviceTaxBook = variables().service_tax_creditcard_three;
            }
        }
        else {
            if(sumPrepayAmount <= 30) {
                serviceTaxBook = variables().tax_one;
            }

            if(sumPrepayAmount > 30 && sumPrepayAmount <= 100) {
                serviceTaxBook = variables().tax_two;
            }

            if(sumPrepayAmount > 100) {
                serviceTaxBook = variables().tax_three;
            }
        }
        return serviceTaxBook;
    }
});