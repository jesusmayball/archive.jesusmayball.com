// Require.js allows us to configure shortcut alias
require.config({
    // The shim config allows us to configure dependencies for
    // scripts that do not call define() to register a module
    shim: {
        underscore: {
            exports: '_'
        },
        modernizr: {
            exports: 'Modernizr'
        },
        backbone: {
            deps: [
                'underscore',
                'jquery'
            ],
            exports: 'Backbone'
        }
    },
    paths: {
        // NOTE: must also keep GRUNTFILE.JS up to date
        text:                   'lib/requirejs/text',
        analytics:              'lib/analytics/analytics',
        jquery:                 'lib/jquery/jquery',
        jqueryBem:              'lib/jquery/jquery-bem',
        jqueryMayBall:          'lib/jquery/jquery-breakpoint',
        jqueryBreakpoint:       'lib/jquery/jquery-may-ball',
        modernizr:              'lib/modernizr/modernizr',
        underscore:             'lib/underscore/underscore',
        'underscore-mixins':    'lib/underscore/underscore-mixins',
        backbone:               'lib/backbone/backbone',
        backboneBem:            'lib/backbone/backbone-bem',
        bowser:                 'lib/bowser/bowser'
    }
});

require([
    // force plugin-dependent plugins to run
    '../../dir-assets/scripts/lib/console/console',
    'analytics',
    'jquery',
    'jqueryBem',
    'jqueryBreakpoint',
    'jqueryMayBall',
    'modernizr',
    'underscore',
    'underscore-mixins',
    'backbone',
    'backboneBem',
    'bowser',
    'routers/router.instance',
    'views/app.instance',
    'lib/skrollr/init-skrollr',
    'lib/skrollr/init-skrollr-stylesheets'
], function (
    consoleFix,
    ga,
    $,
    jqueryBem,
    jqueryBreakpoint,
    jqueryMayBall,
    modernizr,
    _,
    underscoreMixins,
    backbone,
    backboneBem,
    bowser,
    router,
    app,
    initSkrollr,
    initSkrollrStylesheets
) {
    $(function () {
        $.breakpoint.changeBreakpoints({
            thumb: {
                max: 299
            },
            palm: {
                max: 719
            },
            lap: {
                max: 1023,
                min: 720
            },
            'lap-and-up': {
                min: 720
            },
            portable: {
                max: 1023
            },
            desk: {
                min: 1024
            }
        });

        app.setRouter(router);

        if (bowser.mobile) {
            app.setMain($('.main'));
            app.$el.prependTo('.main');
            $('html').addClass('-bowser-mobile');
        } else {
            app.setMain($('#skrollr-body'));

            // is this necessary?
            app.$el.prependTo('.main');

            _.defer(function () {
                var skrollr;

                initSkrollrStylesheets();
                skrollr = initSkrollr({
                    smoothScrolling: true,
                    forceHeight: false
                });

                app.setCanChangeSectionPredicate(function () {
                    return !skrollr.isAnimatingTo();
                });

                // jump to section of page
                $.setScrollScreenFunction(skrollr.setScrollTop);

                // animate to section of page
                // $.setScrollScreenFunction(function (top) {
                //     skrollr.animateTo(top, {
                //         duration: 200
                //     });
                // });
                $.setGetElScrollPositionFunction(function ($el) {
                    return skrollr.relativeToAbsolute($el.get(0), 'top', 'top');
                });
                $.setGetScreenScrollFunction(skrollr.getScrollTop);
                $.setScreenScrollEventCreator(function (func) {
                    skrollr.on('render', func);
                });
            });
        }
    });
});