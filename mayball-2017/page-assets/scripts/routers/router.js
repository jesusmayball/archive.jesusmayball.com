/*
|-------------------------------------------
| Router
|-------------------------------------------
|
| peforms the necessary logic for generally navigating around the app
|
| type:         Class
| augments:     BackBone.Router
| author:       Josh Bambrick
| version:      0.0.1
| modified:     11/12/14
|
*/

define([
    'underscore',
    'backbone',
    'jquery'
], function (
    _,
    Backbone,
    $
) {
    var Router, sendSectionChange;

    sendSectionChange = _.debounce(function () {
        window.ga('set', 'location', window.location.protocol +
            '//' + window.location.hostname +
            window.location.pathname +
            window.location.search);
        
        window.ga('send', {
            hitType: 'pageview'
        });

        window.ga('send', {
            hitType: 'event',
            eventCategory: 'section',
            eventAction: 'change',
            eventLabel: window.location.pathname + window.location.search
        });
    }, 500);

    Router = Backbone.Router.extend({
        initialize: function () {
            this.on('sectionHighlightChanged', sendSectionChange);
        },
        routes: {
            // no page
            '': 'browserNavigateRequest',
            // any page
            '*request': 'browserNavigateRequest'
        },
        easterEggRequest: function () {
            $(setTimeout(function () {
                var selectors = [".section--hero__intro-line", ".tagline--name", ".tagline--date", ".contents_header", ".book__list-link", ".nav__list-link", ".section__title", ".section__text p", ".section__text a", ".section__text h5", ".section--committee__member-label", "footer small"];
                $(selectors.join(", ")).each(function(){                                                                                                                            
                    var $this = $(this);                                                                                                                                
                    if ($this.text()) {
                        $this.text("Ian White");
                    }
                });
            }, 2500));
            this.browserNavigateRequest();
        },
        browserNavigateRequest: function (sectionName) {
            sectionName = sectionName || '';
            
            // ignore trailing forward slash
            sectionName = sectionName.replace(/\/$/, '');

            if (/white\/?$/.test(sectionName)) return this.easterEggRequest();

            // no url change since that is already the url
            this.changeSection(sectionName, true);

            // don't actually call `navigate` (this adds an extra 'back' step in browser)
            this.trigger('sectionHighlightChanged', sectionName);
        },
        changeUrl: function (sectionName) {
            this.navigate(sectionName);
            this.trigger('sectionHighlightChanged', sectionName);
        },
        changeSection: function (sectionName, noUrlChange) {
            if (!noUrlChange) {
                this.changeUrl(sectionName);
            }

            this.trigger('sectionChanged', sectionName);
        }
    });

    return Router;
});