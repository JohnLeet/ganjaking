/*
* Copyright: (C) 2013 - 2023 José Conti
*/
jQuery(document).ready(function($) {
	$('.js-woo-allowed-users-settings').select2({
		ajax: {
			placeholder: 'Allowed  users',
			url: ajaxurl, // AJAX URL is predefined in WordPress admin
			dataType: 'json',
			delay: 250, // delay in ms while typing when to perform a AJAX search
			data: function (params) {
				return {
					q: params.term, // search query
					action: 'redsys_get_users_settings_search_users' // AJAX action for admin-ajax.php
				};
			},
			processResults: function( data ) {
				var options = [];
				if ( data ) {
					// data is the array of arrays, and each of them contains ID and the Label of the option
					$.each( data, function( index, text ) { // do not forget that "index" is just auto incremented value
						options.push( { id: text[0], text: text[1]  } );
					});
				}
				return {
					results: options
				};
			},
			cache: true
		},
		minimumInputLength: 3 // the minimum of symbols to input before perform a search
	});
	$('.js-woo-show-gateway-test-settings').select2({
		ajax: {
			placeholder: 'Allowed  users',
			url: ajaxurl, // AJAX URL is predefined in WordPress admin
			dataType: 'json',
			delay: 250, // delay in ms while typing when to perform a AJAX search
			data: function (params) {
				return {
					q: params.term, // search query
					action: 'redsys_get_users_settings_search_users_show_gateway' // AJAX action for admin-ajax.php
				};
			},
			processResults: function( data ) {
				var options = [];
				if ( data ) {
					// data is the array of arrays, and each of them contains ID and the Label of the option
					$.each( data, function( index, text ) { // do not forget that "index" is just auto incremented value
						options.push( { id: text[0], text: text[1]  } );
					});
				}
				return {
					results: options
				};
			},
			cache: true
		},
		minimumInputLength: 3 // the minimum of symbols to input before perform a search
	});
});