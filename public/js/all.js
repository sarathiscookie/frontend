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
    // Configure/customize these variables.
    var cabinListShowChar = 375;  // How many characters are shown by default
    var cabinListEllipsesText = "...";
    var cabinListMoreText = "View more";
    var cabinListLessText = "View less";


    $('.cabinListMore').each(function() {
        var content = $(this).html();

        if(content && content.length > cabinListShowChar) {

            var c = content.substr(0, cabinListShowChar);
            var h = content.substr(cabinListShowChar, content.length - cabinListShowChar);

            var html = c + '<span class="cabinListMoreEllipses">' + cabinListEllipsesText+ '&nbsp;</span><span class="cabinListMoreContent"><span>' + h + '</span>&nbsp;&nbsp; <!--<button type="button" class="btn btn-default btn-sm btn-details cabinListMoreLink">' + cabinListMoreText + '</button> --></span>';

            $(this).html(html);
        }

    });

    $(".cabinListMoreLink").click(function(){
        if($(this).hasClass("cabinListLess")) {
            $(this).removeClass("cabinListLess");
            $(this).html(cabinListMoreText);
        } else {
            $(this).addClass("cabinListLess");
            $(this).html(cabinListLessText);
        }
        $(this).parent().prev().toggle();
        $(this).prev().toggle();
        return false;
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

    $("body").on("click", ".addToCart", function(e) {
        e.preventDefault();
        var cabin     = $(this).parent().parent().data("cab");
        var dateFrom  = $("#dateFrom_"+cabin).val();
        var dateTo    = $("#dateTo_"+cabin).val();
        var persons   = $("#persons_"+cabin).val();
        var addToCart = $(this).val();
        var errorsHtml= '';
        $.ajax({
            url: '/cart/store',
            dataType: 'JSON',
            type: 'POST',
            data: { dateFrom: dateFrom, dateTo: dateTo, persons: persons, addToCart: addToCart, cabin: cabin }
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

    // Deduct user money balance amount
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
});

$(function(){

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

});