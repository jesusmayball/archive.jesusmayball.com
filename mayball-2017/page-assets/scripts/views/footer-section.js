/*
|-------------------------------------------
| FooterSectionView
|-------------------------------------------
|
| the view of a 'footer' section
| represents a page footer
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
    'underscore',
    'backbone',
], function (
    _,
    Backbone
) {
    return Backbone.View.extend({
        tagName: 'footer',
        BEMClassName: 'section',
        BEMSuffix: 'footer parallax-footer',
        initialize: function () {
            this.render();
        },
        events: {
            'click .-js-space-pirates-link': 'showOverlay',
            'click .-js-space-pirates-overlay': 'hideOverlay',
        },
        showOverlay: function () {
            this.$el.addBEMSuffix('overlay');
        },
        hideOverlay: function () {
            this.$el.removeBEMSuffix('overlay');
        },
        render: function () {
            var sectionLabel = this.model.get('label');

            this.$el.addBEMSuffix(sectionLabel).html(_.render(this.model.get('template')));
        }
    });
});