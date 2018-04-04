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
        var envBook = {
            tax_one: window.environment.service_tax_one,
            tax_two: window.environment.service_tax_two,
            tax_three: window.environment.service_tax_three
        };

        var cartIdBook     = $(this).parent().parent().data('cartid');
        var serviceTaxBook = '';
        var sleepsBook     = 0; // Sleeps select box value is null for validation purpose. So value is set as 0
        var amountDaysBook = $('.amountBookingDays_'+cartIdBook).data('amountbookingdays');
        if($(this).val() !== ''){
            sleepsBook     = $(this).val()
        }

        var totalBook      = amountDaysBook * sleepsBook;

        $( '.replaceBookingGuest_'+cartIdBook ).html(sleepsBook);
        $( '.replaceBookingDeposit_'+cartIdBook ).html(formatter.format(totalBook));

        var totalBookingCompleteDeposit = 0;

        $('p.bookingDeposit').each(function(){
            totalBookingCompleteDeposit += Number($(this).text().replace(/[^0-9\.-]+/g,"")); // Replace special char and euro symbol
        });

        var convertToStringCompleteDeposit      = totalBookingCompleteDeposit.toString(); // Convert total deposit in to string to add dot before last two number
        var twoDecimalPointAddedCompleteDeposit = convertToStringCompleteDeposit.substring(0, convertToStringCompleteDeposit.length-2)+"."+convertToStringCompleteDeposit.substring(convertToStringCompleteDeposit.length-2); // Adding dot before last two number
        var euroFormatCompleteDeposit           = formatter.format(Number(twoDecimalPointAddedCompleteDeposit));

        if(Number(twoDecimalPointAddedCompleteDeposit) <= 30) {
            serviceTaxBook = envBook.tax_one;
        }

        if(Number(twoDecimalPointAddedCompleteDeposit) > 30 && totalBook <= 100) {
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

    });

    // Beds calculation
    $('.jsBookCalBeds').change(function() {
        // Helping object for env variables
        var envBook = {
            tax_one: window.environment.service_tax_one,
            tax_two: window.environment.service_tax_two,
            tax_three: window.environment.service_tax_three
        };

        var cartIdBook     = $(this).parent().parent().data('cartid');
        var serviceTaxBook = '';
        var bedsBook       = 0; // Beds select box value is null for validation purpose. So value is set as 0
        var dormsBook      = 0; // Dorms select box value is null for validation purpose. So value is set as 0
        var amountDaysBook = $('.amountBookingDays_'+cartIdBook).data('amountbookingdays');

        if($(this).val() !== ''){
            bedsBook       = $(this).val()
        }

        if($(this).closest('div').next('div').find('select').val() !== ''){
            dormsBook      = $(this).closest('div').next('div').find('select').val(); // When beds select closest next dorm value also select
        }

        var guestBook      = parseInt(bedsBook) + parseInt(dormsBook);
        var totalBook      = (parseInt(bedsBook) + parseInt(dormsBook)) * amountDaysBook;

        $( '.replaceBookingGuest_'+cartIdBook ).html(guestBook);
        $( '.replaceBookingDeposit_'+cartIdBook ).html(formatter.format(totalBook));

        var totalBookingCompleteDeposit = 0;

        $('p.bookingDeposit').each(function(){
            totalBookingCompleteDeposit += Number($(this).text().replace(/[^0-9\.-]+/g,"")); // Replace special char and euro symbol
        });

        var convertToStringCompleteDeposit      = totalBookingCompleteDeposit.toString(); // Convert total deposit in to string to add dot before last two number
        var twoDecimalPointAddedCompleteDeposit = convertToStringCompleteDeposit.substring(0, convertToStringCompleteDeposit.length-2)+"."+convertToStringCompleteDeposit.substring(convertToStringCompleteDeposit.length-2); // Adding dot before last two number
        var euroFormatCompleteDeposit           = formatter.format(Number(twoDecimalPointAddedCompleteDeposit));

        if(Number(twoDecimalPointAddedCompleteDeposit) <= 30) {
            serviceTaxBook = envBook.tax_one;
        }

        if(Number(twoDecimalPointAddedCompleteDeposit) > 30 && totalBook <= 100) {
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

    });

    // Dormitory calculation
    $('.jsBookCalDormitory').change(function() {
        // Helping object for env variables
        var envBook = {
            tax_one: window.environment.service_tax_one,
            tax_two: window.environment.service_tax_two,
            tax_three: window.environment.service_tax_three
        };

        var cartIdBook     = $(this).parent().parent().data('cartid');
        var serviceTaxBook = '';
        var dormsBook      = 0; // Dorms select box value is null for validation purpose. So value is set as 0
        var bedsBook       = 0; // Beds select box value is null for validation purpose. So value is set as 0
        var amountDaysBook = $('.amountBookingDays_'+cartIdBook).data('amountbookingdays');

        if($(this).val() !== ''){
            dormsBook      = $(this).val()
        }

        if($(this).closest('div').prev('div').find('select').val() !== ''){
            bedsBook       = $(this).closest('div').prev('div').find('select').val(); // When dorms select closest previous dorm value also select
        }

        var guestBook      = parseInt(dormsBook) + parseInt(bedsBook);
        var totalBook      = (parseInt(dormsBook) + parseInt(bedsBook)) * amountDaysBook;

        $( '.replaceBookingGuest_'+cartIdBook ).html(guestBook);
        $( '.replaceBookingDeposit_'+cartIdBook ).html(formatter.format(totalBook));

        var totalBookingCompleteDeposit = 0;

        $('p.bookingDeposit').each(function(){
            totalBookingCompleteDeposit += Number($(this).text().replace(/[^0-9\.-]+/g,"")); // Replace special char and euro symbol
        });

        var convertToStringCompleteDeposit      = totalBookingCompleteDeposit.toString(); // Convert total deposit in to string to add dot before last two number
        var twoDecimalPointAddedCompleteDeposit = convertToStringCompleteDeposit.substring(0, convertToStringCompleteDeposit.length-2)+"."+convertToStringCompleteDeposit.substring(convertToStringCompleteDeposit.length-2); // Adding dot before last two number
        var euroFormatCompleteDeposit           = formatter.format(Number(twoDecimalPointAddedCompleteDeposit));

        if(Number(twoDecimalPointAddedCompleteDeposit) <= 30) {
            serviceTaxBook = envBook.tax_one;
        }

        if(Number(twoDecimalPointAddedCompleteDeposit) > 30 && totalBook <= 100) {
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

    });

});