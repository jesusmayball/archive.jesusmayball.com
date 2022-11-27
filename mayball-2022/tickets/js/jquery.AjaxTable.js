/*
 *  Project: AjaxTable
 *  Description: Render tabulatable data from an ajax call. Some dependencies on the twitter bootstrap are assumed but can be overriden.
 *  Author: Peter Cowan
 *  License: TBD
 */


;(function ( $, window, document, undefined ) {

    // undefined is used here as the undefined global variable in ECMAScript 3 is
    // mutable (ie. it can be changed by someone else). undefined isn't really being
    // passed in so we can ensure the value of it is truly undefined. In ES5, undefined
    // can no longer be modified.

    // window and document are passed through as local variable rather than global
    // as this (slightly) quickens the resolution process and can be more efficiently
    // minified (especially when both are regularly referenced in your plugin).

    // Create the defaults once
    var pluginName = "ajaxTable",
        defaults = {
            loadingHTML: "<img src=\"/res/ajax.gif\" alt=\"Please wait...\"/><p>Please wait...</p>",
		    ajaxURL : "",	
		    ajaxType : "GET",
		    ajaxData : {},
		    headingClasses : [],
		    headingTitles : [],
		    eachSelector : null,
		    each : function(i, callbacks, data) {},
		    jsonErrorSelector : "error",
		    //pagination
		    enablePagination : false,
		    numberOnPage : 20,
		    tableClass : null,
		    paginationCustom : false,
		    //used when paginationCustom is true
		    paginationURLParam : "page",
		    paginationZeroBased : true,
		    paginationTotalSelector : "total",
		    emptyMessage : "No results"
        };

    // The actual plugin constructor
    function Plugin( element, options ) {
        this.element = $(element);
		this.elementID = element.id;
		
		this.loadingElement = null;
		this.tableElement = null;
		this.errorElement = null;

        // jQuery has an extend method which merges the contents of two or
        // more objects, storing the result in the first object. The first object
        // is generally empty as we don't want to alter the default options for
        // future instances of the plugin
        this.options = $.extend( {}, defaults, options );

        this._defaults = defaults;
        this._name = pluginName;

		// Callbacks
		this.each = this.options.each;

        this.init();
    }

    Plugin.prototype = {

        init: function() {
	        this.tableElement = $("<table class=\"ajax-table\"></table>").appendTo(this.element);
	        tableElement = this.tableElement;
	        if (this.options.tableClass) {
	        	if (typeof(this.options.tableClass) === "object") {
	        		$.each(this.options.tableClass, function(i, tableClass) {
	        			tableElement.addClass(tableClass);
	        		});
	        	}
	        	else {
	        		tableElement.addClass(this.options.tableClass);
	        	}
	        }
		    this.loadingElement = $("<div id=\"" + this.elementID + "-loading\" class=\"ajax-table-loading\" style=\"display:none\">" + this.options.loadingHTML + "</div>").appendTo(this.element);
		    this.errorElement = $("<div id=\"" + this.elementID + "-error\" class=\"ajax-table-error\" style=\"display:none\"></div>").appendTo(this.element);

		    if (this.initialErrorChecking()) {
			this.render();
		    }
        },
	
	initialErrorChecking: function () {
	    //Assert the numberOnPage is 1 or more
            var errors = new Array();
	    if (this.options.numberOnPage < 1) {
		errors.push("Invalid value for numberOnPage argument");
	    }

	    if (errors.length != 0) {
		var errorMessage = "";
		$.each(errors, function (i, value) {
		    errorMessage += value + "<br />";
		});
		this.showError(errorMessage);
		return false;
	    }
	    else {
		return true;
	    }
	},

	clearResults: function () {
	    this.tableElement.empty();
	    $(this.element).find(".ajax-table-pagination").remove();
	},

	render: function (page) {
	    this.clearResults();
	    this.renderHeader();
	    this.renderTable(page);
	},
	
	renderTable: function(page) {
	    var plugin = this;
	    var data = this.options.ajaxData;
	    var lowerBound = 0;
	    var upperBound = Number.MAX_VALUE;
	    if (page == null || page < 1) {
		page = 1;
	    }
	    if (this.options.enablePagination) {
		if(this.options.paginationCustom) {
		    data[this.options.paginationURLParam] = this.options.paginationZeroBased ? page - 1 : page;
		}
		else {
		    lowerBound = (page - 1) * this.options.numberOnPage;
		    upperBound = page * this.options.numberOnPage;
		}
	    }

	    $.ajax({
		// if (page == null) will work
		type : plugin.options.ajaxType,
		url : plugin.options.ajaxURL,
		data : plugin.options.ajaxData,
		dataType : "json",
		async : true,
		beforeSend: function () { plugin.showLoader.call(plugin); },
		complete: function () { plugin.hideLoader.call(plugin); },
		success: function(json) {
			if (plugin.options.jsonErrorSelector in json) {
				plugin.showError(json[plugin.jsonErrorSelector]);
			}
			else {
				plugin.hideError.call(plugin);
				var count = 0;
				var each = plugin.options.eachSelector == null ? json : json[plugin.options.eachSelector];
				$.each(each, function (i, data) {
				    if (count < lowerBound || count >= upperBound) {
					count++;
					return;
				    }

				    var clazz = "";
				    if (count % 2 == 0) {
					clazz += "ajax-table-result-odd";
				    }
				    else {
					clazz += "ajax-table-result-even";
				    }
				    var row = $("<tr id=\"" + plugin.elementID + "-" + i + "\" class=\"ajax-table-result " + clazz + "\"></tr>").appendTo(plugin.tableElement);
				    var callBacks = {};
				    $.each(plugin.options.headingClasses, function (j, headingClass) {
					var cell = $("<td class=\"" + headingClass + "\"></td>").appendTo(row);
					callBacks[headingClass] = function (element) {
									cell.append(element);
			 	    				};
				    });
				    plugin.each(i, callBacks, data);
				    count++;
				});

				if (plugin.options.enablePagination && count != 0) {
					var total = 0;
					if (plugin.options.paginationCustom) {
					    total = json[plugin.options.paginationTotalSelector];
					}
					else {
					    total = plugin.getObjectSize(each);
					}
					//render pagination controls
					plugin.renderPagination(page, total);
				}

				if (count == 0) {
				    plugin.showError.call(plugin, plugin.options.emptyMessage);	
				}
				else {
				    plugin.showResults.call(plugin);
				}
			}
		},
		error: function(errorJson, errorType, errorMessage) {
			var message = "An error occured when making the ajax call.";
			if (errorMessage != "") {
				message += "<br />" + errorType.charAt(0).toUpperCase() + errorType.slice(1) + " - " + errorMessage;
			}
			plugin.showError.call(plugin, message);
		}
	    });
	},

	renderHeader : function () {
		var tableElementHeading = $("<tr id=\"" + this.elementID + "-results-heading\" class=\"ajax-table-result-heading\"></tr>").appendTo(this.tableElement);
		for (var i = 0; i < this.options.headingTitles.length; i++) {
			tableElementHeading.append("<th class=\"" + this.options.headingClasses[i] + "\">" + this.options.headingTitles[i] + "</th>");
		}
	},

	renderPagination: function (page, total) {
		var plugin = this;
		var pages = Math.ceil(total / this.options.numberOnPage);
		if (pages > 1) {
			var paginationul = $("<ul class=\"pagination\"></ul>").appendTo($("<div id=\"" + this.elementID + "-pagination\" class=\"ajax-table-pagination text-center\"></div>").appendTo(this.element));
				
			function createRenderCallback(i) {
				return function() {
					plugin.render(i);
				}
			}
			if (page > 1) {
				var prev = $("<li><a href=\"javascript:void(0)\">&laquo;</a></li>").appendTo(paginationul);
				prev.click(createRenderCallback(page - 1));
			}
			for (var i = 1; i <= pages; i++) {
				var t = null;
				if (page == i) {
					t = $("<li><a class=\"active\" href=\"javascript:void(0)\">" + i + "</a></li>").appendTo(paginationul);
				}
				else {
					t = $("<li><a href=\"javascript:void(0)\">" + i + "</a></li>").appendTo(paginationul);
				}
				t.click(createRenderCallback(i));
			}
			if (page < pages) {
				var next = $("<li><a href=\"javascript:void(0)\">&raquo;</a></li>").appendTo(paginationul);
				next.click(createRenderCallback(page + 1));
			}
		}
	},

	hideError : function(message) {
		this.errorElement.hide();
	},

	showError : function(message) {
		this.errorElement.html(message);
		this.errorElement.show();
		this.hideResults();
		this.hideLoader();
	},
	
	hideLoader : function() {
		this.loadingElement.hide();
	},
	
	showLoader : function() {
		this.loadingElement.show();
		this.hideResults();
		this.hideError();
	},
	
	hideResults : function() {
		this.tableElement.hide();
	},
	
	showResults : function() {
		this.tableElement.show();
		this.hideLoader();
		this.hideError();
	},

	getObjectSize : function (obj) { 
		var size = 0, key;
		for (key in obj) {
		    if (obj.hasOwnProperty(key)) {
			size++;
		    }
		}
		return size;
	}
    };

    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations and which
    // re-renders the table instead
    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName, new Plugin( this, options ));
            }
	    else {
		$.data(this, "plugin_" + pluginName).render();
	    }
        });
    };

})( jQuery, window, document );