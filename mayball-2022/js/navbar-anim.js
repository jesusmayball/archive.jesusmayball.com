function LoadNav () {
    var scrollTop = $(document).scrollTop();
    var windowHeight = $(window).innerHeight()
    const $nav = $("#navbar-nav");
    if (scrollTop > windowHeight * 0.7) {
        $nav.removeClass("fade-out-quick").addClass('fade-in-quick sticky');
    }

    else if ($nav.css("opacity") > 0) {
        $nav.removeClass('fade-in-quick').addClass("fade-out-quick")
    }
};

$(window).on("load", function () {
    $(window).scroll(function () {
        LoadNav();
        var windowBottom = $(this).scrollTop() + $(this).innerHeight();
        $(".fadeable").each(function () {
            /* Check the location of each desired element */
            var objectBottom = $(this).offset().top + $(this).outerHeight();

            /* If the element is completely within bounds of the window, fade it in */
            if (objectBottom < windowBottom) { //object comes into view (scrolling down)
                if ($(this).css("opacity") == 0) { $(this).fadeTo(750, 1); }
            } else { //object goes out of view (scrolling up)
                if ($(this).css("opacity") == 1) { $(this).fadeTo(500, 0); }
            }
        });
    }).scroll(); //invoke scroll-handler on page-load
});