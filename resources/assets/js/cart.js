/* Js for cart module */
$(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    /* Deduct user money balance amount */
    $(".moneyBalance").click(function(){
        if($(this).is(":checked")) {
            $('.moneyBalanceCal').show();
            $('.normalCalculation').hide();
        }
        else {
            $('.moneyBalanceCal').hide();
            $('.normalCalculation').show();
        }
    });

    /* Amount calc of sleeps, beds & dorms */
    // Create our number formatter.
    var formatter = new Intl.NumberFormat('de-DE', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 2
    });

    // Sleeps calculation
    $('.jsBookCalSleep').change(function() {
        // Helping object for env variables
        var env = {
            tax_one: window.environment.service_tax_one,
            tax_two: window.environment.service_tax_two,
            tax_three: window.environment.service_tax_three
        };

        // Sleeps select box value is null for validation purpose. So value is set as 0
        var sleeps        = 0;

        if($(this).val() !== ''){
            sleeps    = $(this).val()
        }
        $( '.replaceBookingGuest' ).html(sleeps);
    });
});
