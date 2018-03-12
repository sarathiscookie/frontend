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
            .done(function( response ) {
                if(response.status === 'error') {
                    errorsHtml = '<div class="alert alert-danger"><ul>';
                    errorsHtml += '<li>' + response.message + '</li>';
                    errorsHtml += '</ul></div>';
                    $( "#errors_"+cabin ).html( errorsHtml );
                }
                else if(response.status === 'inquiry') {
                    errorsHtml = '<div class="alert alert-info"><ul>';
                    errorsHtml += '<li>' + response.message + '</li>';
                    errorsHtml += '</ul></div>';
                    $( "#errors_"+cabin ).html( errorsHtml );
                }
                else if(response.status === 'success') {
                    var redirect_url = '/cart';
                    $( "#errors_"+cabin ).hide();
                    window.location.href = redirect_url;
                }
            })
            .fail(function(response, jqxhr, textStatus, error) {
                if( response.status === 422 ) {
                    var errors = response.responseJSON.errors;
                    errorsHtml = '<div class="alert alert-danger"><ul>';
                    $.each( errors , function( key, value ) {
                        errorsHtml += '<li>' + value + '</li>';
                    });
                    errorsHtml += '</ul></div>';
                    $( "#errors_"+cabin ).html( errorsHtml );
                }
            });
    });
});
