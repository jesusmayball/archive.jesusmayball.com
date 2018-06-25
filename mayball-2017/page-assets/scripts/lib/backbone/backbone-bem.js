(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define(['backbone'], factory);
    } else {
        // Browser globals
        factory(this.Backbone);
    }
}(function (Backbone) {
    var oldEnsureElement = Backbone.View.prototype._ensureElement;  

    // BEM class & suffix support for views
    Backbone.View.prototype._ensureElement = function (options) {
        var options = options || {};

        oldEnsureElement.call(this);

        if (this.BEMClassName || options.BEMClassName) {
            this.$el.addBEMClass(this.BEMClassName || options.BEMClassName);
        }

        if (this.BEMSuffix || options.BEMSuffix) {
            this.$el.addBEMSuffix(this.BEMSuffix || options.BEMSuffix);    
        }
    };
}));