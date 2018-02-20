$(function(){

    /* When button click showing being and end season */
    $(".toggleSeasonTime").on('click', function(){
        $(".seasonTimes").toggle(500);
    });

    /* Button click show more details */
    var showChar = 500;
    var ellipsestext = "...";
    var moretext = "Show more";
    var lesstext = "Show less";

    var content = $(".more").html();

    if(content && content.length > showChar) {

        var c = content.substr(0, showChar);
        var h = content.substr(showChar, content.length - showChar);

        var html = c + '<span class="moreellipses">' + ellipsestext+ '&nbsp;</span><span class="morecontent"><span>' + h + '</span><button class="btn btn-sm morelink">' + moretext + '</button></span>';

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
});