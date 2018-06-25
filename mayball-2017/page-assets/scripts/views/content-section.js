/*
|-------------------------------------------
| ContentSectionView
|-------------------------------------------
|
| the view of a 'contents' section
| represents a single article eg 'Ents' or 'Staffing'
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
    'backbone'
], function (
    $,
    _,
    Backbone
) {
    return Backbone.View.extend({
        tagName: 'article',
        BEMClassName: 'section',
        BEMSuffix: 'contents parallax-contents',
        initialize: function () {
            this.render();
        },
        render: function () {
            var sectionLabel = this.model.get('label'), titleTemplate = this.model.get('titleTemplate'), noImage = !!this.model.get('noImage'),
                getClasses = _.partial(_.getBEMClasses, 'section', [sectionLabel, 'contents', noImage ? 'no-image' : 'image']),
                $contents, $text, $title, $titleContainer, $imgFigure, $spriteShrinker;

            this.$el.addBEMSuffix(sectionLabel);

            $contents = $('<div>').addClass(getClasses('contents')).appendTo(this.$el);

            this.$el.addBEMSuffix(noImage ? 'no-image' : 'image');
            
            if (!noImage) {
                $imgFigure = $('<figure>').addClass(getClasses('img-figure')).appendTo($contents);
                if (this.model.has('spriteImageType')) {
                    $spriteShrinker = $('<div>').addClass(getClasses('sprite-img-shrinker', this.model.get('spriteImageType'))).appendTo($imgFigure);
                    $('<span>').addClass(getClasses('sprite-img', this.model.get('spriteImageType'))).appendTo($spriteShrinker);
                } else {
                    $('<span>').addClass(getClasses('img')).appendTo($imgFigure);
                }
            }
            
            $text = $('<div>').addClass(getClasses('text')).html(_.render(this.model.get('template'))).appendTo($contents);
            

            $titleContainer = $('<div>').addClass(getClasses('title-container')).prependTo($text);
            $title = $('<h4>').addClass(getClasses('title')).html(_.render(titleTemplate)).appendTo($titleContainer);
            $('<div>').addClass(getClasses('title-underline')).appendTo($titleContainer);


            $.breakpoint.on(['lap-and-up', 'thumb', 'palm'], _.bind(function (breakpoint) {
                var palmClass = getClasses('title', 'palm', true),
                    lapClass = getClasses('title', 'lap-and-up', true),
                    thumbClass = getClasses('title', 'thumb', true);

                switch (breakpoint) {
                    case 'lap-and-up':
                        $title.addClass(lapClass);
                        $title.removeClass(palmClass);
                        $title.removeClass(thumbClass);
                        break;
                    case 'palm':
                        $title.addClass(palmClass);
                        $title.removeClass(thumbClass);
                        $title.removeClass(lapClass);
                        break;
                    case 'thumb':
                        $title.addClass(thumbClass);
                        $title.removeClass(palmClass);
                        $title.removeClass(lapClass);
                        break;
                }
            }, this), true);
        }
    });
});