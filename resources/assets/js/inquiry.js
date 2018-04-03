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
    $('#textarea_feedback').html(text_max + ' characters remaining');

    $('#comments').keyup(function() {
        var text_length = $('#comments').val().length;
        var text_remaining = text_max - text_length;

        $('#textarea_feedback').html(text_remaining + ' characters remaining');
    });
    /* Character limit for comments end */

    /* Amount calc of sleeps, beds & dorms */
    // Create our number formatter.
    var formatter = new Intl.NumberFormat('de-DE', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 2
    });

    // Sleeps calculation
    $('.jsCalSleep').change(function() {
        // Helping object for env variables
        var env = {
            tax_one: window.environment.service_tax_one,
            tax_two: window.environment.service_tax_two,
            tax_three: window.environment.service_tax_three
        };

        // Days multiply with prepayment_amount
        var amountDays    = $('.amountDays').data('amountdays');

        var serviceTax    = '';

        // Sleeps select box value is null for validation purpose. So value is set as 0
        var sleeps        = 0;

        if($(this).val() !== ''){
            sleeps    = $(this).val()
        }

        var total     = amountDays * sleeps;
        $( '.replaceInquiryGuest' ).html(sleeps);
        $( '.replaceInquiryDeposit' ).html(formatter.format(total));
        if(total <= 30) {
            serviceTax = env.tax_one;
        }

        if(total > 30 && total <= 100) {
            serviceTax = env.tax_two;
        }

        if(total > 100) {
            serviceTax = env.tax_three;
        }

        var sumPrepaymentAmountPercentage   = (serviceTax / 100) * total;
        var sumPrepaymentAmountServiceTotal = total + sumPrepaymentAmountPercentage;

        $( '.replaceInquiryCompleteDeposit' ).html(formatter.format(total));
        $( '.replaceInquiryServiceFee' ).html(serviceTax+' %');
        $( '.replaceInquiryCompletePayment' ).html(formatter.format(sumPrepaymentAmountServiceTotal));
    });

    // Beds calculation
    $('.jsCalBed').change(function() {
        // Helping object for env variables
        var env = {
            tax_one: window.environment.service_tax_one,
            tax_two: window.environment.service_tax_two,
            tax_three: window.environment.service_tax_three
        };

        // Days multiply with prepayment_amount
        var amountDays = $('.amountDays').data('amountdays');

        var serviceTax = '';

        // Beds & Dorms select box value is null for validation purpose. So value is set as 0
        var dorms      = 0;
        var beds       = 0;
        if($(this).val() !== ''){
            beds       = $(this).val();
        }

        if($('.jsCalDorm').val() !== ''){
            dorms      = $('.jsCalDorm').val();
        }

        var guest      = parseInt(beds) + parseInt(dorms);
        var total      = (parseInt(beds) + parseInt(dorms)) * amountDays;

        $( '.replaceInquiryGuest' ).html(guest);
        $( '.replaceInquiryDeposit' ).html(formatter.format(total));

        if(total <= 30) {
            serviceTax = env.tax_one;
        }

        if(total > 30 && total <= 100) {
            serviceTax = env.tax_two;
        }

        if(total > 100) {
            serviceTax = env.tax_three;
        }

        var sumPrepaymentAmountPercentage   = (serviceTax / 100) * total;
        var sumPrepaymentAmountServiceTotal = total + sumPrepaymentAmountPercentage;

        $( '.replaceInquiryCompleteDeposit' ).html(formatter.format(total));
        $( '.replaceInquiryServiceFee' ).html(serviceTax+' %');
        $( '.replaceInquiryCompletePayment' ).html(formatter.format(sumPrepaymentAmountServiceTotal));
    });

    // Dorms calculation
    $('.jsCalDorm').change(function() {
        // Helping object for env variables
        var env = {
            tax_one: window.environment.service_tax_one,
            tax_two: window.environment.service_tax_two,
            tax_three: window.environment.service_tax_three
        };

        // Days multiply with prepayment_amount
        var amountDays = $('.amountDays').data('amountdays');

        var serviceTax = '';

        // Beds & Dorms select box value is null for validation purpose. So value is set as 0
        var dorms      = 0;
        var beds       = 0;
        if($(this).val() !== ''){
            dorms      = $(this).val();
        }

        if($('.jsCalBed').val() !== ''){
            beds       = $('.jsCalBed').val();
        }

        var guest      = parseInt(dorms) + parseInt(beds);
        var total      = (parseInt(dorms) + parseInt(beds)) * amountDays;

        $( '.replaceInquiryGuest' ).html(guest);
        $( '.replaceInquiryDeposit' ).html(formatter.format(total));

        if(total <= 30) {
            serviceTax = env.tax_one;
        }

        if(total > 30 && total <= 100) {
            serviceTax = env.tax_two;
        }

        if(total > 100) {
            serviceTax = env.tax_three;
        }

        var sumPrepaymentAmountPercentage   = (serviceTax / 100) * total;
        var sumPrepaymentAmountServiceTotal = total + sumPrepaymentAmountPercentage;

        $( '.replaceInquiryCompleteDeposit' ).html(formatter.format(total));
        $( '.replaceInquiryServiceFee' ).html(serviceTax+' %');
        $( '.replaceInquiryCompletePayment' ).html(formatter.format(sumPrepaymentAmountServiceTotal));
    });
});