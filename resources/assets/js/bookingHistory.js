/* Js for booking history module */
$(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });


    function convertDate(dateString){
        var dateSplit = dateString.split('.');
        var date      = new Date("20"+dateSplit[2], dateSplit[1]-1, dateSplit[0]); //Here 20 hard coded because date posting from form is d.m.y (eg: 03.12.18). Firefox year format is YYYY (eg 03.12.2018). If we didn't hardcoded 20 Firefox will think that year is 1918.
        return date;
    }

    /* Calendar availability check begin */
    $("body").on("mousedown", ".dateFromEditBook", function() {
        var dataId          = $(this).parent().parent().data("id");
        var $this           = $("#dateFromEditBook_"+dataId);
        var returnResult    = [];

        var holidayDates    = $(".holidayEditBook_"+dataId).data("holiday");
        var greenDates      = $(".greenEditBook_"+dataId).data("green");
        var orangeDates     = $(".orangeEditBook_"+dataId).data("orange");
        var redDates        = $(".redEditBook_"+dataId).data("red");
        var not_season_time = $(".notSeasonTimeEditBook_"+dataId).data("notseasontime");
        var start_date      = '';

        $this.datepicker({
            showAnim: "drop",
            dateFormat: "dd.mm.y",
            changeMonth: true,
            changeYear: true,
            monthNames: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
            monthNamesShort: ["Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez"],
            dayNamesMin: ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"],
            minDate: '+1d',
            yearRange: "0:+2"
        });

        $this.datepicker("option", "onSelect", function(date) {
            var dt2       = $("#dateToEditBook_"+dataId);
            var startDate = $this.datepicker('getDate');
            var minDate   = $this.datepicker('getDate');
            dt2.datepicker('setDate', minDate);
            startDate.setDate(startDate.getDate() + 60); //sets dt2 maxDate to the last day of 60 days window
            minDate.setDate(minDate.getDate() + 1); //sets dt2 minDate to the +1 day of from date
            dt2.datepicker('option', 'maxDate', startDate);
            dt2.datepicker('option', 'minDate', minDate);

            // Taking date difference and calculating amount while changing dates
            var dateOne   = convertDate(date);
            var dateTwo   = convertDate(dt2.val());
            var timeDiff  = Math.abs(dateTwo.getTime() - dateOne.getTime());
            var diffDays  = Math.ceil(timeDiff / (1000 * 3600 * 24));

            if(dateOne < dateTwo) {
                $(".daysEditBook").attr("data-days", diffDays);
                $(".replaceNumberOfNights").html(diffDays+' Tag(e)');
                var newDiffDays = $(".daysEditBook").attr("data-days");
                calculateAmount(newDiffDays);
            }
            /*else {
                alert('Ankunftsdatum muss vor dem Abreisedatum liegen.')
            }*/

        });

        $this.datepicker("option", "onChangeMonthYear", function(year,month,inst) {
            if (year != undefined && month != undefined) {
                start_date = year +'-';
                start_date += month +'-';
                start_date += '01';
            }
            $.ajax({
                url: '/calendar/ajax',
                dataType: 'JSON',
                type: 'POST',
                data: { dateFrom: start_date, dataId: dataId },
                success: function (response) {
                    for (var i = 0; i < response.holidayDates.length; i++) {
                        holidayDates.push(response.holidayDates[i]);
                    }

                    for (var i = 0; i < response.greenDates.length; i++) {
                        greenDates.push(response.greenDates[i]);
                    }

                    for (var i = 0; i < response.orangeDates.length; i++) {
                        orangeDates.push(response.orangeDates[i]);
                    }

                    for (var i = 0; i < response.redDates.length; i++) {
                        redDates.push(response.redDates[i]);
                    }

                    for (var i = 0; i < response.not_season_time.length; i++) {
                        not_season_time.push(response.not_season_time[i]);
                    }

                    $this.datepicker("refresh");
                }
            });
        });

        $this.datepicker("option", "beforeShowDay", function(date) {
            var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
            if( greenDates.indexOf(string) >=0 ) {
                returnResult = [true, "greenDates", "Verfügbar"];
            }
            if( orangeDates.indexOf(string) >=0 ) {
                returnResult = [true, "orangeDates", "Begrenzt"];
            }
            if( redDates.indexOf(string) >=0 ) {
                returnResult = [true, "redDates", "Ausgebucht"];
            }
            if( not_season_time.indexOf(string) >=0 ) {
                returnResult = [false, "", "Geschlossen"];
            }
            if( holidayDates.indexOf(string) >=0 ) {
                returnResult = [false, "", "Ruhetag"];
            }
            return returnResult;
        });

        $this.datepicker("show");
    });


    $("body").on("mousedown", ".dateToEditBook", function() {
        var dataId          = $(this).parent().parent().data("id");
        var $this           = $("#dateToEditBook_"+dataId);
        var returnResults   = [];

        var holidayDates    = $(".holidayEditBook_"+dataId).data("holiday");
        var greenDates      = $(".greenEditBook_"+dataId).data("green");
        var orangeDates     = $(".orangeEditBook_"+dataId).data("orange");
        var redDates        = $(".redEditBook_"+dataId).data("red");
        var not_season_time = $(".notSeasonTimeEditBook_"+dataId).data("notseasontime");
        var start_date      = '';

        $this.datepicker({
            showAnim: "drop",
            dateFormat: "dd.mm.y",
            changeMonth: true,
            changeYear: true,
            monthNames: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
            monthNamesShort: ["Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez"],
            dayNamesMin: ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"],
            yearRange: "0:+2"
        });

        // Taking date difference and calculating amount while changing dates
        $this.datepicker("option", "onSelect", function(date) {
            var dt1         = $("#dateFromEditBook_"+dataId);
            var dateOne     = convertDate(dt1.val());
            var dateTwo     = convertDate(date);
            var timeDiff    = Math.abs(dateTwo.getTime() - dateOne.getTime());
            var diffDays    = Math.ceil(timeDiff / (1000 * 3600 * 24));

            if(dateOne < dateTwo) {
                $(".daysEditBook").attr("data-days", diffDays);
                $(".replaceNumberOfNights").html(diffDays+' Tag(e)');
                var newDiffDays = $(".daysEditBook").attr("data-days");
                calculateAmount(newDiffDays);
            }
            /*else {
                alert('Ankunftsdatum muss vor dem Abreisedatum liegen.')
            }*/

        });

        $this.datepicker("option", "onChangeMonthYear", function(year,month,inst) {
            if (year != undefined && month != undefined) {
                start_date = year +'-';
                start_date += month +'-';
                start_date += '01';
            }
            $.ajax({
                url: '/calendar/ajax',
                dataType: 'JSON',
                type: 'POST',
                data: { dateFrom: start_date, dataId: dataId },
                success: function (response) {
                    for (var i = 0; i < response.holidayDates.length; i++) {
                        holidayDates.push(response.holidayDates[i]);
                    }

                    for (var i = 0; i < response.greenDates.length; i++) {
                        greenDates.push(response.greenDates[i]);
                    }

                    for (var i = 0; i < response.orangeDates.length; i++) {
                        orangeDates.push(response.orangeDates[i]);
                    }

                    for (var i = 0; i < response.redDates.length; i++) {
                        redDates.push(response.redDates[i]);
                    }

                    for (var i = 0; i < response.not_season_time.length; i++) {
                        not_season_time.push(response.not_season_time[i]);
                    }

                    $this.datepicker("refresh");
                }
            });
        });

        $this.datepicker("option", "beforeShowDay", function(date) {
            var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
            if( greenDates.indexOf(string) >=0 ) {
                returnResults = [true, "greenDates", "Verfügbar"];
            }
            if( orangeDates.indexOf(string) >=0 ) {
                returnResults = [true, "orangeDates", "Begrenzt"];
            }
            if( redDates.indexOf(string) >=0 ) {
                returnResults = [true, "redDates", "Ausgebucht"];
            }
            if( holidayDates.indexOf(string) >=0 ) {
                returnResults = [true, "holidayDates", "Ruhetag"];
            }
            if( not_season_time.indexOf(string) >=0 ) {
                returnResults = [false, "", "Geschlossen"];
            }
            return returnResults;
        });

        $this.datepicker("show");

    });

    /* Function begins to calculate amount while changing dates */
    function calculateAmount(diffDays) {

        var oldVoucherAmount  = $( ".daysEditBook" ).data('prepaymentamount');
        var cabinPrepayAmount = $( ".daysEditBook" ).data('cabinprepaymentamount');
        var sleepingPlace     = $( ".daysEditBook" ).data('sleepingplace');
        var sumBedsDorms      = 0;

        if(sleepingPlace !== 1){
            // Beds & Dorms select box value is null for validation purpose. So value is set as 0
            var dorms         = 0;
            var beds          = 0;

            if($( ".jsEditBookBed" ).val() !== ''){
                beds          = $( ".jsEditBookBed" ).val();
            }

            if($( ".jsEditBookDorm" ).val() !== ''){
                dorms         = $( ".jsEditBookDorm" ).val();
            }

            sumBedsDorms      = parseInt(beds) + parseInt(dorms);
        }
        else {
            // Sleeps select box value is null for validation purpose. So value is set as 0
            var sleeps        = 0;

            if($( ".jsEditBookSleep" ).val() !== ''){
                sleeps        = $( ".jsEditBookSleep" ).val();
            }
        }

        var guest             = (sleepingPlace === 1) ? sleeps : sumBedsDorms;
        var amount            = (cabinPrepayAmount * diffDays) * guest;
        var total             = (amount > oldVoucherAmount) ? amount - oldVoucherAmount : amount;

        $( '.replaceEditBookingGuest' ).html(guest);

        editCartTotalDepositCalc(total, oldVoucherAmount, amount);
    }
    /* Function ends to calculate amount while changing dates */

    /* Calendar availability check end */

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
                        $('.responseMessage').html('<div class="alert alert-warning alert-dismissible response" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Whoops! </strong>'+translation().deleteFailed+'</div>');
                    }
                })
                .fail(function() {
                    $btn.btnBootstrap('reset');
                    $('.responseMessage').html('<div class="alert alert-warning alert-dismissible response" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Whoops! </strong>'+translation().deleteFailed+'</div>');
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
    var formatter = new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR', minimumFractionDigits: 2 });

    function editCartTotalDepositCalc(total, oldVoucherAmount, amount)
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

        if(amount > oldVoucherAmount) {
            $( ".amountGreater" ).show();
            $( ".voucherGreater" ).hide();
            $( ".replaceEditBookingCompleteDeposit" ).html(formatter.format(total));
            $( ".replaceEditBookingServiceFee" ).html(serviceTaxEditBooking+' %');
            $( ".replaceEditBookingCompletePayment" ).html(formatter.format(sumPrepayAmountServiceTotal));
        }
        /*else if(amount === oldVoucherAmount) {
            $( ".amountGreater" ).show();
            $( ".voucherGreater" ).hide();
            $( ".replaceEditBookingCompleteDeposit" ).html(formatter.format(0));
            $( ".replaceEditBookingServiceFee" ).html(0+' %');
            $( ".replaceEditBookingCompletePayment" ).html(formatter.format(0));
        }*/
        else {
            $( ".voucherGreater" ).show();
            $( ".amountGreater" ).hide();
            $( '.replaceEditBookingCompleteDeposit' ).html(formatter.format(total));
            $( '.replaceEditBookingToWallet' ).html(formatter.format(oldVoucherAmount - total));
        }
    }

    // Sleeps calculation
    $('.jsEditBookSleep').change(function() {

        var days             = $( ".daysEditBook" ).attr("data-days");
        var oldVoucherAmount = $( ".daysEditBook" ).data('prepaymentamount');
        var cabinPrepay      = $( ".daysEditBook" ).data('cabinprepaymentamount');

        // Sleeps select box value is null for validation purpose. So value is set as 0
        var sleeps           = 0;

        if($(this).val() !== ''){
            sleeps           = $(this).val();
        }

        var amount           = (cabinPrepay * days) * sleeps;
        var total            = (amount > oldVoucherAmount) ? amount - oldVoucherAmount : amount;

        $( '.replaceEditBookingGuest' ).html(sleeps);

        editCartTotalDepositCalc(total, oldVoucherAmount, amount);
    });

    // Beds calculation
    $('.jsEditBookBed').change(function() {

        var days              = $( ".daysEditBook" ).attr("data-days");
        var oldVoucherAmount  = $( ".daysEditBook" ).data('prepaymentamount');
        var cabinPrepayAmount = $( ".daysEditBook" ).data('cabinprepaymentamount');

        // Beds & Dorms select box value is null for validation purpose. So value is set as 0
        var dorms      = 0;
        var beds       = 0;

        if($(this).val() !== ''){
            beds       = $(this).val();
        }

        if($('.jsEditBookDorm').val() !== ''){
            dorms      = $('.jsEditBookDorm').val();
        }

        var guest      = parseInt(beds) + parseInt(dorms);
        var amount     = (cabinPrepayAmount * days) * guest;
        var total      = (amount > oldVoucherAmount) ? amount - oldVoucherAmount : amount;

        $( '.replaceEditBookingGuest' ).html(guest);

        editCartTotalDepositCalc(total, oldVoucherAmount, amount);
    });

    // Dorms calculation
    $('.jsEditBookDorm').change(function() {

        var days              = $( ".daysEditBook" ).attr("data-days");
        var oldVoucherAmount  = $( ".daysEditBook" ).data('prepaymentamount');
        var cabinPrepayAmount = $( ".daysEditBook" ).data('cabinprepaymentamount');

        // Beds & Dorms select box value is null for validation purpose. So value is set as 0
        var dorms      = 0;
        var beds       = 0;

        if($(this).val() !== ''){
            dorms      = $(this).val();
        }

        if($('.jsEditBookBed').val() !== ''){
            beds       = $('.jsEditBookBed').val();
        }

        var guest      = parseInt(dorms) + parseInt(beds);
        var amount     = (cabinPrepayAmount * days) * guest;
        var total      = (amount > oldVoucherAmount) ? amount - oldVoucherAmount : amount;

        $( '.replaceEditBookingGuest' ).html(guest);

        editCartTotalDepositCalc(total, oldVoucherAmount, amount);
    });
    /* Amount calc of sleeps, beds & dorms end */

    /* Send chat message to cabin owner */
    $(".sendChatMsg").on("click", function(e){
        e.preventDefault();
        var chatBookId     = $(this).data('chatbookid');
        var $btn           = $(this).btnBootstrap('loading');
        var message        = $(".chatMessage_"+chatBookId).val();
        if(chatBookId !== '') {
            $.ajax({
                url: '/booking/history/inquiry/message/send',
                dataType: 'JSON',
                type: 'POST',
                data: { chatBookId: chatBookId, message: message }
            })
                .done(function( result ) {
                    if(result.status === 'success') {
                        $btn.btnBootstrap('reset');
                        $(".hideAfterSuccess").hide();
                        $(".successMessage_"+chatBookId).html('<div class="alert alert-success alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> <h4><i class="icon fa fa-check"></i></h4>Die Nachricht wurde nun versendet</div>');
                        setInterval(function () {
                            window.location.href = '/booking/history/';
                        }, 1000);
                    }
                })
                .fail(function(data) {
                    $btn.btnBootstrap('reset');
                    if( data.status === 422 ) {
                        var errors = data.responseJSON.errors;
                        if(errors) {
                            $( "#chatSendFailed_"+chatBookId ).hide();
                            $( "#errorSendFailed_"+chatBookId ).show();
                            errorsHtml = '<div class="alert alert-danger"><ul>';
                            $.each( errors , function( key, value ) {
                                errorsHtml += '<li>' + value + '</li>';
                            });
                            errorsHtml += '</ul></div>';
                            $( "#errorSendFailed_"+chatBookId ).html( errorsHtml );
                        }
                        else {
                            $( "#errorSendFailed_"+chatBookId ).hide();
                            $( "#chatSendFailed_"+chatBookId ).show();
                            var warning = data.responseJSON;
                            errorsHtml = '<div class="alert alert-info"><ul>';
                            $.each( warning , function( key, value ) {
                                errorsHtml += '<li>' + value + '</li>';
                            });
                            errorsHtml += '</ul></div>';
                            $( "#chatSendFailed_"+chatBookId ).html( errorsHtml );
                        }
                    }
                });
        }
    });
});