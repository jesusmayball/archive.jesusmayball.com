/*
|-------------------------------------------
| app
|-------------------------------------------
|
| the instance of AppView which is added to the DOM
|
| type:     AppView
| author:   Josh Bambrick
| version:  0.0.1
| modified: 11/12/14
|
*/

define([
    'collections/sections.instance',
    'views/app'
], function (
    sections,
    AppView
) {
    return new AppView({collection: sections});
});