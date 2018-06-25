/*
|-------------------------------------------
| AppView
|-------------------------------------------
|
| the view of the app, an instance of which is added to the DOM
|
| type:         Class
| augments:     BackBone.View
| collection:   the sections to display
| author:       Josh Bambrick
| version:      0.0.3
| modified:     11/12/14
|
*/

define([
    'jquery',
    'underscore',
    'backbone',
    'views/nav',
    'views/sections'
], function (
    $,
    _,
    Backbone,
    NavView,
    SectionsView
) {
    return Backbone.View.extend({
        initialize: function () {
            this.sections = null;
            this.$mainContents = null;
            this.viewedSectionLabel = '';
            this.render();
        },
        canChangeSection: function () {
            return true;
        },
        setCanChangeSectionPredicate: function (func) {
            this.canChangeSection = func;
        },
        setRouter: function (newRouter) {
            this.router = newRouter;

            // scroll changes
            this.router.on('sectionHighlightChanged', this.fixedNav.sectionHighlightChanged, this.fixedNav);
            this.router.on('sectionHighlightChanged', this.sections.sectionHighlightChanged, this.sections);
            this.router.on('sectionHighlightChanged', this.updateViewedSection, this);

            // clicked nav button
            this.router.on('sectionChanged', this.updateViewedSection, this);
            this.router.on('sectionChanged', this.sections.jumpToSection, this.sections);

            _.defer(function () {
                // Start monitoring url changes (including the initial url), this is dependent on the above so only call once they exist
                Backbone.history.start({ 
                    pushState: true                // use pushState methods if available (otherwise use hashChanges)
                });
            });
        },
        setMain: function ($main) {
            $main.append(this.$mainContents);
        },
        updateViewedSection: function (newSectionLabel) {
            this.viewedSectionLabel = newSectionLabel;
        },
        getSectionHyperLink: function (sectionLabel) {
            return $('<a>', {href: '/' + sectionLabel}).on('click', _.bind(function (e) {
                e.preventDefault();
                if (this.canChangeSection()) {
                    this.router.changeSection(sectionLabel);
                }
            }, this));
        },
        render: function () {
            var getSectionHyperLink = _.bind(this.getSectionHyperLink, this);

            this.$mainContents = $('<div>');

            this.fixedNav = new NavView({
                hide: true,
                fixed: true,
                collection: this.collection,
                getSectionHyperLink: getSectionHyperLink
            });

            this.fixedNav.$el.appendTo(this.$el);

            this.sections = new SectionsView({
                getViewedSectionLabel: _.bind(function () {
                    return this.viewedSectionLabel;
                }, this),
                getScrollOffset: _.bind(function () {
                    return this.fixedNav.getHidden() ? 0 : -this.fixedNav.$el.outerHeight();
                }, this),
                notifyNavHit: _.bind(function (down) {
                    if (down) {
                        this.fixedNav.show();
                    } else {
                        this.fixedNav.hide();
                    }
                }, this),
                collection: this.collection,
                // used by sections to just update url without making any extra changes
                updateUrl: _.bind(function (newSection) {
                    if (this.canChangeSection()) {
                        this.router.changeUrl(newSection);
                    }
                }, this),
                // used by navigate elements
                getSectionHyperLink: getSectionHyperLink
            });

            this.sections.$el.appendTo(this.$mainContents);
        }
    });
});