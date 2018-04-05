/* Js for cart module */
$(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
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

        var cartIdBook     = $(this).parent().parent().data('cartid');
        var sleepsBook     = 0; // Select box value of sleeps is null for validation purpose. So value is set as 0
        var amountDaysBook = $('.amountBookingDays_'+cartIdBook).data('amountbookingdays'); // Prepayment amount multiply with no of nights
        if($(this).val() !== ''){
            sleepsBook     = $(this).val()
        }

        var totalBook      = amountDaysBook * sleepsBook;

        $( '.replaceBookingGuest_'+cartIdBook ).html(sleepsBook);
        $( '.replaceBookingDeposit_'+cartIdBook ).html(formatter.format(totalBook));

        totalDepositCalculation()

    });

    // Beds calculation
    $('.jsBookCalBeds').change(function() {

        var cartIdBook     = $(this).parent().parent().data('cartid');
        var bedsBook       = 0; // Select box value of beds is null for validation purpose. So value is set as 0
        var dormsBook      = 0; // Select box value of dorms is null for validation purpose. So value is set as 0
        var amountDaysBook = $('.amountBookingDays_'+cartIdBook).data('amountbookingdays'); // Prepayment amount multiply with no of nights

        if($(this).val() !== ''){
            bedsBook       = $(this).val()
        }

        if($(this).closest('div').next('div').find('select').val() !== ''){
            dormsBook      = $(this).closest('div').next('div').find('select').val(); // When beds select closest next dorms value also select
        }

        var guestBook      = parseInt(bedsBook) + parseInt(dormsBook);
        var totalBook      = guestBook * amountDaysBook;

        $( '.replaceBookingGuest_'+cartIdBook ).html(guestBook);
        $( '.replaceBookingDeposit_'+cartIdBook ).html(formatter.format(totalBook));

        totalDepositCalculation()

    });

    // Dormitory calculation
    $('.jsBookCalDormitory').change(function() {

        var cartIdBook     = $(this).parent().parent().data('cartid');
        var dormsBook      = 0; // Select box value of dorms is null for validation purpose. So value is set as 0
        var bedsBook       = 0; // Select box value of beds is null for validation purpose. So value is set as 0
        var amountDaysBook = $('.amountBookingDays_'+cartIdBook).data('amountbookingdays'); // Prepayment amount multiply with no of nights

        if($(this).val() !== ''){
            dormsBook      = $(this).val()
        }

        if($(this).closest('div').prev('div').find('select').val() !== ''){
            bedsBook       = $(this).closest('div').prev('div').find('select').val(); // When dorms select closest previous beds value also select
        }

        var guestBook      = parseInt(dormsBook) + parseInt(bedsBook);
        var totalBook      = guestBook * amountDaysBook;

        $( '.replaceBookingGuest_'+cartIdBook ).html(guestBook);
        $( '.replaceBookingDeposit_'+cartIdBook ).html(formatter.format(totalBook));

        totalDepositCalculation()

    });

    function totalDepositCalculation()
    {
        // Helping object for env variables
        var envBook = {
            tax_one: window.environment.service_tax_one,
            tax_two: window.environment.service_tax_two,
            tax_three: window.environment.service_tax_three
        };
        var serviceTaxBook                      = '';
        var totalBookingCompleteDeposit         = 0;

        $('p.bookingDeposit').each(function(){
            totalBookingCompleteDeposit += Number($(this).text().replace(/[^0-9\.-]+/g,"")); // Replace special char and euro symbol
        });

        var convertToStringCompleteDeposit      = totalBookingCompleteDeposit.toString(); // Convert total deposit in to string to add dot before last two number
        var twoDecimalPointAddedCompleteDeposit = convertToStringCompleteDeposit.substring(0, convertToStringCompleteDeposit.length-2)+"."+convertToStringCompleteDeposit.substring(convertToStringCompleteDeposit.length-2); // Adding dot before last two number
        var euroFormatCompleteDeposit           = formatter.format(Number(twoDecimalPointAddedCompleteDeposit)); // Sum of complete deposit convert in to euro format

        // Condition for service tax calculation
        if(Number(twoDecimalPointAddedCompleteDeposit) <= 30) {
            serviceTaxBook = envBook.tax_one;
        }

        if(Number(twoDecimalPointAddedCompleteDeposit) > 30 && Number(twoDecimalPointAddedCompleteDeposit) <= 100) {
            serviceTaxBook = envBook.tax_two;
        }

        if(Number(twoDecimalPointAddedCompleteDeposit) > 100) {
            serviceTaxBook = envBook.tax_three;
        }

        var sumPrepaymentAmountPercentage       = (serviceTaxBook / 100) * Number(twoDecimalPointAddedCompleteDeposit);
        var sumPrepaymentAmountServiceTotal     = Number(twoDecimalPointAddedCompleteDeposit) + sumPrepaymentAmountPercentage;

        $( '.replaceBookingCompleteDeposit' ).html(euroFormatCompleteDeposit);
        $( '.replaceBookingServiceFee' ).html(serviceTaxBook+' %');
        $( '.replaceBookingCompletePayment' ).html(formatter.format(sumPrepaymentAmountServiceTotal));
    }

});