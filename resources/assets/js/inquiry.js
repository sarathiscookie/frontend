/* Js for inquiry module */
$(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    /* Character limit for comments begin */
    var text_max = 300;
    $('#textarea_feedback').css('color', 'red');
    $('#textarea_feedback').html('noch '+ text_max + ' Zeichen');

    $('#comments').keyup(function() {
        var text_length = $('#comments').val().length;
        var text_remaining = text_max - text_length;

        $('#textarea_feedback').html('noch '+text_remaining + ' Zeichen');
    });
    /* Character limit for comments end */

    /* Amount calc of sleeps, beds & dorms */
    // Euro number formatter.
    var formatter = new Intl.NumberFormat('de-DE', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 2
    });

    // Sleeps calculation
    $('.jsCalSleep').change(function() {

        // Days multiply with prepayment_amount
        var amountDays   = $('.amountDays').data('amountdays');

        // Prepayment amount
        var prepayAmount = $('.amountDays').data('prepayamountdeductdays');

        // Sleeps select box value is null for validation purpose. So value is set as 0
        var sleeps = 0;

        if($(this).val() !== ''){
            sleeps = $(this).val();
        }

        var total            = amountDays * sleeps;
        var amountDeductDays = prepayAmount * sleeps;

        $( '.replaceInquiryGuest' ).html(sleeps);
        $( '.replaceInquiryDeposit' ).html(formatter.format(total));

        inquiryTotalDepositCalc(total, amountDeductDays);
    });

    // Beds calculation
    $('.jsCalBed').change(function() {

        // Days multiply with prepayment_amount
        var amountDays = $('.amountDays').data('amountdays');

        // Prepayment amount
        var prepayAmount = $('.amountDays').data('prepayamountdeductdays');

        // Beds & Dorms select box value is null for validation purpose. So value is set as 0
        var dorms      = 0;
        var beds       = 0;
        if($(this).val() !== ''){
            beds       = $(this).val();
        }

        if($('.jsCalDorm').val() !== ''){
            dorms      = $('.jsCalDorm').val();
        }

        var guest            = parseInt(beds) + parseInt(dorms);
        var total            = (parseInt(beds) + parseInt(dorms)) * amountDays;
        var amountDeductDays = prepayAmount * guest;

        $( '.replaceInquiryGuest' ).html(guest);
        $( '.replaceInquiryDeposit' ).html(formatter.format(total));

        inquiryTotalDepositCalc(total, amountDeductDays);
    });

    // Dorms calculation
    $('.jsCalDorm').change(function() {

        // Days multiply with prepayment_amount
        var amountDays = $('.amountDays').data('amountdays');

        // Prepayment amount
        var prepayAmount = $('.amountDays').data('prepayamountdeductdays');

        // Beds & Dorms select box value is null for validation purpose. So value is set as 0
        var dorms      = 0;
        var beds       = 0;
        if($(this).val() !== ''){
            dorms      = $(this).val();
        }

        if($('.jsCalBed').val() !== ''){
            beds       = $('.jsCalBed').val();
        }

        var guest            = parseInt(dorms) + parseInt(beds);
        var total            = (parseInt(dorms) + parseInt(beds)) * amountDays;
        var amountDeductDays = prepayAmount * guest;

        $( '.replaceInquiryGuest' ).html(guest);
        $( '.replaceInquiryDeposit' ).html(formatter.format(total));

        inquiryTotalDepositCalc(total, amountDeductDays);
    });

    function inquiryTotalDepositCalc(total, amountDeductDays)
    {
        // Helping object for env variables
        var env = {
            tax_one: window.environment.service_tax_one,
            tax_two: window.environment.service_tax_two,
            tax_three: window.environment.service_tax_three
        };

        var serviceTax = '';

        if(amountDeductDays <= 30) {
            serviceTax = env.tax_one;
        }

        if(amountDeductDays > 30 && amountDeductDays <= 100) {
            serviceTax = env.tax_two;
        }

        if(amountDeductDays > 100) {
            serviceTax = env.tax_three;
        }

        var sumPrepaymentAmountPercentage   = (serviceTax / 100) * amountDeductDays;
        var sumPrepaymentAmountServiceTotal = total + sumPrepaymentAmountPercentage;

        $( '.replaceInquiryCompleteDeposit' ).html(formatter.format(total));
        $( '.replaceInquiryServiceFee' ).html(formatter.format(sumPrepaymentAmountPercentage));
        $( '.replaceInquiryCompletePayment' ).html(formatter.format(sumPrepaymentAmountServiceTotal));
    }
});