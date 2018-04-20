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

            var serviceTaxBook  = 0;
            var redeemAmount    = $(this).parents().eq(2).data('redeem'); // We can use parent().parent().parent() also but it is slower.
            var sumPrepayAmount = $( ".sumPrepayAmount" ).data('sumprepayamount');

            /* Condition for service tax calculation begin */
            if(sumPrepayAmount <= 30) {
                serviceTaxBook = envBook.tax_one;
            }

            if(sumPrepayAmount > 30 && sumPrepayAmount <= 100) {
                serviceTaxBook = envBook.tax_two;
            }

            if(sumPrepayAmount > 100) {
                serviceTaxBook = envBook.tax_three;
            }
            /* Condition for service tax calculation end */

            if (redeemAmount > sumPrepayAmount) {
                var afterRedeemAmount     = redeemAmount - sumPrepayAmount;
                var sumPrepayPercentage   = (serviceTaxBook / 100) * afterRedeemAmount;
                var sumPrepayServiceTotal = afterRedeemAmount + sumPrepayPercentage;
                $( ".reducedAmount" ).html(formatter.format(redeemAmount));
                $( ".afterRedeemAmount" ).html(formatter.format(afterRedeemAmount));
                $( ".sumPrepayServiceTotal" ).html(formatter.format(sumPrepayServiceTotal));
            }
            else {
                var afterRedeemAmount     = sumPrepayAmount - redeemAmount;
                var sumPrepayPercentage   = (serviceTaxBook / 100) * afterRedeemAmount;
                var sumPrepayServiceTotal = afterRedeemAmount + sumPrepayPercentage;
                $( ".reducedAmount" ).html(formatter.format(redeemAmount));
                $( ".afterRedeemAmount" ).html(formatter.format(afterRedeemAmount));
                $( ".sumPrepayServiceTotal" ).html(formatter.format(sumPrepayServiceTotal));
            }
        }
        else {
            $( ".afterRedeem" ).hide();
        }
    });
});