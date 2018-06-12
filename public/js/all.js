/* Js for home module */
/* Js for search module */
$(function(){

    /* Reset the checkbox on page loads */
    $('input:checkbox').prop('checked', false);

    /* Typeahead auto complete begin */
    var cabin_names = new Bloodhound({
        datumTokenizer: function(datum) {
            return Bloodhound.tokenizers.whitespace(datum.value);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        prefetch: {
            url: "/",
            transform: function(response) {
                return $.map(response, function(cabin_name) {
                    return { value: cabin_name.name };
                });
            }
        },
        remote: {
            wildcard: '%QUERY',
            url: "search/cabin/%QUERY",
            transform: function(response) {
                return $.map(response, function(cabin_name) {
                    return { value: cabin_name.name };
                });
            }
        }
    });

    $('#prefetch .typeahead').typeahead({
            hint: false,
            highlight: true,
            minLength: 2,
            limit: 10
        },
        {
            name: 'Cabins',
            display: 'value',
            source: cabin_names
        });

    /* Typeahead auto complete end */
});
/* Js for calendar module */
$(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    /* Calendar availability check begin */
    $("body").on("mousedown", ".dateFrom", function() {
        var dataId          = $(this).parent().parent().data("id");
        var $this           = $("#dateFrom_"+dataId);
        var returnResult    = [];

        var holidayDates    = $(".holiday_"+dataId).data("holiday");
        var greenDates      = $(".green_"+dataId).data("green");
        var orangeDates     = $(".orange_"+dataId).data("orange");
        var redDates        = $(".red_"+dataId).data("red");
        var not_season_time = $(".notSeasonTime_"+dataId).data("notseasontime");
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
            var dt2       = $("#dateTo_"+dataId);
            var startDate = $this.datepicker('getDate');
            var minDate   = $this.datepicker('getDate');
            dt2.datepicker('setDate', minDate);
            startDate.setDate(startDate.getDate() + 60); //sets dt2 maxDate to the last day of 60 days window
            minDate.setDate(minDate.getDate() + 1); //sets dt2 minDate to the +1 day of from date
            dt2.datepicker('option', 'maxDate', startDate);
            dt2.datepicker('option', 'minDate', minDate);
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
                returnResult = [true, "greenDates", "Available"];
            }
            if( orangeDates.indexOf(string) >=0 ) {
                returnResult = [true, "orangeDates", "Few are available"];
            }
            if( redDates.indexOf(string) >=0 ) {
                returnResult = [true, "redDates", "Not available"];
            }
            if( not_season_time.indexOf(string) >=0 ) {
                returnResult = [false, "", "Not season time"];
            }
            if( holidayDates.indexOf(string) >=0 ) {
                returnResult = [false, "", "Holiday"];
            }
            return returnResult;
        });

        $this.datepicker("show");
    });


    $("body").on("mousedown", ".dateTo", function() {
        var dataId          = $(this).parent().parent().data("id");
        var $this           = $("#dateTo_"+dataId);
        var returnResults   = [];

        var holidayDates    = $(".holiday_"+dataId).data("holiday");
        var greenDates      = $(".green_"+dataId).data("green");
        var orangeDates     = $(".orange_"+dataId).data("orange");
        var redDates        = $(".red_"+dataId).data("red");
        var not_season_time = $(".notSeasonTime_"+dataId).data("notseasontime");
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
                returnResults = [true, "greenDates", "Available"];
            }
            if( orangeDates.indexOf(string) >=0 ) {
                returnResults = [true, "orangeDates", "Few are available"];
            }
            if( redDates.indexOf(string) >=0 ) {
                returnResults = [true, "redDates", "Not available"];
            }
            if( not_season_time.indexOf(string) >=0 ) {
                returnResults = [false, "", "Not season time"];
            }
            if( holidayDates.indexOf(string) >=0 ) {
                returnResults = [false, "", "Holiday"];
            }
            return returnResults;
        });

        $this.datepicker("show");

    });
    /* Calendar availability check end */
});
/* Js for cabin list module */
$(function() {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var cabinListShowChar = 375;  // How many characters are shown by default
    var cabinListEllipsesText = "...";

    /* Limiting the characters */
    $('.cabinListMore').each(function() {
        var content = $(this).html();

        if(content && content.length > cabinListShowChar) {

            var c = content.substr(0, cabinListShowChar);
            var h = content.substr(cabinListShowChar, content.length - cabinListShowChar);

            var html = c + '<span class="cabinListMoreEllipses">' + cabinListEllipsesText+ '&nbsp;</span><span class="cabinListMoreContent"><span>' + h + '</span>&nbsp;&nbsp;</span>';

            $(this).html(html);
        }

    });

    /* Functionality for add to cart */
    $("body").on("click", ".addToCart", function(e) {
        e.preventDefault();
        var cabin          = $(this).parent().parent().data("cab");
        var dateFrom       = $("#dateFrom_"+cabin).val();
        var dateTo         = $("#dateTo_"+cabin).val();
        var beds           = $("#beds_"+cabin).val();
        var dorms          = $("#dorms_"+cabin).val();
        var sleeps         = $("#sleeps_"+cabin).val();
        var sleeping_place = $("#sleeping_place_"+cabin).val();
        var addToCart      = $(this).val();
        var errorsHtml     = '';
        $.ajax({
            url: '/add/to/cart',
            dataType: 'JSON',
            type: 'POST',
            data: { dateFrom: dateFrom, dateTo: dateTo, beds: beds, dorms: dorms, sleeps: sleeps, addToCart: addToCart, cabin: cabin, sleeping_place:sleeping_place }
        })
            .done(function( data ) {
                if(data.response === 'success') {
                    var redirect_url = '/cart';
                    $( "#errors_"+cabin ).hide();
                    $( "#warning_"+cabin ).hide();
                    window.location.href = redirect_url;
                }
            })
            .fail(function(data, jqxhr, textStatus, error) {
                if( data.status === 422 ) {
                    var errors = data.responseJSON.errors;
                    if(errors) {
                        $( "#warning_"+cabin ).hide();
                        $( "#errors_"+cabin ).show();
                        errorsHtml = '<div class="alert alert-danger"><ul>';
                        $.each( errors , function( key, value ) {
                            errorsHtml += '<li>' + value + '</li>';
                        });
                        errorsHtml += '</ul></div>';
                        $( "#errors_"+cabin ).html( errorsHtml );
                    }
                    else {
                        $( "#errors_"+cabin ).hide();
                        $( "#warning_"+cabin ).show();
                        var warning = data.responseJSON;
                        errorsHtml = '<div class="alert alert-info"><ul>';
                        $.each( warning , function( key, value ) {
                            errorsHtml += '<li>' + value + '</li>';
                        });
                        errorsHtml += '</ul></div>';
                        $( "#warning_"+cabin ).html( errorsHtml );
                    }
                }
            });
    });
});
/* Js for cabin details module */
$(function(){

    /* When button click showing begin and end season */
    $(".toggleSeasonTime").on('click', function(){
        $(".seasonTimes").toggle(500);
    });

    /* Button click show more details */
    var showChar = 375;
    var ellipsestext = "...";
    var moretext = "View more";
    var lesstext = "View less";

    var content = $(".more").html();

    if(content && content.length > showChar) {

        var c = content.substr(0, showChar);
        var h = content.substr(showChar, content.length - showChar);

        var html = c + '<span class="moreellipses">' + ellipsestext+ '&nbsp;</span><span class="morecontent"><span>' + h + '</span><button class="btn btn-default btn-sm btn-details morelink">' + moretext + '</button></span>';

        $(".more").html(html);
    }

    $(".morelink").click(function(){
        if($(this).hasClass("less")) {
            $(this).removeClass("less");
            $(this).html(moretext);
        }
        else {
            $(this).addClass("less");
            $(this).html(lesstext);
        }
        $(this).parent().prev().toggle();
        $(this).prev().toggle();
        return false;
    });

    /* Tool tip on facilities */
    $('[data-toggle="tooltip"]').tooltip();

    /* Image gallery and slide */
    $('#image-gallery').lightSlider({
        gallery:true,
        item:1,
        thumbItem:9,
        slideMargin: 0,
        speed:700,
        auto:true,
        loop:true,
        onSliderLoad: function() {
            $('#image-gallery').removeClass('cS-hidden');
        }
    });
});
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

        var cartIdBook     = $(this).parent().parent().data('cartid');
        var sleepsBook     = 0; // Select box value of sleeps is null for validation purpose. So value is set as 0
        var amountDaysBook = $('.amountBookingDays_'+cartIdBook).data('amountbookingdays'); // Prepayment amount multiply with no of nights
        if($(this).val() !== ''){
            sleepsBook     = $(this).val()
        }

        var totalBook      = amountDaysBook * sleepsBook;

        $( '.replaceBookingGuest_'+cartIdBook ).html(sleepsBook);
        $( '.replaceBookingDeposit_'+cartIdBook ).html(formatter.format(totalBook));

        totalDepositCalculation();

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

        totalDepositCalculation();

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
        var amountDays    = $('.amountDays').data('amountdays');

        // Sleeps select box value is null for validation purpose. So value is set as 0
        var sleeps        = 0;

        if($(this).val() !== ''){
            sleeps    = $(this).val()
        }

        var total     = amountDays * sleeps;
        $( '.replaceInquiryGuest' ).html(sleeps);
        $( '.replaceInquiryDeposit' ).html(formatter.format(total));

        inquiryTotalDepositCalc(total);
    });

    // Beds calculation
    $('.jsCalBed').change(function() {

        // Days multiply with prepayment_amount
        var amountDays = $('.amountDays').data('amountdays');

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

        inquiryTotalDepositCalc(total);
    });

    // Dorms calculation
    $('.jsCalDorm').change(function() {

        // Days multiply with prepayment_amount
        var amountDays = $('.amountDays').data('amountdays');

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

        inquiryTotalDepositCalc(total);
    });

    function inquiryTotalDepositCalc(total)
    {
        // Helping object for env variables
        var env = {
            tax_one: window.environment.service_tax_one,
            tax_two: window.environment.service_tax_two,
            tax_three: window.environment.service_tax_three
        };

        var serviceTax    = '';

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
    }
});
/* Js for payment module */
$(function() {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(".moneyBalance").click(function(){

        /* Function for german number formatter. */
        var formatter = new Intl.NumberFormat('de-DE', {
            style: 'currency',
            currency: 'EUR',
            minimumFractionDigits: 2
        });

        /* Helping objects for env variables */
        var envBook = {
            tax_one: window.environment.service_tax_one,
            tax_two: window.environment.service_tax_two,
            tax_three: window.environment.service_tax_three
        };

        if($(this).is(":checked")) {
            $( ".afterRedeem" ).show();

            var redeemAmount              = $(this).parents().eq(2).data('redeem'); // We can use parent().parent().parent() also but it is slower.
            var sumPrepayAmount           = $( ".sumPrepayAmount" ).data('sumprepayamount');

            if (redeemAmount >= sumPrepayAmount) {
                var afterRedeemAmount     = redeemAmount - sumPrepayAmount;
                $( ".redeemAmount" ).html('<p class="info-listing-booking2">Redeem Amount:</p><p class="info-listing-price-booking2">'+formatter.format(redeemAmount)+'</p>');
                $( ".moneyBalance" ).html('<p class="info-listing-booking2">Money Balance:</p><p class="info-listing-price-booking2">'+formatter.format(afterRedeemAmount)+'</p>');
                $( ".afterRedeemAmount" ).html();
                $( ".sumPrepayServiceTotal" ).html();
                $( ".jsServiceFee" ).hide();
                $( ".serviceFee" ).hide();
                $( ".totalPrepayAmount" ).hide();
            }
            else {
                var afterRedeemAmount     = sumPrepayAmount - redeemAmount;
                var serviceTaxBook        = serviceFees(afterRedeemAmount);
                var sumPrepayPercentage   = (serviceTaxBook / 100) * afterRedeemAmount;
                var sumPrepayServiceTotal = afterRedeemAmount + sumPrepayPercentage;
                $( ".redeemAmount" ).html('<p class="info-listing-booking2">Redeem Amount:</p><p class="info-listing-price-booking2">'+formatter.format(redeemAmount)+'</p>');
                $( ".moneyBalance" ).html();
                $( ".afterRedeemAmount" ).html('<p class="info-listing-booking2">Amount:</p><p class="info-listing-price-booking2">'+formatter.format(afterRedeemAmount)+'</p>');
                $( ".sumPrepayServiceTotal" ).html(formatter.format(sumPrepayServiceTotal));
                $( ".jsServiceFee" ).show();
                $( ".jsServiceFee" ).html('<p class="info-listing-booking2">Service fee:</p><p class="info-listing-price-booking2">'+serviceTaxBook+'%</p>');
                $( ".serviceFee" ).hide();
                $( ".totalPrepayAmount" ).show();
            }
        }
        else {
            $( ".afterRedeem" ).hide();
            var sumPrepaymentAmount    = $( ".sumPrepayAmount" ).data('sumprepayamount');
            var serviceFee             = serviceFees(sumPrepaymentAmount);
            var sumPrepaymentPerc      = (serviceFee / 100) * sumPrepaymentAmount;
            var sumPrepaymentServTotal = sumPrepaymentAmount + sumPrepaymentPerc;

            $( ".sumPrepayServiceTotal" ).html(formatter.format(sumPrepaymentServTotal));
            $( ".jsServiceFee" ).hide();
            $( ".serviceFee" ).show();
            $( ".totalPrepayAmount" ).show();
        }

        /* Function for service tax calculation */
        function serviceFees(sumPrepayAmount)
        {
            var serviceTaxBook = 0;

            if(sumPrepayAmount <= 30) {
                serviceTaxBook = envBook.tax_one;
            }

            if(sumPrepayAmount > 30 && sumPrepayAmount <= 100) {
                serviceTaxBook = envBook.tax_two;
            }

            if(sumPrepayAmount > 100) {
                serviceTaxBook = envBook.tax_three;
            }

            return serviceTaxBook;
        }

    });

    /* Credit card functionality */
    $("input[name='payment']").on("click", function(){
        if($(this).val() === 'creditCard') {
            $("#creditcard").show();
        }
        else {
            $("#creditcard").hide();
        }
    });
});
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