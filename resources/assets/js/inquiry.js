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