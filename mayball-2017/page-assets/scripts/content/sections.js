/*
|-------------------------------------------
| sections
|-------------------------------------------
|
| an array containing the data for all sections
|
| type:     Array
| author:   Josh Bambrick
| version:  0.0.1
| modified: 11/12/14
|
*/

define([
    'text!templates/book.html',
    'text!templates/night.html',
    'text!templates/entertainment.html',
    'text!templates/ticket-info.html',
    'text!templates/sponsors.html',
    'text!templates/charities.html',
    'text!templates/work.html',
    'text!templates/committee.html'
], function (
    bookTemplate,
    nightTemplate,
    entsTemplate,
    ticketInfoTemplate,
    sponsorsTemplate,
    charitiesTemplate,
    workTemplate,
    committeeTemplate
) {
    return [{
        // identifier-friendly label
        label: 'hero',
        // second is important
        // given special styling in locations (eg navigation links)
        important: true,
        // title (defaults to `label` if undefined)
        // used to refer to this section with user  (eg navigation links)
        title: 'Between The Lines',
        titleTemplate: 'BETWEEN THE LINES',
        type: 'book-hero',
        introTemplate: '<span><span class="first-word"><span class="first-letter">O</span><span class="rest">nce</span></span> <span class="rest">upon a time...</span></span>',
        bookTemplate: bookTemplate,
        spriteImageTemplate: '<span class="section__sprite-img section__sprite-img--romance"></span>',
        introLineAppearDelay: 1500,
        appearDelay: 2250,
        openDelay: 4500
    }, {
        label: 'section-gap-0',
        type: 'section-gap',
        layers: ['parallax', 'rotate', 'rotate']
    }, {
        label: 'night',
        title: 'The Night',
        titleTemplate: 'The Night',
        type: 'content',
        template: nightTemplate,
        spriteImageType: 'steampunk'
    }, {
        label: 'section-gap-1',
        type: 'section-gap',
        layers: ['parallax', 'rotate', 'rotate']
    }, {
        label: 'entertainment',
        title: 'Entertainment',
        titleTemplate: 'Enter<wbr>tain<wbr>ment',
        type: 'content',
        template: entsTemplate,
        spriteImageType: 'gothic'
    }, {
        label: 'section-gap-2',
        type: 'section-gap',
        layers: ['parallax', 'rotate', 'rotate']
    }, {
        label: 'ticket-info',
        title: 'Tickets',
        titleTemplate: 'Tick<wbr>ets',
        type: 'content',
        template: ticketInfoTemplate,
        spriteImageType: 'fantasy'
    }, {
        label: 'section-gap-3',
        type: 'section-gap',
        layers: ['parallax', 'rotate', 'rotate']
    }, {
        label: 'sponsors',
        title: 'Sponsors',
        titleTemplate: 'Spon<wbr>sors',
        type: 'content',
        template: sponsorsTemplate,
        spriteImageType: 'modernism'
    }, {
        label: 'section-gap-4',
        type: 'section-gap',
        layers: ['parallax', 'rotate', 'rotate']
    }, {
        label: 'charities',
        title: 'Charities',
        titleTemplate: 'Char<wbr>ities',
        type: 'content',
        template: charitiesTemplate,
        spriteImageType: 'mystery'
    }, {
        label: 'section-gap-5',
        type: 'section-gap',
        layers: ['parallax']
    }, /*{
        label: 'work',
        title: 'Work',
        titleTemplate: 'Work',
        type: 'content',
        template: workTemplate
    },*/ {
        label: 'committee',
        title: 'Committee',
        titleTemplate: 'Commi<wbr>ttee',
        type: 'content',
        template: committeeTemplate,
        noImage: true
    }, {
        label: 'section-gap-6',
        type: 'section-gap',
        layers: ['parallax', {type: 'rotate', title: 'This is a bit of a Wildcard graphic, eh?'}]
    }, /*{
        label: 'section-gap-7',
        type: 'section-gap',
        layers: ['parallax']
    },*/ {
        label: 'footer',
        template: '<small>&copy; Jesus May Ball 2017</small><small class="section--footer__space-pirates-link -js-space-pirates-link">Click to see the real theme.</small><div class="section--footer__space-pirates-overlay -js-space-pirates-overlay"><h1 class="section--footer__space-pirates-title">SPAAAAACE PIRATES</h1><img src="./page-assets/img/spaceship.png" class="section--footer__spaceship" /></div>',
        type: 'footer'
    }];
});