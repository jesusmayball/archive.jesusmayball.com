/*
|-------------------------------------------
| SectionsView
|-------------------------------------------
|
| the view containing all the sections, an instance of which is added to the app
|
| type:         Class
| augments:     BackBone.View
| collection:   the sections to display
| author:       Josh Bambrick
| version:      0.0.2
| modified:     15/12/14
|
*/

define([
    'jquery',
    'underscore',
    'backbone',
    'views/nav',
    'views/book-hero-section',
    'views/hero-section',
    'views/content-section',
    'views/section-gap',
    'views/footer-section'
], function (
    $,
    _,
    Backbone,
    NavView,
    BookHeroSectionView,
    HeroSectionView,
    ContentSectionView,
    SectionGapView,
    FooterSectionView
) {
    return Backbone.View.extend({
        BEMClassName: 'sections',
        initialize: function (options) {
            this.updateUrl = options.updateUrl;
            this.getSectionHyperLink = options.getSectionHyperLink;
            this.notifyNavHit = options.notifyNavHit;
            this.getScrollOffset = options.getScrollOffset;
            this.getViewedSectionLabel = options.getViewedSectionLabel;
            this.$sectionEl = {};
            this.contentSectionCount = 0;
            this.nav = null;
            this.render();
        },
        jumpToSection: function (sectionLabel) {
            if (this.$sectionEl[sectionLabel]) {
                // force the changes that this would effect
                this.$sectionEl[sectionLabel].scrollTo(this.getScrollOffset());

                // this is a very hacky approach to ensuring that all call stacks
                // triggered by the above are completed before jumping again
                _.bindDefer(function () {
                    _.bindDefer(function () {
                        _.bindDefer(function () {
                            // the new top of this section may have changed (eg if the offset
                            // increased as the fixed nav appeared), so scroll to it again
                            // this doesn't help if we are using animation
                            this.$sectionEl[sectionLabel].scrollTo(this.getScrollOffset());
                        }, this);
                    }, this);
                }, this);
            }
        },
        sectionHighlightChanged: function (sectionLabel) {
            this.nav.sectionHighlightChanged(sectionLabel);
        },
        render: function () {
            var firstContentSectionLabel;

            this.nav = new NavView({
                collection: this.collection,
                getSectionHyperLink: this.getSectionHyperLink,
                notifyTopHit: this.notifyNavHit
            });

            firstContentSectionLabel = this.collection.findWhere({type: 'content'}).get('label');

            // build each section in turn
            this.collection.each(function (curSection) {
                var curSectionLabel = curSection.get('label'),
                    curSectionType = curSection.get('type'),
                    curSectionHasContent = curSectionType !== 'section-gap',
                    $curSection;

                // create section contents and add to this element
                switch (curSectionType) {
                    case 'content':
                        this.contentSectionCount += 1;
                        $curSection = (new ContentSectionView({model: curSection})).$el.addBEMSuffix(this.contentSectionCount % 2 === 0 ? 'even' : 'odd');
                        break;
                    case 'hero':
                        $curSection = (new HeroSectionView({
                            model: curSection,
                            nav: this.nav,
                            firstContentSectionLabel: firstContentSectionLabel,
                            getSectionHyperLink: this.getSectionHyperLink
                        })).$el;
                        break;
                    case 'book-hero':
                        $curSection = (new BookHeroSectionView({
                            model: curSection,
                            collection: this.collection,
                            nav: this.nav,
                            getSectionHyperLink: this.getSectionHyperLink
                        })).$el;
                        break;
                    case 'footer':
                        $curSection = (new FooterSectionView({model: curSection})).$el;
                        break;
                    case 'section-gap':
                        $curSection = $();
                        $curSection = (new SectionGapView({model: curSection})).$el;
                        break;
                }

                $curSection.appendTo(this.$el);

                if (curSectionHasContent) {
                    $curSection.onScrollChange(_.bind(function (hit) {
                        if (hit && this.getViewedSectionLabel() !== curSectionLabel) {
                            // could send null if hit is false, but probably better to just keep it set to the most recent
                            // may have other conditions elsewhere to update url to null
                            this.updateUrl(curSectionLabel);
                        }
                    }, this), _.bind(function () {
                        return -this.getScrollOffset();
                    }, this));
                }

                this.$sectionEl[curSectionLabel] = $curSection;
            }, this);
        }
    });
});