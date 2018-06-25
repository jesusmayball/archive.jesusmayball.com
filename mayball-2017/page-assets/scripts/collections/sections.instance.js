/*
|-------------------------------------------
| sections
|-------------------------------------------
|
| the instance of SectionsCollection which is contains all of the sections
|
| type:     SectionsCollection
| author:   Josh Bambrick
| version:  0.0.1
| modified: 11/12/14
|
*/

define([
    'collections/sections',
    'content/sections'
], function (
    SectionsCollection,
    sections
) {
    return new SectionsCollection(sections);
});