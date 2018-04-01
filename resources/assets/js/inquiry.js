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

    /* Calculations */
    // Create our number formatter.
    var formatter = new Intl.NumberFormat('de-DE', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 2
    });


    $('.jsCalSleep').change(function() {
        var amountDays    = $('.amountDays').data('amountdays');
        var sleeps        = $(this).val();
        var total         = amountDays * sleeps;
        $( '.replaceInquiryGuest' ).html(sleeps);
        $( '.replaceInquiryDeposit' ).html(formatter.format(total));
    });
});