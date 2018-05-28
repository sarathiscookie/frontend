/* Js for booking history module */
$(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $( ".deleteBookingHistory" ).on('click',  function(e){
        e.preventDefault();
        var delId = $(this).data('del');
        var $btn = $(this).btnBootstrap('loading');

        $.ajax({
            url: '/booking/history/delete',
            dataType: 'JSON',
            type: 'POST',
            data: {delId: delId}
        })
            .done(function( result ) {
                if(result === 'success') {
                    $btn.btnBootstrap('reset');
                    window.location.href = '/booking/history/';
                }
            })
            .fail(function() {
                $btn.btnBootstrap('reset');
            });
    });

});