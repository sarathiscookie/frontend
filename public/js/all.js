/* Js for home module */
/* Js for search module */
$(function(){

    /* Reset the checkbox on page loads */
    $('.filterCheckbox').prop('checked', false);

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
                    $("#beds_"+cabin).val("");
                    $("#dorms_"+cabin).val("");

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

    /* Functionality to cookie to cart */
    $("body").on("click", ".cookieToCart", function(e) {
        e.preventDefault();
        var cookieToCart   = $(this).val();
        var cabin          = $(this).parent().parent().data("cab");
        var dateFrom       = $("#dateFrom_"+cabin).val();
        var dateTo         = $("#dateTo_"+cabin).val();
        var beds           = $("#beds_"+cabin).val();
        var dorms          = $("#dorms_"+cabin).val();
        var sleeps         = $("#sleeps_"+cabin).val();
        var sleeping_place = $("#sleeping_place_"+cabin).val();
        var errorsHtml     = '';

        $.ajax({
            url: '/session/data/to/cart',
            dataType: 'JSON',
            type: 'POST',
            data: { dateFrom: dateFrom, dateTo: dateTo, beds: beds, dorms: dorms, sleeps: sleeps, cookieToCart: cookieToCart, cabin: cabin, sleeping_place:sleeping_place }
        })
            .done(function( data ) {
                if(data.response === 'success') {
                    var redirect_url = '/login';
                    $( "#errors_"+cabin ).hide();
                    $( "#warning_"+cabin ).hide();
                    window.location.href = redirect_url;
                }
            })
            .fail(function(data, jqxhr, textStatus, error) {
                if( data.status === 422 ) {
                    $("#beds_"+cabin).val("");
                    $("#dorms_"+cabin).val("");

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

    /* Helping object for translation */
    var translations = '';

    function translation() {
        translations = {
            more_details: window.environment.more_details,
            less_details: window.environment.less_details
        };
        return translations;
    }

    /* When button click showing begin and end season */
    $(".toggleSeasonTime").on('click', function() {
        $(".seasonTimes").toggle(500);
    });

    /* Button click show more details */


    $(function(){
       $("#btn-more-details").click(function () {
          $(this).text(function(i, text) {
                if (text === window.environment.more_details) {
                    $('.details-info-cabin-details').addClass('more');
                    $('.details-info-cabin-details').removeClass('less');

                    return window.environment.less_details;
                } else {
                    $('.details-info-cabin-details').addClass('less');
                    $('.details-info-cabin-details').removeClass('more');

                    return window.environment.more_details;
                }
          })
       });
    })

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
/* Js for payment module */
$(function() {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    /* Function for german number formatter. */
    var formatterPayment = new Intl.NumberFormat('de-DE', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 2
    });

    /* Helping objects for env variables */
    function variables() {
        var envBook = {
            tax_one: window.environment.service_tax_one,
            tax_two: window.environment.service_tax_two,
            tax_three: window.environment.service_tax_three,
            service_tax_paybybill_one: window.environment.service_tax_paybybill_one,
            service_tax_paybybill_two: window.environment.service_tax_paybybill_two,
            service_tax_paybybill_three: window.environment.service_tax_paybybill_three,
            service_tax_sofort_one: window.environment.service_tax_sofort_one,
            service_tax_sofort_two: window.environment.service_tax_sofort_two,
            service_tax_sofort_three: window.environment.service_tax_sofort_three,
            service_tax_paydirect_one: window.environment.service_tax_paydirect_one,
            service_tax_paydirect_two: window.environment.service_tax_paydirect_two,
            service_tax_paydirect_three: window.environment.service_tax_paydirect_three,
            service_tax_paypal_one: window.environment.service_tax_paypal_one,
            service_tax_paypal_two: window.environment.service_tax_paypal_two,
            service_tax_paypal_three: window.environment.service_tax_paypal_three,
            service_tax_creditcard_one: window.environment.service_tax_creditcard_one,
            service_tax_creditcard_two: window.environment.service_tax_creditcard_two,
            service_tax_creditcard_three: window.environment.service_tax_creditcard_three,
            redeemedAmountPayment: window.environment.redeemedAmountPayment,
            moneyBalancePayment: window.environment.moneyBalancePayment,
            serviceFeePayment: window.environment.serviceFeePayment,
            amountPayment: window.environment.amountPayment
        };

        return envBook;
    }

    var paymentChoosePassAmount           = $( ".sumPrepayAmount" ).data('sumprepayamount'); // If click on any payment method, pass amount to function to calculate service fee.
    var paymentChoosePassAmountDeductDays = $( ".sumPrepayAmount" ).data('amountafterdeductdays'); // If click on any payment method, pass amount to function to calculate service fee.

    // Function for calculating amount after click or property true of money balance checkbox
    function checkingPropertyCheckbox()
    {
        if($(".moneyBalanceCheckbox").is(":checked")) {
            $( ".afterRedeem" ).show();

            var redeemAmount              = $(".moneyBalanceCheckbox").parents().eq(2).data('redeem'); // We can use parent().parent().parent() also but it is slower.
            var sumPrepayAmount           = $( ".sumPrepayAmount" ).data('sumprepayamount');

            if (redeemAmount >= sumPrepayAmount) {
                var afterRedeemAmount     = redeemAmount - sumPrepayAmount;
                $( ".redeemAmount" ).html('<p class="info-listing-booking2">'+variables().redeemedAmountPayment+':</p><p class="info-listing-price-booking2">'+formatterPayment.format(redeemAmount)+'</p>');
                $( ".moneyBalance" ).html('<p class="info-listing-booking2">'+variables().moneyBalancePayment+':</p><p class="info-listing-price-booking2">'+formatterPayment.format(afterRedeemAmount)+'</p>');
                $( ".afterRedeemAmount" ).html();
                $( ".sumPrepayServiceTotal" ).html();
                $( ".serviceFee" ).hide();
                $( ".totalPrepayAmount" ).hide();
            }
            else {
                $( ".serviceFee" ).show();
                var afterRedeemAmount             = sumPrepayAmount - redeemAmount;
                var deductedDays                  = $( ".sumPrepayAmount" ).data('deducteddays');
                var afterRedeemAmountWithoutDays  = afterRedeemAmount / deductedDays;
                var paymentMethod                 = $( ".serviceFee" ).attr('data-paymentmethod');
                var serviceTaxBook                = serviceFees(afterRedeemAmountWithoutDays, paymentMethod);
                paymentChoosePassAmount           = afterRedeemAmount; // If click on any payment method, pass amount to function to calculate service fee.
                paymentChoosePassAmountDeductDays = afterRedeemAmountWithoutDays; // If click on any payment method, pass amount to function to calculate service fee.
                var sumPrepayPercentage           = (serviceTaxBook / 100) * afterRedeemAmountWithoutDays;
                var sumPrepayServiceTotal         = afterRedeemAmount + sumPrepayPercentage;
                $( ".redeemAmount" ).html('<p class="info-listing-booking2">'+variables().redeemedAmountPayment+':</p><p class="info-listing-price-booking2">'+formatterPayment.format(redeemAmount)+'</p>');
                $( ".moneyBalance" ).html();
                $( ".afterRedeemAmount" ).html('<p class="info-listing-booking2">'+variables().amountPayment+':</p><p class="info-listing-price-booking2">'+formatterPayment.format(afterRedeemAmount)+'</p>');
                $( ".sumPrepayServiceTotal" ).html(formatterPayment.format(sumPrepayServiceTotal));
                $( ".serviceFee" ).html('<p class="info-listing-booking2">'+variables().serviceFeePayment+':</p><p class="info-listing-price-booking2">'+formatterPayment.format(sumPrepayPercentage)+'</p>');
                $( ".totalPrepayAmount" ).show();
            }
        }
        else {
            $( ".afterRedeem" ).hide();
            $( ".serviceFee" ).show();
            var sumPrepaymentAmount           = $( ".sumPrepayAmount" ).data('sumprepayamount');
            var amountAfterDeductDays         = $( ".sumPrepayAmount" ).data('amountafterdeductdays');
            var paymentMethod                 = $( ".serviceFee" ).attr('data-paymentmethod');
            var serviceFee                    = serviceFees(amountAfterDeductDays, paymentMethod);
            paymentChoosePassAmount           = sumPrepaymentAmount; // If click on any payment method, pass amount to function to calculate service fee.
            paymentChoosePassAmountDeductDays = amountAfterDeductDays; // If click on any payment method, pass amount to function to calculate service fee.
            var sumPrepaymentPerc             = (serviceFee / 100) * amountAfterDeductDays;
            var sumPrepaymentServTotal        = sumPrepaymentAmount + sumPrepaymentPerc;

            $( ".sumPrepayServiceTotal" ).html(formatterPayment.format(sumPrepaymentServTotal));
            $( ".serviceFee" ).html('<p class="info-listing-booking2">'+variables().serviceFeePayment+':</p><p class="info-listing-price-booking2">'+formatterPayment.format(sumPrepaymentPerc)+'</p>');
            $( ".totalPrepayAmount" ).show();
        }
    }

    // Redeem amount functionality works when user clicked on checkbox
    $(".moneyBalanceCheckbox").click(function(){
        checkingPropertyCheckbox();
    });

    // If money balance checkbox property is selected on page load then amount will calculate and show.
    if($(".moneyBalanceCheckbox").prop("checked") === true){
        checkingPropertyCheckbox();
    }

    /* When click on payment method pass amount and calculate service fee. Open hide credit card iframe */
    $("input[name='payment']").on("click", function(){
        checkingPropertyRadiobutton($(this).val())
    });

    // If payment type radio button property is selected on page load then amount will calculate and show.
    if($("input[name='payment']:checked").val()){
        checkingPropertyRadiobutton($("input[name='payment']:checked").val())
    }

    // Function for calculating amount after click or property true of payment type radio button
    function checkingPropertyRadiobutton(val)
    {
        $( ".serviceFee" ).attr('data-paymentmethod', val);

        if(val === 'creditCard') {
            $("#creditcard").show();
        }
        else {
            $("#creditcard").hide();
        }

        var serviceFeePayMethod   = serviceFees(paymentChoosePassAmountDeductDays, val);
        var sumPayMethodPerc      = (serviceFeePayMethod / 100) * paymentChoosePassAmountDeductDays;
        var sumPayMethodServTotal = paymentChoosePassAmount + sumPayMethodPerc;
        $( ".serviceFee" ).html('<p class="info-listing-booking2">'+variables().serviceFeePayment+':</p><p class="info-listing-price-booking2">'+formatterPayment.format(sumPayMethodPerc)+'</p>');
        $( ".sumPrepayServiceTotal" ).html(formatterPayment.format(sumPayMethodServTotal));
    }

    /* Function to service tax calculation */
    function serviceFees(sumPrepayAmount, paymentMethod)
    {
        paymentMethod = paymentMethod || '';

        var serviceTaxBook = 0;

        if(paymentMethod === 'payByBill') {
            if(sumPrepayAmount <= 30) {
                serviceTaxBook = variables().service_tax_paybybill_one;
            }

            if(sumPrepayAmount > 30 && sumPrepayAmount <= 100) {
                serviceTaxBook = variables().service_tax_paybybill_two;
            }

            if(sumPrepayAmount > 100) {
                serviceTaxBook = variables().service_tax_paybybill_three;
            }
        }
        else if(paymentMethod === 'sofort') {
            if(sumPrepayAmount <= 30) {
                serviceTaxBook = variables().service_tax_sofort_one;
            }

            if(sumPrepayAmount > 30 && sumPrepayAmount <= 100) {
                serviceTaxBook = variables().service_tax_sofort_two;
            }

            if(sumPrepayAmount > 100) {
                serviceTaxBook = variables().service_tax_sofort_three;
            }
        }
        else if(paymentMethod === 'payDirect') {
            if(sumPrepayAmount <= 30) {
                serviceTaxBook = variables().service_tax_paydirect_one;
            }

            if(sumPrepayAmount > 30 && sumPrepayAmount <= 100) {
                serviceTaxBook = variables().service_tax_paydirect_two;
            }

            if(sumPrepayAmount > 100) {
                serviceTaxBook = variables().service_tax_paydirect_three;
            }
        }
        else if(paymentMethod === 'payPal') {
            if(sumPrepayAmount <= 30) {
                serviceTaxBook = variables().service_tax_paypal_one;
            }

            if(sumPrepayAmount > 30 && sumPrepayAmount <= 100) {
                serviceTaxBook = variables().service_tax_paypal_two;
            }

            if(sumPrepayAmount > 100) {
                serviceTaxBook = variables().service_tax_paypal_three;
            }
        }
        else if(paymentMethod === 'creditCard') {
            if(sumPrepayAmount <= 30) {
                serviceTaxBook = variables().service_tax_creditcard_one;
            }

            if(sumPrepayAmount > 30 && sumPrepayAmount <= 100) {
                serviceTaxBook = variables().service_tax_creditcard_two;
            }

            if(sumPrepayAmount > 100) {
                serviceTaxBook = variables().service_tax_creditcard_three;
            }
        }
        else {
            if(sumPrepayAmount <= 30) {
                serviceTaxBook = variables().tax_one;
            }

            if(sumPrepayAmount > 30 && sumPrepayAmount <= 100) {
                serviceTaxBook = variables().tax_two;
            }

            if(sumPrepayAmount > 100) {
                serviceTaxBook = variables().tax_three;
            }
        }
        return serviceTaxBook;
    }
});
/* Js for booking history module */
$(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });


    function convertDate(dateString){
        var dateSplit = dateString.split('.');
        var date      = new Date("20"+dateSplit[2], dateSplit[1]-1, dateSplit[0]); //Here 20 hard coded because date posting from form is d.m.y (eg: 03.12.18). Firefox year format is YYYY (eg 03.12.2018). If we didn't hardcoded 20, Firefox will think that year is 1918.
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
            var diffDays  = Math.ceil(Math.round(timeDiff / (1000 * 3600 * 24)));

            if(dateOne < dateTwo) {
                $(".daysEditBook").attr("data-days", diffDays);
                $(".replaceNumberOfNights").html(diffDays+' Tag(e)');
                var newDiffDays = $(".daysEditBook").attr("data-days");
                calculateAmount(newDiffDays);
            }
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
            var diffDays    = Math.ceil(Math.round(timeDiff / (1000 * 3600 * 24)));

            if(dateOne < dateTwo) {
                $(".daysEditBook").attr("data-days", diffDays);
                $(".replaceNumberOfNights").html(diffDays+' Tag(e)');
                var newDiffDays = $(".daysEditBook").attr("data-days");
                calculateAmount(newDiffDays);
            }
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
        var amountDeductDays  = (amount > oldVoucherAmount) ? total / diffDays : total;

        $( '.replaceEditBookingGuest' ).html(guest);

        editCartTotalDepositCalc(total, oldVoucherAmount, amount, amountDeductDays);
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

    function editCartTotalDepositCalc(total, oldVoucherAmount, amount, amountDeductDays)
    {
        // Helping objects for env variables
        var env_for_edit_booking = {
            tax_one_for_edit_booking: window.environment.service_tax_one_for_edit_booking,
            tax_two_for_edit_booking: window.environment.service_tax_two_for_edit_booking,
            tax_three_for_edit_booking: window.environment.service_tax_three_for_edit_booking
        };

        var serviceTaxEditBooking = '';

        if(amountDeductDays <= 30) {
            serviceTaxEditBooking = env_for_edit_booking.tax_one_for_edit_booking;
        }

        if(amountDeductDays > 30 && amountDeductDays <= 100) {
            serviceTaxEditBooking = env_for_edit_booking.tax_two_for_edit_booking;
        }

        if(amountDeductDays > 100) {
            serviceTaxEditBooking = env_for_edit_booking.tax_three_for_edit_booking;
        }

        var sumPrepayAmountPerc   = (serviceTaxEditBooking / 100) * amountDeductDays;
        var sumPrepayAmountServiceTotal = total + sumPrepayAmountPerc;

        if(amount > oldVoucherAmount) {
            $( ".amountGreater" ).show();
            $( ".voucherGreater" ).hide();
            $( ".replaceEditBookingCompleteDeposit" ).html(formatter.format(total));
            $( ".replaceEditBookingServiceFee" ).html(formatter.format(sumPrepayAmountPerc));
            $( ".replaceEditBookingCompletePayment" ).html(formatter.format(sumPrepayAmountServiceTotal));
        }
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
        var amountDeductDays = (amount > oldVoucherAmount) ? total / days : total;

        $( '.replaceEditBookingGuest' ).html(sleeps);

        editCartTotalDepositCalc(total, oldVoucherAmount, amount, amountDeductDays);
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

        var guest            = parseInt(beds) + parseInt(dorms);
        var amount           = (cabinPrepayAmount * days) * guest;
        var total            = (amount > oldVoucherAmount) ? amount - oldVoucherAmount : amount;
        var amountDeductDays = (amount > oldVoucherAmount) ? total / days : total;

        $( '.replaceEditBookingGuest' ).html(guest);

        editCartTotalDepositCalc(total, oldVoucherAmount, amount, amountDeductDays);
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

        var guest            = parseInt(dorms) + parseInt(beds);
        var amount           = (cabinPrepayAmount * days) * guest;
        var total            = (amount > oldVoucherAmount) ? amount - oldVoucherAmount : amount;
        var amountDeductDays = (amount > oldVoucherAmount) ? total / days : total;

        $( '.replaceEditBookingGuest' ).html(guest);

        editCartTotalDepositCalc(total, oldVoucherAmount, amount, amountDeductDays);
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
function acceptCookies() {
    var link_to = window.location.origin + "/data/protection/accept";
    $.ajax({
      headers: { 'X-CSRF-Token' : $('meta[name="csrf"]').attr('content') },
      type: "POST",
      url: link_to,
      success: function () {
        $('.cookie-banner').remove();
        $('.overlay').css('display', 'none');
      }
    });
  }