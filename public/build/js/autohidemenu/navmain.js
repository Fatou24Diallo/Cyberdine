(function($) {
  "use strict";
  
// Sticky Header
$(window).scroll(function() {

    if ($(window).scrollTop() > 100) {
        $('.main_holder').addClass('sticky');
    } else {
        $('.main_holder').removeClass('sticky');
    }
});

// Mobile Navigation
$('.mobile-toggle').click(function() {
    if ($('.main_holder').hasClass('open-nav')) {
        $('.main_holder').removeClass('open-nav');
    } else {
        $('.main_holder').addClass('open-nav');
    }
});

})(jQuery);

