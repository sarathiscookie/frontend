/* Js for cabin list module */
$(function() {

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
});