/**
 * Lazy Load XT v1.1.0 2016-01-12
 * http://ressio.github.io/lazy-load-xt
 * (C) 2016 RESS.io
 * Licensed under MIT
 * reformatted code by ThemeComplete
 */

( function( $, window, document ) {
	'use strict';

	// options
	var lazyLoadXT = 'lazyLoadXT',
		dataLazied = 'lazied',
		loadError = 'load error',
		classLazyHidden = 'lazy-hidden',
		docElement = document.documentElement || document.body,
		//  force load all images in Opera Mini and some mobile browsers without scroll event or getBoundingClientRect()
		forceLoad =
			window.onscroll === undefined ||
			!! window.operamini ||
			! docElement.getBoundingClientRect,
		options = {
			autoInit: true, // auto initialize in document ready
			selector: 'img[data-src]', // selector for lazyloading elements
			blankImage: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
			throttle: 99, // interval (ms) for changes check
			forceLoad: forceLoad, // force auto load all images

			loadEvent: 'pageshow', // check AJAX-loaded content in jQueryMobile
			updateEvent: 'load orientationchange resize scroll touchmove focus', // page-modified events
			forceEvent: 'lazyloadall', // force loading of all elements

			//onstart: null,
			oninit: { removeClass: 'lazy' }, // init handler
			onshow: { addClass: classLazyHidden }, // start loading handler
			onload: { removeClass: classLazyHidden, addClass: 'lazy-loaded' }, // load success handler
			onerror: { removeClass: classLazyHidden }, // error handler
			//oncomplete: null, // complete handler

			//scrollContainer: undefined,
			checkDuplicates: true
		},
		elementOptions = {
			srcAttr: 'data-src',
			edgeX: 0,
			edgeY: 0,
			visibleOnly: true
		},
		$window = $( window ),
		$isFunction = function( f ) {
			return typeof f === 'function';
		},
		$extend = $.extend,
		$data =
			$.data ||
			function( el, name ) {
				return $( el ).data( name );
			},
		elements = [],
		topLazy = 0,
		/*
         waitingMode=0 : no setTimeout
         waitingMode=1 : setTimeout, no deferred events
         waitingMode=2 : setTimeout, deferred events
         */
		waitingMode = 0;

	$[ lazyLoadXT ] = $extend( options, elementOptions, $[ lazyLoadXT ] );

	/**
	 * Return options.prop if obj.prop is undefined, otherwise return obj.prop
	 *
	 * @param {*} obj
	 * @param {*} prop
	 */
	function getOrDef( obj, prop ) {
		return obj[ prop ] === undefined ? options[ prop ] : obj[ prop ];
	}

	function scrollTop() {
		var scroll = window.pageYOffset;
		return scroll === undefined ? docElement.scrollTop : scroll;
	}

	/**
	 * Run check of lazy elements after timeout
	 */
	function timeoutLazyElements() {
		if ( waitingMode > 1 ) {
			waitingMode = 1;
			checkLazyElements();
			setTimeout( timeoutLazyElements, options.throttle );
		} else {
			waitingMode = 0;
		}
	}

	/**
	 * Queue check of lazy elements because of event e
	 *
	 * @param {Event} [e]
	 */
	function queueCheckLazyElements( e ) {
		if ( ! elements.length ) {
			return;
		}

		// fast check for scroll event without new visible elements
		if ( e && e.type === 'scroll' && e.currentTarget === window ) {
			if ( topLazy >= scrollTop() ) {
				return;
			}
		}

		if ( ! waitingMode ) {
			setTimeout( timeoutLazyElements, 0 );
		}
		waitingMode = 2;
	}

	/**
	 * Process function/object event handler
	 *
	 * @param {string} event suffix
	 * @param {jQuery} $el
	 */
	function triggerEvent( event, $el ) {
		var handler = options[ 'on' + event ];
		if ( handler ) {
			if ( $isFunction( handler ) ) {
				handler.call( $el[ 0 ] );
			} else {
				if ( handler.addClass ) {
					$el.addClass( handler.addClass );
				}
				if ( handler.removeClass ) {
					$el.removeClass( handler.removeClass );
				}
			}
		}

		$el.trigger( 'lazy' + event, [ $el ] );

		// queue next check as images may be resized after loading of actual file
		queueCheckLazyElements();
	}

	/**
	 * Trigger onload/onerror handler
	 *
	 * @param {Event} e
	 */
	function triggerLoadOrError( e ) {
		triggerEvent( e.type, $( e.currentTarget ).off( loadError, triggerLoadOrError ) );
	}

	/**
	 * Load visible elements
	 *
	 * @param {bool} [force] loading of all elements
	 */
	function checkLazyElements( force ) {
		var viewportTop, viewportHeight, viewportWidth, i, length;

		var $el, el, objData, removeNode, visible, topEdge;

		var elPos, edgeX, edgeY;

		var srcAttr, src;

		if ( ! elements.length ) {
			return;
		}

		force = force || options.forceLoad;

		topLazy = Infinity;

		viewportTop = scrollTop();
		viewportHeight = window.innerHeight || docElement.clientHeight;
		viewportWidth = window.innerWidth || docElement.clientWidth;

		for ( i = 0, length = elements.length; i < length; i += 1 ) {
			$el = elements[ i ];
			el = $el[ 0 ];
			objData = $el[ lazyLoadXT ];
			removeNode = false;
			visible = force || $data( el, dataLazied ) < 0;

			// remove items that are not in DOM
			if ( ! $.contains( docElement, el ) ) {
				removeNode = true;
			} else if ( force || ! objData.visibleOnly || el.offsetWidth || el.offsetHeight ) {
				if ( ! visible ) {
					elPos = el.getBoundingClientRect();
					edgeX = objData.edgeX;
					edgeY = objData.edgeY;

					topEdge = elPos.top + viewportTop - edgeY - viewportHeight;

					visible = topEdge <= viewportTop && elPos.bottom > -edgeY && elPos.left <= viewportWidth + edgeX && elPos.right > -edgeX;
				}

				if ( visible ) {
					$el.on( loadError, triggerLoadOrError );

					triggerEvent( 'show', $el );

					srcAttr = objData.srcAttr;
					src = $isFunction( srcAttr ) ? srcAttr( $el ) : el.getAttribute( srcAttr );

					if ( src ) {
						el.src = src;
					}

					removeNode = true;
				} else if ( topEdge < topLazy ) {
					topLazy = topEdge;
				}
			}

			if ( removeNode ) {
				$data( el, dataLazied, 0 );
				elements.splice( i, 1 );
				i = i - 1;
				length = length - 1;
			}
		}

		if ( ! length ) {
			triggerEvent( 'complete', $( docElement ) );
		}
	}

	/**
	 * Add new elements to lazy-load list:
	 * $(elements).lazyLoadXT() or $(window).lazyLoadXT()
	 *
	 * @param {Object} [overrides] override global options
	 */
	$.fn[ lazyLoadXT ] = function( overrides ) {
		var blankImage;
		var checkDuplicates;
		var scrollContainer;
		var forceShow;
		var elementOptionsOverrides = {};

		overrides = overrides || {};

		blankImage = getOrDef( overrides, 'blankImage' );
		checkDuplicates = getOrDef( overrides, 'checkDuplicates' );
		scrollContainer = getOrDef( overrides, 'scrollContainer' );
		forceShow = getOrDef( overrides, 'show' );

		// empty overrides.scrollContainer is supported by both jQuery and Zepto
		$( scrollContainer ).on( 'scroll', queueCheckLazyElements );

		Object.keys( elementOptions ).forEach( function( prop ) {
			elementOptionsOverrides[ prop ] = getOrDef( overrides, prop );
		} );

		return this.each( function( index, el ) {
			var duplicate;
			var $el;
			if ( el === window ) {
				$( options.selector ).lazyLoadXT( overrides );
			} else {
				duplicate = checkDuplicates && $data( el, dataLazied );
				$el = $( el ).data( dataLazied, forceShow ? -1 : 1 );

				// prevent duplicates
				if ( duplicate ) {
					queueCheckLazyElements();
					return;
				}

				if ( blankImage && el.tagName === 'IMG' && ! el.src ) {
					el.src = blankImage;
				}

				// clone elementOptionsOverrides object
				$el[ lazyLoadXT ] = $extend( {}, elementOptionsOverrides );

				triggerEvent( 'init', $el );

				elements.push( $el );
				queueCheckLazyElements();
			}
		} );
	};

	/**
	 * Initialize list of hidden elements
	 */
	function initLazyElements() {
		$window.lazyLoadXT();
	}

	/**
	 * Loading of all elements
	 */
	function forceLoadAll() {
		checkLazyElements( true );
	}

	/**
	 * Initialization
	 */
	// document ready
	$( function() {
		triggerEvent( 'start', $window );

		$window.on( options.updateEvent, queueCheckLazyElements ).on( options.forceEvent, forceLoadAll );

		$( document ).on( options.updateEvent, queueCheckLazyElements );

		if ( options.autoInit ) {
			$window.on( options.loadEvent, initLazyElements );
			initLazyElements(); // standard initialization
		}
	} );
}( window.jQuery || window.Zepto || window.$, window, document ) );

( function( $ ) {
	'use strict';

	var options = $.lazyLoadXT;

	options.selector += ',video,iframe[data-src]';
	options.videoPoster = 'data-poster';

	$( document ).on( 'lazyshow', 'video', function( e, $el ) {
		var srcAttr = $el.lazyLoadXT.srcAttr,
			isFuncSrcAttr = typeof srcAttr === 'function',
			changed = false;

		$el.attr( 'poster', $el.attr( options.videoPoster ) );
		$el.children( 'source,track' ).each( function( index, el ) {
			var $child = $( el ),
				src = isFuncSrcAttr ? srcAttr( $child ) : $child.attr( srcAttr );
			if ( src ) {
				$child.attr( 'src', src );
				changed = true;
			}
		} );

		// reload video
		if ( changed ) {
			this.load();
		}
	} );
}( window.jQuery || window.Zepto || window.$ ) );
