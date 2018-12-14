/* Js for cabin details module */
$(function(){

    /* Helping object for translation */
    var translations = '';

    function translation() {
        translations = {
            more_details: window.environment.more_details,
            less_details: window.environment.less_details
        };
        return translations;
    }

    /* When button click showing begin and end season */
    $(".toggleSeasonTime").on('click', function() {
        $(".seasonTimes").toggle(500);
    });

    /* Button click show more details */


    $(function(){
       $("#btn-more-details").click(function () {
          $(this).text(function(i, text) {
                if (text === window.environment.more_details) {
                    $('.details-info-cabin-details').addClass('more');
                    $('.details-info-cabin-details').removeClass('less');

                    return window.environment.less_details;
                } else {
                    $('.details-info-cabin-details').addClass('less');
                    $('.details-info-cabin-details').removeClass('more');

                    return window.environment.more_details;
                }
          })
       });
    })

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