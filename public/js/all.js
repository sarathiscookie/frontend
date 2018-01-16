$(function(){
    /* Scroll nav bar begin */
    $(".navbar a, footer a[href='#myPage']").on('click', function(event) {
        // Make sure this.hash has a value before overriding default behavior
        if (this.hash !== "") {
            // Prevent default anchor click behavior
            event.preventDefault();

            // Store hash
            var hash = this.hash;

            // Using jQuery's animate() method to add smooth page scroll
            // The optional number (900) specifies the number of milliseconds it takes to scroll to the specified area
            $('html, body').animate({
                scrollTop: $(hash).offset().top
            }, 900, function(){

                // Add hash (#) to URL when done scrolling (default click behavior)
                window.location.hash = hash;
            });
        } // End if
    });

    $(window).scroll(function() {
        $(".slideanim").each(function(){
            var pos = $(this).offset().top;

            var winTop = $(window).scrollTop();
            if (pos < winTop + 600) {
                $(this).addClass("slide");
            }
        });
    });
    /* Scroll nav bar end */
});
$(function(){

    /* Reset the checkbox on page loads */
    $('input:checkbox').prop('checked', false);

    /* Typeahead auto complete begin */
    var cabin_names = new Bloodhound({
        datumTokenizer: function(datum) {
            return Bloodhound.tokenizers.whitespace(datum.value);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        prefetch: {
            url: "/",
            transform: function(response) {
                return $.map(response, function(cabin_name) {
                    return { value: cabin_name.name };
                });
            }
        },
        remote: {
            wildcard: '%QUERY',
            url: "search/cabin/%QUERY",
            transform: function(response) {
                return $.map(response, function(cabin_name) {
                    return { value: cabin_name.name };
                });
            }
        }
    });

    $('#prefetch .typeahead').typeahead({
            hint: false,
            highlight: true,
            minLength: 2,
            limit: 10
        },
        {
            name: 'Cabins',
            display: 'value',
            source: cabin_names
        });

    /* Typeahead auto complete end */

});