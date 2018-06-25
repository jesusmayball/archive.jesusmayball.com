/*
|-------------------------------------------
| BookHeroSectionView
|-------------------------------------------
|
| the view of a 'hero' section with a book
| there should only be one instance of this
| forms the header of the page
|
| type:         Class
| augments:     BackBone.View
| model:        information about the section
| collection:   the sections on the page
| author:       Josh Bambrick
| version:      0.0.1
| modified:     15/12/14
|
*/

define([
    'jquery',
    'underscore',
    'backbone',
    'modernizr',
    'bowser',
    'views/book'
], function (
    $,
    _,
    Backbone,
    modernizr,
    bowser,
    BookView
) {
    return Backbone.View.extend({
        tagName: 'header',
        BEMClassName: 'section',
        BEMSuffix: 'book-hero parallax-book-hero',
        maxBookAnimationBreakpoint: 'desk',
        renderAnimatedBook: !bowser.mobile && modernizr.csstransforms3d && modernizr.preserve3d,
        $animatedBook: $(),
        $staticBook: null,
        initialize: function (options) {
            this.nav = options.nav;
            this.getSectionHyperLink = options.getSectionHyperLink;
            this.render();
        },
        updateBookVisibility: function (breakpoint) {
            if (!this.renderAnimatedBook || breakpoint !== this.maxBookAnimationBreakpoint) {
                this.$animatedBook.addBEMSuffix('hidden');
                this.$staticBook.removeBEMSuffix('hidden');
            } else {
                this.$animatedBook.removeBEMSuffix('hidden');
                this.$staticBook.addBEMSuffix('hidden');
            }
        },
        render: function () {
            var sectionLabel = this.model.get('label'),
                getClasses = _.partial(_.getBEMClasses, 'section', [sectionLabel, 'book-hero']),
                animatedBook,
                $main, $mainContents, $introLine, $introLineContainer;

            this.$el.addBEMSuffix(sectionLabel);

            $main = $('<div>').addClass(getClasses('main')).appendTo(this.$el);
            $mainContents = $('<div>').addClass(getClasses('main-contents')).appendTo($main);
            $introLineContainer = $('<div>').addClass(getClasses('intro-line-container')).appendTo($mainContents);
            $introLine = $(_.render(this.model.get('introTemplate'))).addClass(getClasses('intro-line', 'hidden')).appendTo($introLineContainer);

            $(_.bind(function () {
                setTimeout(_.bind(function () {
                    $introLine.removeClass(getClasses('intro-line', 'hidden', true));
                }, this), this.model.get('introLineAppearDelay'));
            }, this));

            if (this.renderAnimatedBook) {
                animatedBook = new BookView({
                    collection: this.collection,
                    getSectionHyperLink: this.getSectionHyperLink,
                    spriteImageTemplate: this.model.get('spriteImageTemplate'),
                    openDelay: this.model.get('openDelay'),
                    template: this.model.get('bookTemplate')});
                this.$animatedBook = animatedBook.$el;
                this.$animatedBook.appendTo($mainContents);
            }

            this.$staticBook = $('<figure>').addBEMClass('book').addBEMSuffix('static start').appendTo($mainContents);
            $('<div>').addBEMClass('coverDesign').appendTo(this.$staticBook);

            this.nav.$el.addBEMSuffix('book-hero-nav').appendTo(this.$el);

            $.breakpoint.on(['desk', 'lap', 'palm', 'thumb'], _.bind(this.updateBookVisibility, this), true);


            $(_.bind(function () {
                setTimeout(_.bind(function () {
                    this.$animatedBook.removeBEMSuffix('start');
                    this.$staticBook.removeBEMSuffix('start');
                }, this), this.model.get('appearDelay'));
            }, this));
        }
    });
});