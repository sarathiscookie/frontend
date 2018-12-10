/* Js for cabin details module */
$(function(){

    /* When button click showing begin and end season */
    $(".toggleSeasonTime").on('click', function() {
        $(".seasonTimes").toggle(500);
    });

    /* Button click show more details */
    $("#btn-more-details").click(function() {
        $('.details-info-cabin-details').toggleClass('more');
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