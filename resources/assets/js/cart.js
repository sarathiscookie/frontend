/* Js for cart module */
$(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    /* Character limit for comments begin */

    if($(".forComments").length > 0) {
        var cartCommentsId = $(".forComments").map(function() {
            return $(this).data("cartid");
        }).get();

        $.each(cartCommentsId, function(key, item) {
            var text_max = 300;
            $('#textarea_feedback_'+item).css('color', 'red');
            $('#textarea_feedback_'+item).html('noch '+ text_max + ' Zeichen');

            $('#comments_'+item).keyup(function() {
                var text_length = $('#comments_'+item).val().length;
                var text_remaining = text_max - text_length;

                $('#textarea_feedback_'+item).html('noch '+ text_remaining + ' Zeichen');
            });
        })
    }

    /* Character limit for comments end */

    /* Amount calc of sleeps, beds & dorms */
    // Create our number formatter.
    var formatter = new Intl.NumberFormat('de-DE', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 2
    });

    // Sleeps calculation
    $('.jsBookCalSleep').change(function() {

        var cartIdBook      = $(this).parent().parent().data('cartid');
        var sleepsBook      = 0; // Select box value of sleeps is null for validation purpose. So value is set as 0
        var amountDaysBook  = $('.amountBookingDays_'+cartIdBook).data('amountbookingdays'); // Prepayment amount multiply with no of nights
        var cabinPrepayment = $('.amountBookingDays_'+cartIdBook).data('cabinprepayamount'); // Prepayment amount of cabin

        if($(this).val() !== ''){
            sleepsBook     = $(this).val();
        }

        var totalBook        = amountDaysBook * sleepsBook;
        var amountDeductDays = cabinPrepayment * sleepsBook;

        $( '.replaceBookingDeposit_'+cartIdBook ).attr('data-amountdaysdeduct', amountDeductDays); //Updating amount after deduct days
        $( '.replaceBookingGuest_'+cartIdBook ).html(sleepsBook);
        $( '.replaceBookingDeposit_'+cartIdBook ).html(formatter.format(totalBook));

        totalDepositCalculation();

    });

    // Beds calculation
    $('.jsBookCalBeds').change(function() {

        var cartIdBook      = $(this).parent().parent().data('cartid');
        var bedsBook        = 0; // Select box value of beds is null for validation purpose. So value is set as 0
        var dormsBook       = 0; // Select box value of dorms is null for validation purpose. So value is set as 0
        var amountDaysBook  = $('.amountBookingDays_'+cartIdBook).data('amountbookingdays'); // Prepayment amount multiply with no of nights
        var cabinPrepayment = $('.amountBookingDays_'+cartIdBook).data('cabinprepayamount'); // Prepayment amount of cabin

        if($(this).val() !== ''){
            bedsBook       = $(this).val();
        }

        if($(this).closest('div').next('div').find('select').val() !== ''){
            dormsBook      = $(this).closest('div').next('div').find('select').val(); // When beds select closest next dorms value also select
        }

        var guestBook        = parseInt(bedsBook) + parseInt(dormsBook);
        var totalBook        = guestBook * amountDaysBook;
        var amountDeductDays = cabinPrepayment * guestBook;

        $( '.replaceBookingDeposit_'+cartIdBook ).attr('data-amountdaysdeduct', amountDeductDays); //Updating amount after deduct days
        $( '.replaceBookingGuest_'+cartIdBook ).html(guestBook);
        $( '.replaceBookingDeposit_'+cartIdBook ).html(formatter.format(totalBook));

        totalDepositCalculation();

    });

    // Dormitory calculation
    $('.jsBookCalDormitory').change(function() {

        var cartIdBook      = $(this).parent().parent().data('cartid');
        var dormsBook       = 0; // Select box value of dorms is null for validation purpose. So value is set as 0
        var bedsBook        = 0; // Select box value of beds is null for validation purpose. So value is set as 0
        var amountDaysBook  = $('.amountBookingDays_'+cartIdBook).data('amountbookingdays'); // Prepayment amount multiply with no of nights
        var cabinPrepayment = $('.amountBookingDays_'+cartIdBook).data('cabinprepayamount'); // Prepayment amount of cabin

        if($(this).val() !== ''){
            dormsBook      = $(this).val();
        }

        if($(this).closest('div').prev('div').find('select').val() !== ''){
            bedsBook       = $(this).closest('div').prev('div').find('select').val(); // When dorms select closest previous beds value also select
        }

        var guestBook        = parseInt(dormsBook) + parseInt(bedsBook);
        var totalBook        = guestBook * amountDaysBook;
        var amountDeductDays = cabinPrepayment * guestBook;

        $( '.replaceBookingDeposit_'+cartIdBook ).attr('data-amountdaysdeduct', amountDeductDays); //Updating amount after deduct days
        $( '.replaceBookingGuest_'+cartIdBook ).html(guestBook);
        $( '.replaceBookingDeposit_'+cartIdBook ).html(formatter.format(totalBook));

        totalDepositCalculation();

    });

    function totalDepositCalculation()
    {
        // Helping object for env variables
        var envBook = {
            tax_one: window.environment.service_tax_one,
            tax_two: window.environment.service_tax_two,
            tax_three: window.environment.service_tax_three
        };
        var serviceTaxBook              = '';
        var totalBookingCompleteDeposit = 0;
        var totalAmountAfterDeductDays  = 0;

        $('p.bookingDeposit').each(function(){
            totalBookingCompleteDeposit += Number($(this).text().replace(/[^0-9\.-]+/g,"")); // Replace special char and euro symbol
            totalAmountAfterDeductDays += parseFloat($(this).attr('data-amountdaysdeduct'), 10); // Total amount after deduct days
        });

        console.log(totalAmountAfterDeductDays);

        var convertToStringCompleteDeposit      = totalBookingCompleteDeposit.toString(); // Convert total deposit in to string to add dot before last two number
        var twoDecimalPointAddedCompleteDeposit = convertToStringCompleteDeposit.substring(0, convertToStringCompleteDeposit.length-2)+"."+convertToStringCompleteDeposit.substring(convertToStringCompleteDeposit.length-2); // Adding dot before last two number
        var euroFormatCompleteDeposit           = formatter.format(Number(twoDecimalPointAddedCompleteDeposit)); // Sum of complete deposit convert in to euro format

        // Condition for service tax calculation
        if(totalAmountAfterDeductDays <= 30) {
            serviceTaxBook = envBook.tax_one;
        }

        if(totalAmountAfterDeductDays > 30 && totalAmountAfterDeductDays <= 100) {
            serviceTaxBook = envBook.tax_two;
        }

        if(totalAmountAfterDeductDays > 100) {
            serviceTaxBook = envBook.tax_three;
        }

        var sumPrepaymentAmountPercentage   = (serviceTaxBook / 100) * totalAmountAfterDeductDays;
        var sumPrepaymentAmountServiceTotal = Number(twoDecimalPointAddedCompleteDeposit) + sumPrepaymentAmountPercentage;

        $( '.replaceBookingCompleteDeposit' ).html(euroFormatCompleteDeposit);
        $( '.replaceBookingServiceFee' ).html(formatter.format(sumPrepaymentAmountPercentage));
        $( '.replaceBookingCompletePayment' ).html(formatter.format(sumPrepaymentAmountServiceTotal));
    }

});