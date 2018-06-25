/*
|-------------------------------------------
| BookView
|-------------------------------------------
|
| the view of a 'book'
| there should only be one instance of this
| has links to content below
|
| type:         Class
| augments:     BackBone.View
| collection:   the sections on the page
| author:       Josh Bambrick
| version:      0.0.1
| modified:     28/01/16
|
*/

define([
    'jquery',
    'underscore',
    'backbone',
    'bowser',
    'lib/skrollr/init-skrollr'
], function (
    $,
    _,
    Backbone,
    bowser,
    getSkrollrInstance
) {
    return Backbone.View.extend({
        tagName: 'figure',
        BEMClassName: 'book',
        BEMSuffix: 'start animated',
        showCloseLinkBackground: !bowser.msedge,
        enableListLinks: !bowser.safari,
        highPerspective: bowser.safari,
        initialize: function (options) {
            this.getSectionHyperLink = options.getSectionHyperLink;
            this.spriteImageTemplate = options.spriteImageTemplate;
            this.openDelay = options.openDelay;
            this.template = options.template;
            this.sprite = null;
            this.render();
        },
        events: {
            "click .-js-front-cover": "changeOpenStatus",
            "click .-js-preceding-page": "changeOpenStatus"
        },
        changeOpenStatus: function (open) {
            var skrollr = getSkrollrInstance();

            switch (open) {
                case true:
                    this.$el.addBEMSuffix('open');
                    break;
                case false:
                    this.$el.removeBEMSuffix('open');
                    break;
                default:
                    this.$el.toggleBEMSuffix('open');
            }

            if (skrollr) {
                skrollr.refresh(this.sprite);
            }
        },
        render: function () {
            var getClasses = _.partial(_.getBEMClasses, 'book', null),
                $bookContent, $precedingPage, $spriteFigure, $closeLinkContainer, $closeLink, $navList;


            if (this.highPerspective) {
                this.$el.addBEMSuffix('high-perspective');
            }

            $bookContent = this.$el.append(_.render(this.template));
            $precedingPage = $bookContent.find('.-js-preceding-page');
            $navList = $bookContent.find('.-js-contents-list');


            if (this.spriteImageTemplate) {
                $spriteFigure = $('<figure>').addClass(getClasses('sprite-figure')).appendTo($precedingPage);
                this.sprite = $(_.render(this.spriteImageTemplate)).appendTo($spriteFigure);
            }

            $closeLinkContainer = $('<div>').addClass(getClasses('close-link-container')).appendTo($precedingPage);
            $closeLink = $('<span>').text('CLOSE').appendTo($closeLinkContainer);
            if (this.showCloseLinkBackground) {
                $precedingPage.addClass("page--with-close-link-background");
                $closeLink.addClass(getClasses('close-link', 'with-background'));
            } else {
                $precedingPage.addClass("page--without-close-link-background");
                $closeLink.addClass(getClasses('close-link', 'without-background'));
            }

            // add the links to the appropriate sections
            this.collection.each(function (curSection) {
                var curSectionLabel = curSection.get('label'),
                    curSectionTitle = curSection.get('title'),
                    $listItem;
                if (curSectionTitle != null) {
                    if (this.enableListLinks) {
                        $listItem = this.getSectionHyperLink(curSectionLabel).addClass(getClasses('list-link'));
                    } else {
                        $listItem = $('<span>');
                    }

                    $listItem
                        .text(curSectionTitle)
                        .appendTo($('<li>').appendTo($navList));
                }
            }, this);

            $(_.bind(function () {
                setTimeout(_.bind(this.changeOpenStatus, this, true), this.openDelay);
            }, this));
        }
    });
});