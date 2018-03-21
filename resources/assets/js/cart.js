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
