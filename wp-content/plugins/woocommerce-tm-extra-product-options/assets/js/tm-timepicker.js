/*! jQuery Timepicker Addon - v1.6.3 - 2016-04-20
 * http://trentrichardson.com/examples/timepicker
 * Modified version of jQuery Timepicker
 * Copyright (c) 2016 Trent Richardson; Licensed MIT */
( function( factory ) {
	'use strict';
	if ( typeof window.define === 'function' && window.define.amd ) {
		window.define( [ 'jquery', 'jquery-ui' ], factory );
	} else {
		factory( window.jQuery );
	}
}( function( $ ) {
	'use strict';

	/*
	 * jQuery extend now ignores nulls!
	 */
	var extendRemove = function( target, props ) {
		var name;
		$.extend( target, props );
		for ( name in props ) {
			if ( props[ name ] === null || props[ name ] === undefined ) {
				target[ name ] = props[ name ];
			}
		}
		return target;
	};

	/*
	 * jQuery isEmptyObject does not check hasOwnProperty - if someone has added to the object prototype,
	 * it will return false for all objects
	 */
	var isEmptyObject = function( obj ) {
		var prop;
		for ( prop in obj ) {
			if ( Object.prototype.hasOwnProperty.call( obj, prop ) ) {
				return false;
			}
		}
		return true;
	};

	/*
	 * Determine by the time format which units are supported
	 * Returns an object of booleans for each unit
	 */
	var detectSupport = function( timeFormat ) {
		var tf = timeFormat.replace( /'.*?'/g, '' ).toLowerCase(), // removes literals
			isIn = function( f, t ) {
				// does the format contain the token?
				return f.indexOf( t ) !== -1 ? true : false;
			};
		return {
			hour: isIn( tf, 'h' ),
			minute: isIn( tf, 'm' ),
			second: isIn( tf, 's' ),
			millisec: isIn( tf, 'l' ),
			microsec: isIn( tf, 'c' ),
			timezone: isIn( tf, 'z' ),
			ampm: isIn( tf, 't' ) && isIn( timeFormat, 'h' ),
			iso8601: isIn( timeFormat, 'Z' )
		};
	};

	/*
	 * Converts 24 hour format into 12 hour
	 * Returns 12 hour without leading 0
	 */
	var convert24to12 = function( hour ) {
		hour %= 12;

		if ( hour === 0 ) {
			hour = 12;
		}

		return String( hour );
	};

	var computeEffectiveSetting = function( settings, property ) {
		return settings && settings[ property ] ? settings[ property ] : $.tm_timepicker._defaults[ property ];
	};

	/*
	 * Splits datetime string into date and time substrings.
	 * Throws exception when date can't be parsed
	 * Returns {dateString: dateString, timeString: timeString}
	 */
	var splitDateTime = function( dateTimeString, timeSettings ) {
		// The idea is to get the number separator occurrences in datetime and the time format requested (since time has
		// fewer unknowns, mostly numbers and am/pm). We will use the time pattern to split.
		var separator = computeEffectiveSetting( timeSettings, 'separator' ),
			format = computeEffectiveSetting( timeSettings, 'timeFormat' ),
			timeParts = format.split( separator ), // how many occurrences of separator may be in our format?
			timePartsLen = timeParts.length,
			allParts = dateTimeString.split( separator ),
			allPartsLen = allParts.length;

		if ( allPartsLen > 1 ) {
			return {
				dateString: allParts.splice( 0, allPartsLen - timePartsLen ).join( separator ),
				timeString: allParts.splice( 0, timePartsLen ).join( separator )
			};
		}

		return {
			dateString: dateTimeString,
			timeString: ''
		};
	};

	/*
	 * Internal function to parse datetime interval
	 * Returns: {date: Date, timeObj: Object}, where
	 *   date - parsed date without time (type Date)
	 *   timeObj = {hour: , minute: , second: , millisec: , microsec: } - parsed time. Optional
	 */
	var parseDateTimeInternal = function( dateFormat, timeFormat, dateTimeString, dateSettings, timeSettings ) {
		var date, parts, parsedTime;

		parts = splitDateTime( dateTimeString, timeSettings );
		date = $.tm_datepicker._base_parseDate( dateFormat, parts.dateString, dateSettings );

		if ( parts.timeString === '' ) {
			return {
				date: date
			};
		}

		parsedTime = $.tm_datepicker.parseTime( timeFormat, parts.timeString, timeSettings );

		if ( ! parsedTime ) {
			throw 'Wrong time format';
		}

		return {
			date: date,
			timeObj: parsedTime
		};
	};

	/*
	 * Internal function to set timezone_select to the local timezone
	 */
	var selectLocalTimezone = function( tp_inst, date ) {
		var now;
		if ( tp_inst && tp_inst.timezone_select ) {
			now = date || new Date();
			tp_inst.timezone_select.val( -now.getTimezoneOffset() );
		}
	};

	/*
	 * Timepicker manager.
	 * Use the singleton instance of this class, $.tm_timepicker, to interact with the time picker.
	 * Settings for (groups of) time pickers are maintained in an instance object,
	 * allowing multiple different settings on the same page.
	 */
	var Timepicker = function() {
		this.regional = []; // Available regional settings, indexed by language code
		this.regional[ '' ] = {
			// Default regional settings
			currentText: 'Now',
			closeText: 'Done',
			amNames: [ 'AM', 'A' ],
			pmNames: [ 'PM', 'P' ],
			timeFormat: 'HH:mm',
			timeSuffix: '',
			timeOnlyTitle: 'Choose Time',
			timeText: 'Time',
			hourText: 'Hour',
			minuteText: 'Minute',
			secondText: 'Second',
			millisecText: 'Millisecond',
			microsecText: 'Microsecond',
			timezoneText: 'Time Zone',
			isRTL: false
		};
		this._defaults = {
			// Global defaults for all the datetime picker instances
			showButtonPanel: true,
			timeOnly: false,
			timeOnlyShowDate: false,
			showHour: null,
			showMinute: null,
			showSecond: null,
			showMillisec: null,
			showMicrosec: null,
			showTimezone: null,
			showTime: true,
			stepHour: 1,
			stepMinute: 1,
			stepSecond: 1,
			stepMillisec: 1,
			stepMicrosec: 1,
			hour: 0,
			minute: 0,
			second: 0,
			millisec: 0,
			microsec: 0,
			timezone: null,
			hourMin: 0,
			minuteMin: 0,
			secondMin: 0,
			millisecMin: 0,
			microsecMin: 0,
			hourMax: 23,
			minuteMax: 59,
			secondMax: 59,
			millisecMax: 999,
			microsecMax: 999,
			minDateTime: null,
			maxDateTime: null,
			maxTime: null,
			minTime: null,
			onSelect: null,
			hourGrid: 0,
			minuteGrid: 0,
			secondGrid: 0,
			millisecGrid: 0,
			microsecGrid: 0,
			alwaysSetTime: true,
			separator: ' ',
			altFieldTimeOnly: true,
			altTimeFormat: null,
			altSeparator: null,
			altTimeSuffix: null,
			altRedirectFocus: true,
			pickerTimeFormat: null,
			pickerTimeSuffix: null,
			showTimepicker: true,
			timezoneList: null,
			addSliderAccess: false,
			sliderAccessArgs: null,
			controlType: 'slider',
			oneLine: false,
			defaultValue: null,
			parse: 'strict',
			afterInject: null
		};
		$.extend( this._defaults, this.regional[ '' ] );
	};

	/*
	 * Lets not redefine tm_timepicker, Prevent "Uncaught RangeError: Maximum call stack size exceeded"
	 */
	$.ui.tm_timepicker = $.ui.tm_timepicker || {};
	if ( $.ui.tm_timepicker.version ) {
		return;
	}

	/*
	 * Extend jQueryUI, get it started with our version number
	 */
	$.extend( $.ui, {
		tm_timepicker: {
			version: '1.6.3'
		}
	} );

	$.extend( Timepicker.prototype, {
		$input: null,
		$altInput: null,
		$timeObj: null,
		inst: null,
		hour_slider: null,
		minute_slider: null,
		second_slider: null,
		millisec_slider: null,
		microsec_slider: null,
		timezone_select: null,
		maxTime: null,
		minTime: null,
		hour: 0,
		minute: 0,
		second: 0,
		millisec: 0,
		microsec: 0,
		timezone: null,
		hourMinOriginal: null,
		minuteMinOriginal: null,
		secondMinOriginal: null,
		millisecMinOriginal: null,
		microsecMinOriginal: null,
		hourMaxOriginal: null,
		minuteMaxOriginal: null,
		secondMaxOriginal: null,
		millisecMaxOriginal: null,
		microsecMaxOriginal: null,
		ampm: '',
		formattedDate: '',
		formattedTime: '',
		formattedDateTime: '',
		timezoneList: null,
		units: [ 'hour', 'minute', 'second', 'millisec', 'microsec' ],
		support: {},
		control: null,

		/*
		 * Override the default settings for all instances of the time picker.
		 * @param  {Object} settings  object - the new settings to use as defaults (anonymous object)
		 * @return {Object} the manager object
		 */
		setDefaults: function( settings ) {
			extendRemove( this._defaults, settings || {} );
			return this;
		},

		/*
		 * Create a new Timepicker instance
		 */
		_newInst: function( $input, opts ) {
			var tp_inst = new Timepicker(),
				inlineSettings = {},
				fns = {},
				overrides,
				i;
			var attrName;
			var attrValue;
			var timezoneList;
			var tzl;
			var tzi = 0;
			var tzv = null;

			for ( attrName in this._defaults ) {
				if ( Object.prototype.hasOwnProperty.call( this._defaults, attrName ) ) {
					attrValue = $input.attr( 'data-' + attrName );
					if ( attrValue ) {
						inlineSettings[ attrName ] = attrValue;
					}
				}
			}

			overrides = {
				beforeShow: function( input, dp_inst ) {
					if ( typeof tp_inst._defaults.evnts.beforeShow === 'function' ) {
						return tp_inst._defaults.evnts.beforeShow.call( $input[ 0 ], input, dp_inst, tp_inst );
					}
				},
				onChangeMonthYear: function( year, month, dp_inst ) {
					// Update the time as well : this prevents the time from disappearing from the $input field.
					// tp_inst._updateDateTime(dp_inst);
					if ( typeof tp_inst._defaults.evnts.onChangeMonthYear === 'function' ) {
						tp_inst._defaults.evnts.onChangeMonthYear.call( $input[ 0 ], year, month, dp_inst, tp_inst );
					}
				},
				onClose: function( dateText, dp_inst ) {
					if ( tp_inst.timeDefined === true && $input.val() !== '' ) {
						tp_inst._updateDateTime( dp_inst );
					}
					if ( typeof tp_inst._defaults.evnts.onClose === 'function' ) {
						tp_inst._defaults.evnts.onClose.call( $input[ 0 ], dateText, dp_inst, tp_inst );
					}
				}
			};
			for ( i in overrides ) {
				if ( Object.prototype.hasOwnProperty.call( overrides, i ) ) {
					fns[ i ] = opts[ i ] || this._defaults[ i ] || null;
				}
			}

			tp_inst._defaults = $.extend( {}, this._defaults, inlineSettings, opts, overrides, {
				evnts: fns,
				tm_timepicker: tp_inst // add tm_timepicker as a property of tm_datepicker: $.tm_datepicker._get(dp_inst, 'tm_timepicker');
			} );
			tp_inst.amNames = $.map( tp_inst._defaults.amNames, function( val ) {
				return val.toUpperCase();
			} );
			tp_inst.pmNames = $.map( tp_inst._defaults.pmNames, function( val ) {
				return val.toUpperCase();
			} );

			// detect which units are supported
			tp_inst.support = detectSupport( tp_inst._defaults.timeFormat + ( tp_inst._defaults.pickerTimeFormat ? tp_inst._defaults.pickerTimeFormat : '' ) + ( tp_inst._defaults.altTimeFormat ? tp_inst._defaults.altTimeFormat : '' ) );

			// controlType is string - key to our this._controls
			if ( typeof tp_inst._defaults.controlType === 'string' ) {
				if ( tp_inst._defaults.controlType === 'slider' && typeof $.ui.slider === 'undefined' ) {
					tp_inst._defaults.controlType = 'select';
				}
				tp_inst.control = tp_inst._controls[ tp_inst._defaults.controlType ];
			} else {
				// controlType is an object and must implement create, options, value methods
				tp_inst.control = tp_inst._defaults.controlType;
			}

			// prep the timezone options
			timezoneList = [ -720, -660, -600, -570, -540, -480, -420, -360, -300, -270, -240, -210, -180, -120, -60, 0, 60, 120, 180, 210, 240, 270, 300, 330, 345, 360, 390, 420, 480, 525, 540, 570, 600, 630, 660, 690, 720, 765, 780, 840 ];
			if ( tp_inst._defaults.timezoneList !== null ) {
				timezoneList = tp_inst._defaults.timezoneList;
			}
			tzl = timezoneList.length;

			if ( tzl > 0 && typeof timezoneList[ 0 ] !== 'object' ) {
				for ( ; tzi < tzl; tzi += 1 ) {
					tzv = timezoneList[ tzi ];
					timezoneList[ tzi ] = {
						value: tzv,
						label: $.tm_timepicker.timezoneOffsetString( tzv, tp_inst.support.iso8601 )
					};
				}
			}
			tp_inst._defaults.timezoneList = timezoneList;

			// set the default units
			tp_inst.timezone = tp_inst._defaults.timezone !== null ? $.tm_timepicker.timezoneOffsetNumber( tp_inst._defaults.timezone ) : new Date().getTimezoneOffset() * -1;
			tp_inst.hour = tp_inst._defaults.hour < tp_inst._defaults.hourMin ? tp_inst._defaults.hourMin : tp_inst._defaults.hour > tp_inst._defaults.hourMax ? tp_inst._defaults.hourMax : tp_inst._defaults.hour;
			tp_inst.minute = tp_inst._defaults.minute < tp_inst._defaults.minuteMin ? tp_inst._defaults.minuteMin : tp_inst._defaults.minute > tp_inst._defaults.minuteMax ? tp_inst._defaults.minuteMax : tp_inst._defaults.minute;
			tp_inst.second = tp_inst._defaults.second < tp_inst._defaults.secondMin ? tp_inst._defaults.secondMin : tp_inst._defaults.second > tp_inst._defaults.secondMax ? tp_inst._defaults.secondMax : tp_inst._defaults.second;
			tp_inst.millisec =
				tp_inst._defaults.millisec < tp_inst._defaults.millisecMin ? tp_inst._defaults.millisecMin : tp_inst._defaults.millisec > tp_inst._defaults.millisecMax ? tp_inst._defaults.millisecMax : tp_inst._defaults.millisec;
			tp_inst.microsec =
				tp_inst._defaults.microsec < tp_inst._defaults.microsecMin ? tp_inst._defaults.microsecMin : tp_inst._defaults.microsec > tp_inst._defaults.microsecMax ? tp_inst._defaults.microsecMax : tp_inst._defaults.microsec;
			tp_inst.ampm = '';
			tp_inst.$input = $input;

			if ( tp_inst._defaults.altField ) {
				tp_inst.$altInput = $( tp_inst._defaults.altField );
				if ( tp_inst._defaults.altRedirectFocus === true ) {
					tp_inst.$altInput
						.css( {
							cursor: 'pointer'
						} )
						.on( 'focus', function() {
							$input.trigger( 'focus' );
						} );
				}
			}

			if ( tp_inst._defaults.minDate === 0 || tp_inst._defaults.minDateTime === 0 ) {
				tp_inst._defaults.minDate = new Date();
			}
			if ( tp_inst._defaults.maxDate === 0 || tp_inst._defaults.maxDateTime === 0 ) {
				tp_inst._defaults.maxDate = new Date();
			}

			// tm_datepicker needs minDate/maxDate, tm_timepicker needs minDateTime/maxDateTime..
			if ( tp_inst._defaults.minDate !== undefined && tp_inst._defaults.minDate instanceof Date ) {
				tp_inst._defaults.minDateTime = new Date( tp_inst._defaults.minDate.getTime() );
			}
			if ( tp_inst._defaults.minDateTime !== undefined && tp_inst._defaults.minDateTime instanceof Date ) {
				tp_inst._defaults.minDate = new Date( tp_inst._defaults.minDateTime.getTime() );
			}
			if ( tp_inst._defaults.maxDate !== undefined && tp_inst._defaults.maxDate instanceof Date ) {
				tp_inst._defaults.maxDateTime = new Date( tp_inst._defaults.maxDate.getTime() );
			}
			if ( tp_inst._defaults.maxDateTime !== undefined && tp_inst._defaults.maxDateTime instanceof Date ) {
				tp_inst._defaults.maxDate = new Date( tp_inst._defaults.maxDateTime.getTime() );
			}
			tp_inst.$input.on( 'focus', function() {
				tp_inst._onFocus();
			} );

			tp_inst.$input.next( '.ui-tm-timepicker-trigger' ).on( 'click', function() {
				tp_inst._onFocus();
				/*if ( $.tm_datepicker._datepickerShowing && $.tm_datepicker._lastInput === input[ 0 ] ) {
                 $.tm_datepicker._hideDatepicker();
                 } else if ( $.tm_datepicker._datepickerShowing && $.tm_datepicker._lastInput !== input[ 0 ] ) {
                 $.tm_datepicker._hideDatepicker();
                 $.tm_datepicker._showDatepicker( input[ 0 ] );
                 } else {
                 $.tm_datepicker._showDatepicker( input[ 0 ] );
                 }*/
				return false;
			} );

			return tp_inst;
		},

		/*
		 * add our sliders to the calendar
		 */
		_addTimePicker: function( dp_inst ) {
			var currDT = $.epoAPI.util.trim( this.$altInput && this._defaults.altFieldTimeOnly ? this.$input.val() + ' ' + this.$altInput.val() : this.$input.val() );

			this.timeDefined = this._parseTime( currDT );
			this._limitMinMaxDateTime( dp_inst, false );
			this._injectTimePicker();
			this._afterInject();
		},

		/*
		 * parse the time string from input value or _setTime
		 */
		_parseTime: function( timeString, withDate ) {
			var dp_dateFormat;
			var parseRes;
			var timeObj;

			if ( ! this.inst ) {
				this.inst = $.tm_datepicker._getInst( this.$input[ 0 ] );
			}

			if ( withDate || ! this._defaults.timeOnly ) {
				dp_dateFormat = $.tm_datepicker._get( this.inst, 'dateFormat' );
				try {
					parseRes = parseDateTimeInternal( dp_dateFormat, this._defaults.timeFormat, timeString, $.tm_datepicker._getFormatConfig( this.inst ), this._defaults );
					if ( ! parseRes.timeObj ) {
						return false;
					}
					$.extend( this, parseRes.timeObj );
				} catch ( err ) {
					$.tm_timepicker.log( 'Error parsing the date/time string: ' + err + '\ndate/time string = ' + timeString + '\ntimeFormat = ' + this._defaults.timeFormat + '\ndateFormat = ' + dp_dateFormat );
					return false;
				}
				return true;
			}
			timeObj = $.tm_datepicker.parseTime( this._defaults.timeFormat, timeString, this._defaults );
			if ( ! timeObj ) {
				return false;
			}
			$.extend( this, timeObj );
			return true;
		},

		/*
		 * Handle callback option after injecting tm_timepicker
		 */
		_afterInject: function() {
			var o = this.inst.settings;
			if ( typeof o.afterInject === 'function' ) {
				o.afterInject.call( this );
			}
		},

		_ui_tpicker_onclick: function( event ) {
			var $t = $( event.target ),
				h = $t.html(),
				n = parseInt( h.replace( /[^0-9]/g ), 10 ),
				ap = h.replace( /[^apm]/gi ),
				f = $t.data( 'for' ); // loses scope, so we use data-for

			if ( f === 'hour' ) {
				if ( ap.indexOf( 'p' ) !== -1 && n < 12 ) {
					n += 12;
				} else if ( ap.indexOf( 'a' ) !== -1 && n === 12 ) {
					n = 0;
				}
			}

			event.data.tp_inst.control.value( event.data.tp_inst, event.data.tp_inst[ f + '_slider' ], event.data.litem, n );

			event.data.tp_inst._onTimeChange();
			event.data.tp_inst._onSelectHandler();
		},
		/*
		 * generate and inject html for tm_timepicker into ui tm_datepicker
		 */
		_injectTimePicker: function() {
			var $dp = this.inst.dpDiv,
				o = this.inst.settings,
				tp_inst = this,
				litem = '',
				uitem = '',
				show = null,
				max = {},
				gridSize = {},
				size = null,
				i = 0,
				l = 0;
			var noDisplay;
			var html;
			var h;
			var tmph;
			var m;
			var showTz;
			var $tp;
			var local_timezone;
			var $buttonPanel;
			var timeDefined;
			var sliderAccessArgs;
			var rtl;

			// Prevent displaying twice
			if ( $dp.find( 'div.ui-tm-timepicker-div' ).length === 0 && o.showTimepicker ) {
				noDisplay = ' ui_tpicker_unit_hide';
				html =
					'<div class="ui-tm-timepicker-div' +
					( o.isRTL ? ' ui-tm-timepicker-rtl' : '' ) +
					( o.oneLine && o.controlType === 'select' ? ' ui-tm-timepicker-oneLine' : '' ) +
					'"><dl>' +
					'<dt class="ui_tpicker_time_label' +
					( o.showTime ? '' : noDisplay ) +
					'">' +
					o.timeText +
					'</dt>' +
					'<dd class="ui_tpicker_time ' +
					( o.showTime ? '' : noDisplay ) +
					'"><input class="ui_tpicker_time_input" ' +
					( o.timeInput ? '' : 'disabled' ) +
					'></dd>';

				// Create the markup
				for ( i = 0, l = this.units.length; i < l; i += 1 ) {
					litem = this.units[ i ];
					uitem = litem.substring( 0, 1 ).toUpperCase() + litem.substring( 1 );
					show = o[ 'show' + uitem ] !== null ? o[ 'show' + uitem ] : this.support[ litem ];

					// Added by Peter Medeiros:
					// - Figure out what the hour/minute/second max should be based on the step values.
					// - Example: if stepMinute is 15, then minMax is 45.
					max[ litem ] = parseInt( o[ litem + 'Max' ] - ( ( o[ litem + 'Max' ] - o[ litem + 'Min' ] ) % o[ 'step' + uitem ] ), 10 );
					gridSize[ litem ] = 0;

					html +=
						'<dt class="ui_tpicker_' +
						litem +
						'_label' +
						( show ? '' : noDisplay ) +
						'">' +
						o[ litem + 'Text' ] +
						'</dt>' +
						'<dd class="ui_tpicker_' +
						litem +
						( show ? '' : noDisplay ) +
						'"><div class="ui_tpicker_' +
						litem +
						'_slider' +
						( show ? '' : noDisplay ) +
						'"></div>';

					if ( show && o[ litem + 'Grid' ] > 0 ) {
						html += '<div><table class="ui-tpicker-grid-label"><tr>';

						if ( litem === 'hour' ) {
							for ( h = o[ litem + 'Min' ]; h <= max[ litem ]; h += parseInt( o[ litem + 'Grid' ], 10 ) ) {
								gridSize[ litem ] += 1;
								tmph = $.tm_datepicker.formatTime( this.support.ampm ? 'hht' : 'HH', { hour: h }, o );
								html += '<td data-for="' + litem + '">' + tmph + '</td>';
							}
						} else {
							for ( m = o[ litem + 'Min' ]; m <= max[ litem ]; m += parseInt( o[ litem + 'Grid' ], 10 ) ) {
								gridSize[ litem ] += 1;
								html += '<td data-for="' + litem + '">' + ( m < 10 ? '0' : '' ) + m + '</td>';
							}
						}

						html += '</tr></table></div>';
					}
					html += '</dd>';
				}

				// Timezone
				showTz = o.showTimezone !== null ? o.showTimezone : this.support.timezone;
				html += '<dt class="ui_tpicker_timezone_label' + ( showTz ? '' : noDisplay ) + '">' + o.timezoneText + '</dt>';
				html += '<dd class="ui_tpicker_timezone' + ( showTz ? '' : noDisplay ) + '"></dd>';

				// Create the elements from string
				html += '</dl></div>';
				$tp = $( html );

				// if we only want time picker...
				if ( o.timeOnly === true ) {
					$tp.prepend( '<div class="tm-ui-widget-header">' + '<div class="tm-ui-tp-title">' + o.timeOnlyTitle + '</div>' + '</div>' );
					$dp.find( '.tm-ui-dp-inner-container' ).hide();
				}

				// add sliders, adjust grids, add events
				for ( i = 0, l = tp_inst.units.length; i < l; i += 1 ) {
					litem = tp_inst.units[ i ];
					uitem = litem.substring( 0, 1 ).toUpperCase() + litem.substring( 1 );
					show = o[ 'show' + uitem ] !== null ? o[ 'show' + uitem ] : this.support[ litem ];

					// add the slider
					tp_inst[ litem + '_slider' ] = tp_inst.control.create( tp_inst, $tp.find( '.ui_tpicker_' + litem + '_slider' ), litem, tp_inst[ litem ], o[ litem + 'Min' ], max[ litem ], o[ 'step' + uitem ] );

					// adjust the grid and add click event
					if ( show && o[ litem + 'Grid' ] > 0 ) {
						size = ( 100 * gridSize[ litem ] * o[ litem + 'Grid' ] ) / ( max[ litem ] - o[ litem + 'Min' ] );
						$tp.find( '.ui_tpicker_' + litem + ' table' )
							.css( {
								width: size + '%',
								marginLeft: o.isRTL ? '0' : ( size / ( -2 * gridSize[ litem ] ) ) + '%',
								marginRight: o.isRTL ? ( size / ( -2 * gridSize[ litem ] ) ) + '%' : '0',
								borderCollapse: 'collapse'
							} )
							.find( 'td' )
							.on( 'click', { that: this, tp_inst: tp_inst, litem: litem }, this._ui_tpicker_onclick )
							.css( {
								cursor: 'pointer',
								width: ( 100 / gridSize[ litem ] ) + '%',
								textAlign: 'center',
								overflow: 'hidden'
							} );
					} // end if grid > 0
				} // end for loop

				// Add timezone options
				this.timezone_select = $tp.find( '.ui_tpicker_timezone' ).append( '<select></select>' ).find( 'select' );
				$.fn.append.apply(
					this.timezone_select,
					$.map( o.timezoneList, function( val ) {
						return $( '<option />' )
							.val( typeof val === 'object' ? val.value : val )
							.text( typeof val === 'object' ? val.label : val );
					} )
				);
				if ( typeof this.timezone !== 'undefined' && this.timezone !== null && this.timezone !== '' ) {
					local_timezone = new Date( this.inst.selectedYear, this.inst.selectedMonth, this.inst.selectedDay, 12 ).getTimezoneOffset() * -1;
					if ( local_timezone === this.timezone ) {
						selectLocalTimezone( tp_inst );
					} else {
						this.timezone_select.val( this.timezone );
					}
				} else if ( typeof this.hour !== 'undefined' && this.hour !== null && this.hour !== '' ) {
					this.timezone_select.val( o.timezone );
				} else {
					selectLocalTimezone( tp_inst );
				}
				this.timezone_select.on( 'change', function() {
					tp_inst._onTimeChange();
					tp_inst._onSelectHandler();
					tp_inst._afterInject();
				} );
				// End timezone options

				// inject tm_timepicker into tm_datepicker
				$buttonPanel = $dp.find( '.tm-ui-dp-buttonpane' );
				if ( $buttonPanel.length ) {
					$buttonPanel.before( $tp );
				} else {
					$dp.append( $tp );
				}

				this.$timeObj = $tp.find( '.ui_tpicker_time_input' );
				this.$timeObj.on( 'change', function() {
					var timeFormat = tp_inst.inst.settings.timeFormat;
					var parsedTime = $.tm_datepicker.parseTime( timeFormat, this.value );
					var update = new Date();
					if ( parsedTime ) {
						update.setHours( parsedTime.hour );
						update.setMinutes( parsedTime.minute );
						update.setSeconds( parsedTime.second );
						$.datepicker._setTime( tp_inst.inst, update );
					} else {
						this.value = tp_inst.formattedTime;
						this.trigger( 'blur' );
					}
				} );

				if ( this.inst !== null ) {
					timeDefined = this.timeDefined;
					this._onTimeChange();
					this.timeDefined = timeDefined;
				}

				// slideAccess integration: http://trentrichardson.com/2011/11/11/jquery-ui-sliders-and-touch-accessibility/
				if ( this._defaults.addSliderAccess ) {
					sliderAccessArgs = this._defaults.sliderAccessArgs;
					rtl = this._defaults.isRTL;
					sliderAccessArgs.isRTL = rtl;

					setTimeout( function() {
						// fix for inline mode
						var sliderAccessWidth;
						if ( $tp.find( '.ui-slider-access' ).length === 0 ) {
							$tp.find( '.ui-slider:visible' ).sliderAccess( sliderAccessArgs );

							// fix any grids since sliders are shorter
							sliderAccessWidth = $tp.find( '.ui-slider-access:eq(0)' ).outerWidth( true );
							if ( sliderAccessWidth ) {
								$tp.find( 'table:visible' ).each( function() {
									var $g = $( this ),
										oldWidth = $g.outerWidth(),
										oldMarginLeft = $g
											.css( rtl ? 'marginRight' : 'marginLeft' )
											.toString()
											.replace( '%', '' ),
										newWidth = oldWidth - sliderAccessWidth,
										newMarginLeft = ( ( oldMarginLeft * newWidth ) / oldWidth ) + '%',
										css = { width: newWidth, marginRight: 0, marginLeft: 0 };
									css[ rtl ? 'marginRight' : 'marginLeft' ] = newMarginLeft;
									$g.css( css );
								} );
							}
						}
					}, 10 );
				}
				// end slideAccess integration

				tp_inst._limitMinMaxDateTime( this.inst, true );
			}
		},

		/*
		 * This function tries to limit the ability to go outside the
		 * min/max date range
		 */
		_limitMinMaxDateTime: function( dp_inst, adjustSliders ) {
			var o = this._defaults,
				dp_date = new Date( dp_inst.selectedYear, dp_inst.selectedMonth, dp_inst.selectedDay );
			var minDateTime;
			var minDateTimeDate;
			var maxDateTime;
			var maxDateTimeDate;
			var tempMinTime;
			var tempMaxTime;
			var hourMax;
			var minMax;
			var secMax;
			var millisecMax;
			var microsecMax;

			if ( ! this._defaults.showTimepicker ) {
				return;
			} // No time so nothing to check here

			if ( $.tm_datepicker._get( dp_inst, 'minDateTime' ) !== null && $.tm_datepicker._get( dp_inst, 'minDateTime' ) !== undefined && dp_date ) {
				minDateTime = $.tm_datepicker._get( dp_inst, 'minDateTime' );
				minDateTimeDate = new Date( minDateTime.getFullYear(), minDateTime.getMonth(), minDateTime.getDate(), 0, 0, 0, 0 );

				if ( this.hourMinOriginal === null || this.minuteMinOriginal === null || this.secondMinOriginal === null || this.millisecMinOriginal === null || this.microsecMinOriginal === null ) {
					this.hourMinOriginal = o.hourMin;
					this.minuteMinOriginal = o.minuteMin;
					this.secondMinOriginal = o.secondMin;
					this.millisecMinOriginal = o.millisecMin;
					this.microsecMinOriginal = o.microsecMin;
				}

				if ( dp_inst.settings.timeOnly || minDateTimeDate.getTime() === dp_date.getTime() ) {
					this._defaults.hourMin = minDateTime.getHours();
					if ( this.hour <= this._defaults.hourMin ) {
						this.hour = this._defaults.hourMin;
						this._defaults.minuteMin = minDateTime.getMinutes();
						if ( this.minute <= this._defaults.minuteMin ) {
							this.minute = this._defaults.minuteMin;
							this._defaults.secondMin = minDateTime.getSeconds();
							if ( this.second <= this._defaults.secondMin ) {
								this.second = this._defaults.secondMin;
								this._defaults.millisecMin = minDateTime.getMilliseconds();
								if ( this.millisec <= this._defaults.millisecMin ) {
									this.millisec = this._defaults.millisecMin;
									this._defaults.microsecMin = minDateTime.getMicroseconds();
								} else {
									if ( this.microsec < this._defaults.microsecMin ) {
										this.microsec = this._defaults.microsecMin;
									}
									this._defaults.microsecMin = this.microsecMinOriginal;
								}
							} else {
								this._defaults.millisecMin = this.millisecMinOriginal;
								this._defaults.microsecMin = this.microsecMinOriginal;
							}
						} else {
							this._defaults.secondMin = this.secondMinOriginal;
							this._defaults.millisecMin = this.millisecMinOriginal;
							this._defaults.microsecMin = this.microsecMinOriginal;
						}
					} else {
						this._defaults.minuteMin = this.minuteMinOriginal;
						this._defaults.secondMin = this.secondMinOriginal;
						this._defaults.millisecMin = this.millisecMinOriginal;
						this._defaults.microsecMin = this.microsecMinOriginal;
					}
				} else {
					this._defaults.hourMin = this.hourMinOriginal;
					this._defaults.minuteMin = this.minuteMinOriginal;
					this._defaults.secondMin = this.secondMinOriginal;
					this._defaults.millisecMin = this.millisecMinOriginal;
					this._defaults.microsecMin = this.microsecMinOriginal;
				}
			}

			if ( $.tm_datepicker._get( dp_inst, 'maxDateTime' ) !== null && $.tm_datepicker._get( dp_inst, 'maxDateTime' ) !== undefined && dp_date ) {
				maxDateTime = $.tm_datepicker._get( dp_inst, 'maxDateTime' );
				maxDateTimeDate = new Date( maxDateTime.getFullYear(), maxDateTime.getMonth(), maxDateTime.getDate(), 0, 0, 0, 0 );

				if ( this.hourMaxOriginal === null || this.minuteMaxOriginal === null || this.secondMaxOriginal === null || this.millisecMaxOriginal === null ) {
					this.hourMaxOriginal = o.hourMax;
					this.minuteMaxOriginal = o.minuteMax;
					this.secondMaxOriginal = o.secondMax;
					this.millisecMaxOriginal = o.millisecMax;
					this.microsecMaxOriginal = o.microsecMax;
				}

				if ( dp_inst.settings.timeOnly || maxDateTimeDate.getTime() === dp_date.getTime() ) {
					this._defaults.hourMax = maxDateTime.getHours();
					if ( this.hour >= this._defaults.hourMax ) {
						this.hour = this._defaults.hourMax;
						this._defaults.minuteMax = maxDateTime.getMinutes();
						if ( this.minute >= this._defaults.minuteMax ) {
							this.minute = this._defaults.minuteMax;
							this._defaults.secondMax = maxDateTime.getSeconds();
							if ( this.second >= this._defaults.secondMax ) {
								this.second = this._defaults.secondMax;
								this._defaults.millisecMax = maxDateTime.getMilliseconds();
								if ( this.millisec >= this._defaults.millisecMax ) {
									this.millisec = this._defaults.millisecMax;
									this._defaults.microsecMax = maxDateTime.getMicroseconds();
								} else {
									if ( this.microsec > this._defaults.microsecMax ) {
										this.microsec = this._defaults.microsecMax;
									}
									this._defaults.microsecMax = this.microsecMaxOriginal;
								}
							} else {
								this._defaults.millisecMax = this.millisecMaxOriginal;
								this._defaults.microsecMax = this.microsecMaxOriginal;
							}
						} else {
							this._defaults.secondMax = this.secondMaxOriginal;
							this._defaults.millisecMax = this.millisecMaxOriginal;
							this._defaults.microsecMax = this.microsecMaxOriginal;
						}
					} else {
						this._defaults.minuteMax = this.minuteMaxOriginal;
						this._defaults.secondMax = this.secondMaxOriginal;
						this._defaults.millisecMax = this.millisecMaxOriginal;
						this._defaults.microsecMax = this.microsecMaxOriginal;
					}
				} else {
					this._defaults.hourMax = this.hourMaxOriginal;
					this._defaults.minuteMax = this.minuteMaxOriginal;
					this._defaults.secondMax = this.secondMaxOriginal;
					this._defaults.millisecMax = this.millisecMaxOriginal;
					this._defaults.microsecMax = this.microsecMaxOriginal;
				}
			}

			if ( dp_inst.settings.minTime !== null ) {
				tempMinTime = new Date( '01/01/1970 ' + dp_inst.settings.minTime );
				if ( parseFloat( dp_inst.settings.minTime ).toString() === dp_inst.settings.minTime ) {
					tempMinTime = new Date( Date.now() + parseFloat( dp_inst.settings.minTime ) );
				}
				if ( this.hour < tempMinTime.getHours() ) {
					this._defaults.hourMin = tempMinTime.getHours();
					this.hour = this._defaults.hourMin;
					this._defaults.minuteMin = tempMinTime.getMinutes();
					this.minute = this._defaults.minuteMin;
				} else if ( this.hour === tempMinTime.getHours() && this.minute < tempMinTime.getMinutes() ) {
					this._defaults.minuteMin = tempMinTime.getMinutes();
					this.minute = this._defaults.minuteMin;
				} else if ( this._defaults.hourMin < tempMinTime.getHours() ) {
					this._defaults.hourMin = tempMinTime.getHours();
					this._defaults.minuteMin = tempMinTime.getMinutes();
				} else if ( ( this._defaults.hourMin === tempMinTime.getHours() ) === this.hour && this._defaults.minuteMin < tempMinTime.getMinutes() ) {
					this._defaults.minuteMin = tempMinTime.getMinutes();
				} else {
					this._defaults.minuteMin = 0;
				}
			}

			if ( dp_inst.settings.maxTime !== null ) {
				tempMaxTime = new Date( '01/01/1970 ' + dp_inst.settings.maxTime );
				if ( this.hour > tempMaxTime.getHours() ) {
					this._defaults.hourMax = tempMaxTime.getHours();
					this.hour = this._defaults.hourMax;
					this._defaults.minuteMax = tempMaxTime.getMinutes();
					this.minute = this._defaults.minuteMax;
				} else if ( this.hour === tempMaxTime.getHours() && this.minute > tempMaxTime.getMinutes() ) {
					this._defaults.minuteMax = tempMaxTime.getMinutes();
					this.minute = this._defaults.minuteMax;
				} else if ( this._defaults.hourMax > tempMaxTime.getHours() ) {
					this._defaults.hourMax = tempMaxTime.getHours();
					this._defaults.minuteMax = tempMaxTime.getMinutes();
				} else if ( ( this._defaults.hourMax === tempMaxTime.getHours() ) === this.hour && this._defaults.minuteMax > tempMaxTime.getMinutes() ) {
					this._defaults.minuteMax = tempMaxTime.getMinutes();
				} else {
					this._defaults.minuteMax = 59;
				}
			}

			if ( adjustSliders !== undefined && adjustSliders === true ) {
				hourMax = parseInt( this._defaults.hourMax - ( ( this._defaults.hourMax - this._defaults.hourMin ) % this._defaults.stepHour ), 10 );
				minMax = parseInt( this._defaults.minuteMax - ( ( this._defaults.minuteMax - this._defaults.minuteMin ) % this._defaults.stepMinute ), 10 );
				secMax = parseInt( this._defaults.secondMax - ( ( this._defaults.secondMax - this._defaults.secondMin ) % this._defaults.stepSecond ), 10 );
				millisecMax = parseInt( this._defaults.millisecMax - ( ( this._defaults.millisecMax - this._defaults.millisecMin ) % this._defaults.stepMillisec ), 10 );
				microsecMax = parseInt( this._defaults.microsecMax - ( ( this._defaults.microsecMax - this._defaults.microsecMin ) % this._defaults.stepMicrosec ), 10 );

				if ( this.hour_slider ) {
					this.control.options( this, this.hour_slider, 'hour', {
						min: this._defaults.hourMin,
						max: hourMax,
						step: this._defaults.stepHour
					} );
					this.control.value( this, this.hour_slider, 'hour', this.hour - ( this.hour % this._defaults.stepHour ) );
				}
				if ( this.minute_slider ) {
					this.control.options( this, this.minute_slider, 'minute', {
						min: this._defaults.minuteMin,
						max: minMax,
						step: this._defaults.stepMinute
					} );
					this.control.value( this, this.minute_slider, 'minute', this.minute - ( this.minute % this._defaults.stepMinute ) );
				}
				if ( this.second_slider ) {
					this.control.options( this, this.second_slider, 'second', {
						min: this._defaults.secondMin,
						max: secMax,
						step: this._defaults.stepSecond
					} );
					this.control.value( this, this.second_slider, 'second', this.second - ( this.second % this._defaults.stepSecond ) );
				}
				if ( this.millisec_slider ) {
					this.control.options( this, this.millisec_slider, 'millisec', {
						min: this._defaults.millisecMin,
						max: millisecMax,
						step: this._defaults.stepMillisec
					} );
					this.control.value( this, this.millisec_slider, 'millisec', this.millisec - ( this.millisec % this._defaults.stepMillisec ) );
				}
				if ( this.microsec_slider ) {
					this.control.options( this, this.microsec_slider, 'microsec', {
						min: this._defaults.microsecMin,
						max: microsecMax,
						step: this._defaults.stepMicrosec
					} );
					this.control.value( this, this.microsec_slider, 'microsec', this.microsec - ( this.microsec % this._defaults.stepMicrosec ) );
				}
			}
		},

		/*
		 * when a slider moves, set the internal time...
		 * on time change is also called when the time is updated in the text field
		 */
		_onTimeChange: function() {
			var hour = this.hour_slider ? this.control.value( this, this.hour_slider, 'hour' ) : false,
				minute = this.minute_slider ? this.control.value( this, this.minute_slider, 'minute' ) : false,
				second = this.second_slider ? this.control.value( this, this.second_slider, 'second' ) : false,
				millisec = this.millisec_slider ? this.control.value( this, this.millisec_slider, 'millisec' ) : false,
				microsec = this.microsec_slider ? this.control.value( this, this.microsec_slider, 'microsec' ) : false,
				timezone = this.timezone_select ? this.timezone_select.val() : false,
				o = this._defaults,
				pickerTimeFormat = o.pickerTimeFormat || o.timeFormat,
				pickerTimeSuffix = o.pickerTimeSuffix || o.timeSuffix;
			var ampm;
			var hasChanged;
			var sPos;
			var ePos;

			if ( ! this._defaults.showTimepicker ) {
				return;
			}

			if ( typeof hour === 'object' ) {
				hour = false;
			}
			if ( typeof minute === 'object' ) {
				minute = false;
			}
			if ( typeof second === 'object' ) {
				second = false;
			}
			if ( typeof millisec === 'object' ) {
				millisec = false;
			}
			if ( typeof microsec === 'object' ) {
				microsec = false;
			}
			if ( typeof timezone === 'object' ) {
				timezone = false;
			}

			if ( hour !== false ) {
				hour = parseInt( hour, 10 );
			}
			if ( minute !== false ) {
				minute = parseInt( minute, 10 );
			}
			if ( second !== false ) {
				second = parseInt( second, 10 );
			}
			if ( millisec !== false ) {
				millisec = parseInt( millisec, 10 );
			}
			if ( microsec !== false ) {
				microsec = parseInt( microsec, 10 );
			}
			if ( timezone !== false ) {
				timezone = timezone.toString();
			}

			ampm = o[ hour < 12 ? 'amNames' : 'pmNames' ][ 0 ];

			// If the update was done in the input field, the input field should not be updated.
			// If the update was done using the sliders, update the input field.
			hasChanged =
				hour !== parseInt( this.hour, 10 ) || // sliders should all be numeric
				minute !== parseInt( this.minute, 10 ) ||
				second !== parseInt( this.second, 10 ) ||
				millisec !== parseInt( this.millisec, 10 ) ||
				microsec !== parseInt( this.microsec, 10 ) ||
				( this.ampm.length > 0 && ( hour < 12 ) !== ( $.inArray( this.ampm.toUpperCase(), this.amNames ) !== -1 ) ) ||
				( this.timezone !== null && timezone !== this.timezone.toString() ); // could be numeric or "EST" format, so use toString()

			if ( hasChanged ) {
				if ( hour !== false ) {
					this.hour = hour;
				}
				if ( minute !== false ) {
					this.minute = minute;
				}
				if ( second !== false ) {
					this.second = second;
				}
				if ( millisec !== false ) {
					this.millisec = millisec;
				}
				if ( microsec !== false ) {
					this.microsec = microsec;
				}
				if ( timezone !== false ) {
					this.timezone = timezone;
				}

				if ( ! this.inst ) {
					this.inst = $.tm_datepicker._getInst( this.$input[ 0 ] );
				}

				this._limitMinMaxDateTime( this.inst, true );
			}
			if ( this.support.ampm ) {
				this.ampm = ampm;
			}

			// Updates the time within the tm_timepicker
			this.formattedTime = $.tm_datepicker.formatTime( o.timeFormat, this, o );
			if ( this.$timeObj ) {
				if ( pickerTimeFormat === o.timeFormat ) {
					this.$timeObj.val( this.formattedTime + pickerTimeSuffix );
				} else {
					this.$timeObj.val( $.tm_datepicker.formatTime( pickerTimeFormat, this, o ) + pickerTimeSuffix );
				}
				if ( this.$timeObj[ 0 ].setSelectionRange ) {
					sPos = this.$timeObj[ 0 ].selectionStart;
					ePos = this.$timeObj[ 0 ].selectionEnd;
					this.$timeObj[ 0 ].setSelectionRange( sPos, ePos );
				}
			}

			this.timeDefined = true;
			if ( hasChanged ) {
				this._updateDateTime();
				//this.$input.trigger(' focus' ); // may automatically open the picker on setDate
			}
		},

		/*
		 * call custom onSelect.
		 * bind to sliders slidestop, and grid click.
		 */
		_onSelectHandler: function() {
			var onSelect = this._defaults.onSelect || this.inst.settings.onSelect;
			var inputEl = this.$input ? this.$input[ 0 ] : null;
			if ( onSelect && inputEl ) {
				onSelect.apply( inputEl, [ this.formattedDateTime, this ] );
			}
		},

		/*
		 * update our input with the new date time..
		 */
		_updateDateTime: function( dp_inst ) {
			var dtTmp;
			var dt;
			var dateFmt;
			var formatCfg;
			var timeAvailable;
			var formattedDateTime;
			var altFormattedDateTime = '';
			var altSeparator;
			var altTimeSuffix;

			dp_inst = this.inst || dp_inst;
			dtTmp = dp_inst.currentYear > 0 ? new Date( dp_inst.currentYear, dp_inst.currentMonth, dp_inst.currentDay ) : new Date( dp_inst.selectedYear, dp_inst.selectedMonth, dp_inst.selectedDay );
			dt = $.tm_datepicker._daylightSavingAdjust( dtTmp );
			//dt = $.tm_datepicker._daylightSavingAdjust(new Date(dp_inst.selectedYear, dp_inst.selectedMonth, dp_inst.selectedDay)),
			//dt = $.tm_datepicker._daylightSavingAdjust(new Date(dp_inst.currentYear, dp_inst.currentMonth, dp_inst.currentDay)),
			dateFmt = $.tm_datepicker._get( dp_inst, 'dateFormat' );
			formatCfg = $.tm_datepicker._getFormatConfig( dp_inst );
			timeAvailable = dt !== null && this.timeDefined;
			this.formattedDate = $.tm_datepicker.formatDate( dateFmt, dt === null ? new Date() : dt, formatCfg );
			formattedDateTime = this.formattedDate;

			// if a slider was changed but tm_datepicker doesn't have a value yet, set it
			if ( dp_inst.lastVal === '' ) {
				dp_inst.currentYear = dp_inst.selectedYear;
				dp_inst.currentMonth = dp_inst.selectedMonth;
				dp_inst.currentDay = dp_inst.selectedDay;
			}

			/*
			 * remove following lines to force every changes in date picker to change the input value
			 * Bug descriptions: when an input field has a default value, and click on the field to pop up the date picker.
			 * If the user manually empty the value in the input field, the date picker will never change selected value.
			 */
			//if (dp_inst.lastVal !== undefined && (dp_inst.lastVal.length > 0 && this.$input.val().length === 0)) {
			//return;
			//}

			if ( this._defaults.timeOnly === true && this._defaults.timeOnlyShowDate === false ) {
				formattedDateTime = this.formattedTime;
			} else if ( ( this._defaults.timeOnly !== true && ( this._defaults.alwaysSetTime || timeAvailable ) ) || ( this._defaults.timeOnly === true && this._defaults.timeOnlyShowDate === true ) ) {
				formattedDateTime += this._defaults.separator + this.formattedTime + this._defaults.timeSuffix;
			}

			this.formattedDateTime = formattedDateTime;

			if ( ! this._defaults.showTimepicker ) {
				this.$input.val( this.formattedDate );
			} else if ( this.$altInput && this._defaults.timeOnly === false && this._defaults.altFieldTimeOnly === true ) {
				this.$altInput.val( this.formattedTime );
				this.$input.val( this.formattedDate );
			} else if ( this.$altInput ) {
				this.$input.val( formattedDateTime );
				altFormattedDateTime = '';
				altSeparator = this._defaults.altSeparator !== null ? this._defaults.altSeparator : this._defaults.separator;
				altTimeSuffix = this._defaults.altTimeSuffix !== null ? this._defaults.altTimeSuffix : this._defaults.timeSuffix;

				if ( ! this._defaults.timeOnly ) {
					if ( this._defaults.altFormat ) {
						altFormattedDateTime = $.tm_datepicker.formatDate( this._defaults.altFormat, dt === null ? new Date() : dt, formatCfg );
					} else {
						altFormattedDateTime = this.formattedDate;
					}

					if ( altFormattedDateTime ) {
						altFormattedDateTime += altSeparator;
					}
				}

				if ( this._defaults.altTimeFormat !== null ) {
					altFormattedDateTime += $.tm_datepicker.formatTime( this._defaults.altTimeFormat, this, this._defaults ) + altTimeSuffix;
				} else {
					altFormattedDateTime += this.formattedTime + altTimeSuffix;
				}
				this.$altInput.val( altFormattedDateTime );
			} else {
				this.$input.val( formattedDateTime );
			}

			this.$input.trigger( 'change' );
		},

		_onFocus: function() {
			var inst;
			var tp_inst;
			if ( ! this.$input.val() && this._defaults.defaultValue ) {
				this.$input.val( this._defaults.defaultValue );
				inst = $.tm_datepicker._getInst( this.$input.get( 0 ) );
				tp_inst = $.tm_datepicker._get( inst, 'tm_timepicker' );
				if ( tp_inst ) {
					if ( tp_inst._defaults.timeOnly && inst.input.val() !== inst.lastVal ) {
						try {
							$.tm_datepicker._updateDatepicker( inst );
						} catch ( err ) {
							$.tm_timepicker.log( err );
						}
					}
				}
			}
		},

		/*
		 * Small abstraction to control types
		 * We can add more, just be sure to follow the pattern: create, options, value
		 */
		_controls: {
			// slider methods
			slider: {
				create: function( tp_inst, obj, unit, val, min, max, step ) {
					var rtl = tp_inst._defaults.isRTL; // if rtl go -60->0 instead of 0->60
					return obj.prop( 'slide', null ).slider( {
						orientation: 'horizontal',
						value: rtl ? val * -1 : val,
						min: rtl ? max * -1 : min,
						max: rtl ? min * -1 : max,
						step: step,
						slide: function( event, ui ) {
							tp_inst.control.value( tp_inst, $( this ), unit, rtl ? ui.value * -1 : ui.value );
							tp_inst._onTimeChange();
						},
						stop: function() {
							tp_inst._onSelectHandler();
						}
					} );
				},
				options: function( tp_inst, obj, unit, opts, val ) {
					var min;
					var max;
					if ( tp_inst._defaults.isRTL ) {
						if ( typeof opts === 'string' ) {
							if ( opts === 'min' || opts === 'max' ) {
								if ( val !== undefined ) {
									return obj.slider( opts, val * -1 );
								}
								return Math.abs( obj.slider( opts ) );
							}
							return obj.slider( opts );
						}
						min = opts.min;
						max = opts.max;
						opts.max = null;
						opts.min = null;
						if ( min !== undefined ) {
							opts.max = min * -1;
						}
						if ( max !== undefined ) {
							opts.min = max * -1;
						}
						return obj.slider( opts );
					}
					if ( typeof opts === 'string' && val !== undefined ) {
						return obj.slider( opts, val );
					}
					return obj.slider( opts );
				},
				value: function( tp_inst, obj, unit, val ) {
					if ( tp_inst._defaults.isRTL ) {
						if ( val !== undefined ) {
							return obj.slider( 'value', val * -1 );
						}
						return Math.abs( obj.slider( 'value' ) );
					}
					if ( val !== undefined ) {
						return obj.slider( 'value', val );
					}
					return obj.slider( 'value' );
				}
			},
			// select methods
			select: {
				create: function( tp_inst, obj, unit, val, min, max, step ) {
					var sel = '<select class="ui-tm-timepicker-select ui-state-default ui-corner-all" data-unit="' + unit + '" data-min="' + min + '" data-max="' + max + '" data-step="' + step + '">',
						format = tp_inst._defaults.pickerTimeFormat || tp_inst._defaults.timeFormat;
					var i;

					for ( i = min; i <= max; i += step ) {
						sel += '<option value="' + i + '"' + ( i === val ? ' selected' : '' ) + '>';
						if ( unit === 'hour' ) {
							sel += $.tm_datepicker.formatTime( $.epoAPI.util.trim( format.replace( /[^ht ]/gi, '' ) ), { hour: i }, tp_inst._defaults );
						} else if ( unit === 'millisec' || unit === 'microsec' || i >= 10 ) {
							sel += i;
						} else {
							sel += '0' + i.toString();
						}
						sel += '</option>';
					}
					sel += '</select>';

					obj.children( 'select' ).remove();

					$( sel )
						.appendTo( obj )
						.on( 'change', function() {
							tp_inst._onTimeChange();
							tp_inst._onSelectHandler();
							tp_inst._afterInject();
						} );

					return obj;
				},
				options: function( tp_inst, obj, unit, opts, val ) {
					var o = {},
						$t = obj.children( 'select' );
					if ( typeof opts === 'string' ) {
						if ( val === undefined ) {
							return $t.data( opts );
						}
						o[ opts ] = val;
					} else {
						o = opts;
					}
					return tp_inst.control.create( tp_inst, obj, $t.data( 'unit' ), $t.val(), o.min >= 0 ? o.min : $t.data( 'min' ), o.max || $t.data( 'max' ), o.step || $t.data( 'step' ) );
				},
				value: function( tp_inst, obj, unit, val ) {
					var $t = obj.children( 'select' );
					if ( val !== undefined ) {
						return $t.val( val );
					}
					return $t.val();
				}
			}
		} // end _controls
	} );

	$.fn.extend( {
		/*
		 * shorthand just to use tm_timepicker.
		 */
		tm_timepicker: function( o ) {
			var tmp_args = Array.prototype.slice.call( arguments );
			o = o || {};

			if ( typeof o === 'object' ) {
				tmp_args[ 0 ] = $.extend( o, {
					timeOnly: true
				} );
			}

			return $( this ).each( function() {
				$.fn.tm_datetimepicker.apply( $( this ), tmp_args );
			} );
		},

		/*
		 * extend tm_timepicker to tm_datepicker
		 */
		tm_datetimepicker: function( o ) {
			var tmp_args = arguments;
			o = o || {};

			if ( typeof o === 'string' ) {
				if ( o === 'getDate' || ( o === 'option' && tmp_args.length === 2 && typeof tmp_args[ 1 ] === 'string' ) ) {
					return $.fn.tm_datepicker.apply( $( this[ 0 ] ), tmp_args );
				}
				return this.each( function() {
					var $t = $( this );
					$t.tm_datepicker.apply( $t, tmp_args );
				} );
			}
			return this.each( function() {
				var $t = $( this );
				$t.tm_datepicker( $.tm_timepicker._newInst( $t, o )._defaults );
			} );
		}
	} );

	/*
	 * Public Utility to parse date and time
	 */
	$.tm_datepicker.parseDateTime = function( dateFormat, timeFormat, dateTimeString, dateSettings, timeSettings ) {
		var parseRes = parseDateTimeInternal( dateFormat, timeFormat, dateTimeString, dateSettings, timeSettings );
		var t;
		if ( parseRes.timeObj ) {
			t = parseRes.timeObj;
			parseRes.date.setHours( t.hour, t.minute, t.second, t.millisec );
			parseRes.date.setMicroseconds( t.microsec );
		}

		return parseRes.date;
	};

	/*
	 * Public utility to parse time
	 */
	$.tm_datepicker.parseTime = function( timeFormat, timeString, options ) {
		var o = extendRemove( extendRemove( {}, $.tm_timepicker._defaults ), options || {} );

		// Strict parse requires the timeString to match the timeFormat exactly
		var strictParse = function( f, s, oo ) {
			// pattern for standard and localized AM/PM markers
			var getPatternAmpm = function( amNames, pmNames ) {
				var markers = [];
				if ( amNames ) {
					$.merge( markers, amNames );
				}
				if ( pmNames ) {
					$.merge( markers, pmNames );
				}
				markers = $.map( markers, function( val ) {
					return val.replace( /[.*+?|()[\]{}\\]/g, '\\$&' );
				} );
				return '(' + markers.join( '|' ) + ')?';
			};

			// figure out position of time elements.. cause js cant do named captures
			var getFormatPositions = function( formatoftime ) {
				var finds = formatoftime.toLowerCase().match( /(h{1,2}|m{1,2}|s{1,2}|l{1}|c{1}|t{1,2}|z|'.*?')/g ),
					orders = {
						h: -1,
						m: -1,
						s: -1,
						l: -1,
						c: -1,
						t: -1,
						z: -1
					};
				var i;

				if ( finds ) {
					for ( i = 0; i < finds.length; i += 1 ) {
						if ( orders[ finds[ i ].toString().charAt( 0 ) ] === -1 ) {
							orders[ finds[ i ].toString().charAt( 0 ) ] = i + 1;
						}
					}
				}
				return orders;
			};

			var regstr =
					'^' +
					f
						.toString()
						.replace( /([hH]{1,2}|mm?|ss?|[tT]{1,2}|[zZ]|[lc]|'.*?')/g, function( match ) {
							var ml = match.length;
							switch ( match.charAt( 0 ).toLowerCase() ) {
								case 'h':
									return ml === 1 ? '(\\d?\\d)' : '(\\d{' + ml + '})';
								case 'm':
									return ml === 1 ? '(\\d?\\d)' : '(\\d{' + ml + '})';
								case 's':
									return ml === 1 ? '(\\d?\\d)' : '(\\d{' + ml + '})';
								case 'l':
									return '(\\d?\\d?\\d)';
								case 'c':
									return '(\\d?\\d?\\d)';
								case 'z':
									return '(z|[-+]\\d\\d:?\\d\\d|\\S+)?';
								case 't':
									return getPatternAmpm( oo.amNames, oo.pmNames );
								default:
									// literal escaped in quotes
									return (
										'(' +
										match.replace( /'/g, '' ).replace( /(\.|\$|\^|\\|\/|\(|\)|\[|\]|\?|\+|\*)/g, function( m ) {
											return '\\' + m;
										} ) +
										')?'
									);
							}
						} )
						.replace( /\s/g, '\\s?' ) +
					oo.timeSuffix +
					'$',
				order = getFormatPositions( f ),
				ampm = '',
				treg;

			var resTime = {
				hour: 0,
				minute: 0,
				second: 0,
				millisec: 0,
				microsec: 0
			};

			treg = s.match( new RegExp( regstr, 'i' ) );

			if ( treg ) {
				if ( order.t !== -1 ) {
					if ( treg[ order.t ] === undefined || treg[ order.t ].length === 0 ) {
						ampm = '';
						resTime.ampm = '';
					} else {
						ampm =
							$.inArray(
								treg[ order.t ].toUpperCase(),
								$.map( oo.amNames, function( x ) {
									return x.toUpperCase();
								} )
							) !== -1
								? 'AM'
								: 'PM';
						resTime.ampm = oo[ ampm === 'AM' ? 'amNames' : 'pmNames' ][ 0 ];
					}
				}

				if ( order.h !== -1 ) {
					if ( ampm === 'AM' && treg[ order.h ] === '12' ) {
						resTime.hour = 0; // 12am = 0 hour
					} else if ( ampm === 'PM' && treg[ order.h ] !== '12' ) {
						resTime.hour = parseInt( treg[ order.h ], 10 ) + 12; // 12pm = 12 hour, any other pm = hour + 12
					} else {
						resTime.hour = Number( treg[ order.h ] );
					}
				}

				if ( order.m !== -1 ) {
					resTime.minute = Number( treg[ order.m ] );
				}
				if ( order.s !== -1 ) {
					resTime.second = Number( treg[ order.s ] );
				}
				if ( order.l !== -1 ) {
					resTime.millisec = Number( treg[ order.l ] );
				}
				if ( order.c !== -1 ) {
					resTime.microsec = Number( treg[ order.c ] );
				}
				if ( order.z !== -1 && treg[ order.z ] !== undefined ) {
					resTime.timezone = $.tm_timepicker.timezoneOffsetNumber( treg[ order.z ] );
				}

				return resTime;
			}
			return false;
		}; // end strictParse

		// First try JS Date, if that fails, use strictParse
		var looseParse = function( f, s, oo ) {
			var d;
			try {
				d = new Date( '2012-01-01 ' + s );
				if ( isNaN( d.getTime() ) ) {
					d = new Date( '2012-01-01T' + s );
					if ( isNaN( d.getTime() ) ) {
						d = new Date( '01/01/2012 ' + s );
						if ( isNaN( d.getTime() ) ) {
							throw 'Unable to parse time with native Date: ' + s;
						}
					}
				}

				return {
					hour: d.getHours(),
					minute: d.getMinutes(),
					second: d.getSeconds(),
					millisec: d.getMilliseconds(),
					microsec: d.getMicroseconds(),
					timezone: d.getTimezoneOffset() * -1
				};
			} catch ( err ) {
				try {
					return strictParse( f, s, oo );
				} catch ( err2 ) {
					$.tm_timepicker.log( 'Unable to parse \ntimeString: ' + s + '\ntimeFormat: ' + f );
				}
			}
			return false;
		}; // end looseParse

		if ( typeof o.parse === 'function' ) {
			return o.parse( timeFormat, timeString, o );
		}
		if ( o.parse === 'loose' ) {
			return looseParse( timeFormat, timeString, o );
		}
		return strictParse( timeFormat, timeString, o );
	};

	/**
	 * Public utility to format the time.
	 *
	 * @param {string} format    format of the time
	 * @param {Object} time      Object not a Date for timezones
	 * @param {Object} [options] essentially the regional[].. amNames, pmNames, ampm
	 * @returns {string} the formatted time
	 */
	$.tm_datepicker.formatTime = function( format, time, options ) {
		var tmptime;
		var ampmName;
		var hour;

		options = options || {};
		options = $.extend( {}, $.tm_timepicker._defaults, options );
		time = $.extend(
			{
				hour: 0,
				minute: 0,
				second: 0,
				millisec: 0,
				microsec: 0,
				timezone: null
			},
			time
		);

		tmptime = format;
		ampmName = options.amNames[ 0 ];
		hour = parseInt( time.hour, 10 );

		if ( hour > 11 ) {
			ampmName = options.pmNames[ 0 ];
		}

		tmptime = tmptime.replace( /(?:HH?|hh?|mm?|ss?|[tT]{1,2}|[zZ]|[lc]|'.*?')/g, function( match ) {
			switch ( match ) {
				case 'HH':
					return ( '0' + hour ).slice( -2 );
				case 'H':
					return hour;
				case 'hh':
					return ( '0' + convert24to12( hour ) ).slice( -2 );
				case 'h':
					return convert24to12( hour );
				case 'mm':
					return ( '0' + time.minute ).slice( -2 );
				case 'm':
					return time.minute;
				case 'ss':
					return ( '0' + time.second ).slice( -2 );
				case 's':
					return time.second;
				case 'l':
					return ( '00' + time.millisec ).slice( -3 );
				case 'c':
					return ( '00' + time.microsec ).slice( -3 );
				case 'z':
					return $.tm_timepicker.timezoneOffsetString( time.timezone === null ? options.timezone : time.timezone, false );
				case 'Z':
					return $.tm_timepicker.timezoneOffsetString( time.timezone === null ? options.timezone : time.timezone, true );
				case 'T':
					return ampmName.charAt( 0 ).toUpperCase();
				case 'TT':
					return ampmName.toUpperCase();
				case 't':
					return ampmName.charAt( 0 ).toLowerCase();
				case 'tt':
					return ampmName.toLowerCase();
				default:
					return match.replace( /'/g, '' );
			}
		} );

		return tmptime;
	};

	/*
     * the bad hack :/ override tm_datepicker so it doesn't close on select
     // inspired: http://stackoverflow.com/questions/1252512/jquery-datepicker-prevent-closing-picker-when-clicking-a-date/1762378#1762378
     */
	$.tm_datepicker._base_selectDate = $.tm_datepicker._selectDate;
	$.tm_datepicker._selectDate = function( id, dateStr ) {
		var inst = this._getInst( $( id )[ 0 ] ),
			tp_inst = this._get( inst, 'tm_timepicker' ),
			was_inline;

		if ( tp_inst && inst.settings.showTimepicker ) {
			tp_inst._limitMinMaxDateTime( inst, true );
			was_inline = inst.inline;
			inst.stay_open = true;
			inst.inline = true;
			//This way the onSelect handler called from calendarpicker get the full dateTime
			this._base_selectDate( id, dateStr );
			inst.inline = was_inline;
			inst.stay_open = false;
			this._notifyChange( inst );
			this._updateDatepicker( inst );
		} else {
			this._base_selectDate( id, dateStr );
		}
	};

	/*
	 * second bad hack :/ override tm_datepicker so it triggers an event when changing the input field
	 * and does not redraw the tm_datepicker on every selectDate event
	 */
	$.tm_datepicker._base_updateDatepicker = $.tm_datepicker._updateDatepicker;
	$.tm_datepicker._updateDatepicker = function( inst ) {
		// don't popup the tm_datepicker if there is another instance already opened
		var input = inst.input[ 0 ];
		var tp_inst;
		if ( $.tm_datepicker._curInst && $.tm_datepicker._curInst !== inst && $.tm_datepicker._datepickerShowing && $.tm_datepicker._lastInput !== input ) {
			return;
		}

		if ( typeof inst.stay_open !== 'boolean' || inst.stay_open === false ) {
			this._base_updateDatepicker( inst );

			// Reload the time control when changing something in the input text field.
			tp_inst = this._get( inst, 'tm_timepicker' );
			if ( tp_inst ) {
				tp_inst._addTimePicker( inst );
			}
		}
	};

	/*
	 * third bad hack :/ override tm_datepicker so it allows spaces and colon in the input field
	 */
	$.tm_datepicker._base_doKeyPress = $.tm_datepicker._doKeyPress;
	$.tm_datepicker._doKeyPress = function( event ) {
		var inst = $.tm_datepicker._getInst( event.target ),
			tp_inst = $.tm_datepicker._get( inst, 'tm_timepicker' );
		var ampm;
		var tz;
		var dateChars;
		var datetimeChars;
		var chr;

		if ( tp_inst ) {
			if ( $.tm_datepicker._get( inst, 'constrainInput' ) ) {
				ampm = tp_inst.support.ampm;
				tz = tp_inst._defaults.showTimezone !== null ? tp_inst._defaults.showTimezone : tp_inst.support.timezone;
				dateChars = $.tm_datepicker._possibleChars( $.tm_datepicker._get( inst, 'dateFormat' ) );
				datetimeChars =
					tp_inst._defaults.timeFormat
						.toString()
						.replace( /[hms]/g, '' )
						.replace( /TT/g, ampm ? 'APM' : '' )
						.replace( /Tt/g, ampm ? 'AaPpMm' : '' )
						.replace( /tT/g, ampm ? 'AaPpMm' : '' )
						.replace( /T/g, ampm ? 'AP' : '' )
						.replace( /tt/g, ampm ? 'apm' : '' )
						.replace( /t/g, ampm ? 'ap' : '' ) +
					' ' +
					tp_inst._defaults.separator +
					tp_inst._defaults.timeSuffix +
					( tz ? tp_inst._defaults.timezoneList.join( '' ) : '' ) +
					tp_inst._defaults.amNames.join( '' ) +
					tp_inst._defaults.pmNames.join( '' ) +
					dateChars;
				chr = String.fromCharCode( event.charCode === undefined ? event.keyCode : event.charCode );
				return event.ctrlKey || chr < ' ' || ! dateChars || datetimeChars.indexOf( chr ) > -1;
			}
		}

		return $.tm_datepicker._base_doKeyPress( event );
	};

	/*
	 * Fourth bad hack :/ override _updateAlternate function used in inline mode to init altField
	 * Update any alternate field to synchronise with the main field.
	 */
	$.tm_datepicker._base_updateAlternate = $.tm_datepicker._updateAlternate;
	$.tm_datepicker._updateAlternate = function( inst ) {
		var tp_inst = this._get( inst, 'tm_timepicker' );
		var altField;
		var date;
		var formatCfg;
		var altFormattedDateTime;
		var altSeparator;
		var altTimeSuffix;
		var altTimeFormat;
		if ( tp_inst ) {
			altField = tp_inst._defaults.altField;
			if ( altField ) {
				// update alternate field too
				date = this._getDate( inst );
				formatCfg = $.tm_datepicker._getFormatConfig( inst );
				altFormattedDateTime = '';
				altSeparator = tp_inst._defaults.altSeparator ? tp_inst._defaults.altSeparator : tp_inst._defaults.separator;
				altTimeSuffix = tp_inst._defaults.altTimeSuffix ? tp_inst._defaults.altTimeSuffix : tp_inst._defaults.timeSuffix;
				altTimeFormat = tp_inst._defaults.altTimeFormat !== null ? tp_inst._defaults.altTimeFormat : tp_inst._defaults.timeFormat;

				altFormattedDateTime += $.tm_datepicker.formatTime( altTimeFormat, tp_inst, tp_inst._defaults ) + altTimeSuffix;
				if ( ! tp_inst._defaults.timeOnly && ! tp_inst._defaults.altFieldTimeOnly && date !== null ) {
					if ( tp_inst._defaults.altFormat ) {
						altFormattedDateTime = $.tm_datepicker.formatDate( tp_inst._defaults.altFormat, date, formatCfg ) + altSeparator + altFormattedDateTime;
					} else {
						altFormattedDateTime = tp_inst.formattedDate + altSeparator + altFormattedDateTime;
					}
				}
				$( altField ).val( inst.input.val() ? altFormattedDateTime : '' );
			}
		} else {
			$.tm_datepicker._base_updateAlternate( inst );
		}
	};

	/*
	 * Override key up event to sync manual input changes.
	 */
	$.tm_datepicker._base_doKeyUp = $.tm_datepicker._doKeyUp;
	$.tm_datepicker._doKeyUp = function( event ) {
		var inst = $.tm_datepicker._getInst( event.target ),
			tp_inst = $.tm_datepicker._get( inst, 'tm_timepicker' );

		if ( tp_inst ) {
			if ( tp_inst._defaults.timeOnly && inst.input.val() !== inst.lastVal ) {
				try {
					$.tm_datepicker._updateDatepicker( inst );
				} catch ( err ) {
					$.tm_timepicker.log( err );
				}
			}
		}

		return $.tm_datepicker._base_doKeyUp( event );
	};

	/*
	 * override "Today" button to also grab the time and set it to input field.
	 */
	$.tm_datepicker._base_gotoToday = $.tm_datepicker._gotoToday;
	$.tm_datepicker._gotoToday = function( id ) {
		var inst = this._getInst( $( id )[ 0 ] );
		var tp_inst;
		var tzoffset;
		var now;
		this._base_gotoToday( id );
		tp_inst = this._get( inst, 'timepicker' );
		if ( ! tp_inst ) {
			return;
		}

		tzoffset = $.tm_timepicker.timezoneOffsetNumber( tp_inst.timezone );
		now = new Date();
		now.setMinutes( now.getMinutes() + now.getTimezoneOffset() + parseInt( tzoffset, 10 ) );
		this._setTime( inst, now );
		this._setDate( inst, now );
		tp_inst._onSelectHandler();
	};

	/*
	 * Disable & enable the Time in the datetimepicker
	 */
	$.tm_datepicker._disableTimepickerDatepicker = function( target ) {
		var inst = this._getInst( target );
		var tp_inst;
		if ( ! inst ) {
			return;
		}

		tp_inst = this._get( inst, 'tm_timepicker' );
		$( target ).tm_datepicker( 'getDate' ); // Init selected[Year|Month|Day]
		if ( tp_inst ) {
			inst.settings.showTimepicker = false;
			tp_inst._defaults.showTimepicker = false;
			tp_inst._updateDateTime( inst );
		}
	};

	$.tm_datepicker._enableTimepickerDatepicker = function( target ) {
		var inst = this._getInst( target );
		var tp_inst;
		if ( ! inst ) {
			return;
		}

		tp_inst = this._get( inst, 'tm_timepicker' );
		$( target ).tm_datepicker( 'getDate' ); // Init selected[Year|Month|Day]
		if ( tp_inst ) {
			inst.settings.showTimepicker = true;
			tp_inst._defaults.showTimepicker = true;
			tp_inst._addTimePicker( inst ); // Could be disabled on page load
			tp_inst._updateDateTime( inst );
		}
	};

	/*
	 * Create our own set time function
	 */
	$.tm_datepicker._setTime = function( inst, date ) {
		var tp_inst = this._get( inst, 'tm_timepicker' );
		var defaults;
		if ( tp_inst ) {
			defaults = tp_inst._defaults;

			// calling _setTime with no date sets time to defaults
			tp_inst.hour = date ? date.getHours() : defaults.hour;
			tp_inst.minute = date ? date.getMinutes() : defaults.minute;
			tp_inst.second = date ? date.getSeconds() : defaults.second;
			tp_inst.millisec = date ? date.getMilliseconds() : defaults.millisec;
			tp_inst.microsec = date ? date.getMicroseconds() : defaults.microsec;

			//check if within min/max times..
			tp_inst._limitMinMaxDateTime( inst, true );

			tp_inst._onTimeChange();
			tp_inst._updateDateTime( inst );
		}
	};

	/*
	 * Create new public method to set only time, callable as $().tm_datepicker('setTime', date)
	 */
	$.tm_datepicker._setTimeDatepicker = function( target, date, withDate ) {
		var inst = this._getInst( target );
		var tp_inst;
		var tp_date;

		if ( ! inst ) {
			return;
		}

		tp_inst = this._get( inst, 'tm_timepicker' );

		if ( tp_inst ) {
			this._setDateFromField( inst );

			if ( date ) {
				if ( typeof date === 'string' ) {
					tp_inst._parseTime( date, withDate );
					tp_date = new Date();
					tp_date.setHours( tp_inst.hour, tp_inst.minute, tp_inst.second, tp_inst.millisec );
					tp_date.setMicroseconds( tp_inst.microsec );
				} else {
					tp_date = new Date( date.getTime() );
					tp_date.setMicroseconds( date.getMicroseconds() );
				}
				if ( tp_date.toString() === 'Invalid Date' ) {
					tp_date = undefined;
				}
				this._setTime( inst, tp_date );
			}
		}
	};

	/*
	 * override setDate() to allow setting time too within Date object
	 */
	$.tm_datepicker._base_setDateDatepicker = $.tm_datepicker._setDateDatepicker;
	$.tm_datepicker._setDateDatepicker = function( target, _date ) {
		var inst = this._getInst( target );
		var date = _date;
		var tp_inst;
		var tp_date;
		if ( ! inst ) {
			return;
		}

		if ( typeof _date === 'string' ) {
			date = new Date( _date );
			if ( ! date.getTime() ) {
				this._base_setDateDatepicker.apply( this, arguments );
				date = $( target ).tm_datepicker( 'getDate' );
			}
		}

		tp_inst = this._get( inst, 'tm_timepicker' );
		if ( date instanceof Date ) {
			tp_date = new Date( date.getTime() );
			tp_date.setMicroseconds( date.getMicroseconds() );
		} else {
			tp_date = date;
		}

		// This is important if you are using the timezone option, javascript's Date
		// object will only return the timezone offset for the current locale, so we
		// adjust it accordingly.  If not using timezone option this won't matter..
		// If a timezone is different in tp, keep the timezone as is
		if ( tp_inst && tp_date ) {
			// look out for DST if tz wasn't specified
			if ( ! tp_inst.support.timezone && tp_inst._defaults.timezone === null ) {
				tp_inst.timezone = tp_date.getTimezoneOffset() * -1;
			}
			date = $.tm_timepicker.timezoneAdjust( date, $.tm_timepicker.timezoneOffsetString( -date.getTimezoneOffset() ), tp_inst.timezone );
			tp_date = $.tm_timepicker.timezoneAdjust( tp_date, $.tm_timepicker.timezoneOffsetString( -tp_date.getTimezoneOffset() ), tp_inst.timezone );
		}

		this._updateDatepicker( inst );
		this._base_setDateDatepicker.apply( this, arguments );
		this._setTimeDatepicker( target, tp_date, true );
	};

	/*
	 * override getDate() to allow getting time too within Date object
	 */
	$.tm_datepicker._base_getDateDatepicker = $.tm_datepicker._getDateDatepicker;
	$.tm_datepicker._getDateDatepicker = function( target, noDefault ) {
		var inst = this._getInst( target );
		var tp_inst;
		var date;
		var currDT;

		if ( ! inst ) {
			return;
		}

		tp_inst = this._get( inst, 'tm_timepicker' );

		if ( tp_inst ) {
			// if it hasn't yet been defined, grab from field
			if ( inst.lastVal === undefined ) {
				this._setDateFromField( inst, noDefault );
			}

			date = this._getDate( inst );

			currDT = null;

			if ( tp_inst.$altInput && tp_inst._defaults.altFieldTimeOnly ) {
				currDT = tp_inst.$input.val() + ' ' + tp_inst.$altInput.val();
			} else if ( tp_inst.$input.get( 0 ).tagName !== 'INPUT' && tp_inst.$altInput ) {
				/**
				 * in case the datetimepicker has been applied to a non-input tag for inline UI,
				 * and the user has not configured the plugin to display only time in altInput,
				 * pick current date time from the altInput (and hope for the best, for now, until "ER1" is applied)
				 *
				 * @todo ER1. Since altInput can have a totally difference format, convert it to standard format by reading input format from "altFormat" and "altTimeFormat" option values
				 */
				currDT = tp_inst.$altInput.val();
			} else {
				currDT = tp_inst.$input.val();
			}

			if ( date && tp_inst._parseTime( currDT, ! inst.settings.timeOnly ) ) {
				date.setHours( tp_inst.hour, tp_inst.minute, tp_inst.second, tp_inst.millisec );
				date.setMicroseconds( tp_inst.microsec );

				// This is important if you are using the timezone option, javascript's Date
				// object will only return the timezone offset for the current locale, so we
				// adjust it accordingly.  If not using timezone option this won't matter..
				if ( tp_inst.timezone !== null ) {
					// look out for DST if tz wasn't specified
					if ( ! tp_inst.support.timezone && tp_inst._defaults.timezone === null ) {
						tp_inst.timezone = date.getTimezoneOffset() * -1;
					}
					date = $.tm_timepicker.timezoneAdjust( date, tp_inst.timezone, $.tm_timepicker.timezoneOffsetString( -date.getTimezoneOffset() ) );
				}
			}
			return date;
		}
		return this._base_getDateDatepicker( target, noDefault );
	};

	/*
	 * override parseDate() because UI 1.8.14 throws an error about "Extra characters"
	 * An option in datapicker to ignore extra format characters would be nicer.
	 */
	$.tm_datepicker._base_parseDate = $.tm_datepicker.parseDate;
	$.tm_datepicker.parseDate = function( format, value, settings ) {
		var date;
		try {
			date = this._base_parseDate( format, value, settings );
		} catch ( err ) {
			// Hack!  The error message ends with a colon, a space, and
			// the "extra" characters.  We rely on that instead of
			// attempting to perfectly reproduce the parsing algorithm.
			if ( err.indexOf( ':' ) >= 0 ) {
				date = this._base_parseDate( format, value.substring( 0, value.length - ( err.length - err.indexOf( ':' ) - 2 ) ), settings );
				$.tm_timepicker.log( 'Error parsing the date string: ' + err + '\ndate string = ' + value + '\ndate format = ' + format );
			} else {
				throw err;
			}
		}
		return date;
	};

	/*
	 * override formatDate to set date with time to the input
	 */
	$.tm_datepicker._base_formatDate = $.tm_datepicker._formatDate;
	$.tm_datepicker._formatDate = function( inst ) {
		var tp_inst = this._get( inst, 'tm_timepicker' );
		if ( tp_inst ) {
			tp_inst._updateDateTime( inst );
			return tp_inst.$input.val();
		}
		return this._base_formatDate( inst );
	};

	/*
	 * override options setter to add time to maxDate(Time) and minDate(Time). MaxDate
	 */
	$.tm_datepicker._base_optionDatepicker = $.tm_datepicker._optionDatepicker;
	$.tm_datepicker._optionDatepicker = function( target, name, value ) {
		var inst = this._getInst( target ),
			name_clone;
		var tp_inst;
		var min, max, onselect, overrides, fns, prop, ret, oldVal, $target;

		if ( ! inst ) {
			return null;
		}

		tp_inst = this._get( inst, 'tm_timepicker' );
		if ( tp_inst ) {
			min = null;
			max = null;
			onselect = null;
			overrides = tp_inst._defaults.evnts;
			fns = {};

			if ( typeof name === 'string' ) {
				// if min/max was set with the string
				if ( name === 'minDate' || name === 'minDateTime' ) {
					min = value;
				} else if ( name === 'maxDate' || name === 'maxDateTime' ) {
					max = value;
				} else if ( name === 'onSelect' ) {
					onselect = value;
				} else if ( Object.prototype.hasOwnProperty.call( overrides, name ) ) {
					if ( typeof value === 'undefined' ) {
						return overrides[ name ];
					}
					fns[ name ] = value;
					name_clone = {}; //empty results in exiting function after overrides updated
				}
			} else if ( typeof name === 'object' ) {
				//if min/max was set with the JSON
				if ( name.minDate ) {
					min = name.minDate;
				} else if ( name.minDateTime ) {
					min = name.minDateTime;
				} else if ( name.maxDate ) {
					max = name.maxDate;
				} else if ( name.maxDateTime ) {
					max = name.maxDateTime;
				}
				for ( prop in overrides ) {
					if ( Object.prototype.hasOwnProperty.call( overrides, prop ) && name[ prop ] ) {
						fns[ prop ] = name[ prop ];
					}
				}
			}
			for ( prop in fns ) {
				if ( Object.prototype.hasOwnProperty.call( fns, prop ) ) {
					overrides[ prop ] = fns[ prop ];
					if ( ! name_clone ) {
						name_clone = $.extend( {}, name );
					}
					delete name_clone[ prop ];
				}
			}
			if ( name_clone && isEmptyObject( name_clone ) ) {
				return;
			}
			if ( min ) {
				//if min was set
				if ( min === 0 ) {
					min = new Date();
				} else {
					min = new Date( min );
				}
				tp_inst._defaults.minDate = min;
				tp_inst._defaults.minDateTime = min;
			} else if ( max ) {
				//if max was set
				if ( max === 0 ) {
					max = new Date();
				} else {
					max = new Date( max );
				}
				tp_inst._defaults.maxDate = max;
				tp_inst._defaults.maxDateTime = max;
			} else if ( onselect ) {
				tp_inst._defaults.onSelect = onselect;
			}

			// Datepicker will override our date when we call _base_optionDatepicker when
			// calling minDate/maxDate, so we will first grab the value, call
			// _base_optionDatepicker, then set our value back.
			if ( min || max ) {
				$target = $( target );
				oldVal = $target.tm_datetimepicker( 'getDate' );
				ret = this._base_optionDatepicker.call( $.tm_datepicker, target, name_clone || name, value );
				$target.tm_datetimepicker( 'setDate', oldVal );
				return ret;
			}
		}
		if ( value === undefined ) {
			return this._base_optionDatepicker.call( $.tm_datepicker, target, name );
		}
		return this._base_optionDatepicker.call( $.tm_datepicker, target, name_clone || name, value );
	};

	/*
	 * Create a Singleton Instance
	 */
	$.tm_timepicker = new Timepicker();

	/**
	 * Get the timezone offset as string from a date object (eg '+0530' for UTC+5.5)
	 *
	 * @param {number}  tzMinutes if not a number, less than -720 (-1200), or greater than 840 (+1400) this value is returned
	 * @param {boolean} iso8601   if true formats in accordance to iso8601 "+12:45"
	 * @return {string} timezone Offset
	 */
	$.tm_timepicker.timezoneOffsetString = function( tzMinutes, iso8601 ) {
		var off, minutes, hours, iso, tz;

		if ( isNaN( tzMinutes ) || tzMinutes > 840 || tzMinutes < -720 ) {
			return tzMinutes;
		}

		off = tzMinutes;
		minutes = off % 60;
		hours = ( off - minutes ) / 60;
		iso = iso8601 ? ':' : '';
		tz = ( off >= 0 ? '+' : '-' ) + ( '0' + Math.abs( hours ) ).slice( -2 ) + iso + ( '0' + Math.abs( minutes ) ).slice( -2 );

		if ( tz === '+00:00' ) {
			return 'Z';
		}
		return tz;
	};

	/**
	 * Get the number in minutes that represents a timezone string
	 *
	 * @param {string} tzString formatted like "+0500", "-1245", "Z"
	 * @return {number} the offset minutes or the original string if it doesn't match expectations
	 */
	$.tm_timepicker.timezoneOffsetNumber = function( tzString ) {
		var normalized = tzString.toString().replace( ':', '' ); // excuse any iso8601, end up with "+1245"

		if ( normalized.toUpperCase() === 'Z' ) {
			// if iso8601 with Z, its 0 minute offset
			return 0;
		}

		if ( ! /^(-|\+)\d{4}$/.test( normalized ) ) {
			// possibly a user defined tz, so just give it back
			return parseInt( tzString, 10 );
		}

		return (
			( normalized.substring( 0, 1 ) === '-' ? -1 : 1 ) * // plus or minus
			( ( parseInt( normalized.substring( 1, 3 ), 10 ) * 60 ) + // hours (converted to minutes)
				parseInt( normalized.substring( 3, 5 ), 10 ) )
		); // minutes
	};

	/**
	 * No way to set timezone in js Date, so we must adjust the minutes to compensate.
	 * (think setDate, getDate)
	 *
	 * @param {Date}   date
	 * @param {string} fromTimezone formatted like "+0500", "-1245"
	 * @param {string} toTimezone   formatted like "+0500", "-1245"
	 * @return {Date} Date
	 */
	$.tm_timepicker.timezoneAdjust = function( date, fromTimezone, toTimezone ) {
		var fromTz = $.timepicker.timezoneOffsetNumber( fromTimezone );
		var toTz = $.tm_timepicker.timezoneOffsetNumber( toTimezone );
		if ( ! isNaN( toTz ) ) {
			date.setMinutes( date.getMinutes() + -fromTz - -toTz );
		}
		return date;
	};

	/**
	 * Calls `tm_timepicker()` on the `startTime` and `endTime` elements, and configures them to
	 * enforce date range limits.
	 * n.b. The input value must be correctly formatted (reformatting is not supported)
	 *
	 * @param {Element} startTime
	 * @param {Element} endTime
	 * @param {Object}  options   Options for the tm_timepicker() call
	 * @return {jQuery} time range
	 */
	$.tm_timepicker.timeRange = function( startTime, endTime, options ) {
		return $.tm_timepicker.handleRange( 'tm_timepicker', startTime, endTime, options );
	};

	/**
	 * Calls `datetimepicker` on the `startTime` and `endTime` elements, and configures them to
	 * enforce date range limits.
	 *
	 * @param {Element} startTime
	 * @param {Element} endTime
	 * @param {Object}  options   Options for the `tm_timepicker()` call. Also supports `reformat`,
	 *                            a boolean value that can be used to reformat the input values to the `dateFormat`.
	 */
	$.tm_timepicker.datetimeRange = function( startTime, endTime, options ) {
		$.tm_timepicker.handleRange( 'tm_datetimepicker', startTime, endTime, options );
	};

	/**
	 * Calls `tm_datepicker` on the `startTime` and `endTime` elements, and configures them to
	 * enforce date range limits.
	 *
	 * @param {Element} startTime
	 * @param {Element} endTime
	 * @param {Object}  options   Options for the `tm_timepicker()` call. Also supports `reformat`,
	 *                            a boolean value that can be used to reformat the input values to the `dateFormat`.
	 */
	$.tm_timepicker.dateRange = function( startTime, endTime, options ) {
		$.tm_timepicker.handleRange( 'tm_datepicker', startTime, endTime, options );
	};

	/**
	 * Calls `method` on the `startTime` and `endTime` elements, and configures them to
	 * enforce date range limits.
	 *
	 * @param {string}  method    Can be used to specify the type of picker to be added
	 * @param {Element} startTime
	 * @param {Element} endTime
	 * @param {Object}  options   Options for the `tm_timepicker()` call. Also supports `reformat`,
	 *                            a boolean value that can be used to reformat the input values to the `dateFormat`.
	 * @return {jQuery} range
	 */
	$.tm_timepicker.handleRange = function( method, startTime, endTime, options ) {
		var timeOnly = false;
		options = $.extend(
			{},
			{
				minInterval: 0, // min allowed interval in milliseconds
				maxInterval: 0, // max allowed interval in milliseconds
				start: {}, // options for start picker
				end: {} // options for end picker
			},
			options
		);

		// for the mean time this fixes an issue with calling getDate with tm_timepicker()

		if ( method === 'tm_timepicker' ) {
			timeOnly = true;
			method = 'tm_datetimepicker';
		}

		function checkDates( changed, other ) {
			var startdt = startTime[ method ]( 'getDate' ),
				enddt = endTime[ method ]( 'getDate' ),
				changeddt = changed[ method ]( 'getDate' );
			var minDate;
			var maxDate;

			if ( startdt !== null ) {
				minDate = new Date( startdt.getTime() );
				maxDate = new Date( startdt.getTime() );

				minDate.setMilliseconds( minDate.getMilliseconds() + options.minInterval );
				maxDate.setMilliseconds( maxDate.getMilliseconds() + options.maxInterval );

				if ( options.minInterval > 0 && minDate > enddt ) {
					// minInterval check
					endTime[ method ]( 'setDate', minDate );
				} else if ( options.maxInterval > 0 && maxDate < enddt ) {
					// max interval check
					endTime[ method ]( 'setDate', maxDate );
				} else if ( startdt > enddt ) {
					other[ method ]( 'setDate', changeddt );
				}
			}
		}

		function selected( changed, other, option ) {
			var date;
			if ( ! changed.val() ) {
				return;
			}
			date = changed[ method ].call( changed, 'getDate' );
			if ( date !== null && options.minInterval > 0 ) {
				if ( option === 'minDate' ) {
					date.setMilliseconds( date.getMilliseconds() + options.minInterval );
				}
				if ( option === 'maxDate' ) {
					date.setMilliseconds( date.getMilliseconds() - options.minInterval );
				}
			}

			if ( date.getTime ) {
				other[ method ].call( other, 'option', option, date );
			}
		}

		$.fn[ method ].call(
			startTime,
			$.extend(
				{
					timeOnly: timeOnly,
					onClose: function() {
						checkDates( $( this ), endTime );
					},
					onSelect: function() {
						selected( $( this ), endTime, 'minDate' );
					}
				},
				options,
				options.start
			)
		);
		$.fn[ method ].call(
			endTime,
			$.extend(
				{
					timeOnly: timeOnly,
					onClose: function() {
						checkDates( $( this ), startTime );
					},
					onSelect: function() {
						selected( $( this ), startTime, 'maxDate' );
					}
				},
				options,
				options.end
			)
		);

		checkDates( startTime, endTime );

		selected( startTime, endTime, 'minDate' );
		selected( endTime, startTime, 'maxDate' );

		return $( [ startTime.get( 0 ), endTime.get( 0 ) ] );
	};

	/**
	 * Log error or data to the console during error or debugging
	 *
	 * @return {void}
	 */
	$.tm_timepicker.log = function() {
		// Older IE (9, maybe 10) throw error on accessing `window.console.log.apply`, so check first.
		if ( window.console && window.console.log && window.console.log.apply ) {
			window.console.log.apply( window.console, Array.prototype.slice.call( arguments ) );
		}
	};

	/*
	 * Add util object to allow access to private methods for testability.
	 */
	$.tm_timepicker._util = {
		_extendRemove: extendRemove,
		_isEmptyObject: isEmptyObject,
		_convert24to12: convert24to12,
		_detectSupport: detectSupport,
		_selectLocalTimezone: selectLocalTimezone,
		_computeEffectiveSetting: computeEffectiveSetting,
		_splitDateTime: splitDateTime,
		_parseDateTimeInternal: parseDateTimeInternal
	};

	/*
	 * Microsecond support
	 */
	if ( ! Date.prototype.getMicroseconds ) {
		Object.defineProperty( Date.prototype, 'microseconds', {
			value: 0,
			writable: true
		} );

		Object.defineProperty( Date.prototype, 'getMicroseconds', {
			value: function() {
				return this.microseconds;
			}
		} );

		Object.defineProperty( Date.prototype, 'setMicroseconds', {
			value: function( m ) {
				this.setMilliseconds( this.getMilliseconds() + Math.floor( m / 1000 ) );
				this.microseconds = m % 1000;
				return this;
			}
		} );
	}

	/*
	 * Keep up with the version
	 */
	$.tm_timepicker.version = '1.6.3';
} ) );
