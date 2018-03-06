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