/*
|-------------------------------------------
| HeroSectionView
|-------------------------------------------
|
| the view of a 'hero' section
| there should only be one instance of this
| forms the header of the page
|
| type:         Class
| augments:     BackBone.View
| model:        information about the section
| author:       Josh Bambrick
| version:      0.0.1
| modified:     15/12/14
|
*/

define([
    'jquery',
    'underscore',
    'backbone',
], function (
    $,
    _,
    Backbone
) {
    return Backbone.View.extend({
        tagName: 'header',
        BEMClassName: 'section',
        BEMSuffix: 'hero parallax-hero',
        initialize: function (options) {
            this.nav = options.nav;
            this.getSectionHyperLink = options.getSectionHyperLink;
            this.firstContentSectionLabel = options.firstContentSectionLabel;
            this.render();
        },
        render: function () {
            var sectionLabel = this.model.get('label'),
                sectionTitleTemplate = this.model.get('titleTemplate'),
                sectionTitleShowTime = this.model.get('titleShowTime'),
                sectionSubheadings = this.model.get('subheadings'),
                sectionBackgroundLayers = this.model.get('backgroundLayers'),
                getClasses = _.partial(_.getBEMClasses, 'section', [sectionLabel, 'hero']),
                $main, $title, $subheadings, $figure, $arrowRow;

            this.$el.addBEMSuffix(sectionLabel);

            $main = $('<div>').addClass(getClasses('main')).appendTo(this.$el);

            // add layers for background images
            _.each(sectionBackgroundLayers, function (curLayer, curLayerIndex) {
                var curLayerModifiers = curLayer.showFromStart ? [curLayerIndex] : [curLayerIndex, 'unshown'],
                    $curLayer = $('<div>').addClass(getClasses('background-layer', curLayerModifiers)).appendTo($main);
                
                if (!curLayer.showFromStart) {
                    setTimeout(function () {
                        $curLayer.removeClass(getClasses('background-layer', 'unshown', true));
                    }, curLayer.showTime);
                }
            }, this);

            $figure = $('<figure>').addClass(getClasses('figure')).appendTo($main);
            $('<span>').addClass(getClasses('img')).appendTo($figure);

            $title = $('<h1>').addClass(getClasses('title', 'unshown')).html(_.render(sectionTitleTemplate)).appendTo($main);

            // remove 'unshown' class from title (start animation)
            setTimeout(function () {
                $title.removeClass(getClasses('title', 'unshown', true));
            }, sectionTitleShowTime);

            $subheadings = $();

            // create subheadings
            _.each(sectionSubheadings, function (curSubheading) {
                var $curSubheading = $('<h3>').addClass(getClasses('subheading', [curSubheading.label, 'unshown'])).text(curSubheading.text).appendTo($main);

                $subheadings = $subheadings.add($curSubheading);

                // remove 'unshown' class from subheading (start animation)
                setTimeout(function () {
                    $curSubheading.removeClass(getClasses('subheading', 'unshown', true));
                }, curSubheading.showTime);
            }, this);

            // include an arrow to jump to the first section, if necessary
            if (this.model.get('jumpArrow')) {
                $arrowRow = $('<div>').addClass(
                    getClasses('arrow-row')
                ).appendTo($main);

                this.getSectionHyperLink(this.firstContentSectionLabel)
                    .addClass(getClasses('arrow'))
                    .appendTo($arrowRow);
            }

            // add/remove screen size based BEM modifiers
            _.bindDefer(function () {
                var items = [{
                    $el: $title,
                    elementName: 'title'
                }, {
                    $el: $subheadings,
                    elementName: 'subheading'
                }];

                $.breakpoint.on(['thumb', 'palm'], function (breakpoint) {
                    _.each(items, function (curItem) {
                        var palmClass = getClasses(curItem.elementName, 'palm', true),
                            thumbClass = getClasses(curItem.elementName, 'thumb', true);
                        
                        switch (breakpoint) {
                            case 'palm':
                                curItem.$el.removeClass(thumbClass);
                                curItem.$el.addClass(palmClass);
                                break;
                            case 'thumb':
                                curItem.$el.removeClass(palmClass);
                                curItem.$el.addClass(thumbClass);
                                break;
                            default:
                                curItem.$el.removeClass(palmClass);
                                curItem.$el.removeClass(thumbClass);
                                break;
                        }
                    });
                }, true);
            }, this);

            this.nav.$el.addBEMSuffix('hero-nav').appendTo(this.$el);
        }
    });
});