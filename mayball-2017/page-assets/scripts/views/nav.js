/*
|-------------------------------------------
| NavView
|-------------------------------------------
|
| the view of the nav, an instance of which is added to the app
|
| type:         Class
| augments:     BackBone.View
| collection:   the sections to display
| author:       Josh Bambrick
| version:      0.0.1
| modified:     11/12/14
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
        tagName: 'nav',
        BEMClassName: 'nav',
        initialize: function (options) {
            this.getSectionHyperlink = options.getSectionHyperLink;
            
            this.selectedSectionLabel = '';
            this.$sectionLink = {};

            this.fixed = options.fixed;
            this.hidden = !!options.hide;

            this.notifyTopHit = options.notifyTopHit;

            this.render();
        },
        show: function () {
            this.$el.removeBEMSuffix('hidden');
            this.hidden = false;
        },
        hide: function () {
            this.$el.addBEMSuffix('hidden');
            this.hidden = true;
        },
        getHidden: function () {
            return this.hidden;
        },
        sectionHighlightChanged: function (sectionLabel) {
            if (this.selectedSectionLabel) {
                this.$sectionLink[this.selectedSectionLabel].removeBEMSuffix('selected');
            }

            if (this.$sectionLink[sectionLabel]) {
                this.$sectionLink[sectionLabel].addBEMSuffix('selected');
                this.selectedSectionLabel = sectionLabel;
            } else {
                this.selectedSectionLabel = null;
            }
                
        },
        render: function () {
            if (this.fixed) {
                this.$el.addBEMSuffix('fixed');
            }

            if (this.hidden) {
                this.hide();
            }

            this.$navList = $('<ul>').addBEMClass('nav__list').appendTo(this.$el);

            // add the links to the appropriate sections
            this.collection.each(function (curSection, curSectionIndex) {
                var curSectionLabel = curSection.get('label'),
                    curSectionTitle = curSection.get('title'),
                    curSectionImportant = curSection.get('important'),
                    firstNavLink = curSectionIndex === 0;

                if (!this.$sectionLink[curSectionLabel] && curSectionTitle != null) {
                    this.selectedSectionLabel = firstNavLink ? curSectionLabel : this.selectedSectionLabel;

                    this.$sectionLink[curSectionLabel] = this.getSectionHyperlink(curSectionLabel)
                        .addBEMClass('nav__list-link')
                        .addBEMSuffix(curSectionLabel + (firstNavLink ? ' selected' : '') + (curSectionImportant ? ' important' : ''))
                        .text(curSectionTitle)
                        .appendTo($('<li>').appendTo(this.$navList));
                }
            }, this);

            // call a callback if the user scrolls past the top of the nav in either direction
            // the nav may, for example, be shown or hidden as a result
            _.bindDefer(function () {
                this.$el.onScrollTop(this.notifyTopHit || _.noop, true);
            }, this);
        }
    });
});