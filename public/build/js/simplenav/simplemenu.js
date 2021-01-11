(function($) {
  "use strict";
  
  $(document).ready(function () {
    $(".simplenavicon").click(function () {
        $(".mobilenav").fadeToggle(500);
        $(".top-menu").toggleClass("top-animate");
        $(".mid-menu").toggleClass("mid-animate");
        $(".bottom-menu").toggleClass("bottom-animate");
		<!--$("body").css("overflow", "hidden");-->
    });
	
	$(".menu a").click(function () {
        $(".mobilenav").fadeToggle(500);
        $(".top-menu").toggleClass("top-animate");
        $(".mid-menu").toggleClass("mid-animate");
        $(".bottom-menu").toggleClass("bottom-animate");
		<!--$("body").css("overflow", "auto");-->
    });
	
	$(".simplenavicon2").click(function () {
        $(".mobilenav").fadeToggle(500);
        $(".top-menu").toggleClass("top-animate");
        $(".mid-menu").toggleClass("mid-animate");
        $(".bottom-menu").toggleClass("bottom-animate");
		<!--$("body").css("overflow", "auto");-->
    });
	
	
});
})(jQuery);