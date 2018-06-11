/* Js for booking history module */
$(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

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

});