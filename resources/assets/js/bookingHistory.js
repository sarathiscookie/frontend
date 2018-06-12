/* Js for booking history module */
$(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    /* Character limit for comments begin */
    var text_max = 300;
    $('#update_book_comment').css('color', 'red');
    $('#update_book_comment').html('noch '+ text_max + ' Zeichen');

    $('#comments').keyup(function() {
        var text_length = $('#comments').val().length;
        var text_remaining = text_max - text_length;

        $('#update_book_comment').html('noch '+text_remaining + ' Zeichen');
    });
    /* Character limit for comments end */

    /* Helping object for translation */
    var translations = '';

    function translation() {
        translations = {
            confirmDeleteBooking: window.environment.confirmDeleteBooking,
            deleteFailed: window.environment.deleteFailed,
            cancelBookingMoneyReturnConfirm: window.environment.cancelBookingMoneyReturnConfirm,
            cancelBookingMoneyNotReturnConfirm: window.environment.cancelBookingMoneyNotReturnConfirm
        };
        return translations;
    }

    /* Delete cancelled booking */
    $( ".deleteCancelledBookingHistory" ).on('click',  function(e){
        e.preventDefault();
        var delId = $(this).data('del');
        var $btn  = $(this).btnBootstrap('loading');
        var conf  = confirm(translation().confirmDeleteBooking);
        if (conf === true) {
            $.ajax({
                url: '/booking/history/delete/cancelled/booking',
                dataType: 'JSON',
                type: 'POST',
                data: {delId: delId}
            })
                .done(function( result ) {
                    if(result.status === 'success') {
                        $btn.btnBootstrap('reset');
                        window.location.href = '/booking/history/';
                    }
                    else {
                        $('.responseMessage').html('<div class="alert alert-warning alert-dismissible response" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>OOPS! </strong>'+translation().deleteFailed+'</div>');
                    }
                })
                .fail(function() {
                    $btn.btnBootstrap('reset');
                    $('.responseMessage').html('<div class="alert alert-warning alert-dismissible response" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>OOPS! </strong>'+translation().deleteFailed+'</div>');
                });
        }
        else {
            $btn.btnBootstrap('reset');
        }
    });

    /* Delete waiting prepayment booking */
    $( ".deleteWaitingPrepaymentBookingHistory" ).on('click',  function(e){
        e.preventDefault();
        var delId = $(this).data('delwaitingprepay');
        var $btn  = $(this).btnBootstrap('loading');
        var conf  = confirm(translation().confirmDeleteBooking);
        if (conf === true) {
            $.ajax({
                url: '/booking/history/delete/waiting/prepay',
                dataType: 'JSON',
                type: 'POST',
                data: {delId: delId}
            })
                .done(function( result ) {
                    if(result.status === 'success') {
                        $btn.btnBootstrap('reset');
                        window.location.href = '/booking/history/';
                    }
                    else {
                        $('.responseMessage').html('<div class="alert alert-warning alert-dismissible response" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>OOPS! </strong>'+translation().deleteFailed+'</div>');
                    }
                })
                .fail(function() {
                    $btn.btnBootstrap('reset');
                    $('.responseMessage').html('<div class="alert alert-warning alert-dismissible response" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>OOPS! </strong>'+translation().deleteFailed+'</div>');
                });
        }
        else {
            $btn.btnBootstrap('reset');
        }
    });

    /* Delete approved inquiry booking */
    $( ".deleteInquiryApprovedBookingHistory" ).on('click',  function(e){
        e.preventDefault();
        var delId = $(this).data('delapprovedinquiry');
        var $btn  = $(this).btnBootstrap('loading');
        var conf  = confirm(translation().confirmDeleteBooking);
        if (conf === true) {
            $.ajax({
                url: '/booking/history/delete/approved/inquiry',
                dataType: 'JSON',
                type: 'POST',
                data: {delId: delId}
            })
                .done(function( result ) {
                    if(result.status === 'success') {
                        $btn.btnBootstrap('reset');
                        window.location.href = '/booking/history/';
                    }
                    else {
                        $('.responseMessage').html('<div class="alert alert-warning alert-dismissible response" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>OOPS! </strong>'+translation().deleteFailed+'</div>');
                    }
                })
                .fail(function() {
                    $btn.btnBootstrap('reset');
                    $('.responseMessage').html('<div class="alert alert-warning alert-dismissible response" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>OOPS! </strong>'+translation().deleteFailed+'</div>');
                });
        }
        else {
            $btn.btnBootstrap('reset');
        }
    });

    /* Delete waiting inquiry booking */
    $( ".deleteInquiryWaitingBookingHistory" ).on('click',  function(e){
        e.preventDefault();
        var delId = $(this).data('delwaitinginquiry');
        var $btn  = $(this).btnBootstrap('loading');
        var conf  = confirm(translation().confirmDeleteBooking);
        if (conf === true) {
            $.ajax({
                url: '/booking/history/delete/waiting/inquiry',
                dataType: 'JSON',
                type: 'POST',
                data: {delId: delId}
            })
                .done(function( result ) {
                    if(result.status === 'success') {
                        $btn.btnBootstrap('reset');
                        window.location.href = '/booking/history/';
                    }
                    else {
                        $('.responseMessage').html('<div class="alert alert-warning alert-dismissible response" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>OOPS! </strong>'+translation().deleteFailed+'</div>');
                    }
                })
                .fail(function() {
                    $btn.btnBootstrap('reset');
                    $('.responseMessage').html('<div class="alert alert-warning alert-dismissible response" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>OOPS! </strong>'+translation().deleteFailed+'</div>');
                });
        }
        else {
            $btn.btnBootstrap('reset');
        }
    });


    /* Delete rejected inquiry booking */
    $( ".deleteInquiryRejectedBookingHistory" ).on('click',  function(e){
        e.preventDefault();
        var delId = $(this).data('delrejectedinquiry');
        var $btn  = $(this).btnBootstrap('loading');
        var conf  = confirm(translation().confirmDeleteBooking);
        if (conf === true) {
            $.ajax({
                url: '/booking/history/delete/rejected/inquiry',
                dataType: 'JSON',
                type: 'POST',
                data: {delId: delId}
            })
                .done(function( result ) {
                    if(result.status === 'success') {
                        $btn.btnBootstrap('reset');
                        window.location.href = '/booking/history/';
                    }
                    else {
                        $('.responseMessage').html('<div class="alert alert-warning alert-dismissible response" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>OOPS! </strong>'+translation().deleteFailed+'</div>');
                    }
                })
                .fail(function() {
                    $btn.btnBootstrap('reset');
                    $('.responseMessage').html('<div class="alert alert-warning alert-dismissible response" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>OOPS! </strong>'+translation().deleteFailed+'</div>');
                });
        }
        else {
            $btn.btnBootstrap('reset');
        }
    });

    /* Cancel booking and return money */
    $( ".cancelMoneyReturn" ).on('click',  function(e){
        e.preventDefault();
        var cancelId    = $(this).data('cancel');
        var returnMoney = $(this).data('return');
        var $btn        = $(this).btnBootstrap('loading');
        var conf        = '';
        if(returnMoney === 'yes') {
            conf        = confirm(translation().cancelBookingMoneyReturnConfirm);
        }
        else {
            conf        = confirm(translation().cancelBookingMoneyNotReturnConfirm);
        }

        if (conf === true) {
            $.ajax({
                url: '/booking/history/cancel',
                dataType: 'JSON',
                type: 'POST',
                data: { cancelId: cancelId }
            })
                .done(function( result ) {
                    console.log(result);
                    if(result.status === 'success') {
                        $btn.btnBootstrap('reset');
                        window.location.href = '/booking/history/';
                    }
                    else {
                        $('.responseCancelMessage_'+cancelId).html('<div class="alert alert-warning alert-dismissible response" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>OOPS! </strong>'+translation().deleteFailed+'</div>');
                    }
                })
                .fail(function() {
                    $btn.btnBootstrap('reset');
                    $('.responseCancelMessage_'+cancelId).html('<div class="alert alert-warning alert-dismissible response" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>OOPS! </strong>'+translation().deleteFailed+'</div>');
                });
        }
        else {
            $btn.btnBootstrap('reset');
        }
    });

    /* Amount calc of sleeps, beds & dorms begins */
    // Euro number formatter.
    var formatter = new Intl.NumberFormat('de-DE', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 2
    });

    function editCartTotalDepositCalc(total)
    {
        // Helping objects for env variables
        var env_for_edit_booking = {
            tax_one_for_edit_booking: window.environment.service_tax_one_for_edit_booking,
            tax_two_for_edit_booking: window.environment.service_tax_two_for_edit_booking,
            tax_three_for_edit_booking: window.environment.service_tax_three_for_edit_booking
        };

        var serviceTaxEditBooking = '';

        if(total <= 30) {
            serviceTaxEditBooking = env_for_edit_booking.tax_one_for_edit_booking;
        }

        if(total > 30 && total <= 100) {
            serviceTaxEditBooking = env_for_edit_booking.tax_two_for_edit_booking;
        }

        if(total > 100) {
            serviceTaxEditBooking = env_for_edit_booking.tax_three_for_edit_booking;
        }

        var sumPrepayAmountPerc   = (serviceTaxEditBooking / 100) * total;
        var sumPrepayAmountServiceTotal = total + sumPrepayAmountPerc;

        $( '.replaceEditBookingCompleteDeposit' ).html(formatter.format(total));
        $( '.replaceEditBookingServiceFee' ).html(serviceTaxEditBooking+' %');
        $( '.replaceEditBookingCompletePayment' ).html(formatter.format(sumPrepayAmountServiceTotal));
    }

    // Sleeps calculation
    $('.jsEditBookSleep').change(function() {

        // Days multiply with prepayment_amount
        var amountDays    = $('.amountDaysEditBook').data('amountdayseditbook');

        // Sleeps select box value is null for validation purpose. So value is set as 0
        var sleeps        = 0;

        if($(this).val() !== ''){
            sleeps    = $(this).val()
        }

        var total     = amountDays * sleeps;
        $( '.replaceEditBookingGuest' ).html(sleeps);
        $( '.replaceEditBookingDeposit' ).html(formatter.format(total));

        editCartTotalDepositCalc(total);
    });

    // Beds calculation
    $('.jsEditBookBed').change(function() {

        // Days multiply with prepayment_amount
        var amountDays = $('.amountDaysEditBook').data('amountdayseditbook');

        // Beds & Dorms select box value is null for validation purpose. So value is set as 0
        var dorms      = 0;
        var beds       = 0;
        if($(this).val() !== ''){
            beds       = $(this).val();
        }

        if($('.jsCalDorm').val() !== ''){
            dorms      = $('.jsEditBookDorm').val();
        }

        var guest      = parseInt(beds) + parseInt(dorms);
        var total      = (parseInt(beds) + parseInt(dorms)) * amountDays;

        $( '.replaceEditBookingGuest' ).html(guest);
        $( '.replaceEditBookingDeposit' ).html(formatter.format(total));

        editCartTotalDepositCalc(total);
    });

    // Dorms calculation
    $('.jsEditBookDorm').change(function() {

        // Days multiply with prepayment_amount
        var amountDays = $('.amountDaysEditBook').data('amountdayseditbook');

        // Beds & Dorms select box value is null for validation purpose. So value is set as 0
        var dorms      = 0;
        var beds       = 0;
        if($(this).val() !== ''){
            dorms      = $(this).val();
        }

        if($('.jsCalBed').val() !== ''){
            beds       = $('.jsEditBookBed').val();
        }

        var guest      = parseInt(dorms) + parseInt(beds);
        var total      = (parseInt(dorms) + parseInt(beds)) * amountDays;

        $( '.replaceEditBookingGuest' ).html(guest);
        $( '.replaceEditBookingDeposit' ).html(formatter.format(total));

        editCartTotalDepositCalc(total);
    });
    /* Amount calc of sleeps, beds & dorms end */

});