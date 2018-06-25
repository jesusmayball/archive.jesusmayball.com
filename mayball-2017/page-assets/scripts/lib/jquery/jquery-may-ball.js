(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define(['jquery'], factory);
    } else {
        // Browser globals
        factory(this.$);
    }
}(function ($) {
    var $window = $(window),
        scrollScreen = $.proxy($window.scrollTop, $window),
        getScreenScroll = $.proxy($window.scrollTop, $window),
        getElScrollPosition = function ($el) {
            return $el.offset().top;
        },
        scrollEventHandlers = [],
        // callback to call every scroll handler
        callScreenScrollHandlers = function () {
            $.each(scrollEventHandlers, function (curHandlerIndex, curHandler) {
                curHandler();
            });
        },
        addScreenScrollEvent = function (handler) {
            scrollEventHandlers.push(handler);
        };

    $window.on('scroll', callScreenScrollHandlers);
    $window.on('resize', callScreenScrollHandlers);

    $.setScrollScreenFunction = function (func) {
        scrollScreen = func;
    };

    $.setGetScreenScrollFunction = function (func) {
        getScreenScroll = func;
    };

    $.setGetElScrollPositionFunction = function (func) {
        getElScrollPosition = func;
    };

    $.setScreenScrollEventCreator = function (func) {
        // set a callback to call every handler
        func(callScreenScrollHandlers);
    };


    (function () {
        var els = [];

        addScreenScrollEvent(function () {
            var scrollTop = getScreenScroll();

            $.each(els, function (curElIndex, curEl) {
                var elScrollTop = scrollTop + curEl.offset(),
                    elTop = getElScrollPosition(curEl.$el),
                    elBottom = elTop + curEl.$el.outerHeight(),
                    newHit = elScrollTop >= elTop && elScrollTop <= elBottom;

                if (newHit !== curEl.hit) {
                    curEl.callback(newHit);

                    curEl.hit = newHit;
                }
            });
        });

        $.fn.onScrollChange = function (callback, offset) {
            offset = offset != null ? offset : 0;

            els.push({
                $el: this,
                hit: false,
                offset: $.isFunction(offset) ? offset : function () { return offset; },
                callback: callback
            });

            return this;
        };
    })();

    (function () {
        var els = [], testEl, testElWithKnownScroll;

        testElWithKnownScroll = function (el, scrollTop) {
            var elTop = getElScrollPosition(el.$el),
                newAbove = scrollTop >= elTop;
            
            if (newAbove !== el.above) {
                el.callback(newAbove);

                el.above = newAbove;
            }
        };

        testEl = function (el) {
            testElWithKnownScroll(el, getScreenScroll());
        };

        addScreenScrollEvent(function () {
            var scrollTop = getScreenScroll();
            $.each(els, function (curElIndex, curEl) {
                testElWithKnownScroll(curEl, scrollTop);
            });
        });

        // call a callback every time the users scrolls past the
        // top of the element (in either direction)
        $.fn.onScrollTop = function (callback, init) {
            els.push({
                $el: this,
                above: true,
                callback: callback
            });

            if (init) {
                testEl(els[els.length - 1]);
            }

            return this;
        };
    })();

    $.fn.scrollTo = function (offset) {
        scrollScreen(getElScrollPosition(this) + (offset || 0));
        return this;
    };


    (function () {
        var els = [], testEl;

        // how far the user has scrolled down the screen is known
        testElWithKnownScroll = function (el, scrollTop) {
            var elHeight = el.$el.outerHeight(),
                elTop = getElScrollPosition(el.$el),
                elBottom = elTop + elHeight,
                newCoeff;

                if (scrollTop < elTop) {
                    newCoeff = 0;
                } else if (scrollTop > elBottom) {
                    newCoeff = 1;
                } else  {
                    newCoeff = (scrollTop - elTop) / elHeight;
                }

            if (newCoeff !== el.coeff) {
                el.callback(newCoeff);

                el.coeff = newCoeff;
            }
        };

        testEl = function (el) {
            testElWithKnownScroll(el, getScreenScroll());
        };

        addScreenScrollEvent(function () {
            var scrollTop = getScreenScroll();

            $.each(els, function (curElIndex, curEl) {
                testElWithKnownScroll(curEl, scrollTop);
            });
        });

        // calls a callback whenever the users scrolls over the element, passing
        // a coeff (0 to 1), of how close to the bottom they are
        $.fn.onScrollCoeffChange = function (callback, testNow) {
            els.push({
                $el: this,
                coeff: 0,
                callback: callback
            });

            if (testNow) {
                testEl(els[els.length - 1]);
            }

            return this;
        };
    })();
}));