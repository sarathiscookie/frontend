/* Js for payment module */
$(function() {
    $(".moneyBalance").click(function(){

        /* Function for german number formatter. */
        var formatter = new Intl.NumberFormat('de-DE', {
            style: 'currency',
            currency: 'EUR',
            minimumFractionDigits: 2
        });

        /* Helping objects for env variables */
        var envBook = {
            tax_one: window.environment.service_tax_one,
            tax_two: window.environment.service_tax_two,
            tax_three: window.environment.service_tax_three
        };

        if($(this).is(":checked")) {
            $( ".afterRedeem" ).show();

            var redeemAmount              = $(this).parents().eq(2).data('redeem'); // We can use parent().parent().parent() also but it is slower.
            var sumPrepayAmount           = $( ".sumPrepayAmount" ).data('sumprepayamount');

            if (redeemAmount >= sumPrepayAmount) {
                var afterRedeemAmount     = redeemAmount - sumPrepayAmount;
                $( ".redeemAmount" ).html('<p class="info-listing-booking2">Redeem Amount:</p><p class="info-listing-price-booking2">'+formatter.format(redeemAmount)+'</p>');
                $( ".moneyBalance" ).html('<p class="info-listing-booking2">Money Balance:</p><p class="info-listing-price-booking2">'+formatter.format(afterRedeemAmount)+'</p>');
                $( ".afterRedeemAmount" ).html();
                $( ".sumPrepayServiceTotal" ).html();
                $( ".serviceFee" ).hide();
                $( ".totalPrepayAmount" ).hide();
            }
            else {
                var afterRedeemAmount     = sumPrepayAmount - redeemAmount;
                var serviceTaxBook        = serviceFees(afterRedeemAmount);
                var sumPrepayPercentage   = (serviceTaxBook / 100) * afterRedeemAmount;
                var sumPrepayServiceTotal = afterRedeemAmount + sumPrepayPercentage;
                $( ".redeemAmount" ).html('<p class="info-listing-booking2">Redeem Amount:</p><p class="info-listing-price-booking2">'+formatter.format(redeemAmount)+'</p>');
                $( ".moneyBalance" ).html();
                $( ".afterRedeemAmount" ).html('<p class="info-listing-booking2">Amount:</p><p class="info-listing-price-booking2">'+formatter.format(afterRedeemAmount)+'</p>');
                $( ".sumPrepayServiceTotal" ).html(formatter.format(sumPrepayServiceTotal));
                $( ".serviceFee" ).html('<p class="info-listing-booking2">Service fee:</p><p class="info-listing-price-booking2">'+serviceTaxBook+'%</p>');
                $( ".totalPrepayAmount" ).show();
            }
        }
        else {
            $( ".afterRedeem" ).hide();
            var sumPrepaymentAmount    = $( ".sumPrepayAmount" ).data('sumprepayamount');
            var serviceFee             = serviceFees(sumPrepaymentAmount);
            var sumPrepaymentPerc      = (serviceFee / 100) * sumPrepaymentAmount;
            var sumPrepaymentServTotal = sumPrepaymentAmount + sumPrepaymentPerc;

            $( ".sumPrepayServiceTotal" ).html(formatter.format(sumPrepaymentServTotal));
            $( ".serviceFee" ).show();
            $( ".totalPrepayAmount" ).show();
        }

        /* Function for service tax calculation */
        function serviceFees(sumPrepayAmount)
        {
            var serviceTaxBook = 0;

            if(sumPrepayAmount <= 30) {
                serviceTaxBook = envBook.tax_one;
            }

            if(sumPrepayAmount > 30 && sumPrepayAmount <= 100) {
                serviceTaxBook = envBook.tax_two;
            }

            if(sumPrepayAmount > 100) {
                serviceTaxBook = envBook.tax_three;
            }

            return serviceTaxBook;
        }

    });

    /* Credit card functionality */
    $("input[name='payment']").on("click", function(){
        if($(this).val() === 'creditCard') {
            $("#creditcard").show();
        }
        else {
            $("#creditcard").hide();
        }
    });


});