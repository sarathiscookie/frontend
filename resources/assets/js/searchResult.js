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