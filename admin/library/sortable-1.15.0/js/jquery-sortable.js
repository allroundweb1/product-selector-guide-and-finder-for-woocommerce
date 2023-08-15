(function (factory) {
	"use strict";
	var velosortable,
		jq,
		_this = this
	;

	if (typeof define === "function" && define.amd) {
		try {
			define(["velosortablejs", "jquery"], function(VeloSortable, $) {
				velosortable = VeloSortable;
				jq = $;
				checkErrors();
				factory(VeloSortable, $);
			});
		} catch(err) {
			checkErrors();
		}
		return;
	} else if (typeof exports === 'object') {
		try {
			velosortable = require('velosortablejs');
			jq = require('jquery');
		} catch(err) { }
	}

	if (typeof jQuery === 'function' || typeof $ === 'function') {
		jq = jQuery || $;
	}

	if (typeof VeloSortable !== 'undefined') {
		velosortable = VeloSortable;
	}

	function checkErrors() {
		if (!jq) {
			throw new Error('jQuery is required for jquery-velosortablejs');
		}

		if (!velosortable) {
			throw new Error('VeloSortableJS is required for jquery-velosortablejs (https://github.com/VeloSortableJS/VeloSortable)');
		}
	}
	checkErrors();
	factory(velosortable, jq);
})(function (VeloSortable, $) {
	"use strict";

	$.fn.velosortable = function (options) {
		var retVal,
			args = arguments;

		this.each(function () {
			var $el = $(this),
				velosortable = $el.data('velosortable');

			if (!velosortable && (options instanceof Object || !options)) {
				velosortable = new VeloSortable(this, options);
				$el.data('velosortable', velosortable);
			} else if (velosortable) {
				if (options === 'destroy') {
					velosortable.destroy();
					$el.removeData('velosortable');
				} else if (options === 'widget') {
					retVal = velosortable;
				} else if (typeof velosortable[options] === 'function') {
					retVal = velosortable[options].apply(velosortable, [].slice.call(args, 1));
				} else if (options in velosortable.options) {
					retVal = velosortable.option.apply(velosortable, args);
				}
			}
		});

		return (retVal === void 0) ? this : retVal;
	};
});
