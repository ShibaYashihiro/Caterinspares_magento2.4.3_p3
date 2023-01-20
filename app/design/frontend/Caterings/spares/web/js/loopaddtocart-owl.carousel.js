define([
	'jquery',
	'jquery-ui-modules/widget',
	'jquery-ui-modules/core',
	'AtAddToCart'
], function ($) {
	'use strict';

	$.widget('ox.AtloopOwlAddtocart', {
		options: {
		},
		_create: function () {
			this.element.find('.cloned [data-role=tocart-form]').AtAddToCart();
			this.element.on('initialize.owl.carousel initialized.owl.carousel', function (e) {
				$(this).find('.cloned [data-role=tocart-form]').AtAddToCart();
			});
		}
	});

	return $.ox.AtloopOwlAddtocart;
});
