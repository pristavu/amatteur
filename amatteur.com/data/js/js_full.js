
$(document).ready(function(){
	if($.browser.mozilla || $.browser.webkit){
		$('.c_icons_small').show();
	}else{
		
	}

//	$(".preview").live('hover',
//			
//					
//						function(){
//							
//							if($.browser.mozilla || $.browser.webkit){
//								$(this).find($(".PriceContainer")).addClass('animateOut');
//								$(this).find($(".PriceContainer")).removeClass('animateIn');
//								
//							}else{
//								$(this).find($(".PriceContainer")).hide();
//								$(this).find($('.c_icons_small')).show();
//							
//							}
//						},
//						function(){
//							//
//							if($.browser.mozilla || $.browser.webkit){
//								
//								$(this).find($(".PriceContainer")).addClass('animateIn');
//								$(this).find($(".PriceContainer")).removeClass('animateOut');
//							}else{
//								
//								$(this).find($(".PriceContainer")).show();
//								$(this).find($('.c_icons_small')).hide();
//							}
//						}
//					
//					
//		);

	$('.c_icons_small li a').hover(
			function(){
				$(this).find($('.text')).addClass('shown');
			},
			function(){
				$(this).find($('.text')).removeClass('shown');
			}
		
	)
});

$(document).ready(function(){
	$('#ContextBarqSearch').css({
		'min-width' : Math.ceil(($(window).width()/10)*9)
	});
	
	$(document).ajaxComplete(function(e, xhr, settings) {
		if(settings.dataType == "json") {
			try {
				json = jQuery.parseJSON(xhr.responseText);
				if(json && json.error && typeof json.error == 'object') {
					var string = '';
					for(i in json.error) {
						string += i + ': ' + json.error[i] + '<br />';
					}
					Pins.error(string);
				}
			} catch(e) {}
		}
	});
});
/* autocomplete */
(function( $, window, undefined ) {

	// Expose autoComplete to the jQuery chain
	$.fn.autoComplete = function() {
		// Force array of arguments
		var args = Slice.call( arguments ),
			self = this, 
			first = args.shift(),
			isMethod = typeof first === 'string',
			handler, el;

		// Deep namespacing is not supported in jQuery, a mistake I made in v4.1
		if ( isMethod ) {
			first = first.replace( rdot, '-' );
		}
		
		// Allow for passing array of arguments, or multiple arguments
		// Eg: .autoComplete('trigger', [arg1, arg2, arg3...]) or .autoComplete('trigger', arg1, arg2, arg3...)
		// Mainly to allow for .autoComplete('trigger', arguments) to work
		// Note*: Some triggers pass an array as the first param, so check against that first
		args = ( AutoComplete.arrayMethods[ first ] === TRUE && $.isArray( args[0] ) && $.isArray( args[0][0] ) ) || 
			( args.length === 1 && $.isArray( args[0] ) ) ? 
				args[0] : args;

		// Check method against handlers that need to use triggerHandler 
		handler = isMethod && ( AutoComplete.handlerMethods[ first ] === -1 || args.length < ( AutoComplete.handlerMethods[ first ] || 0 ) ) ? 
			'triggerHandler' : 'trigger';

		return isMethod ?
			self[ handler ]( 'autoComplete.' + first, args ) :

			// Allow passing a jquery event special object {from $.Event()}
			first && first.preventDefault !== undefined ? self.trigger( first, args ) :

			// Initiate the autocomplete on each element (Only takes a single argument, the options object)
			self.each(function(){
				if ( $( el = this ).data( 'autoComplete' ) !== TRUE ) {
					AutoCompleteFunction( el, first );
				}
			});
	};

	// bgiframe is needed to fix z-index problem for IE6 users.
	$.fn.bgiframe = $.fn.bgiframe ? $.fn.bgiframe : $.fn.bgIframe ? $.fn.bgIframe : function() {
		// For applications that don't have bgiframe plugin installed, create a useless 
		// function that doesn't break the chain
		return this;
	};

	// Allows for single event binding to document and forms associated with the autoComplete inputs
	// by deferring the event to the input in focus
	function setup( $input, inputIndex ) {
		if ( setup.flag !== TRUE ) {
			setup.flag = TRUE;
			rootjQuery.bind( 'click.autoComplete', function( event ) {
				AutoComplete.getFocus( TRUE ).trigger( 'autoComplete.document-click', [ event ] );
			});
		}

		var $form = $input.closest( 'form' ), formList = $form.data( 'ac-inputs' ) || {}, $el;

		formList[ inputIndex ] = TRUE;
		$form.data( 'ac-inputs', formList );

		if ( $form.data( 'autoComplete' ) !== TRUE ) {
			$form.data( 'autoComplete', TRUE ).bind( 'submit.autoComplete', function( event ) {
				return ( $el = AutoComplete.getFocus( TRUE ) ).length ?
					$el.triggerHandler( 'autoComplete.form-submit', [ event, this ] ) :
					TRUE;
			});
		}
	}

	// Removes the single events attached to the document and respective input form
	function teardown( $input, inputIndex ) {
		AutoComplete.remove( inputIndex );

		if ( setup.flag === TRUE && AutoComplete.length === 0 ) {
			setup.flag = FALSE;
			rootjQuery.unbind( 'click.autoComplete' );
		}

		var $form = $input.closest( 'form' ), formList = $form.data( 'ac-inputs' ) || {}, i;

		formList[ inputIndex ] = FALSE;
		for ( i in formList ) {
			if ( formList.hasOwnProperty( i ) && formList[ i ] === TRUE ) {
				return;
			}
		}

		$form.unbind( 'submit.autoComplete' );
	}

	// Default function for adding all supply items to the list
	function allSupply( event, ui ) {
		if ( ! $.isArray( ui.supply ) ) {
			return [];
		}

		for ( var i = -1, l = ui.supply.length, ret = [], entry; ++i < l; ) {
			entry = ui.supply[ i ];
			entry = entry && entry.value ? entry : { value: entry };
			ret.push( entry );
		}

		return ret;
	}



// Internals
var
	// Munging
	TRUE = true,
	FALSE = false,

	// Copy of the slice prototype
	Slice = Array.prototype.slice,

	// Make a copy of the document element for caching
	rootjQuery = $( window.document ),

	// Also make a copy of an empty jQuery set for fast referencing
	emptyjQuery = $( ),

	// regex's
	rdot = /\./,

	// Opera and Firefox on Mac need to use the keypress event to track holding of
	// a key down and not releasing
	keypress = window.opera || ( /macintosh/i.test( window.navigator.userAgent ) && $.browser.mozilla ),

	// Event flag that gets passed around
	ExpandoFlag = 'autoComplete_' + $.expando,

	// Make a local copy of the key codes used throughout the plugin
	KEY = {
		backspace: 8,
		tab: 9,
		enter: 13,
		shift: 16,
		space: 32,
		pageup: 33,
		pagedown: 34,
		left: 37,
		up: 38,
		right: 39,
		down: 40
	},

	// Attach global aspects to jQuery itself
	AutoComplete = $.autoComplete = {
		// Autocomplete Version
		version: '5.1',

		// Index Counter
		counter: 0,

		// Length of stack
		length: 0,

		// Storage of elements
		stack: {},

		// jQuery object versions of the storage elements
		jqStack: {},

		// Storage order of uid's
		order: [],

		// Global access to elements in use
		hasFocus: FALSE,

		// Expose the used keycodes
		keys: KEY,

		// Methods whose first argument may contain an array
		arrayMethods: {
			'button-supply': TRUE,
			'direct-supply': TRUE
		},

		// Defines the maximum number of arguments that can be passed for using
		// triggerHandler method instead of trigger. Passing -1 forces triggerHandler
		// no matter the number of arguments
		handlerMethods: {
			'option': 2
		},

		// Events triggered whenever one of the autoComplete
		// input's come into focus or blur out.
		focus: undefined,
		blur: undefined,

		// Allow access to jquery cached object versions of the elements
		getFocus: function( jqStack ) {
			return ! AutoComplete.order[0] ? jqStack ? emptyjQuery : undefined :
				jqStack ? AutoComplete.jqStack[ AutoComplete.order[0] ] :
				AutoComplete.stack[ AutoComplete.order[0] ];
		},

		getPrevious: function( jqStack ) {
			// Removing elements cause some indexs on the order stack
			// to become undefined, so loop until one is found
			for ( var i = 0, l = AutoComplete.order.length; ++i < l; ) {
				if ( AutoComplete.order[i] ) {
					return jqStack ?
						AutoComplete.jqStack[ AutoComplete.order[i] ] :
						AutoComplete.stack[ AutoComplete.order[i] ];
				}
			}

			return jqStack ? emptyjQuery : undefined;
		},

		remove: function( n ) {
			for ( var i = -1, l = AutoComplete.order.length; ++i < l; ) {
				if ( AutoComplete.order[i] === n ) {
					AutoComplete.order[i] = undefined;
				}
			}

			AutoComplete.length--;
			delete AutoComplete.stack[n];
		},

		// Returns full stack in jQuery form
		getAll: function(){
			for ( var i = -1, l = AutoComplete.counter, stack = []; ++i < l; ) {
				if ( AutoComplete.stack[i] ) {
					stack.push( AutoComplete.stack[i] );
				}
			}
			return $( stack );
		},

		defaults: {
			// To smooth upgrade process to 5.x, set backwardsCompatible to true
			backwardsCompatible: FALSE,
			// Server Script Path
			ajax: 'ajax.php',
			ajaxCache: $.ajaxSettings.cache,
			// Data Configuration
			dataSupply: [],
			dataFn: undefined,
			formatSupply: undefined,
			// Drop List CSS
			list: 'auto-complete-list',
			rollover: 'auto-complete-list-rollover',
			width: undefined, // Defined as inputs width when extended (can be overridden with this global/options/meta)
			striped: undefined,
			maxHeight: undefined,
			bgiframe: undefined,
			newList: FALSE,
			// Post Data
			postVar: 'value',
			postData: {},
			postFormat: undefined,
			// Limitations
			minChars: 1,
			maxItems: -1,
			maxRequests: 0,
			maxRequestsDeep: FALSE,
			requestType: 'POST',
			// Input
			inputControl: undefined,
			autoFill: FALSE,
			nonInput: [ KEY.shift, KEY.left, KEY.right ],
			multiple: FALSE,
			multipleSeparator: ' ',
			// Events
			onBlur: undefined,
			onFocus: undefined,
			onHide: undefined,
			onLoad: undefined,
			onMaxRequest: undefined,
			onRollover: undefined,
			onSelect: undefined,
			onShow: undefined,
			onListFormat: undefined,
			onSubmit: undefined,
			spinner: undefined,
			preventEnterSubmit: TRUE,
			delay: 0,
			// Caching Options
			useCache: TRUE,
			cacheLimit: 50
		}
	},

	// Autocomplete function
	AutoCompleteFunction = function( self, options ) {
		// Start with counters as they are used within declarations
		AutoComplete.length++;
		AutoComplete.counter++;

		// Input specific vars
		var $input = $( self ).attr( 'autocomplete', 'off' ),
			// Data object stored on 'autoComplete' data namespace of input
			ACData = {},
			// Track every event triggered
			LastEvent = {},
			// String of current input value
			inputval = '',
			// Holds the current list
			currentList = [],
			// Place holder for all list elements
			$elems = { length: 0 },
			// Place holder for the list element in focus
			$li,
			// View and heights for scrolling
			view, ulHeight, liHeight, liPerView,
			// Hardcoded value for ul visiblity
			ulOpen = FALSE,
			// Timer for delay
			timeid,
			// Ajax requests holder
			xhr,
			// li element in focus, and its data
			liFocus = -1, liData,
			// Fast referencing for multiple selections
			separator,
			// Index of current input
			inputIndex = AutoComplete.counter,
			// Number of requests made
			requests = 0,
			// Internal Per Input Cache
			cache = {
				length: 0,
				val: undefined,
				list: {}
			},

			// Merge defaults with passed options and metadata options
			settings = $.extend(
				{ width: $input.outerWidth() },
				AutoComplete.defaults, 
				options||{},
				$.metadata ? $input.metadata() : {}
			),

			// Create the drop list (Use an existing one if possible)
			$ul = ! settings.newList && rootjQuery.find( 'ul.' + settings.list )[ 0 ] ?
				rootjQuery.find( 'ul.' + settings.list ).eq( 0 ).bgiframe( settings.bgiframe ) :
				$('<ul/>').appendTo('body').addClass( settings.list ).bgiframe( settings.bgiframe ).hide().data( 'ac-selfmade', TRUE );


		// Start Binding
		$input.data( 'autoComplete', ACData = {
			index: inputIndex,
			hasFocus: FALSE,
			active: TRUE,
			settings: settings,
			initialSettings: $.extend( TRUE, {}, settings )
		});

		// IE catches the enter key only on keypress/keyup, so add a helper
		// to track that event if needed
		if ( $.browser.msie ) {
			$input.bind( 'keypress.autoComplete', function( event ) {
				if ( ! ACData.active ) {
					return TRUE;
				}

				if ( event.keyCode === KEY.enter ) {
					var enter = TRUE;

					// See entertracking on main key(press/down) event for explanation
					if ( $li && $li.hasClass( settings.rollover ) ) {
						enter = settings.preventEnterSubmit && ulOpen ? FALSE : TRUE;
						select( event );
					}
					else if ( ulOpen ) { 
						$ul.hide( event );
					}

					return enter;
				}
			});
		}


		// Opera && firefox on Mac use keypress to track holding down of key, 
		// while everybody else uses keydown for same functionality
		$input.bind( keypress ? 'keypress.autoComplete' : 'keydown.autoComplete' , function( event ) {
			// If autoComplete has been disabled, prevent input events
			if ( ! ACData.active ) {
				return TRUE;
			}

			// Track last event and store code for munging
			var key = ( LastEvent = event ).keyCode, enter = FALSE;


			// Tab Key
			if ( key === KEY.tab && ulOpen ) {
				select( event );
			}
			// Enter Key
			else if ( key === KEY.enter ) {
				// When tracking whether to submit the form or not, we have
				// to ensure that the user is actually selecting an element from the drop
				// down list. It no element is selected, then hide the list and track form
				// submission. If an element is selected, then track for submission first, 
				// then hide the list.
				enter = TRUE;
				if ( $li && $li.hasClass( settings.rollover ) ) {
					enter = settings.preventEnterSubmit && ulOpen ? FALSE : TRUE;
					select( event );
				}
				else if ( ulOpen ) { 
					$ul.hide( event );
				}
			}
			// Up Arrow
			else if ( key === KEY.up && ulOpen ) {
				if ( liFocus > 0 ) {
					liFocus--;
					up( event );
				} else {
					liFocus = -1;
					$input.val( inputval );
					$ul.hide( event );
				}
			}
			// Down Arrow
			else if ( key === KEY.down && ulOpen ) { 
				if ( liFocus < $elems.length - 1 ) {
					liFocus++;
					down( event );
				}
			}
			// Page Up
			else if ( key === KEY.pageup && ulOpen ) {
				if ( liFocus > 0 ) {
					liFocus -= liPerView;

					if ( liFocus < 0 ) {
						liFocus = 0;
					}

					up( event );
				}
			}
			// Page Down
			else if ( key === KEY.pagedown && ulOpen ) {
				if ( liFocus < $elems.length - 1 ) {
					liFocus += liPerView;

					if ( liFocus > $elems.length - 1 ) {
						liFocus = $elems.length - 1;
					}

					down( event );
				}
			}
			// Check for non input values defined by user
			else if ( settings.nonInput && $.inArray( key, settings.nonInput ) > -1 ) {
				$ul.html('').hide( event );
				enter = TRUE;
			}
			// Everything else is considered possible input, so
			// return before keyup prevention flag is set
			else {
				return TRUE;
			}

			// Prevent autoComplete keyup event's from triggering by
			// attaching a flag to the last event
			LastEvent[ 'keydown_' + ExpandoFlag ] = TRUE;
			return enter;
		})
		.bind({
			'keyup.autoComplete': function( event ) {
				// If autoComplete has been disabled or keyup prevention 
				// flag has be set, prevent input events
				if ( ! ACData.active || LastEvent[ 'keydown_' + ExpandoFlag ] ) {
					return TRUE;
				}

				// If no special operations were run on keydown,
				// allow for regular text searching
				inputval = $input.val();
				var key = ( LastEvent = event ).keyCode, val = separator ? inputval.split( separator ).pop() : inputval;

				// Still check to make sure 'enter' wasn't pressed
				if ( key != KEY.enter ) {

					// Caching key value
					cache.val = settings.inputControl === undefined ? val : 
						settings.inputControl.apply( self, settings.backwardsCompatible ? 
							[ val, key, $ul, event, settings, cache ] :
							[ event, {
								val: val,
								key: key,
								settings: settings,
								cache: cache,
								ul: $ul
							}]
						);

					// Only send request if character length passes
					if ( cache.val.length >= settings.minChars ) {
						sendRequest( event, settings, cache, ( key === KEY.backspace || key === KEY.space ) );
					}
					// Remove list on backspace of small string
					else if ( key == KEY.backspace ) {
						$ul.html('').hide( event );
					}
				}
			},

			'blur.autoComplete': function( event ) {
				// If autoComplete has been disabled or the drop list
				// is still open, prevent input events
				if ( ! ACData.active || ulOpen ) {
					return TRUE;
				}

				// Only push undefined index onto order stack
				// if not already there (in-case multiple blur events occur)
				if ( AutoComplete.order[0] !== undefined ) {
					AutoComplete.order.unshift( undefined );
				}

				// Expose focus
				AutoComplete.hasFocus = FALSE;
				ACData.hasFocus = FALSE;
				liFocus = -1;
				$ul.hide( LastEvent = event );

				// Trigger both the global and element specific blur events
				if ( AutoComplete.blur ) {
					AutoComplete.blur.call( self, event, { settings: settings, cache: cache, ul: $ul } );
				}

				if ( settings.onBlur ) {
					settings.onBlur.apply( self, settings.backwardsCompatible ?
						[ inputval, $ul, event, settings, cache ] : [ event, {
							val: inputval,
							settings: settings,
							cache: cache,
							ul: $ul
						}]
					);
				}
			},

			'focus.autoComplete': function( event, flag ) {
				// Prevent inner focus events if caused by autoComplete inner functionality
				// Also, because IE triggers focus AND closes the drop list before form submission,
				// keep the select flag by not reseting the last event
				if ( ! ACData.active || ( ACData.hasFocus && flag === ExpandoFlag ) || LastEvent[ 'enter_' + ExpandoFlag ] ) {
					return TRUE;
				}

				if ( inputIndex !== $ul.data( 'ac-input-index' ) ) {
					$ul.html('').hide( event );
				}

				// Overwrite undefined index pushed on by the blur event
				if ( AutoComplete.order[0] === undefined ) {
					if ( AutoComplete.order[1] === inputIndex ) {
						AutoComplete.order.shift();
					} else {
						AutoComplete.order[0] = inputIndex;
					}
				}
				else if ( AutoComplete.order[0] != inputIndex && AutoComplete.order[1] != inputIndex ) {
					AutoComplete.order.unshift( inputIndex );
				}

				if ( AutoComplete.defaults.cacheLimit !== -1 && AutoComplete.order.length > AutoComplete.defaults.cacheLimit ) {
					AutoComplete.order.pop();
				}

				// Expose focus
				AutoComplete.hasFocus = TRUE;
				ACData.hasFocus = TRUE;
				LastEvent = event;

				// Trigger both the global and element specific focus events
				if ( AutoComplete.focus ) {
					AutoComplete.focus.call( self, event, { settings: settings, cache: cache, ul: $ul } );
				}

				if ( settings.onFocus ) {
					settings.onFocus.apply( self, 
						settings.backwardsCompatible ? [ $ul, event, settings, cache ] : [ event, {
							settings: settings,
							cache: cache,
							ul: $ul
						}]
					);
				}
			},

			/**
			 * Autocomplete Custom Methods (Extensions off autoComplete event)
			 */ 
			// Catches document click events from the global scope
			'autoComplete.document-click': function( e, event ) {
				if ( ACData.active && ulOpen &&
					// Double check the event timestamps to ensure there isn't a delayed reaction from a button
					( ! LastEvent || event.timeStamp - LastEvent.timeStamp > 200 ) && 
					// Check the target after all other checks are passed (less processing)
					$( event.target ).closest( 'ul' ).data( 'ac-input-index' ) !== inputIndex ) {
						$ul.hide( LastEvent = event );
						$input.blur();
				}
			},

			// Catches form submission ( so only one event is attached to the form )
			'autoComplete.form-submit': function( e, event, form ) {
				if ( ! ACData.active ) {
					return TRUE;
				}

				LastEvent = event;

				// Because IE triggers focus AND closes the drop list before form submission,
				// tracking enter is set on the keydown event
				return settings.preventEnterSubmit && ( ulOpen || LastEvent[ 'enter_' + ExpandoFlag ] ) ? FALSE : 
					settings.onSubmit === undefined ? TRUE : 
					settings.onSubmit.call( self, event, { form: form, settings: settings, cache: cache, ul: $ul } );
			},

			// Catch mouseovers on the drop down element
			'autoComplete.ul-mouseenter': function( e, event, li ) {
				if ( $li ) {
					$li.removeClass( settings.rollover );
				}

				$li = $( li ).addClass( settings.rollover );
				liFocus = $elems.index( li );
				liData = currentList[ liFocus ];
				view = $ul.scrollTop() + ulHeight;
				LastEvent = event;

				if ( settings.onRollover ) {
					settings.onRollover.apply( self, settings.backwardsCompatible ? 
						[ liData, $li, $ul, event, settings, cache ] : 
						[ event, {
							data: liData,
							li: $li,
							settings: settings,
							cache: cache,
							ul: $ul
						}]
					);
				}
			},

			// Catch click events on the drop down
			'autoComplete.ul-click': function( e, event ) {
				// Refocus the input box and pass flag to prevent inner focus events
				$input.trigger( 'focus', [ ExpandoFlag ] );

				// Check against separator for input value
				$input.val( inputval === separator ? 
					inputval.substr( 0, inputval.length - inputval.split( separator ).pop().length ) + liData.value + separator :
					liData.value 
				);

				$ul.hide( LastEvent = event );
				autoFill();

				if ( settings.onSelect ) {
					settings.onSelect.apply( self, settings.backwardsCompatible ? 
						[ liData, $li, $ul, event, settings, cache ] :
						[ event, {
							data: liData,
							li: $li,
							settings: settings,
							cache: cache,
							ul: $ul
						}]
					);
				}
			},

			// Allow for change of settings at any point
			'autoComplete.settings': function( event, newSettings ) {
				if ( ! ACData.active ) {
					return TRUE;
				}

				var ret, $el;
				LastEvent = event;

				// Give access to current settings and cache
				if ( $.isFunction( newSettings ) ) {
					ret = newSettings.apply( self, settings.backwardsCompatible ? 
						[ settings, cache, $ul, event ] : [ event, { settings: settings, cache: cache, ul: $ul } ]
					);

					// Allow for extending of settings/cache based off function return values
					if ( $.isArray( ret ) && ret[0] !== undefined ) {
						$.extend( TRUE, settings, ret[0] || settings );
						$.extend( TRUE, cache, ret[1] || cache );
					}
				} else {
					$.extend( TRUE, settings, newSettings || {} );
				}

				// Change the drop down if dev want's a differen't class attached
				$ul = ! settings.newList && $ul.hasClass( settings.list ) ? $ul : 
					! settings.newList && ( $el = rootjQuery.find( 'ul.' + settings.list ).eq( 0 ) ).length ? 
						$el.bgiframe( settings.bgiframe ) :
						$('<ul/>').appendTo('body').addClass( settings.list )
							.bgiframe( settings.bgiframe ).hide().data( 'ac-selfmade', TRUE );

				// Custom drop list modifications
				newUl();

				// Change case here so it doesn't have to be done on every request
				settings.requestType = settings.requestType.toUpperCase();

				// Local copy of the seperator for faster referencing
				separator = settings.multiple ? settings.multipleSeparator : undefined;

				// Just to be sure, reset the settings object into the data storage
				ACData.settings = settings;
			},

			// Clears the Cache & requests (requests can be blocked from clearing)
			'autoComplete.flush': function( event, cacheOnly ) {
				if ( ! ACData.active ) {
					return TRUE;
				}
				
				if ( ! cacheOnly ) {
					requests = 0;
				}

				cache = { length: 0, val: undefined, list: {} };
				LastEvent = event;
			},

			// External button trigger for ajax requests
			'autoComplete.button-ajax': function( event, postData, cacheName ) {
				if ( ! ACData.active ) {
					return TRUE;
				}

				if ( typeof postData === 'string' ) {
					cacheName = postData;
					postData = {};
				}

				// Save off the last event before triggering focus on the off-chance
				// it is needed by a secondary focus event
				LastEvent = event;

				// Refocus the input box, but pass flag to prevent inner focus events
				$input.trigger( 'focus', [ ExpandoFlag ] );

				// If no cache name is given, supply a non-common word
				cache.val = cacheName || 'button-ajax_' + ExpandoFlag;

				return sendRequest(
					event, 
					$.extend( TRUE, {}, settings, { maxItems: -1, postData: postData || {} } ),
					cache
				);
			},

			// External button trigger for supplied data
			'autoComplete.button-supply': function( event, data, cacheName ) {
				if ( ! ACData.active ) {
					return TRUE;
				}

				if ( typeof data === 'string' ) {
					cacheName = data;
					data = undefined;
				}

				// Again, save off event before triggering focus
				LastEvent = event;

				// Refocus the input box and pass flag to prevent inner focus events
				$input.trigger( 'focus', [ ExpandoFlag ] );

				// If no cache name is given, supply a non-common word
				cache.val = cacheName || 'button-supply_' + ExpandoFlag;

				// If no data is supplied, use data in settings
				data = $.isArray( data ) ? data : settings.dataSupply;

				return sendRequest(
					event,
					$.extend( TRUE, {}, settings, { maxItems: -1, dataSupply: data, formatSupply: allSupply } ),
					cache
				);
			},

			// Supply list directly into the result function
			'autoComplete.direct-supply': function( event, data, cacheName ) {
				if ( ! ACData.active ) {
					return TRUE;
				}

				if ( typeof data === 'string' ) {
					cacheName = data;
					data = undefined;
				}

				// Again, save off event before triggering focus
				LastEvent = event;

				// Refocus the input box and pass flag to prevent inner focus events
				$input.trigger( 'focus', [ ExpandoFlag ] );

				// If no cache name is given, supply a non-common word
				cache.val = cacheName || 'direct-supply_' + ExpandoFlag;

				// If no data is supplied, use data in settings
				data = $.isArray( data ) && data.length ? data : settings.dataSupply;

				// Load the results directly into the results function bypassing request holdups
				return loadResults(
					event,
					data,
					$.extend( TRUE, {}, settings, { maxItems: -1, dataSupply: data, formatSupply: allSupply } ),
					cache
				);
			},

			// Triggering autocomplete programatically
			'autoComplete.search': function( event, value ) {
				if ( ! ACData.active ) {
					return TRUE;
				}

				cache.val = value || '';
				return sendRequest( LastEvent = event, settings, cache );
			},

			// Add jquery-ui like option access
			'autoComplete.option': function( event, name, value ) {
				if ( ! ACData.active ) {
					return TRUE;
				}

				LastEvent = event;
				switch ( Slice.call( arguments ).length ) {
					case 3: 
						settings[ name ] = value;
						return value;
					case 2:
						return name === 'ul' ? $ul :
							name === 'cache' ? cache :
							name === 'xhr' ? xhr :
							name === 'input' ? $input :
							settings[ name ] || undefined;
					default:
						return settings;
				}
			},

			// Add enabling event (only applicable after disable)
			'autoComplete.enable': function( event ) {
				ACData.active = TRUE;
				LastEvent = event;
			},

			// Add disable event
			'autoComplete.disable': function( event ) {
				ACData.active = FALSE;
				$ul.html('').hide( LastEvent = event );
			},

			// Add a destruction function
			'autoComplete.destroy': function( event ) {
				var list = $ul.html('').hide( LastEvent = event ).data( 'ac-inputs' ) || {}, i;

				// Remove all autoComplete specific data and events
				$input.removeData( 'autoComplete' ).unbind( '.autoComplete autoComplete' );

				// Remove form/drop list/document event catchers if possible
				teardown( $input, inputIndex );

				// Remove input from the drop down element of inputs
				list[ inputIndex ] = undefined;

				// Go through the drop down element and see if any other inputs are attached to it
				for ( i in list ) {
					if ( list.hasOwnProperty( i ) && list[ i ] === TRUE ) {
						return LastEvent;
					}
				}

				// Remove the element from the DOM if self created
				if ( $ul.data( 'ac-selfmade' ) === TRUE ) {
					$ul.remove();
				}
				// Kill all data associated with autoComplete for a cleaned drop down element
				else {
					$ul.removeData( 'autoComplete' ).removeData( 'ac-input-index' ).removeData( 'ac-inputs' );
				}
			}
		});

		// Ajax/Cache Request
		function sendRequest( event, settings, cache, backSpace, timeout ) {
			// Merely setting max requests still allows usage of cache and supplied data,
			// this 'Deep' option prevents those scenarios if needed
			if ( settings.maxRequestsDeep === true && requests >= settings.maxRequests ) {
				return FALSE;
			}

			if ( settings.spinner ) {
				settings.spinner.call( self, event, { active: TRUE, settings: settings, cache: cache, ul: $ul } );
			}

			if ( timeid ) {
				timeid = clearTimeout( timeid );
			}

			// Call send request again with timeout flag if on delay
			if ( settings.delay > 0 && timeout === undefined ) {
				timeid = window.setTimeout(function(){
					sendRequest( event, settings, cache, backSpace, TRUE );
				}, settings.delay );
				return timeid;
			}

			// Abort previous request incase it's still running
			if ( xhr ) {
				xhr.abort();
			}

			// Load from cache if possible
			if ( settings.useCache && $.isArray( cache.list[ cache.val ] ) ) {
				return loadResults( event, cache.list[ cache.val ], settings, cache, backSpace );
			}

			// Use user supplied data when defined
			if ( settings.dataSupply.length ) {
				return userSuppliedData( event, settings, cache, backSpace );
			}

			// Check Max requests first before sending request
			if ( settings.maxRequests && ++requests >= settings.maxRequests ) {
				$ul.html('').hide( event );

				if ( settings.spinner ) {
					settings.spinner.call( self, event, { active: FALSE, settings: settings, cache: cache, ul: $ul } );
				}

				if ( settings.onMaxRequest && requests === settings.maxRequests ) {
					return settings.onMaxRequest.apply( self, settings.backwardsCompatible ? 
						[ cache.val, $ul, event, inputval, settings, cache ] : 
						[ event, {
							search: cache.val,
							val: inputval,
							settings: settings,
							cache: cache,
							ul: $ul
						}]
					);
				}
				
				return FALSE;
			}

			settings.postData[ settings.postVar ] = cache.val;
			xhr = $.ajax({
				type: settings.requestType,
				url: settings.ajax,
				cache: settings.ajaxCache,
				dataType: 'json',

				// Send personalised data
				data: settings.postFormat ?
					settings.postFormat.call( self, event, {
						data: settings.postData,
						search: cache.val,
						val: inputval,
						settings: settings,
						cache: cache,
						ul: $ul
					}) :
					settings.postData,

				success: function( list ) {
					loadResults( event, list, settings, cache, backSpace );
				},

				error: function() {
					$ul.html('').hide( event );
					if ( settings.spinner ) {
						settings.spinner.call( self, event, { active: FALSE, settings: settings, cache: cache, ul: $ul } );
					}
				}
			});

			return xhr;
		}

		// Parse User Supplied Data
		function userSuppliedData( event, settings, cache, backSpace ) {
			var list = [], args = [],
				fn = $.isFunction( settings.dataFn ),
				regex = fn ? undefined : new RegExp( '^'+cache.val, 'i' ),
				items = 0, entry, i = -1, l = settings.dataSupply.length;

			if ( settings.formatSupply ) {
				list = settings.formatSupply.call( self, event, {
					search: cache.val,
					supply: settings.dataSupply,
					settings: settings,
					cache: cache,
					ul: $ul
				});
			} else {
				for ( ; ++i < l ; ) {
					// Force object wrapper for entry
					entry = settings.dataSupply[i];
					entry = entry && typeof entry.value === 'string' ? entry : { value: entry };

					// Setup arguments for dataFn in a backwards compatible way if needed
					args = settings.backwardsCompatible ? 
						[ cache.val, entry.value, list, i, settings.dataSupply, $ul, event, settings, cache ] :
						[ event, {
							search: cache.val,
							entry: entry.value,
							list: list,
							i: i,
							supply: settings.dataSupply,
							settings: settings,
							cache: cache,
							ul: $ul
						}];

					// If user supplied function, use that, otherwise test with default regex
					if ( ( fn && settings.dataFn.apply( self, args ) ) || ( ! fn && entry.value.match( regex ) ) ) {
						// Reduce browser load by breaking on limit if it exists
						if ( settings.maxItems > -1 && ++items > settings.maxItems ) {
							break;
						}
						list.push( entry );
					}
				}
			}

			// Use normal load functionality
			return loadResults( event, list, settings, cache, backSpace );
		}

		// Key element Selection
		function select( event ) {
			// Ensure the select function only gets fired when list of open
			if ( ulOpen ) {
				if ( settings.onSelect ) {
					settings.onSelect.apply( self, settings.backwardsCompatible ? 
						[ liData, $li, $ul, event, settings, cache ] :
						[ event, {
							data: liData,
							li: $li,
							settings: settings,
							cache: cache,
							ul: $ul
						}]
					);
				}

				autoFill();
				inputval = $input.val();

				// Because IE triggers focus AND closes the drop list before form submission
				// attach a flag on 'enter' selection
				if ( LastEvent.type === 'keydown' ) {
					LastEvent[ 'enter_' + ExpandoFlag ] = TRUE;
				}

				$ul.hide( event );
			}

			$li = undefined;
		}

		// Key direction up
		function up( event ) {
			if ( $li ) {
				$li.removeClass( settings.rollover );
			}

			$ul.show( event );
			$li = $elems.eq( liFocus ).addClass( settings.rollover );
			liData = currentList[ liFocus ];

			if ( ! $li.length || ! liData ) {
				return FALSE;
			}

			autoFill( liData.value );
			if ( settings.onRollover ) {
				settings.onRollover.apply( self, settings.backwardsCompatible ? 
					[ liData, $li, $ul, event, settings, cache ] :
					[ event, {
						data: liData,
						li: $li,
						settings: settings,
						cache: cache,
						ul: $ul
					}]
				);
			}

			// Scrolling
			var scroll = liFocus * liHeight;
			if ( scroll < view - ulHeight ) {
				view = scroll + ulHeight;
				$ul.scrollTop( scroll );
			}
		}

		// Key direction down
		function down( event ) {
			if ( $li ) {
				$li.removeClass( settings.rollover );
			}

			$ul.show( event );
			$li = $elems.eq( liFocus ).addClass( settings.rollover );
			liData = currentList[ liFocus ];

			if ( ! $li.length || ! liData ) {
				return FALSE;
			}

			autoFill( liData.value );

			// Scrolling
			var scroll = ( liFocus + 1 ) * liHeight;
			if ( scroll > view ) {
				$ul.scrollTop( ( view = scroll ) - ulHeight );
			}

			if ( settings.onRollover ) {
				settings.onRollover.apply( self, settings.backwardsCompatible ? 
					[ liData, $li, $ul, event, settings, cache ] : [ event, {
						data: liData,
						li: $li,
						settings: settings,
						cache: cache,
						ul: $ul
					}]
				);
			}
		}

		// Attach new show/hide functionality to only the ul object (so not to infect all of jQuery),
		// And also attach event handlers if not already done so
		function newUl() {
			var hide = $ul.hide, show = $ul.show, list = $ul.data( 'ac-inputs' ) || {};

			if ( ! $ul[ ExpandoFlag ] ) {
				$ul.hide = function( event, speed, callback ) {
					if ( settings.onHide && ulOpen ) {
						settings.onHide.call( self, event, { ul: $ul, settings: settings, cache: cache } );
					}

					ulOpen = FALSE;
					return hide.call( $ul, speed, callback );
				};

				$ul.show = function( event, speed, callback ) {
					if ( settings.onShow && ! ulOpen ) {
						settings.onShow.call( self, event, { ul: $ul, settings: settings, cache: cache } );
					}

					ulOpen = TRUE;
					return show.call( $ul, speed, callback );
				};

				// A flag must be attached to the $ul cached object
				$ul[ ExpandoFlag ] = TRUE;
			}

			// Attach global handlers for event delegation (So there is no more loss time in unbinding and rebinding)
			if ( $ul.data( 'autoComplete' ) !== TRUE ) {
				$ul.data( 'autoComplete', TRUE )
				.delegate( 'li', 'mouseenter.autoComplete', function( event ) {
					AutoComplete.getFocus( TRUE ).trigger( 'autoComplete.ul-mouseenter', [ event, this ] );
				})
				.bind( 'click.autoComplete', function( event ) {
					AutoComplete.getFocus( TRUE ).trigger( 'autoComplete.ul-click', [ event ] );
					return FALSE;
				});
			}

			list[ inputIndex ] = TRUE;
			$ul.data( 'ac-inputs', list );
		}

		// Auto-fill the input
		// Credit to Jörn Zaefferer @ http://bassistance.de/jquery-plugins/jquery-plugin-autocomplete/
		// and http://www.pengoworks.com/workshop/jquery/autocomplete.htm for this functionality
		function autoFill( val ) {
			var start, end, range;

			// Set starting and ending points based on values
			if ( val === undefined || val === '' ) {
				start = end = $input.val().length;
			} else {
				if ( separator ) {
					val = inputval.substr( 0, inputval.length - inputval.split( separator ).pop().length ) + val + separator;
				}

				start = inputval.length;
				end = val.length;
				$input.val( val );
			}

			// Create selection if allowed
			if ( ! settings.autoFill || start > end ) {
				return FALSE;
			}
			else if ( self.createTextRange ) {
				range = self.createTextRange();
				if ( val === undefined ) {
					range.move( 'character', start );
					range.select();
				} else {
					range.collapse( TRUE );
					range.moveStart( 'character', start );
					range.moveEnd( 'character', end );
					range.select();
				}
			}
			else if ( self.setSelectionRange ) {
				self.setSelectionRange( start, end );
			}
			else if ( self.selectionStart ) {
				self.selectionStart = start;
				self.selectionEnd = end;
			}
		}

		// List Functionality
		function loadResults( event, list, settings, cache, backSpace ) {
			// Allow another level of result handling
			currentList = settings.onLoad ?
				settings.onLoad.call( self, event, { list: list, settings: settings, cache: cache, ul: $ul } ) : list;

			// Tell spinner function to stop if set
			if ( settings.spinner ) {
				settings.spinner.call( self, event, { active: FALSE, settings: settings, cache: cache, ul: $ul } );
			}

			// Store results into the cache if allowed
			if ( settings.useCache && ! $.isArray( cache.list[ cache.val ] ) ) {
				cache.length++;
				cache.list[ cache.val ] = list;

				// Clear cache if necessary
				if ( settings.cacheLimit !== -1 && cache.length > settings.cacheLimit ) {
					cache.list = {};
					cache.length = 0;
				}
			}

			// Ensure there is a list
			if ( ! currentList || currentList.length < 1 ) {
				return $ul.html('').hide( event );
			}

			// Refocus list element
			liFocus = -1;

			// Initialize Vars together (save bytes)
			var offset = $input.offset(), // Input position
				container = [], // Container for list elements
				items = 0, i = -1, striped = FALSE, length = currentList.length; // Loop Items

			if ( settings.onListFormat ) {
				settings.onListFormat.call( self, event, { list: currentList, settings: settings, cache: cache, ul: $ul } );
			}
			else {
				// Push items onto container
				for ( ; ++i < length; ) {
					if ( currentList[i].value ) {
						if ( settings.maxItems > -1 && ++items > settings.maxItems ) {
							break;
						}

						container.push(
							settings.striped && striped ? '<li class="' + settings.striped + '">' : '<li>',
							currentList[i].display || currentList[i].value,
							'</li>'
						);

						striped = ! striped;
					}
				}
				$ul.html( container.join('') );
			}

			// Cache the list items
			$elems = $ul.children( 'li' );

			// Autofill input with first entry
			if ( settings.autoFill && ! backSpace ) {
				liFocus = 0;
				liData = currentList[ 0 ];
				autoFill( liData.value );
				$li = $elems.eq( 0 ).addClass( settings.rollover );
			}

			// Align the drop down element
			$ul.data( 'ac-input-index', inputIndex ).scrollTop( 0 ).css({
				top: offset.top + $input.outerHeight(),
				left: offset.left,
				width: settings.width
			})
			// The drop list has to be shown before maxHeight can be configured
			.show( event );

			// Log li height for less computation
			liHeight = $elems.eq( 0 ).outerHeight();

			// If Max Height specified, control it
			if ( settings.maxHeight ) {
				$ul.css({
					height: liHeight * $elems.length > settings.maxHeight ? settings.maxHeight : 'auto', 
					overflow: 'auto'
				});
			}

			// ulHeight gets manipulated, so assign to viewport seperately 
			// so referencing conflicts don't override viewport
			ulHeight = $ul.outerHeight();
			view = ulHeight;

			// Number of elements per viewport
			liPerView = liHeight === 0 ? 0 : Math.floor( view / liHeight );

			// Include amount of time it took to load the list
			// and run modifications
			LastEvent.timeStamp = ( new Date() ).getTime();
		}

		// Custom modifications to the drop down element
		newUl();

		// Do case change on initialization so it's not run on every request
		settings.requestType = settings.requestType.toUpperCase();

		// Local quick copy of the seperator (so we don't have to run this check every time)
		separator = settings.multiple ? settings.multipleSeparator : undefined;

		// Expose copies of both the input element and the cached jQuery version
		AutoComplete.stack[ inputIndex ] = self;
		AutoComplete.jqStack[ inputIndex ] = $input;

		// Form and Document event attachment
		setup( $input, inputIndex );
	};

})( jQuery, window || this );









/* fancybox */
(function($) {
	var tmp, loading, overlay, wrap, outer, content, close, title, nav_left, nav_right,

		selectedIndex = 0, selectedOpts = {}, selectedArray = [], currentIndex = 0, currentOpts = {}, currentArray = [],

		ajaxLoader = null, imgPreloader = new Image(), imgRegExp = /\.(jpg|gif|png|bmp|jpeg)(.*)?$/i, swfRegExp = /[^\.]\.(swf)\s*$/i,

		loadingTimer, loadingFrame = 1,

		titleHeight = 0, titleStr = '', start_pos, final_pos, busy = false, fx = $.extend($('<div/>')[0], { prop: 0 }),

		isIE6 = $.browser.msie && $.browser.version < 7 && !window.XMLHttpRequest,

		/*
		 * Private methods 
		 */

		_abort = function() {
			loading.hide();

			imgPreloader.onerror = imgPreloader.onload = null;

			if (ajaxLoader) {
				ajaxLoader.abort();
			}

			tmp.empty();
		},

		_error = function() {
			if (false === selectedOpts.onError(selectedArray, selectedIndex, selectedOpts)) {
				loading.hide();
				busy = false;
				return;
			}

			selectedOpts.titleShow = false;

			selectedOpts.width = 'auto';
			selectedOpts.height = 'auto';

			tmp.html( '<p id="fancybox-error">The requested content cannot be loaded.<br />Please try again later.</p>' );

			_process_inline();
		},

		_start = function() {
			var obj = selectedArray[ selectedIndex ],
				href, 
				type, 
				title,
				str,
				emb,
				ret;

			_abort();

			selectedOpts = $.extend({}, $.fn.fancybox.defaults, (typeof $(obj).data('fancybox') == 'undefined' ? selectedOpts : $(obj).data('fancybox')));

			ret = selectedOpts.onStart(selectedArray, selectedIndex, selectedOpts);

			if (ret === false) {
				busy = false;
				return;
			} else if (typeof ret == 'object') {
				selectedOpts = $.extend(selectedOpts, ret);
			}

			title = selectedOpts.title || (obj.nodeName ? $(obj).attr('title') : obj.title) || '';

			if (obj && obj.nodeName && !selectedOpts.orig) {
				selectedOpts.orig = $(obj).children("img:first").length ? $(obj).children("img:first") : $(obj);
			}

			if (title === '' && selectedOpts.orig && selectedOpts.titleFromAlt) {
				title = selectedOpts.orig.attr('alt');
			}

			href = selectedOpts.href || (obj.nodeName ? $(obj).attr('href') : obj.href) || null;

			if ((/^(?:javascript)/i).test(href) || href == '#') {
				href = null;
			}

			if (selectedOpts.type) {
				type = selectedOpts.type;

				if (!href) {
					href = selectedOpts.content;
				}

			} else if (selectedOpts.content) {
				type = 'html';

			} else if (href) {
				if (href.match(imgRegExp)) {
					type = 'image';

				} else if (href.match(swfRegExp)) {
					type = 'swf';

				} else if ($(obj).hasClass("iframe")) {
					type = 'iframe';

				} else if (href.indexOf("#") === 0) {
					type = 'inline';

				} else {
					type = 'ajax';
				}
			}

			if (!type) {
				_error();
				return;
			}

			if (type == 'inline') {
				obj	= href.substr(href.indexOf("#"));
				type = $(obj).length > 0 ? 'inline' : 'ajax';
			}

			selectedOpts.type = type;
			selectedOpts.href = href;
			selectedOpts.title = title;

			if (selectedOpts.autoDimensions) {
				if (selectedOpts.type == 'html' || selectedOpts.type == 'inline' || selectedOpts.type == 'ajax') {
					selectedOpts.width = 'auto';
					selectedOpts.height = 'auto';
				} else {
					selectedOpts.autoDimensions = false;	
				}
			}

			if (selectedOpts.modal) {
				selectedOpts.overlayShow = true;
				selectedOpts.hideOnOverlayClick = false;
				selectedOpts.hideOnContentClick = false;
				selectedOpts.enableEscapeButton = false;
				selectedOpts.showCloseButton = false;
			}

			selectedOpts.padding = parseInt(selectedOpts.padding, 10);
			selectedOpts.margin = parseInt(selectedOpts.margin, 10);

			tmp.css('padding', (selectedOpts.padding + selectedOpts.margin));

			$('.fancybox-inline-tmp').unbind('fancybox-cancel').bind('fancybox-change', function() {
				$(this).replaceWith(content.children());				
			});

			switch (type) {
				case 'html' :
					tmp.html( selectedOpts.content );
					_process_inline();
				break;

				case 'inline' :
					if ( $(obj).parent().is('#fancybox-content') === true) {
						busy = false;
						return;
					}

					$('<div class="fancybox-inline-tmp" />')
						.hide()
						.insertBefore( $(obj) )
						.bind('fancybox-cleanup', function() {
							$(this).replaceWith(content.children());
						}).bind('fancybox-cancel', function() {
							$(this).replaceWith(tmp.children());
						});

					$(obj).appendTo(tmp);

					_process_inline();
				break;

				case 'image':
					busy = false;

					$.fancybox.showActivity();

					imgPreloader = new Image();

					imgPreloader.onerror = function() {
						_error();
					};

					imgPreloader.onload = function() {
						busy = true;

						imgPreloader.onerror = imgPreloader.onload = null;

						_process_image();
					};

					imgPreloader.src = href;
				break;

				case 'swf':
					selectedOpts.scrolling = 'no';

					str = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="' + selectedOpts.width + '" height="' + selectedOpts.height + '"><param name="movie" value="' + href + '"></param>';
					emb = '';

					$.each(selectedOpts.swf, function(name, val) {
						str += '<param name="' + name + '" value="' + val + '"></param>';
						emb += ' ' + name + '="' + val + '"';
					});

					str += '<embed src="' + href + '" type="application/x-shockwave-flash" width="' + selectedOpts.width + '" height="' + selectedOpts.height + '"' + emb + '></embed></object>';

					tmp.html(str);

					_process_inline();
				break;

				case 'ajax':
					busy = false;

					$.fancybox.showActivity();

					selectedOpts.ajax.win = selectedOpts.ajax.success;

					if(href.indexOf('?') > -1) {
						href += '&RSP=ajax';
					} else {
						href += '?RSP=ajax';
					}
					
					ajaxLoader = $.ajax($.extend({}, selectedOpts.ajax, {
						url	: href,
						data : selectedOpts.ajax.data || {},
						error : function(XMLHttpRequest, textStatus, errorThrown) {
							if ( XMLHttpRequest.status > 0 ) {
								_error();
							}
						},
						success : function(data, textStatus, XMLHttpRequest) {
							var o = typeof XMLHttpRequest == 'object' ? XMLHttpRequest : ajaxLoader;
							if (o.status == 200) {
								if ( typeof selectedOpts.ajax.win == 'function' ) {
									ret = selectedOpts.ajax.win(href, data, textStatus, XMLHttpRequest);

									if (ret === false) {
										loading.hide();
										return;
									} else if (typeof ret == 'string' || typeof ret == 'object') {
										data = ret;
									}
								}
								
								if(_isJson(data) && Pins && Pins.messagesJson) {
									var evalute = jQuery.parseJSON(data);
									Pins.messagesJson(evalute);
								} else {
									tmp.html( data );
									_process_inline();
								}
							}
						}
					}));

				break;

				case 'iframe':
					_show();
				break;
			}
		},
		
		_isJson = function(str) {
		 if (jQuery.trim(str) == '') return false;
		 str = str.replace(/\\./g, '@').replace(/"[^"\\\n\r]*"/g, '');
		 return (/^[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]*$/).test(str);
		},

		_process_inline = function() {
			var
				w = selectedOpts.width,
				h = selectedOpts.height;

			if (w.toString().indexOf('%') > -1) {
				w = parseInt( ($(window).width() - (selectedOpts.margin * 2)) * parseFloat(w) / 100, 10) + 'px';

			} else {
				w = w == 'auto' ? 'auto' : w + 'px';	
			}

			if (h.toString().indexOf('%') > -1) {
				h = parseInt( ($(window).height() - (selectedOpts.margin * 2)) * parseFloat(h) / 100, 10) + 'px';

			} else {
				h = h == 'auto' ? 'auto' : h + 'px';	
			}

			tmp.wrapInner('<div style="width:' + w + ';height:' + h + ';overflow: ' + (selectedOpts.scrolling == 'auto' ? 'auto' : (selectedOpts.scrolling == 'yes' ? 'scroll' : 'hidden')) + ';position:relative;"></div>');

			selectedOpts.width = tmp.width();
			selectedOpts.height = tmp.height();

			_show();
		},

		_process_image = function() {
			selectedOpts.width = imgPreloader.width;
			selectedOpts.height = imgPreloader.height;

			$("<img />").attr({
				'id' : 'fancybox-img',
				'src' : imgPreloader.src,
				'alt' : selectedOpts.title
			}).appendTo( tmp );

			_show();
		},

		_show = function() {
			var pos, equal;

			loading.hide();

			if (wrap.is(":visible") && false === currentOpts.onCleanup(currentArray, currentIndex, currentOpts)) {
				$.event.trigger('fancybox-cancel');

				busy = false;
				return;
			}

			busy = true;

			$(content.add( overlay )).unbind();

			$(window).unbind("resize.fb scroll.fb");
			$(document).unbind('keydown.fb');

			if (wrap.is(":visible") && currentOpts.titlePosition !== 'outside') {
				wrap.css('height', wrap.height());
			}

			currentArray = selectedArray;
			currentIndex = selectedIndex;
			currentOpts = selectedOpts;

			if (currentOpts.overlayShow) {
				overlay.css({
					'background-color' : currentOpts.overlayColor,
					'opacity' : currentOpts.overlayOpacity,
					'cursor' : currentOpts.hideOnOverlayClick ? 'pointer' : 'auto',
					'height' : $(document).height()
				});

				if (!overlay.is(':visible')) {
					if (isIE6) {
						$('select:not(#fancybox-tmp select)').filter(function() {
							return this.style.visibility !== 'hidden';
						}).css({'visibility' : 'hidden'}).one('fancybox-cleanup', function() {
							this.style.visibility = 'inherit';
						});
					}

					overlay.show();
				}
			} else {
				overlay.hide();
			}

			final_pos = _get_zoom_to();

			_process_title();

			if (wrap.is(":visible")) {
				$( close.add( nav_left ).add( nav_right ) ).hide();

				pos = wrap.position(),

				start_pos = {
					top	 : pos.top,
					left : pos.left,
					width : wrap.width(),
					height : wrap.height()
				};

				equal = (start_pos.width == final_pos.width && start_pos.height == final_pos.height);

				content.fadeTo(currentOpts.changeFade, 0.3, function() {
					var finish_resizing = function() {
						content.html( tmp.contents() ).fadeTo(currentOpts.changeFade, 1, _finish);
					};

					$.event.trigger('fancybox-change');

					content
						.empty()
						.removeAttr('filter')
						.css({
							'border-width' : currentOpts.padding,
							'width'	: final_pos.width - currentOpts.padding * 2,
							'height' : selectedOpts.autoDimensions ? 'auto' : final_pos.height - titleHeight - currentOpts.padding * 2
						});

					if (equal) {
						finish_resizing();

					} else {
						fx.prop = 0;

						$(fx).animate({prop: 1}, {
							 duration : currentOpts.changeSpeed,
							 easing : currentOpts.easingChange,
							 step : _draw,
							 complete : finish_resizing
						});
					}
				});

				return;
			}

			wrap.removeAttr("style");

			content.css('border-width', currentOpts.padding);

			if (currentOpts.transitionIn == 'elastic') {
				start_pos = _get_zoom_from();

				content.html( tmp.contents() );

				wrap.show();

				if (currentOpts.opacity) {
					final_pos.opacity = 0;
				}

				fx.prop = 0;

				$(fx).animate({prop: 1}, {
					 duration : currentOpts.speedIn,
					 easing : currentOpts.easingIn,
					 step : _draw,
					 complete : _finish
				});

				return;
			}

			if (currentOpts.titlePosition == 'inside' && titleHeight > 0) {	
				title.show();	
			}

			content
				.css({
					'width' : final_pos.width - currentOpts.padding * 2,
					'height' : selectedOpts.autoDimensions ? 'auto' : final_pos.height - titleHeight - currentOpts.padding * 2
				})
				.html( tmp.contents() );

			wrap
				.css(final_pos)
				.fadeIn( currentOpts.transitionIn == 'none' ? 0 : currentOpts.speedIn, _finish );
		},

		_format_title = function(title) {
			if (title && title.length) {
				if (currentOpts.titlePosition == 'float') {
					return '<table id="fancybox-title-float-wrap" cellpadding="0" cellspacing="0"><tr><td id="fancybox-title-float-left"></td><td id="fancybox-title-float-main">' + title + '</td><td id="fancybox-title-float-right"></td></tr></table>';
				}

				return '<div id="fancybox-title-' + currentOpts.titlePosition + '">' + title + '</div>';
			}

			return false;
		},

		_process_title = function() {
			titleStr = currentOpts.title || '';
			titleHeight = 0;

			title
				.empty()
				.removeAttr('style')
				.removeClass();

			if (currentOpts.titleShow === false) {
				title.hide();
				return;
			}

			titleStr = $.isFunction(currentOpts.titleFormat) ? currentOpts.titleFormat(titleStr, currentArray, currentIndex, currentOpts) : _format_title(titleStr);

			if (!titleStr || titleStr === '') {
				title.hide();
				return;
			}

			title
				.addClass('fancybox-title-' + currentOpts.titlePosition)
				.html( titleStr )
				.appendTo( 'body' )
				.show();

			switch (currentOpts.titlePosition) {
				case 'inside':
					title
						.css({
							'width' : final_pos.width - (currentOpts.padding * 2),
							'marginLeft' : currentOpts.padding,
							'marginRight' : currentOpts.padding
						});

					titleHeight = title.outerHeight(true);

					title.appendTo( outer );

					final_pos.height += titleHeight;
				break;

				case 'over':
					title
						.css({
							'marginLeft' : currentOpts.padding,
							'width'	: final_pos.width - (currentOpts.padding * 2),
							'bottom' : currentOpts.padding
						})
						.appendTo( outer );
				break;

				case 'float':
					title
						.css('left', parseInt((title.width() - final_pos.width - 40)/ 2, 10) * -1)
						.appendTo( wrap );
				break;

				default:
					title
						.css({
							'width' : final_pos.width - (currentOpts.padding * 2),
							'paddingLeft' : currentOpts.padding,
							'paddingRight' : currentOpts.padding
						})
						.appendTo( wrap );
				break;
			}

			title.hide();
		},

		_set_navigation = function() {
			if (currentOpts.enableEscapeButton || currentOpts.enableKeyboardNav) {
				$(document).bind('keydown.fb', function(e) {
					if (e.keyCode == 27 && currentOpts.enableEscapeButton) {
						e.preventDefault();
						$.fancybox.close();

					} else if ((e.keyCode == 37 || e.keyCode == 39) && currentOpts.enableKeyboardNav && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA' && e.target.tagName !== 'SELECT') {
						e.preventDefault();
						$.fancybox[ e.keyCode == 37 ? 'prev' : 'next']();
					}
				});
			}

			if (!currentOpts.showNavArrows) { 
				nav_left.hide();
				nav_right.hide();
				return;
			}

			if ((currentOpts.cyclic && currentArray.length > 1) || currentIndex !== 0) {
				nav_left.show();
			}

			if ((currentOpts.cyclic && currentArray.length > 1) || currentIndex != (currentArray.length -1)) {
				nav_right.show();
			}
		},

		_finish = function () {
			if (!$.support.opacity) {
				content.get(0).style.removeAttribute('filter');
				wrap.get(0).style.removeAttribute('filter');
			}

			if (selectedOpts.autoDimensions) {
				content.css('height', 'auto');
			}

			wrap.css('height', 'auto');

			if (titleStr && titleStr.length) {
				title.show();
			}

			if (currentOpts.showCloseButton) {
				close.show();
			}

			_set_navigation();
	
			if (currentOpts.hideOnContentClick)	{
				content.bind('click', $.fancybox.close);
			}

			if (currentOpts.hideOnOverlayClick)	{
				overlay.bind('click', $.fancybox.close);
			}

			$(window).bind("resize.fb", $.fancybox.resize);

			if (currentOpts.centerOnScroll) {
				$(window).bind("scroll.fb", $.fancybox.center);
			}

			if (currentOpts.type == 'iframe') {
				$('<iframe id="fancybox-frame" name="fancybox-frame' + new Date().getTime() + '" frameborder="0" hspace="0" ' + ($.browser.msie ? 'allowtransparency="true""' : '') + ' scrolling="' + selectedOpts.scrolling + '" src="' + currentOpts.href + '"></iframe>').appendTo(content);
			}

			wrap.show();

			busy = false;

			$.fancybox.center();

			currentOpts.onComplete(currentArray, currentIndex, currentOpts);

			_preload_images();
		},

		_preload_images = function() {
			var href, 
				objNext;

			if ((currentArray.length -1) > currentIndex) {
				href = currentArray[ currentIndex + 1 ].href;

				if (typeof href !== 'undefined' && href.match(imgRegExp)) {
					objNext = new Image();
					objNext.src = href;
				}
			}

			if (currentIndex > 0) {
				href = currentArray[ currentIndex - 1 ].href;

				if (typeof href !== 'undefined' && href.match(imgRegExp)) {
					objNext = new Image();
					objNext.src = href;
				}
			}
		},

		_draw = function(pos) {
			var dim = {
				width : parseInt(start_pos.width + (final_pos.width - start_pos.width) * pos, 10),
				height : parseInt(start_pos.height + (final_pos.height - start_pos.height) * pos, 10),

				top : parseInt(start_pos.top + (final_pos.top - start_pos.top) * pos, 10),
				left : parseInt(start_pos.left + (final_pos.left - start_pos.left) * pos, 10)
			};

			if (typeof final_pos.opacity !== 'undefined') {
				dim.opacity = pos < 0.5 ? 0.5 : pos;
			}

			wrap.css(dim);

			content.css({
				'width' : dim.width - currentOpts.padding * 2,
				'height' : dim.height - (titleHeight * pos) - currentOpts.padding * 2
			});
		},

		_get_viewport = function() {
			return [
				$(window).width() - (currentOpts.margin * 2),
				$(window).height() - (currentOpts.margin * 2),
				$(document).scrollLeft() + currentOpts.margin,
				$(document).scrollTop() + currentOpts.margin
			];
		},

		_get_zoom_to = function () {
			var view = _get_viewport(),
				to = {},
				resize = currentOpts.autoScale,
				double_padding = currentOpts.padding * 2,
				ratio;

			if (currentOpts.width.toString().indexOf('%') > -1) {
				to.width = parseInt((view[0] * parseFloat(currentOpts.width)) / 100, 10);
			} else {
				to.width = currentOpts.width + double_padding;
			}

			if (currentOpts.height.toString().indexOf('%') > -1) {
				to.height = parseInt((view[1] * parseFloat(currentOpts.height)) / 100, 10);
			} else {
				to.height = currentOpts.height + double_padding;
			}

			if (resize && (to.width > view[0] || to.height > view[1])) {
				if (selectedOpts.type == 'image' || selectedOpts.type == 'swf') {
					ratio = (currentOpts.width ) / (currentOpts.height );

					if ((to.width ) > view[0]) {
						to.width = view[0];
						to.height = parseInt(((to.width - double_padding) / ratio) + double_padding, 10);
					}

					if ((to.height) > view[1]) {
						to.height = view[1];
						to.width = parseInt(((to.height - double_padding) * ratio) + double_padding, 10);
					}

				} else {
					to.width = Math.min(to.width, view[0]);
					to.height = Math.min(to.height, view[1]);
				}
			}

			to.top = parseInt(Math.max(view[3] - 20, view[3] + ((view[1] - to.height - 40) * 0.5)), 10);
			to.left = parseInt(Math.max(view[2] - 20, view[2] + ((view[0] - to.width - 40) * 0.5)), 10);

			return to;
		},

		_get_obj_pos = function(obj) {
			var pos = obj.offset();

			pos.top += parseInt( obj.css('paddingTop'), 10 ) || 0;
			pos.left += parseInt( obj.css('paddingLeft'), 10 ) || 0;

			pos.top += parseInt( obj.css('border-top-width'), 10 ) || 0;
			pos.left += parseInt( obj.css('border-left-width'), 10 ) || 0;

			pos.width = obj.width();
			pos.height = obj.height();

			return pos;
		},

		_get_zoom_from = function() {
			var orig = selectedOpts.orig ? $(selectedOpts.orig) : false,
				from = {},
				pos,
				view;

			if (orig && orig.length) {
				pos = _get_obj_pos(orig);

				from = {
					width : pos.width + (currentOpts.padding * 2),
					height : pos.height + (currentOpts.padding * 2),
					top	: pos.top - currentOpts.padding - 20,
					left : pos.left - currentOpts.padding - 20
				};

			} else {
				view = _get_viewport();

				from = {
					width : currentOpts.padding * 2,
					height : currentOpts.padding * 2,
					top	: parseInt(view[3] + view[1] * 0.5, 10),
					left : parseInt(view[2] + view[0] * 0.5, 10)
				};
			}

			return from;
		},

		_animate_loading = function() {
			if (!loading.is(':visible')){
				clearInterval(loadingTimer);
				return;
			}

			$('div', loading).css('top', (loadingFrame * -40) + 'px');

			loadingFrame = (loadingFrame + 1) % 12;
		};

	/*
	 * Public methods 
	 */

	$.fn.fancybox = function(options) {
		if (!$(this).length) {
			return this;
		}

		$(this)
			.data('fancybox', $.extend({}, options, ($.metadata ? $(this).metadata() : {})))
			.unbind('click.fb')
			.bind('click.fb', function(e) {
				e.preventDefault();

				if (busy) {
					return;
				}

				busy = true;

				$(this).blur();

				selectedArray = [];
				selectedIndex = 0;

				var rel = $(this).attr('rel') || '';

				if (!rel || rel == '' || rel === 'nofollow') {
					selectedArray.push(this);

				} else {
					selectedArray = $("a[rel=" + rel + "], area[rel=" + rel + "]");
					selectedIndex = selectedArray.index( this );
				}

				_start();

				return;
			});

		return this;
	};

	$.fancybox = function(obj) {
		var opts;

		if (busy) {
			return;
		}

		busy = true;
		opts = typeof arguments[1] !== 'undefined' ? arguments[1] : {};

		selectedArray = [];
		selectedIndex = parseInt(opts.index, 10) || 0;

		if ($.isArray(obj)) {
			for (var i = 0, j = obj.length; i < j; i++) {
				if (typeof obj[i] == 'object') {
					$(obj[i]).data('fancybox', $.extend({}, opts, obj[i]));
				} else {
					obj[i] = $({}).data('fancybox', $.extend({content : obj[i]}, opts));
				}
			}

			selectedArray = jQuery.merge(selectedArray, obj);

		} else {
			if (typeof obj == 'object') {
				$(obj).data('fancybox', $.extend({}, opts, obj));
			} else {
				obj = $({}).data('fancybox', $.extend({content : obj}, opts));
			}

			selectedArray.push(obj);
		}

		if (selectedIndex > selectedArray.length || selectedIndex < 0) {
			selectedIndex = 0;
		}

		_start();
	};

	$.fancybox.showActivity = function() {
		clearInterval(loadingTimer);

		loading.show();
		loadingTimer = setInterval(_animate_loading, 66);
	};

	$.fancybox.hideActivity = function() {
		loading.hide();
	};

	$.fancybox.next = function() {
		return $.fancybox.pos( currentIndex + 1);
	};

	$.fancybox.prev = function() {
		return $.fancybox.pos( currentIndex - 1);
	};

	$.fancybox.pos = function(pos) {
		if (busy) {
			return;
		}

		pos = parseInt(pos);

		selectedArray = currentArray;

		if (pos > -1 && pos < currentArray.length) {
			selectedIndex = pos;
			_start();

		} else if (currentOpts.cyclic && currentArray.length > 1) {
			selectedIndex = pos >= currentArray.length ? 0 : currentArray.length - 1;
			_start();
		}

		return;
	};

	$.fancybox.cancel = function() {
		if (busy) {
			return;
		}

		busy = true;

		$.event.trigger('fancybox-cancel');

		_abort();

		selectedOpts.onCancel(selectedArray, selectedIndex, selectedOpts);

		busy = false;
	};

	// Note: within an iframe use - parent.$.fancybox.close();
	$.fancybox.close = function() {
		if (busy || wrap.is(':hidden')) {
			return;
		}

		busy = true;

		if (currentOpts && false === currentOpts.onCleanup(currentArray, currentIndex, currentOpts)) {
			busy = false;
			return;
		}

		_abort();

		$(close.add( nav_left ).add( nav_right )).hide();

		$(content.add( overlay )).unbind();

		$(window).unbind("resize.fb scroll.fb");
		$(document).unbind('keydown.fb');

		content.find('iframe').attr('src', isIE6 && /^https/i.test(window.location.href || '') ? 'javascript:void(false)' : 'about:blank');

		if (currentOpts.titlePosition !== 'inside') {
			title.empty();
		}

		wrap.stop();

		function _cleanup() {
			overlay.fadeOut('fast');

			title.empty().hide();
			wrap.hide();

			$.event.trigger('fancybox-cleanup');

			content.empty();

			currentOpts.onClosed(currentArray, currentIndex, currentOpts);

			currentArray = selectedOpts	= [];
			currentIndex = selectedIndex = 0;
			currentOpts = selectedOpts	= {};

			busy = false;
		}

		if (currentOpts.transitionOut == 'elastic') {
			start_pos = _get_zoom_from();

			var pos = wrap.position();

			final_pos = {
				top	 : pos.top ,
				left : pos.left,
				width :	wrap.width(),
				height : wrap.height()
			};

			if (currentOpts.opacity) {
				final_pos.opacity = 1;
			}

			title.empty().hide();

			fx.prop = 1;

			$(fx).animate({ prop: 0 }, {
				 duration : currentOpts.speedOut,
				 easing : currentOpts.easingOut,
				 step : _draw,
				 complete : _cleanup
			});

		} else {
			wrap.fadeOut( currentOpts.transitionOut == 'none' ? 0 : currentOpts.speedOut, _cleanup);
		}
	};

	$.fancybox.resize = function() {
		if (overlay.is(':visible')) {
			overlay.css('height', $(document).height());
		}

		$.fancybox.center(true);
	};

	$.fancybox.center = function() {
		var view, align;

		if (busy) {
			return;	
		}

		align = arguments[0] === true ? 1 : 0;
		view = _get_viewport();

		if (!align && (wrap.width() > view[0] || wrap.height() > view[1])) {
			return;	
		}

		wrap
			.stop()
			.animate({
				'top' : parseInt(Math.max(view[3] - 20, view[3] + ((view[1] - content.height() - 40) * 0.5) - currentOpts.padding)),
				'left' : parseInt(Math.max(view[2] - 20, view[2] + ((view[0] - content.width() - 40) * 0.5) - currentOpts.padding))
			}, typeof arguments[0] == 'number' ? arguments[0] : 200);
	};

	$.fancybox.init = function() {
		if ($("#fancybox-wrap").length) {
			return;
		}

		$('body').append(
			tmp	= $('<div id="fancybox-tmp"></div>'),
			loading	= $('<div id="fancybox-loading"><div></div></div>'),
			overlay	= $('<div id="fancybox-overlay"></div>'),
			wrap = $('<div id="fancybox-wrap"></div>')
		);

		outer = $('<div id="fancybox-outer"></div>')
			.append('<div class="fancybox-bg" id="fancybox-bg-n"></div><div class="fancybox-bg" id="fancybox-bg-ne"></div><div class="fancybox-bg" id="fancybox-bg-e"></div><div class="fancybox-bg" id="fancybox-bg-se"></div><div class="fancybox-bg" id="fancybox-bg-s"></div><div class="fancybox-bg" id="fancybox-bg-sw"></div><div class="fancybox-bg" id="fancybox-bg-w"></div><div class="fancybox-bg" id="fancybox-bg-nw"></div>')
			.appendTo( wrap );

		outer.append(
			content = $('<div id="fancybox-content"></div>'),
			close = $('<a id="fancybox-close"></a>'),
			title = $('<div id="fancybox-title"></div>'),

			nav_left = $('<a href="javascript:;" id="fancybox-left"><span class="fancy-ico" id="fancybox-left-ico"></span></a>'),
			nav_right = $('<a href="javascript:;" id="fancybox-right"><span class="fancy-ico" id="fancybox-right-ico"></span></a>')
		);

		close.click($.fancybox.close);
		loading.click($.fancybox.cancel);

		nav_left.click(function(e) {
			e.preventDefault();
			$.fancybox.prev();
		});

		nav_right.click(function(e) {
			e.preventDefault();
			$.fancybox.next();
		});

		if ($.fn.mousewheel) {
			wrap.bind('mousewheel.fb', function(e, delta) {
				if (busy) {
					e.preventDefault();

				} else if ($(e.target).get(0).clientHeight == 0 || $(e.target).get(0).scrollHeight === $(e.target).get(0).clientHeight) {
					e.preventDefault();
					$.fancybox[ delta > 0 ? 'prev' : 'next']();
				}
			});
		}

		if (!$.support.opacity) {
			wrap.addClass('fancybox-ie');
		}

		if (isIE6) {
			loading.addClass('fancybox-ie6');
			wrap.addClass('fancybox-ie6');

			$('<iframe id="fancybox-hide-sel-frame" src="' + (/^https/i.test(window.location.href || '') ? 'javascript:void(false)' : 'about:blank' ) + '" scrolling="no" border="0" frameborder="0" tabindex="-1"></iframe>').prependTo(outer);
		}
	};

	$.fn.fancybox.defaults = {
		padding : 10,
		margin : 40,
		opacity : false,
		modal : false,
		cyclic : false,
		scrolling : 'auto',	// 'auto', 'yes' or 'no'

		width : 560,
		height : 340,

		autoScale : true,
		autoDimensions : true,
		centerOnScroll : false,

		ajax : {},
		swf : { wmode: 'transparent' },

		hideOnOverlayClick : true,
		hideOnContentClick : false,

		overlayShow : true,
		overlayOpacity : 0.7,
		overlayColor : '#777',

		titleShow : true,
		titlePosition : 'float', // 'float', 'outside', 'inside' or 'over'
		titleFormat : null,
		titleFromAlt : false,

		transitionIn : 'fade', // 'elastic', 'fade' or 'none'
		transitionOut : 'fade', // 'elastic', 'fade' or 'none'

		speedIn : 300,
		speedOut : 300,

		changeSpeed : 300,
		changeFade : 'fast',

		easingIn : 'swing',
		easingOut : 'swing',

		showCloseButton	 : true,
		showNavArrows : true,
		enableEscapeButton : true,
		enableKeyboardNav : true,

		onStart : function(){},
		onCancel : function(){},
		onComplete : function(){},
		onCleanup : function(){},
		onClosed : function(){},
		onError : function(){}
	};

	$(document).ready(function() {
		$.fancybox.init();
	});

})(jQuery);

/* jo LazyLoad */
(function($){
	$.joQueue = {
	    _timer: null,
	    _joQueue: [],
	    add: function(fn, context, time) {
	        var setTimer = function(time) {
	            $.joQueue._timer = setTimeout(function() {
	                time = $.joQueue.add();
	                if ($.joQueue._joQueue.length) {
	                    setTimer(time);
	                }
	            }, time || 2);
	        };

	        if (fn) {
	            $.joQueue._joQueue.push([fn, context, time]);
	            if ($.joQueue._joQueue.length == 1) {
	                setTimer(time);
	            }
	            return;
	        };

	        var next = $.joQueue._joQueue.shift();
	        if (!next) {
	            return 0;
	        };
	        next[0].call(next[1] || window);
	        return next[2];
	    },
	    clear: function() {
	        clearTimeout($.joQueue._timer);
	        $.joQueue._joQueue = [];
	    }
	};
	
	$.fn.LazyLoad = function() {
		
		loadImage = function(el, src) {
			if(!src) return;
			var image = new Image();
			image.src = src;
			image.onload = function() {
				el.src = image.src;
			};
			image.onerror = function() {
				if(!el.src_test) { el.src_test = 0; }
				el.src_test++;
				if(el.src_test < 10) {
					loadImage(el, src);
				}
			}
		};
		
		return this.each(function(i, item){
			$.joQueue.add(function () { loadImage(this, $(this).data('original'), 1); }, this);
		});
	}
})(jQuery);

/* jQuery Easing */
eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('h.i[\'1a\']=h.i[\'z\'];h.O(h.i,{y:\'D\',z:9(x,t,b,c,d){6 h.i[h.i.y](x,t,b,c,d)},17:9(x,t,b,c,d){6 c*(t/=d)*t+b},D:9(x,t,b,c,d){6-c*(t/=d)*(t-2)+b},13:9(x,t,b,c,d){e((t/=d/2)<1)6 c/2*t*t+b;6-c/2*((--t)*(t-2)-1)+b},X:9(x,t,b,c,d){6 c*(t/=d)*t*t+b},U:9(x,t,b,c,d){6 c*((t=t/d-1)*t*t+1)+b},R:9(x,t,b,c,d){e((t/=d/2)<1)6 c/2*t*t*t+b;6 c/2*((t-=2)*t*t+2)+b},N:9(x,t,b,c,d){6 c*(t/=d)*t*t*t+b},M:9(x,t,b,c,d){6-c*((t=t/d-1)*t*t*t-1)+b},L:9(x,t,b,c,d){e((t/=d/2)<1)6 c/2*t*t*t*t+b;6-c/2*((t-=2)*t*t*t-2)+b},K:9(x,t,b,c,d){6 c*(t/=d)*t*t*t*t+b},J:9(x,t,b,c,d){6 c*((t=t/d-1)*t*t*t*t+1)+b},I:9(x,t,b,c,d){e((t/=d/2)<1)6 c/2*t*t*t*t*t+b;6 c/2*((t-=2)*t*t*t*t+2)+b},G:9(x,t,b,c,d){6-c*8.C(t/d*(8.g/2))+c+b},15:9(x,t,b,c,d){6 c*8.n(t/d*(8.g/2))+b},12:9(x,t,b,c,d){6-c/2*(8.C(8.g*t/d)-1)+b},Z:9(x,t,b,c,d){6(t==0)?b:c*8.j(2,10*(t/d-1))+b},Y:9(x,t,b,c,d){6(t==d)?b+c:c*(-8.j(2,-10*t/d)+1)+b},W:9(x,t,b,c,d){e(t==0)6 b;e(t==d)6 b+c;e((t/=d/2)<1)6 c/2*8.j(2,10*(t-1))+b;6 c/2*(-8.j(2,-10*--t)+2)+b},V:9(x,t,b,c,d){6-c*(8.o(1-(t/=d)*t)-1)+b},S:9(x,t,b,c,d){6 c*8.o(1-(t=t/d-1)*t)+b},Q:9(x,t,b,c,d){e((t/=d/2)<1)6-c/2*(8.o(1-t*t)-1)+b;6 c/2*(8.o(1-(t-=2)*t)+1)+b},P:9(x,t,b,c,d){f s=1.l;f p=0;f a=c;e(t==0)6 b;e((t/=d)==1)6 b+c;e(!p)p=d*.3;e(a<8.w(c)){a=c;f s=p/4}m f s=p/(2*8.g)*8.r(c/a);6-(a*8.j(2,10*(t-=1))*8.n((t*d-s)*(2*8.g)/p))+b},H:9(x,t,b,c,d){f s=1.l;f p=0;f a=c;e(t==0)6 b;e((t/=d)==1)6 b+c;e(!p)p=d*.3;e(a<8.w(c)){a=c;f s=p/4}m f s=p/(2*8.g)*8.r(c/a);6 a*8.j(2,-10*t)*8.n((t*d-s)*(2*8.g)/p)+c+b},T:9(x,t,b,c,d){f s=1.l;f p=0;f a=c;e(t==0)6 b;e((t/=d/2)==2)6 b+c;e(!p)p=d*(.3*1.5);e(a<8.w(c)){a=c;f s=p/4}m f s=p/(2*8.g)*8.r(c/a);e(t<1)6-.5*(a*8.j(2,10*(t-=1))*8.n((t*d-s)*(2*8.g)/p))+b;6 a*8.j(2,-10*(t-=1))*8.n((t*d-s)*(2*8.g)/p)*.5+c+b},F:9(x,t,b,c,d,s){e(s==u)s=1.l;6 c*(t/=d)*t*((s+1)*t-s)+b},E:9(x,t,b,c,d,s){e(s==u)s=1.l;6 c*((t=t/d-1)*t*((s+1)*t+s)+1)+b},16:9(x,t,b,c,d,s){e(s==u)s=1.l;e((t/=d/2)<1)6 c/2*(t*t*(((s*=(1.B))+1)*t-s))+b;6 c/2*((t-=2)*t*(((s*=(1.B))+1)*t+s)+2)+b},A:9(x,t,b,c,d){6 c-h.i.v(x,d-t,0,c,d)+b},v:9(x,t,b,c,d){e((t/=d)<(1/2.k)){6 c*(7.q*t*t)+b}m e(t<(2/2.k)){6 c*(7.q*(t-=(1.5/2.k))*t+.k)+b}m e(t<(2.5/2.k)){6 c*(7.q*(t-=(2.14/2.k))*t+.11)+b}m{6 c*(7.q*(t-=(2.18/2.k))*t+.19)+b}},1b:9(x,t,b,c,d){e(t<d/2)6 h.i.A(x,t*2,0,c,d)*.5+b;6 h.i.v(x,t*2-d,0,c,d)*.5+c*.5+b}});',62,74,'||||||return||Math|function|||||if|var|PI|jQuery|easing|pow|75|70158|else|sin|sqrt||5625|asin|||undefined|easeOutBounce|abs||def|swing|easeInBounce|525|cos|easeOutQuad|easeOutBack|easeInBack|easeInSine|easeOutElastic|easeInOutQuint|easeOutQuint|easeInQuint|easeInOutQuart|easeOutQuart|easeInQuart|extend|easeInElastic|easeInOutCirc|easeInOutCubic|easeOutCirc|easeInOutElastic|easeOutCubic|easeInCirc|easeInOutExpo|easeInCubic|easeOutExpo|easeInExpo||9375|easeInOutSine|easeInOutQuad|25|easeOutSine|easeInOutBack|easeInQuad|625|984375|jswing|easeInOutBounce'.split('|'),0,{}));

/* jquery mousewheel */
(function(d){function e(a){var b=a||window.event,c=[].slice.call(arguments,1),f=0,e=0,g=0,a=d.event.fix(b);a.type="mousewheel";b.wheelDelta&&(f=b.wheelDelta/120);b.detail&&(f=-b.detail/3);g=f;b.axis!==void 0&&b.axis===b.HORIZONTAL_AXIS&&(g=0,e=-1*f);b.wheelDeltaY!==void 0&&(g=b.wheelDeltaY/120);b.wheelDeltaX!==void 0&&(e=-1*b.wheelDeltaX/120);c.unshift(a,f,e,g);return(d.event.dispatch||d.event.handle).apply(this,c)}var c=["DOMMouseScroll","mousewheel"];if(d.event.fixHooks)for(var h=c.length;h;)d.event.fixHooks[c[--h]]=
d.event.mouseHooks;d.event.special.mousewheel={setup:function(){if(this.addEventListener)for(var a=c.length;a;)this.addEventListener(c[--a],e,false);else this.onmousewheel=e},teardown:function(){if(this.removeEventListener)for(var a=c.length;a;)this.removeEventListener(c[--a],e,false);else this.onmousewheel=null}};d.fn.extend({mousewheel:function(a){return a?this.bind("mousewheel",a):this.trigger("mousewheel")},unmousewheel:function(a){return this.unbind("mousewheel",a)}})})(jQuery);

/* jquery scroll */
(function($){
	$.fn.easyscroll = function() {
		return $(this).unbind('click').click(function(event){
			
			event.preventDefault();
			
			parts = this.href.split("#");
			if(!parts || !parts[1]) {
				return;
			}
			var trgt = parts[1];
			
			if($(trgt).size() > 0) {
				var target_offset = $( trgt ).offset();
			} else if($('#' + trgt).size() > 0) {
				var target_offset = $( '#' + trgt ).offset();
			} else if($('.' + trgt).size() > 0) {
				var target_offset = $( '.' + trgt ).offset();
			} else {
				return;
			}
			
			$('html, body').animate({scrollTop:target_offset.top}, 500);
			
		});
	}
})(jQuery);

/* jquery masonry */
try
{
    (function(a,b,c){
        "use strict";
        var d=b.event,e;
        d.special.smartresize={
            setup:function(){
                b(this).bind("resize",d.special.smartresize.handler)
            }
            ,teardown:function(){
                b(this).unbind("resize",d.special.smartresize.handler)
            }
            ,handler:function(a,b){
                var c=this,d=arguments;a.type="smartresize",e&&clearTimeout(e),e=setTimeout(function(){
                    jQuery.event.handle.apply(c,d)},b==="execAsap"?0:100)
            }
        },b.fn.smartresize=function(a){
            return a?this.bind("smartresize",a):this.trigger("smartresize",["execAsap"])
        },b.Mason=function(a,c){
            this.element=b(c),this._create(a),this._init()
        },b.Mason.settings={
            isResizable:!0,isAnimated:!1,animationOptions:{
                queue:!1,duration:500
            },gutterWidth:0,isRTL:!1,isFitWidth:!1,containerStyle:{
                position:"relative"
            }
        },b.Mason.prototype={
            _filterFindBricks:function(a){
                var b=this.options.itemSelector;return b?a.filter(b).add(a.find(b)):a
            },_getBricks:function(a){
                var b=this._filterFindBricks(a).css({
                    position:"absolute"
                }).addClass("masonry-brick");
                return b
            },_create:function(c){
                this.options=b.extend(!0,{},b.Mason.settings,c),this.styleQueue=[];
                var d=this.element[0].style;
                this.originalStyle={
                    height:d.height||""
                };
                var e=this.options.containerStyle;
                for(var f in e)
                    this.originalStyle[f]=d[f]||"";
                this.element.css(e),this.horizontalDirection=this.options.isRTL?"right":"left",this.offset={
                    x:parseInt(this.element.css("padding-"+this.horizontalDirection),10),y:parseInt(this.element.css("padding-top"),10)
                },this.isFluid=this.options.columnWidth&&typeof this.options.columnWidth=="function";
                var g=this;setTimeout(function(){
                    g.element.addClass("masonry")
                },0),this.options.isResizable&&b(a).bind("smartresize.masonry",function(){
                    g.resize()
                }),this.reloadItems()},
            _init:function(a){
                this._getColumns(),this._reLayout(a)
            },option:function(a,c){
                b.isPlainObject(a)&&(this.options=b.extend(!0,this.options,a))
            },layout:function(a,b){
                for(var c=0,d=a.length;c<d;c++)
                    this._placeBrick(a[c]);
                var e={};
                e.height=Math.max.apply(Math,this.colYs);
                if(this.options.isFitWidth){
                    var f=0;
                    c=this.cols;
                    while(--c){
                        if(this.colYs[c]!==0)break;
                        f++
                    }
                    e.width=(this.cols-f)*this.columnWidth-this.options.gutterWidth
                }
                this.styleQueue.push({
                    $el:this.element,style:e
                });
                var g=this.isLaidOut?this.options.isAnimated?"animate":"css":"css",h=this.options.animationOptions,i;
                for(c=0,d=this.styleQueue.length;c<d;c++)
                    i=this.styleQueue[c],i.$el[g](i.style,h);
                this.styleQueue=[],b&&b.call(a),this.isLaidOut=!0},
            _getColumns:function(){
                var a=this.options.isFitWidth?this.element.parent():this.element,b=a.width();
                this.columnWidth=this.isFluid?this.options.columnWidth(b):this.options.columnWidth||this.$bricks.outerWidth(!0)||b,this.columnWidth+=this.options.gutterWidth,this.cols=Math.floor((b+this.options.gutterWidth)/this.columnWidth),this.cols=Math.max(this.cols,1)
            }
            ,_placeBrick:function(a){
                var c=b(a),d,e,f,g,h;d=Math.ceil(c.outerWidth(!0)/(this.columnWidth+this.options.gutterWidth)),d=Math.min(d,this.cols);
                if(d===1)
                    f=this.colYs;
                else{
                    e=this.cols+1-d,f=[];
                    for(h=0;h<e;h++)g=this.colYs.slice(h,h+d),f[h]=Math.max.apply(Math,g)}var i=Math.min.apply(Math,f),j=0;for(var k=0,l=f.length;k<l;k++)if(f[k]===i){
                    j=k;break
                }
                var m={top:i+this.offset.y};
                m[this.horizontalDirection]=this.columnWidth*j+this.offset.x,this.styleQueue.push({
                    $el:c,style:m
                });
                var n=i+c.outerHeight(!0),o=this.cols+1-l;
                for(k=0;k<o;k++)
                    this.colYs[j+k]=n
            }
            ,resize:function(){
                var a=this.cols;
                this._getColumns(),(this.isFluid||this.cols!==a)&&this._reLayout()
            }
            ,_reLayout:function(a){
                var b=this.cols;
                this.colYs=[];
                while(b--)
                    this.colYs.push(0);
                this.layout(this.$bricks,a)
            },reloadItems:function(){
                this.$bricks=this._getBricks(this.element.children())
            },reload:function(a){
                this.reloadItems(),this._init(a)
            },appended:function(a,b,c){if(b){
                    this._filterFindBricks(a).css({
                        top:this.element.height()
                    });
                    var d=this;setTimeout(function(){
                        d._appended(a,c)},1)
                }else 
                    this._appended(a,c)
            },_appended:function(a,b){
                var c=this._getBricks(a);
                this.$bricks=this.$bricks.add(c),this.layout(c,b)
            },remove:function(a){
                this.$bricks=this.$bricks.not(a),a.remove()
            },destroy:function(){
                this.$bricks.removeClass("masonry-brick").each(function(){
                    this.style.position="",this.style.top="",this.style.left=""
                });
                var c=this.element[0].style;
                for(var d in this.originalStyle)
                    c[d]=this.originalStyle[d];
                this.element.unbind(".masonry").removeClass("masonry").removeData("masonry"),b(a).unbind(".masonry")
            }
        },b.fn.imagesLoaded=function(a){
            function i(a){
                var c=a.target;
                c.src!==f&&b.inArray(c,g)===-1&&(g.push(c),--e<=0&&(setTimeout(h),d.unbind(".imagesLoaded",i)))
            }function h(){
                a.call(c,d)}var c=this,d=c.find("img").add(c.filter("img")),e=d.length,f="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==",g=[];
            e||h(),d.bind("load.imagesLoaded error.imagesLoaded",i).each(function(){
                var a=this.src;this.src=f,this.src=a});
            return c
        };
        var f=function(b){
            a.console&&a.console.error(b)
        };
        b.fn.masonry=function(a){
            if(typeof a=="string"){
                var c=Array.prototype.slice.call(arguments,1);
                this.each(function(){
                    var d=b.data(this,"masonry");
                    if(!d)
                        f("cannot call methods on masonry prior to initialization; attempted to call method '"+a+"'");
                    else{
                        if(!b.isFunction(d[a])||a.charAt(0)==="_"){
                            f("no such method '"+a+"' for masonry instance")
                            ;return
                        }d[a].apply(d,c)
                    }
                })
            }else this.each(function(){
                var c=b.data(this,"masonry");
                c?(c.option(a||{}),c._init()):b.data(this,"masonry",new b.Mason(a,this))
            });
            return this}
    })(window,jQuery);
}
catch(e)
{
    if (window.console // check for window.console not console
         && window.console.log)
         {
            (function(a,b,c){"use strict";var d=b.event,e;d.special.smartresize={setup:function(){b(this).bind("resize",d.special.smartresize.handler)},teardown:function(){b(this).unbind("resize",d.special.smartresize.handler)},handler:function(a,b){var c=this,d=arguments;a.type="smartresize",e&&clearTimeout(e),e=setTimeout(function(){jQuery.event.handle.apply(c,d)},b==="execAsap"?0:100)}},b.fn.smartresize=function(a){return a?this.bind("smartresize",a):this.trigger("smartresize",["execAsap"])},b.Mason=function(a,c){this.element=b(c),this._create(a),this._init()},b.Mason.settings={isResizable:!0,isAnimated:!1,animationOptions:{queue:!1,duration:500},gutterWidth:0,isRTL:!1,isFitWidth:!1,containerStyle:{position:"relative"}},b.Mason.prototype={_filterFindBricks:function(a){var b=this.options.itemSelector;return b?a.filter(b).add(a.find(b)):a},_getBricks:function(a){var b=this._filterFindBricks(a).css({position:"absolute"}).addClass("masonry-brick");return b},_create:function(c){this.options=b.extend(!0,{},b.Mason.settings,c),this.styleQueue=[];var d=this.element[0].style;this.originalStyle={height:d.height||""};var e=this.options.containerStyle;for(var f in e)this.originalStyle[f]=d[f]||"";this.element.css(e),this.horizontalDirection=this.options.isRTL?"right":"left",this.offset={x:parseInt(this.element.css("padding-"+this.horizontalDirection),10),y:parseInt(this.element.css("padding-top"),10)},this.isFluid=this.options.columnWidth&&typeof this.options.columnWidth=="function";var g=this;setTimeout(function(){g.element.addClass("masonry")},0),this.options.isResizable&&b(a).bind("smartresize.masonry",function(){g.resize()}),this.reloadItems()},_init:function(a){this._getColumns(),this._reLayout(a)},option:function(a,c){b.isPlainObject(a)&&(this.options=b.extend(!0,this.options,a))},layout:function(a,b){for(var c=0,d=a.length;c<d;c++)this._placeBrick(a[c]);var e={};e.height=Math.max.apply(Math,this.colYs);if(this.options.isFitWidth){var f=0;c=this.cols;while(--c){if(this.colYs[c]!==0)break;f++}e.width=(this.cols-f)*this.columnWidth-this.options.gutterWidth}this.styleQueue.push({$el:this.element,style:e});var g=this.isLaidOut?this.options.isAnimated?"animate":"css":"css",h=this.options.animationOptions,i;for(c=0,d=this.styleQueue.length;c<d;c++)i=this.styleQueue[c],i.$el[g](i.style,h);this.styleQueue=[],b&&b.call(a),this.isLaidOut=!0},_getColumns:function(){var a=this.options.isFitWidth?this.element.parent():this.element,b=a.width();this.columnWidth=this.isFluid?this.options.columnWidth(b):this.options.columnWidth||this.$bricks.outerWidth(!0)||b,this.columnWidth+=this.options.gutterWidth,this.cols=Math.floor((b+this.options.gutterWidth)/this.columnWidth),this.cols=Math.max(this.cols,1)},_placeBrick:function(a){var c=b(a),d,e,f,g,h;d=Math.ceil(c.outerWidth(!0)/(this.columnWidth+this.options.gutterWidth)),d=Math.min(d,this.cols);if(d===1)f=this.colYs;else{e=this.cols+1-d,f=[];for(h=0;h<e;h++)g=this.colYs.slice(h,h+d),f[h]=Math.max.apply(Math,g)}var i=Math.min.apply(Math,f),j=0;for(var k=0,l=f.length;k<l;k++)if(f[k]===i){j=k;break}var m={top:i+this.offset.y};m[this.horizontalDirection]=this.columnWidth*j+this.offset.x,this.styleQueue.push({$el:c,style:m});var n=i+c.outerHeight(!0),o=this.cols+1-l;for(k=0;k<o;k++)this.colYs[j+k]=n},resize:function(){var a=this.cols;this._getColumns(),(this.isFluid||this.cols!==a)&&this._reLayout()},_reLayout:function(a){var b=this.cols;this.colYs=[];while(b--)this.colYs.push(0);this.layout(this.$bricks,a)},reloadItems:function(){this.$bricks=this._getBricks(this.element.children())},reload:function(a){this.reloadItems(),this._init(a)},appended:function(a,b,c){if(b){this._filterFindBricks(a).css({top:this.element.height()});var d=this;setTimeout(function(){d._appended(a,c)},1)}else this._appended(a,c)},_appended:function(a,b){var c=this._getBricks(a);this.$bricks=this.$bricks.add(c),this.layout(c,b)},remove:function(a){this.$bricks=this.$bricks.not(a),a.remove()},destroy:function(){this.$bricks.removeClass("masonry-brick").each(function(){this.style.position="",this.style.top="",this.style.left=""});var c=this.element[0].style;for(var d in this.originalStyle)c[d]=this.originalStyle[d];this.element.unbind(".masonry").removeClass("masonry").removeData("masonry"),b(a).unbind(".masonry")}},b.fn.imagesLoaded=function(a){function i(a){var c=a.target;c.src!==f&&b.inArray(c,g)===-1&&(g.push(c),--e<=0&&(setTimeout(h),d.unbind(".imagesLoaded",i)))}function h(){a.call(c,d)}var c=this,d=c.find("img").add(c.filter("img")),e=d.length,f="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==",g=[];e||h(),d.bind("load.imagesLoaded error.imagesLoaded",i).each(function(){var a=this.src;this.src=f,this.src=a});return c};var f=function(b){a.window.console&&a.window.console.error(b)};b.fn.masonry=function(a){if(typeof a=="string"){var c=Array.prototype.slice.call(arguments,1);this.each(function(){var d=b.data(this,"masonry");if(!d)f("cannot call methods on masonry prior to initialization; attempted to call method '"+a+"'");else{if(!b.isFunction(d[a])||a.charAt(0)==="_"){f("no such method '"+a+"' for masonry instance");return}d[a].apply(d,c)}})}else this.each(function(){var c=b.data(this,"masonry");c?(c.option(a||{}),c._init()):b.data(this,"masonry",new b.Mason(a,this))});return this}})(window,jQuery);        
         }
}

/* add masonry container */
$(window).load(function(){
	
	/*onComplete = function() {
        var containerwidth = $("#container").width(); 
		alert(containerwidth);
		$("#topwrapper, #menuwrapper, #category").css({'width' : containerwidth + 'px'});
    };*/
	
	$('#container').masonry({
		itemSelector : '.box',
		columnWidth : 135,
		isAnimated: false,
		isFitWidth: true,
		isResizable: true,
		gutterWidth: 12,
		animate: true,
		animationOptions: {
			duration: 500,
			queue: false/*,
			complete: onComplete*/
		}

	});
	
	var containerwidth = $("#container").width();  
	var max_size = Math.min( Math.max(320, ($(window).width()-10) ), 1170 );
	if(containerwidth < max_size) {
		containerwidth = max_size;
	}
	/*alert(containerwidth);
	$("#topwrapper, #menuwrapper, #category").css({'width' : containerwidth + 'px'});*/

});

/* add silverbox */
$(document).ready(function(){
	$(".silverbox").fancybox({
		'overlayOpacity': 0.85,
		'overlayColor'	: '#fff',
		'scrolling'		: 'no',
		'titlePosition' : 'over',
		'autoDimensions': true,
		'margin'		: 0,
		'padding'		: 0,
		'transitionIn'	: 'none',
		'transitionOut'	: 'none',
		'centerOnScroll': true,
	    'onComplete' : function(){
	    	fancyboxSilverOnComplete();
	    	$('#container').infinitescroll('pause');
	    }, 
	    'onClosed': function(){
	    	$('#container').infinitescroll('resume');
	    }
	}).contextmenu();
});

function fancyboxSilverOnComplete(selector) {
	selector = selector || "#fancybox-content .silverbox";
	$(selector).fancybox({
		'overlayOpacity': 0.85,
		'overlayColor'	: '#fff',
		'scrolling'		: 'no',
		'titlePosition' : 'over',
		'autoDimensions': true,
		'margin'		: 0,
		'padding'		: 0,
		'transitionIn'	: 'none',
		'transitionOut'	: 'none',
		'centerOnScroll': true,
	    'onComplete' : function(){
	    	fancyboxSilverOnComplete();
	    	$('#container').infinitescroll('pause');
	    }, 
	    'onClosed': function(){
	    	$('#container').infinitescroll('resume');
	    }
	}).contextmenu();
}



/* add silverboxMessage */
$(document).ready(function(){
	$(".silverboxMessage").fancybox({
		'overlayOpacity': 0.85,
		'overlayColor'	: '#fff',
		'scrolling'		: 'no',
		'titlePosition' : 'over',
		'autoDimensions': true,
		'margin'		: 0,
		'padding'		: 0,
		'transitionIn'	: 'none',
		'transitionOut'	: 'none',
		'centerOnScroll': true,
	    'onComplete' : function(){
	    	fancyboxSilverOnComplete();
	    	$('#container').infinitescroll('pause');
	    }, 
	    'onClosed': function(){
	    	$('#container').infinitescroll('resume');
                //$('#ProfileHeader .infoMessage').('reload');
                window.location.reload();
	    }
            
	}).contextmenu();
});

function fancyboxSilverMessOnComplete(selector) {
	selector = selector || "#fancybox-content .silverboxMessage";
	$(selector).fancybox({
		'overlayOpacity': 0.85,
		'overlayColor'	: '#fff',
		'scrolling'		: 'no',
		'titlePosition' : 'over',
		'autoDimensions': true,
		'margin'		: 0,
		'padding'		: 0,
		'transitionIn'	: 'none',
		'transitionOut'	: 'none',
		'centerOnScroll': true,
	    'onComplete' : function(){
	    	fancyboxSilverOnComplete();
	    	$('#container').infinitescroll('pause');
	    }, 
	    'onClosed': function(){
	    	$('#container').infinitescroll('resume');
                window.location.reload();
	    }

	}).contextmenu();
}

/* add whitebox */
function fancybox_wrap_mousewheel() {
	$("#fancybox-wrap").bind("mousewheel",function(ev, delta) {
		var scrollTop = $(".pin-overlay").scrollTop();
		$(".pin-overlay").scrollTop(scrollTop - Math.round(delta * 100));
	}); 
}

$(window).load(function(){
	
	$(".whitebox").fancybox({
	
		'overlayOpacity': 0.85,
		'overlayColor'	: '#fff',
		'scrolling'		: 'no',
		'autoDimensions': true,
		'margin'		: 0,
		'padding'		: 0,
		'transitionIn'	: 'none',
		'transitionOut'	: 'none',
		'titleShow'		: false,
		'showCloseButton': false,
		'onStart' : function(){
		    $("#fancybox-wrap").wrap('<div class="pin-overlay" />');
		    $("body").addClass('noscroll');
		    $(".scrolltotop").hide();
		    $("#fancybox-wrap").addClass('wrapTop');
		    $("#fancybox-outer").css('padding-top','0');
		    if ($.browser.msie  && parseInt($.browser.version, 10) === 7) {
		     $("#fancybox-wrap #buttons").css({"position":"absolute"});
		    }
		},
        'onCleanup': function(){
		    $("#fancybox-wrap").unwrap('<div class="pin-overlay" />');
		    $("body").removeClass('noscroll');
		    $(".scrolltotop").show();
		    $("#fancybox-wrap").removeClass('wrapTop');
		    $("#fancybox-outer").css('padding-top','56px');
		    $("#buttonswrapper-popup").hide();
        },
        'onComplete' : function(){
        	fancyboxWhiteOnComplete();
        	$(".pin-overlay").click(function(event) {
    	     if (event.target.className == "pin-overlay") {
    	      $.fancybox.close();
     	     return false;
    	     }
    	    });
    	    fancybox_wrap_mousewheel();
        }
	
	});
	
});

function fancyboxWhiteOnComplete() {
	$("#fancybox-content .whitebox").fancybox({
		'overlayOpacity': 0.85,
		'overlayColor'	: '#fff',
		'scrolling'		: 'no',
		'autoDimensions': true,
		'margin'		: 0,
		'padding'		: 0,
		'transitionIn'	: 'none',
		'transitionOut'	: 'none',
		'titleShow'		: false,
		'showCloseButton': false,
		'onStart' : function(){
		    $("#fancybox-wrap").wrap('<div class="pin-overlay" />');
		    $("body").addClass('noscroll');
		    $(".scrolltotop").hide();
		    $("#fancybox-wrap").addClass('wrapTop');
		    $("#fancybox-outer").css('padding-top','0');
		    if ($.browser.msie  && parseInt($.browser.version, 10) === 7) {
		     $("#fancybox-wrap #buttons").css({"position":"absolute"});
		    }
		},
	    'onCleanup': function(){
		    $("#fancybox-wrap").unwrap('<div class="pin-overlay" />');
		    $("body").removeClass('noscroll');
		    $(".scrolltotop").show();
		    $("#fancybox-wrap").removeClass('wrapTop');
		    $("#fancybox-outer").css('padding-top','56px');
		    $("#buttonswrapper-popup").hide();
	    },
	    'onComplete' : function(){
	    	fancyboxWhiteOnComplete();
	    	$(".pin-overlay").click(function(event) {
		     if (event.target.className == "pin-overlay") {
		      $.fancybox.close();
			     return false;
		     }
		    });
		    fancybox_wrap_mousewheel();
	    }
	});
}

/* price jo */
var regExPrice = {
	expresions: {
		/*price_left: /(\$|\£|\€|\¥|\₪|zł|\฿)([\s]{0,2})?(?:(?:\d{1,5}(?:\,\d{3})+)|(?:\d+))(?:\.\d{2})?/*/
	},
	checkRegex: function(str){
		for(i in this.expresions) {
			if(mymatch = this.expresions[i].exec(str)) {
				return mymatch;
			}
		}
		return false;
	},
	addExpresionLine: function(str, load) {
		mymatch = regExPrice.checkRegex(str);
		if(mymatch) {
			var h = $('.imageholder .price').html(mymatch[0]);
			if(load) { h.css('display','block'); } else { h.fadeIn(100); }
			$('input[name=price]').val(mymatch[0]);
		} else {
			$('.imageholder .price').fadeOut(100).html('');
			$('input[name=price]').val('');
		}
	}
};

/* carousel jo */
(function($){
	$.fn.imageCarousel = function(config){

		var config = $.extend({}, {
			onScrollEnd: null,
			onInit: null,
			duration: 200,
			textNext: 'Next',
			textPrev: 'Prev'
		}, config);
		
		return this.each(function(){

			var $holder = $(this);

			var currentPage = 1;
			
			if($('ul',this).size() < 1) {
				return;
			}

			var $contaner = $('ul:first',this);

			var pages = $contaner.children();
			var width = $holder.width();

			$contaner
			.css({position: "relative", padding: "0", margin: "0", listStyle: "none", width: pages.length * width})
			.find('li').css({width: width});

			var next = $('<a>').click(function(){
				if (currentPage === pages.length) {
					return;
			    }
			    var new_width = -1 * width * currentPage;
			    $contaner.animate({ left: new_width}, config.duration, function(){
				    currentPage++;
					if($.isFunction(config.onScrollEnd)) {
						config.onScrollEnd.call(this, $(pages).get(currentPage-1), (currentPage-1), pages.length, $contaner);
					}
				});
				return false;
			}).html(config.textNext).addClass('image-carousel-next');
			var prev = $('<a>').click(function(){
				if (currentPage === 1) {
					return;
			    }
			    var new_width = -1 * width * (currentPage - 1);
			    $contaner.animate({ left: -1 * width * (currentPage - 2)}, config.duration, function(){
				    currentPage--;
					if($.isFunction(config.onScrollEnd)) {
						config.onScrollEnd.call(this, $(pages).get(currentPage-1), (currentPage-1), pages.length, $contaner);
					}
				});
				return false;
			}).html(config.textPrev).addClass('image-carousel-prev');
			
			if(pages.length > 1) {
				$holder.append($('<div class="image-carousel-navigation">').append(prev).append(next));
			}

			if($.isFunction(config.onInit)) {
				setTimeout(function(){
					config.onInit.call(this, $(pages).get(currentPage-1), (currentPage-1), pages.length, $contaner);
				}, 10)
			}
			
		});
	}
})(jQuery);

/* pins jo */
var Pins = {
	init: function(){
		Pins.initLazyLoad('#pin .box .photo img, #container .thumb img, .box .info .view .photo img, .box .apps .list li img');		
		Pins.showIcons();
		Pins.initBoxClick();
		Pins.initComments();
		Pins.initLike();
		Pins.initRepins();
		Pins.showIcons('.pin', '.photo');
		Pins.initFollow('#category p.follow a');
		Pins.initFollow('.box .apps .follow a');
		Pins.initFollow('.box .WhiteButton');
		Pins.initBoxComments();
		Pins.initBoxCommentsActions();
		Pins.initBoxCommentsActions('.box .comments ul li a.delete');
		Pins.statHide();
	},
	url: window.location.href,
	currentPage: 1,
	marker: '',
	statHide: function(selector) {
		$(selector).hide();
		selector = selector || '.box .preview .stats';
		$(selector).each(function(){
			if( $.trim($('.likes', this).html()) || $.trim($('.comments', this).html()) || $.trim($('.repins', this).html()) ) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
	},
	initMessage: function($class, $title, $text) {
		$('.message-box').remove();
		$text = $text || '';
		var box = $('<div class="message-box">').addClass('message-box-'+$class).css('display', 'none');
		box.append($('<h3>').html($title));
		if($text) {
			box.append($('<p>').html($text));
		}
		$('body').append(box);
		box.slideDown();
		box.click(hideBox);
		setTimeout(hideBox, 15000);
		function hideBox() {
			box.slideUp(function(){
				box.remove();
			});
		}
	},
	messagesJson: function(data){
		if(data.error) { 
			if(typeof data.error == 'object') {
				res = '';
				for(i in data.error) {
					res += i+': '+data.error[i]+'<br />';
				}
			} else {
				res = data.error;
			}
			if(res) {
				Pins.error(res);
			}
		}
	},
	error: function(text){
		Pins.initMessage('error',text);
	},
	success: function(text){
		Pins.initMessage('success',text);
	},
	notify: function(text){
		Pins.initMessage('info',text);
	},
	warning: function(text){
		Pins.initMessage('warning',text);
	},
	initRepins: function(){
		fancyboxSilverOnComplete('.box .icons .silverbox');
	},
	initBoxCommentsActions: function(selector){
		selector = selector || '.box .info .view .commentslist ul li a.delete';
		$(selector).unbind('click').click(function(){
			var element = $(this);
			
			var href = element.attr('href');
			if(href.indexOf('?') > -1) {
				href += '&RSP=ajax';
			} else {
				href += '?RSP=ajax';
			}
			
			$.post(href, function(data){
				if(data.ok) {
					if(data.stats.stats) {
						for(i in data.stats.stats) {
							$('.' + i, element.parents('.box').find('.stats')).html(data.stats.stats[i]);
						}
					}
					Pins.statHide(element.parents('.box').find('.stats'));
					element.parents('li').slideUp(function(){
						$(this).remove(); 
						setTimeout(function(){ $('#container').masonry('reload'); }, 200);
					});
				} else if(data.error) {
					Pins.error(data.error);
				} else {
					Pins.error(data);
				}
			}, 'json');
			return false;
		});
	},
	initFollow: function(selector, callback) {
		$(selector).unbind('click').click(function(){
			var element = $(this);
			
			var href = element.attr('href');
			if(href.indexOf('?') > -1) {
				href += '&RSP=ajax';
			} else {
				href += '?RSP=ajax';
			}
			
			jQuery.post(href, function(data){
				if(data.ok) {
					element.text(data.ok);
					if(data.classs == 'add') {
						element.removeClass('gray');
					} else if(data.classs == 'remove') {
						element.addClass('gray');
					}
					if(callback) {
						callback(element);
					}
					return;
				} else if(data.error) {
					Pins.error(data.error);
					return;
				} else if(data.location) {
					window.location = data.location;
					return;
				}
				Pins.error(data)
			},'json');
			return false;
		});
	},
	initLike: function(){
		$('.box').each(function(){
			var box = this;
			$('a.like', box).not('.login').unbind('click').click(function() {
				var element = $(this);

				var href = element.attr('href');
				if(href.indexOf('?') > -1) {
					href += '&RSP=ajax';
				} else {
					href += '?RSP=ajax';
				}
				
				jQuery.post(href, function(data){
					//console.log(data);
					if(data.ok) {
						element.html('<span class="anim likeIcon"></span><span class="text">'+data.ok+'</span>');
						
						if(data.classs == 'add') {
							element.addClass('disabled');
							
						} else if(data.classs == 'remove') {
							element.removeClass('disabled');
						}
						
						if(data.pin.stats) {
							for(i in data.pin.stats) {
								$('.preview .stats .' + i, box).html(data.pin.stats[i]);
							}
						}
						Pins.statHide($('.preview .stats', box));
						setTimeout(function(){ $('#container').masonry('reload'); }, 200);
						
						return;
					} else if(data.error) {
						Pins.error(data.error);
						return;
					} else if(data.location) {
						window.location = data.location;
						return;
					}
					Pins.error(data)
				},'json');
				return false;
			});
		});
	},
	initBoxComments: function(){
		
		$('#pin_commentt_form .submit input')
		.addClass('disabled')
		.attr('disabled', true);
		
		$('#comment')
		
		.unbind('.JoWriteComBig')
		.bind('keyup.JoWriteComBig change.JoWriteComBig paste.JoWriteComBig input.JoWriteComBig cut.JoWriteComBig keydown.JoWriteComBig focus.JoWriteComBig', function(){
			if($.trim(this.value)) {
				$('#pin_commentt_form .submit input')
				.removeClass('disabled')
				.attr('disabled', false);
			} else {
				$('#pin_commentt_form .submit input')
				.addClass('disabled')
				.attr('disabled', true);
			}
		});
		
		$('#pin_commentt_form').submit(function(){
			var form = $(this);

			var href = form.attr('action');
			if(href.indexOf('?') > -1) {
				href += '&RSP=ajax';
			} else {
				href += '?RSP=ajax';
			}

			$.post(href, form.serialize(), function(result){
				if(result.ok) {
					var link = $('<a>').append( $('<img>').attr('src', result.comment.user.avatar).attr('alt', result.comment.user.fullname) ).attr('href', result.comment.user.profile);
					var avatar = $('<p>').addClass('avatar').append(link);
					var user = $('<h4>').append($('<a>').attr('href', result.comment.user.profile).html(result.comment.user.fullname));
					var message = $('<p>').addClass('text').html(result.comment.comment);
					var deleteComment = null;
					if(result.comment.delete_comment) {
						deleteComment = $('<a />').addClass('delete')
						.attr('href', result.comment.delete_comment).html('X');
					} 
					var li = $('<li>').css('display','none').append(avatar).append(user).append(message).append('<div class="clear"></div>');
					if(result.comment.delete_comment) {
						li.append(deleteComment);
					}
					$(li).insertBefore('.commentslist ul .addComments').slideDown(function(){
						Pins.initBoxCommentsActions();
					});
					$('#pin_commentt_form .submit input')
					.addClass('disabled')
					.attr('disabled', true);
					$('#comment').val('').trigger('change.joAutoresize');
					$('.tagmate-hiliter').html('');
				} else if(result.location) {
					window.location = result.location;
					return;
				} else if(result.error) {
					Pins.error(result.error);
					return;
				} else {
					Pins.error(result)
				}
			}, 'json');
			
			return false;
		});
	},
	initComments: function(){
		$('.box').not('.initCommentSend').each(function(){
			var box = this;
			$('a.comment', box).not('.login').unbind('click').click(function() {
				$('.addComment', box).slideToggle(200,function(){
					if($('a.comment',box).hasClass('disabled')) { 
						$('a.comment', box).removeClass('disabled');
					} else { 
						$('a.comment', box).addClass('disabled');
						$('.addComment .comment-form textarea', box).focus();
					}
					$('#container').masonry('reload');
				});
				return false;
			});
			
			$('.comments li.addComment textarea', box).autoResize({
				onResize: function(){ 
					setTimeout(function(){ $('#container').masonry('reload'); }, 200); 
				}
			});
			
			$('.comments li.addComment .comment-button', box)
			.addClass('disabled')
			.attr('disabled', true);
			$('.comments li.addComment textarea', box)
			.unbind('.JoWriteCom')
			.bind('keyup.JoWriteCom change.JoWriteCom paste.JoWriteCom input.JoWriteCom cut.JoWriteCom keydown.JoWriteCom focus.JoWriteCom', function(){
				if($.trim(this.value)) {
					$('.comments li.addComment .comment-button', box)
					.removeClass('disabled')
					.attr('disabled', false);
				} else {
					$('.comments li.addComment .comment-button', box)
					.addClass('disabled')
					.attr('disabled', true);
				}
			});
			
			$('.comments li.addComment form', box).submit(function(){
				var form = $(this);
				var href = form.attr('action');
				if(href.indexOf('?') > -1) {
					href += '&RSP=ajax';
				} else {
					href += '?RSP=ajax';
				}
				$.post(href, form.serialize(), function(result){
					if(result.ok) {
						$('.comments li.addComment textarea', box).val('');
						$('.comments li.addComment .comment-button', box)
						.addClass('disabled')
						.attr('disabled', true);
						$('a.comment', box).click();
						var link = $('<a>').append( $('<img>').attr('src', result.comment.user.avatar).attr('alt', result.comment.user.fullname) ).attr('href', result.comment.user.profile);
						var avatar = $('<p>').addClass('avatar').append(link);
						var message = $('<p>').addClass('message').append($('<a>').attr('href', result.comment.user.profile).html(result.comment.user.fullname)).append(' '+result.comment.comment);
						var li = $('<li>').css('display','none').append(avatar).append(message).append('<div class="clear"></div>');
						var total_comments = $('.comments ul li:not(.addComment)', box).size();
						
						if(result.comment.delete_comment) {
							deleteComment = $('<a />').addClass('delete')
							.attr('href', result.comment.delete_comment).html('X');
							li.append(deleteComment);
							Pins.initBoxCommentsActions(deleteComment);
						}
						
						if(total_comments <= 5) {
							$(li).insertBefore($('.comments ul .addComment',box)).slideDown(function(){
								$('.comments ul li', box).not('.addComment').removeClass('last').filter(':last').addClass('last');
								setTimeout(function(){ $('#container').masonry('reload'); }, 200);
								Pins.statHide($('.stats',box));
							});
						} else {
							setTimeout(function(){ $('#container').masonry('reload'); }, 200);
						}
						
						if(result.comment.pin.stats) {
							for(i in result.comment.pin.stats) {
								$('.preview .stats .' + i, box).html(result.comment.pin.stats[i]);
							}
						}
						
						if(result.comment.pin.stats.all_comments) {
							if( $('.comments .all a', box).size() > 0 ) {
								$('.comments .all a', box).html(result.comment.pin.stats.all_comments);
							} else {
								$('div.comments', box).append('<p class="all"><a href="' + result.comment.pin.stats.all_comments_href + '">' + result.comment.pin.stats.all_comments + '</a></p>');
							}
						}
						
						return;
					} else if(result.location) {
						window.location = result.location;
						return;
					} else if(result.error) {
						Pins.error(result.error);
						return;
					} else {
						Pins.error(result)
					}
				}, 'json');
				return false;
			});
			$(box).addClass('initCommentSend');
		});
	},
	initLazyLoad: function(selector){
		$(selector).LazyLoad();
	},
	opener: '',
	initPopupOpener: function($pin, opener){
		//XPER: Inicio de ventana de PIN
		hist = new JoGetPinsHistory();
		
		var opener = Pins.opener = window.location.href;
		
		var href = $pin.attr('href');
		if(href.indexOf('?') > -1) {
			href += '&RSP=ajax';
		} else {
			href += '?RSP=ajax';
		}
		
		$('<a>').attr('href', href).fancybox({
			
			'overlayOpacity': 0.85,
			'overlayColor'	: '#fff',
			'scrolling'		: 'no',
			'autoDimensions': true,
			'margin'		: 0,
			'padding'		: 0,
			'transitionIn'	: 'fade',
			'transitionOut'	: 'fade',
			'titleShow'		: false,
			'showCloseButton': false,
			'onStart' : function(){
				
				/*$('#fancybox-wrap, .pin-overlay, #fancybox-overlay').addClass('visibility_hidden');*/
				$('#container').infinitescroll('pause');
				$("#fancybox-wrap").wrap('<div class="pin-overlay" />');
			    $("body").addClass('noscroll');
			    $(".scrolltotop").hide();
			    $("#fancybox-wrap").addClass('wrapTop');
			    $("#fancybox-outer").css('padding-top','0');
			    
			    if ($.browser.msie  && parseInt($.browser.version, 10) === 7) {
			     $("#fancybox-wrap #buttons").css({"position":"absolute"});
			    }
			},
	        'onCleanup': function(){
				$("#fancybox-wrap").unwrap('<div class="pin-overlay" />');
			    $("body").removeClass('noscroll');
			    $(".scrolltotop").show();
			    $("#fancybox-wrap").removeClass('wrapTop');
			    $("#fancybox-outer").css('padding-top','56px');
			    $("#buttonswrapper-popup").hide();
	        },
	        'onComplete' : function(a){
	        	/*var fwarap = $('#fancybox-wrap');
	        	
	        	fwarap.css({'margin-top': -(fwarap.innerHeight(true) + 100)}).removeClass("visibility_hidden");
	        	$('.pin-overlay, #fancybox-overlay').removeClass("visibility_hidden").css({display: "none"}).show(250);
	        	fwarap.animate({'margin-top': 0});*/
	        	
	        	//$('#fancybox-outer').css({"padding-top" : 0});
	        	
	        	$('#container').infinitescroll('pause');
	        	
	        	$('#fancybox-outer').append($('#fancybox-wrap .buttonswrapper'));
	        	Pins.initLazyLoad('#pin .box .photo img');	
	        	
		    	$(".pin-overlay").click(function(event) {
			     if (event.target.className == "pin-overlay") {
			      $.fancybox.close();
			      return false;
			     }
			    });
			    fancybox_wrap_mousewheel();
			    if(hist) {
				if (navigator.userAgent.indexOf('MSIE') ==-1) {                                
                                    hist.setPage({'pin_id': $pin.attr('id')}, $pin.attr('href'));
                                }
			    }
	        	 
	        	$('.buttonswrapper a').click(function(){
	        		var returns = Pins.controls($(this), $(this).attr('href'), opener);
	        		$('#fancybox-content').append($('#fancybox-wrap .buttonswrapper'));
	        		return false;
	        	});
	        	
	        	$('#comment').clearOnFocus();
	        	
	        	//$('#pin .info a').attr('target', '_blank');
	        	
	        	Pins.showIcons('#pin', '.photo');
	        	Pins.initLike();
	        	Pins.initBoxComments();
	        	Pins.initBoxCommentsActions();
	        	fancyboxSilverOnComplete();
	        	
	        }, 
	        'onClosed': function(){
	        	if(opener) {
                            if (navigator.userAgent.indexOf('MSIE') ==-1) {                                
	        		hist.setPage({'pin_id': $pin.attr('id')}, opener);
                            }
	        	}
	        	$('#fancybox-content').append($('#fancybox-wrap .buttonswrapper'));
	        	$('#container').infinitescroll('resume');
	        }
		
		}).contextmenu().click();
		if(hist) {
			return false;
		}
	},
	initSetLink: function(page) {
		hist = new JoGetPinsHistory();
		if (hist) { 
 		   if(Pins.url.indexOf('?') > -1) {
 			   temp = Pins.url.split('?');
 			   set_url = temp[0]+'/page/'+page+'/'+( temp[1] ? '?'+temp[1] : '' );
 		   } else {
 			   set_url = Pins.url+'/page/'+page;
 		   }
 		   hist.setPage(page, set_url); 
 	   }
	},
	initBoxClick: function() {
		$('#container .box').not('.follow-box, .easy-ads, .not-popup').find('.thumb a').unbind('click').bind('click',function(){
			var $pin = $(this);
			var opener = Pins.opener = window.location.href;
			return Pins.initPopupOpener($pin, opener);
		});
	},
	
	showIcons: function(selector, box) {
		selector = selector || '#container';
		box = box || '.box';
		$(box, selector).hover(function() {
			$(this).find(".c_icons_small").fadeIn(125);
			
			if($.browser.mozilla || $.browser.webkit){
				$(this).find($(".PriceContainer")).addClass('animateOut');
				$(this).find($(".PriceContainer")).removeClass('animateIn');
			}else{
				$(this).find($(".PriceContainer")).hide();
			}
			
			$('.c_icons_small li a, .c_icons_small_pb li a').hover(
					function(){
						$(this).find($('.text')).addClass('shown');
					
					},
					function(){
						$(this).find($('.text')).removeClass('shown');
					}
				
			)
		}, function() {
			
			if($.browser.mozilla || $.browser.webkit){
			$(this).find($(".PriceContainer")).removeClass('animateOut');
			$(this).find($(".PriceContainer")).addClass('animateIn');
			}else{
				$(this).find(".c_icons_small").fadeOut(125);
				$(this).find($(".PriceContainer")).show();
			}
			$('.c_icons_small li a').hover(
					function(){
						$(this).find($('.text')).addClass('shown');
					},
					function(){
						$(this).find($('.text')).removeClass('shown');
					}
				
			)
			$(this).find(".c_icons_small").fadeOut(125);
		});
	},
	
	
	controls: function(button, url, opener) { 
		hist = new JoGetPinsHistory();
		switch( true ) {
			case button.hasClass('like'):
			
			break;
			case button.hasClass('tweet'):
				window.open('http://twitter.com/share?url=' + encodeURI(url) + '&text=' + encodeURI(button.attr('title')), 'Twitter share', "width=550,height=370,left="+($(window).width()/2-275)+",top="+($(window).height()/2-185));
				return true;
			break;
			case button.hasClass('embed'):
				fb_type = 'silver';
			break;
			case button.hasClass('report'):
				fb_type = 'silver';
			break;
			case button.hasClass('email'):
				fb_type = 'silver';
			break;
		}
		
		var href = url;
		if(href.indexOf('?') > -1) {
			href += '&RSP=ajax';
		} else {
			href += '?RSP=ajax';
		}
		
		if(url && fb_type == 'silver') {
			$('<a>').attr('href', url).fancybox({
				'overlayOpacity': 0.85,
				'overlayColor'	: '#fff',
				'scrolling'		: 'no',
				'titlePosition' : 'over',
				'autoDimensions': true,
				'margin'		: 0,
				'padding'		: 0,
				'transitionIn'	: 'none',
				'transitionOut'	: 'none',
				'centerOnScroll': true,
		        'onComplete' : function() { 
		        	fancyboxSilverOnComplete();
		        	$('#container').infinitescroll('pause');
		        }, 
		        'onClosed': function(){
		        	if(hist && opener) { 
		        		hist.setPage({'pin_id': 0}, opener);
		        	}
		        	$('#container').infinitescroll('resume');
		        },
		        'titleFormat': function(){
		        	title = button.attr('title');
		        	if(title) {
		        		return '<div id="fancybox-title-over">' + button.attr('title') + '</div>';
		        	}
		        	return ;
		        }
			}).contextmenu().click();
			return url;
		}
		
	}
};

/* jquery clearinginput */
(function($){
	$.fn.clearOnFocus = function(){
	    return this.focus(function(){
	        var v = $(this).val();
	        $(this).val( v === this.defaultValue ? '' : v );
	    }).blur(function(){
	        var v = $(this).val();
	        $(this).val( v.match(/^\s+$|^$/) ? this.defaultValue : v );
	    });
	};
})(jQuery);

/* autoresize jquery */
(function(e, h, f) {
  function g() {
    var a = e(this),
        d = a.height(),
        b = a.data("scrollOffset"),
        c = a.data("minHeight"),
        i = f.scrollTop();
    b = a.height(c).prop("scrollHeight") - b;
    a.height(b);
    f.scrollTop(i);
    d !== b && a.trigger("autoresize:resize", b);
    if(f.op.onResize) {
    	f.op.onResize.call(this);
    }
  }
  function j() {
    var a = e(this),
        d = a.val(),
        b = a.val("").height(),
        c = this.scrollHeight;
    c = c > b ? c - b : 0;
    a.data("minHeight", b);
    a.data("scrollOffset", c);
    a.val(d).unbind('.joAutoresize').bind(k, g);
    g.call(this)
  }
  var k = "keyup.joAutoresize change.joAutoresize paste.joAutoresize input.joAutoresize cut.joAutoresize keydown.joAutoresize focus.joAutoresize";
  h.autoResize = function(con) {
	  f.op = e.extend({onResize: null}, con);
	  return this.filter("textarea").each(j)
  }
})(jQuery, jQuery.fn, jQuery(window));

/* add detail fixed boxes */
$(window).load(function(){
	var boxes = $( "#boxes" );
	var originalBoxesTop = 0;
	if(boxes.size() > 0) {
		originalBoxesTop = boxes.offset().top;
	}
	var view = $( window );

	view.bind(
		"scroll resize",
		function(){
			var viewTop = view.scrollTop();
			if (
				(viewTop > originalBoxesTop) &&
				!boxes.is( ".boxes-fixed" )
				){
				boxes
					.removeClass( "boxes-absolute" )
					.addClass( "boxes-fixed" )
				;
			} else if (
				(viewTop <= originalBoxesTop) &&
				boxes.is( ".boxes-fixed" )
				){
				boxes
					.removeClass( "boxes-fixed" )
					.addClass( "boxes-absolute" )
				;
			}
		}
	);
});

/* add detail center */
$(window).load(function(){
	var detailwidth = $("#detail").width(); 
	$("#topwrapper, #menuwrapper").css({'width' : detailwidth + 'px'});
	$(window).resize(function() {
		var detailwidth = $("#detail").width(); 
		$("#topwrapper, #menuwrapper").css({'width' : detailwidth + 'px'});
	});
});

/* jquery fieldselection */
(function() {
    var fieldSelection = {
        getSelection: function() {
            var e = this.jquery ? this[0] : this;
            
            return (
                ('selectionStart' in e && function() {
                    var l = e.selectionEnd - e.selectionStart;
                    return {
                        start: e.selectionStart,
                        end: e.selectionEnd,
                        length: l,
                        text: e.value.substr(e.selectionStart, l)};
                })

                || (document.selection && function() {
                    e.focus();
                    
                    var r = document.selection.createRange();
                    if (r == null) {
                        return {
                            start: 0,
                            end: e.value.length,
                            length: 0};
                    }
                    
                    var re = e.createTextRange();
                    var rc = re.duplicate();
                    re.moveToBookmark(r.getBookmark());
                    rc.setEndPoint('EndToStart', re);

                    var rcLen = rc.text.length,
                        i,
                        rcLenOut = rcLen;
                    for (i = 0; i < rcLen; i++) {
                        if (rc.text.charCodeAt(i) == 13) rcLenOut--;
                    }
                    var rLen = r.text.length,
                        rLenOut = rLen;
                    for (i = 0; i < rLen; i++) {
                        if (r.text.charCodeAt(i) == 13) rLenOut--;
                    }
                    
                    return {
                        start: rcLenOut,
                        end: rcLenOut + rLenOut,
                        length: rLenOut,
                        text: r.text};
                })

                || function() {
                    return {
                        start: 0,
                        end: e.value.length,
                        length: 0};
                }

            )();

        },

        setSelection: function()
        {
            var e = this.jquery ? this[0] : this;
            var start_pos = arguments[0] || 0;
            var end_pos = arguments[1] || 0;

            return (
                ('selectionStart' in e && function() {
                    e.focus();
                    e.selectionStart = start_pos;
                    e.selectionEnd = end_pos;
                    return this;
                })

                || (document.selection && function() {
                    e.focus();
                    var tr = e.createTextRange();

                    var stop_it = start_pos;
                    for (i=0; i < stop_it; i++) if( e.value[i].search(/[\r\n]/) != -1 ) start_pos = start_pos - .5;
                    stop_it = end_pos;
                    for (i=0; i < stop_it; i++) if( e.value[i].search(/[\r\n]/) != -1 ) end_pos = end_pos - .5;
                
                    tr.moveEnd('textedit',-1);
                    tr.moveStart('character',start_pos);
                    tr.moveEnd('character',end_pos - start_pos);
                    tr.select();

                    return this;
                })

                || function() {
                    return this;
                }
            )();
        },
        
        replaceSelection: function() {
            var e = this.jquery ? this[0] : this;
            var text = arguments[0] || '';
            
            return (
                ('selectionStart' in e && function() {
                    e.value = e.value.substr(0, e.selectionStart) + text + e.value.substr(e.selectionEnd, e.value.length);
                    return this;
                })
                
                || (document.selection && function() {
                    e.focus();
                    document.selection.createRange().text = text;
                    return this;
                })
                
                || function() {
                    e.value += text;
                    return this;
                }
            )();
        }
    };
    
    jQuery.each(fieldSelection, function(i) { jQuery.fn[i] = this; });

})();

/* jquery scrollTo */
(function( $ ){
	
	var $scrollTo = $.scrollTo = function( target, duration, settings ){
		$(window).scrollTo( target, duration, settings );
	};

	$scrollTo.defaults = {
		axis:'xy',
		duration: parseFloat($.fn.jquery) >= 1.3 ? 0 : 1
	};

	$scrollTo.window = function( scope ){
		return $(window)._scrollable();
	};

	$.fn._scrollable = function(){
		return this.map(function(){
			var elem = this,
				isWin = !elem.nodeName || $.inArray( elem.nodeName.toLowerCase(), ['iframe','#document','html','body'] ) != -1;

				if( !isWin )
					return elem;

			var doc = (elem.contentWindow || elem).document || elem.ownerDocument || elem;
			
			return $.browser.safari || doc.compatMode == 'BackCompat' ?
				doc.body : 
				doc.documentElement;
		});
	};

	$.fn.scrollTo = function( target, duration, settings ){
		if( typeof duration == 'object' ){
			settings = duration;
			duration = 0;
		}
		if( typeof settings == 'function' )
			settings = { onAfter:settings };
			
		if( target == 'max' )
			target = 9e9;
			
		settings = $.extend( {}, $scrollTo.defaults, settings );

		duration = duration || settings.speed || settings.duration;

		settings.queue = settings.queue && settings.axis.length > 1;
		
		if( settings.queue ) {
			duration /= 2;
		}
		settings.offset = both( settings.offset );
		settings.over = both( settings.over );

		return this._scrollable().each(function(){
			var elem = this,
				$elem = $(elem),
				targ = target, toff, attr = {},
				win = $elem.is('html,body');

			switch( typeof targ ){
				case 'number':
				case 'string':
					if( /^([+-]=)?\d+(\.\d+)?(px|%)?$/.test(targ) ){
						targ = both( targ );
						break;
					}

					targ = $(targ,this);
				case 'object':
					if( targ.is || targ.style )
						toff = (targ = $(targ)).offset();
			}
			$.each( settings.axis.split(''), function( i, axis ){
				var Pos	= axis == 'x' ? 'Left' : 'Top',
					pos = Pos.toLowerCase(),
					key = 'scroll' + Pos,
					old = elem[key],
					max = $scrollTo.max(elem, axis);

				if( toff ){
					attr[key] = toff[pos] + ( win ? 0 : old - $elem.offset()[pos] );

					if( settings.margin ){
						attr[key] -= parseInt(targ.css('margin'+Pos)) || 0;
						attr[key] -= parseInt(targ.css('border'+Pos+'Width')) || 0;
					}
					
					attr[key] += settings.offset[pos] || 0;
					
					if( settings.over[pos] )
						attr[key] += targ[axis=='x'?'width':'height']() * settings.over[pos];
				}else{ 
					var val = targ[pos];
					attr[key] = val.slice && val.slice(-1) == '%' ? 
						parseFloat(val) / 100 * max
						: val;
				}

				if( /^\d+$/.test(attr[key]) )
					attr[key] = attr[key] <= 0 ? 0 : Math.min( attr[key], max );

				if( !i && settings.queue ){
					if( old != attr[key] )
						animate( settings.onAfterFirst );
					delete attr[key];
				}
			});

			animate( settings.onAfter );			

			function animate( callback ){
				$elem.animate( attr, duration, settings.easing, callback && function(){
					callback.call(this, target, settings);
				});
			};

		}).end();
	};

	$scrollTo.max = function( elem, axis ){
		var Dim = axis == 'x' ? 'Width' : 'Height',
			scroll = 'scroll'+Dim;
		
		if( !$(elem).is('html,body') )
			return elem[scroll] - $(elem)[Dim.toLowerCase()]();
		
		var size = 'client' + Dim,
			html = elem.ownerDocument.documentElement,
			body = elem.ownerDocument.body;

		return Math.max( html[scroll], body[scroll] ) 
			 - Math.min( html[size]  , body[size]   );
			
	};

	function both( val ){
		return typeof val == 'object' ? val : { top:val, left:val };
	};

})( jQuery );

/* jquery tagmate */
var Tagmate = (function() { 
    var HASH_TAG_EXPR = "\\w+";
    var NAME_TAG_EXPR = "\\w+(?: \\w+)*";
    var PRICE_TAG_EXPR = "(?:(?:\\d{1,3}(?:\\,\\d{3})+)|(?:\\d+))(?:\\.\\d{2})?";

    return {
        HASH_TAG_EXPR: HASH_TAG_EXPR,
        NAME_TAG_EXPR: NAME_TAG_EXPR,
        PRICE_TAG_EXPR: PRICE_TAG_EXPR,

        DEFAULT_EXPRS: {
            '@': NAME_TAG_EXPR,
            '#': HASH_TAG_EXPR,
            '$': PRICE_TAG_EXPR
        },

        filterOptions: function(options, term) {
            var filtered = [];
            for (var i = 0; i < options.length; i++) {
                var label_lc = options[i].label.toLowerCase();
                var term_lc = term.toLowerCase();
                if (term_lc.length <= label_lc.length && label_lc.indexOf(term_lc) == 0)
                    filtered.push(options[i]);
            }
            return filtered;
        }
    };
})();

(function($) {
    function regex_index_of(str, regex, startpos) {
        var indexOf = str.substring(startpos || 0).search(regex);
        return (indexOf >= 0) ? (indexOf + (startpos || 0)) : indexOf;
    }
    
    function regex_escape(text) {
        return text.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&");
    }

    function parse_tags(textarea, exprs, sources, mtc) {        
        var tags = {}, matches_res = {};
        for (tok in exprs) {
        	matches_res[tok] = {};
            if (sources && sources[tok]) {
                var matches = {}, indexes = {};
                for (key in sources[tok]) {
                    var value = sources[tok][key].value;
                    var label = sources[tok][key].label;
                    var tag = regex_escape(tok + label);
                    var e = ["(?:^(",")$|^(",")\\W|\\W(",")\\W|\\W(",")$)"].join(tag);
                    var i = 0, re = new RegExp(e, "gm");
                    while ((i = regex_index_of(textarea.val(), re, i)) > -1) {
                        var p = indexes[i] ? indexes[i] : null;
                        if (!p || matches[p].length < label.length)
                            indexes[i] = value;
                        matches[value] = label;
                        matches_res[tok][value] = label;
                        i += label.length + 1;
                    }
                }
                for (i in indexes)
                    tags[tok + indexes[i]] = tok;
            } else {
                var m = null, re = new RegExp("([" + tok + "]" + exprs[tok] + ")", "gm");
                while (m = re.exec(textarea.val())) {
                    tags[m[1]] = tok;
                }
            }
        };

        var results = [];
        for (tag in tags) {
            results.push(tag);
        }
        if(mtc) {
        	return matches_res;
        } else {
        	return results;
        }
    }

    $.fn.extend({
    	objectSize: function(){
	    	var num = 0;
	    	for(i in this[0]) {
	    		num++;
	    	}
	    	return num;
	    },
        getTags: function(exprs, sources) {
            var textarea = $(this);
            exprs = exprs || textarea.data("_tagmate_exprs");
            sources = sources || textarea.data("_tagmate_sources");
            return parse_tags(textarea, exprs, sources, true);
        },
        tagmate: function(options) {
            var defaults = {
                exprs: Tagmate.DEFAULT_EXPRS,
                sources: null, 
                capture_tag: null, 
                replace_tag: null, 
                menu_class: "tagmate-menu",
                menu_option_class: "tagmate-menu-option",
                menu_option_active_class: "tagmate-menu-option-active",
                highlight_tags: false, 
                highlight_class: 'tagmate-highlight'
            };

            function prev_tok(str, tok, pos) {
                var re = new RegExp("[" + tok + "]");
                for (; pos >= 0 && !re.test(str[pos]); pos--) {};
                return pos;
            }

            function parse_tag(textarea) {
                var text = textarea.val();
                var sel = textarea.getSelection();

                var m_pos = -1, m_tok = null;
                for (tok in defaults.exprs) {
                    var pos = prev_tok(text, tok, sel.start);
                    if (pos > m_pos) {
                        m_pos = pos;
                        m_tok = tok;
                    }
                }

                var sub = text.substring(m_pos + 1, sel.start);

                var re = new RegExp("^[" + m_tok + "]" + defaults.exprs[m_tok]);
                if (re.exec(m_tok + sub))
                    return m_tok + sub;

                return null;
            }

            function replace_tag(textarea, tag, value) {
                var text = textarea.val();

                var sel = textarea.getSelection();
                var pos = prev_tok(text, tag[0], sel.start);
                var l = text.substr(0, pos);
                var r = text.substr(pos + tag.length);
                textarea.val(l + tag[0] + value + r);

                var sel_pos = pos + value.length + 1;
                textarea.setSelection(sel_pos, sel_pos);

                if (defaults.replace_tag)
                    defaults.replace_tag(tag, value);
            }

            function update_menu(menu, options) {
                options = options.sort(function(a, b) {
                    var a_lc = a.label.toLowerCase();
                    var b_lc = b.label.toLowerCase();
                    if (a_lc > b_lc)
                        return 1;
                    else if (a_lc < b_lc)
                        return -1;
                    return 0;
                });

                for (var i = 0; i < options.length; i++) {
                    var label = options[i].label;
                    var value = options[i].value;
                    var image = options[i].image;
                    if (i == 0)
                        menu.html("");
                    var content = "<span>" + label + "</span>";
                    if (image)
                        content = "<img src='" + image + "' alt='" + label + "'/>" + content;
                    var classes = defaults.menu_option_class;
                    if (i == 0)
                        classes += " " + defaults.menu_option_active_class;
                    menu.append("<div class='" + classes + "'>" + content + "</div>");
                }
            }

            function scroll_menu(menu, direction) {
                var child_selector = direction == "down" ? ":first-child" : ":last-child";
                var sibling_func = direction == "down" ? "next" : "prev";
                var active = menu.children("." + defaults.menu_option_active_class);

                if (active.length == 0) {
                    active = menu.children(child_selector);
                    active.addClass(defaults.menu_option_active_class);
                } else {
                    active.removeClass(defaults.menu_option_active_class);
                    active = active[sibling_func]().length > 0 ? active[sibling_func]() : active;
                    active.addClass(defaults.menu_option_active_class);
                }

                var i, options = menu.children();
                var n = Math.floor($(menu).height() / $(options[0]).height()) - 1;
                if ($(menu).height() % $(options[0]).height() > 0)
                    n -= 1; 
                for (i = 0; i < options.length && $(options[i]).html() != $(active).html(); i++) {};
                if (i > n && (i - n) >= 0 && (i - n) < options.length)
                    menu.scrollTo(options[i - n]);
            }

            function init_hiliter(textarea) {
                textarea.css("background", "transparent");

                var container = $(textarea).wrap("<div class='tagmate-container'/>");

                var hiliter = $("<pre class='tagmate-hiliter'></pre>");
                
                
                hiliter.css("height", textarea.height() + "px");
                hiliter.css("width", textarea.width() + "px");
                hiliter.css("border", "1px solid #FFF");
                hiliter.css("margin", "0");
                hiliter.css("padding-top", textarea.css("padding-top"));
                hiliter.css("padding-bottom", textarea.css("padding-bottom"));
                hiliter.css("padding-left", textarea.css("padding-left"));
                hiliter.css("padding-right", textarea.css("padding-right"));
                hiliter.css("color", "#999");
                hiliter.css("z-index", "-1");
                hiliter.css("font-family", textarea.css("font-family"));
                hiliter.css("font-size", textarea.css("font-size"));
                hiliter.css("line-height", textarea.css("line-height"));

                hiliter.css("white-space", "pre-wrap");
                hiliter.css("white-space", "-moz-pre-wrap !important");
                hiliter.css("white-space", "-pre-wrap");
                hiliter.css("white-space", "-o-pre-wrap");
                hiliter.css("word-wrap", "break-word");
                
                hiliter.css("color", "transparent");
                hiliter.css("color", textarea.css('background-color'));

                textarea.before(hiliter);
                textarea.css("margin-top", "-" + textarea.outerHeight() + "px");

                return hiliter;
            }

            function update_hiliter(textarea, hiliter) {
                var html = textarea.val();
                var sources = textarea.data("_tagmate_sources");
                var tags = parse_tags(textarea, defaults.exprs, sources);

                for (var i = 0; i < tags.length; i++) {
                    var expr = tags[i], tok = tags[i][0], term = tags[i].substr(1);
                    if (sources && sources[tok]) {
                        for (var j = 0; j < sources[tok].length; j++) {
                            var option = sources[tok][j];
                            if (option.value == term) {
                                expr = tok + option.label;
                                break;
                            }
                        }
                    }

                    var re = new RegExp(regex_escape(expr), "g");
                    var span = "<span class='" + defaults.highlight_class + "'>" + expr + "</span>";
                    html = html.replace(re, span);
                }

                hiliter.html(html);
            }

            return this.each(function() {
                if (options)
                    $.extend(defaults, options);

                var textarea = $(this);

                var hiliter = null;
                if (defaults.highlight_tags)
                    hiliter = init_hiliter(textarea);

                textarea.data("_tagmate_exprs", defaults.exprs);

                var sources_holder = {};
                for (var tok in defaults.sources)
                    sources_holder[tok] = [];
                textarea.data("_tagmate_sources", sources_holder);

                var menu = $("<div class=" + defaults.menu_class + "></div>");
                textarea.after(menu);

                var pos = textarea.offset();
                menu.css("position", "absolute");
                menu.hide();

                function tag_check() {
                    menu.hide();

                    var tag = parse_tag(textarea);
                    if (tag) {
                        var tok = tag[0], term = tag.substr(1);
                        var sel = textarea.getSelection();
                        var pos = prev_tok(textarea.val(), tok, sel.start);
                        if ((sel.start - pos) <= tag.length) {
                            (function(done) {
                                if (typeof defaults.sources[tok] === 'object')
                                    done(Tagmate.filterOptions(defaults.sources[tok], term));
                                else if (typeof defaults.sources[tok] === 'function')
                                    defaults.sources[tok]({term:term}, done);
                                else if (typeof defaults.sources[tok] === 'string')
                                    $.getJSON(defaults.sources[tok], {term:term}, function(res) {
                                        done(res.options);
                                    });
                                else
                                    done();
                            })(function(options) {
                                if (options && options.length > 0) {
                                    update_menu(menu, options);
                                    menu.css("top", (textarea.outerHeight() - 1) + "px");
                                    menu.show();

                                    var _sources = textarea.data("_tagmate_sources");
                                    for (var i = 0; i < options.length; i++) {
                                        var found = false;
                                        for (var j = 0; !found && j < _sources[tok].length; j++)
                                            found = _sources[tok][j].value == options[i].value;
                                        if (!found)
                                            _sources[tok].push(options[i]);
                                    }
                                }

                                if (tag && defaults.capture_tag)
                                    defaults.capture_tag(tag);
                            });
                        }
                    }
                }

                var ignore_keyup = false;

                $(textarea)
                    .unbind('.tagmate')
                    .bind('focus.tagmate', function(e) {
                        tag_check();
                    })
                    .bind('blur.tagmate', function(e) {
                        setTimeout(function() { menu.hide(); }, 300);
                    })
                    .bind('click.tagmate', function(e) {
                        tag_check();
                    })
                    .bind('keydown.tagmate', function(e) {
                        if (menu.is(":visible")) {
                            if (e.keyCode == 40) { 
                                scroll_menu(menu, "down");
                                ignore_keyup = true;
                                return false;
                            } else if (e.keyCode == 38) { 
                                scroll_menu(menu, "up");
                                ignore_keyup = true;
                                return false;
                            } else if (e.keyCode == 13) {
                                var value = menu.children("." + defaults.menu_option_active_class).text();
                                var tag = parse_tag(textarea);
                                if (tag && value) {
                                    replace_tag(textarea, tag, value);
                                    menu.hide();
                                    ignore_keyup = true;
                                    return false;
                                }
                            } else if (e.keyCode == 27) {
                                menu.hide();
                                ignore_keyup = true;
                                return false;
                            }
                        }
                    })
                    .bind('keyup.tagmate focus.tagmate', function(e) {

                    	if (ignore_keyup) {
                            ignore_keyup = false;
                            return true;
                        }
                        tag_check();

                        if (hiliter)
                            update_hiliter(textarea, hiliter);
                    });

                $("." + defaults.menu_class + " ." + defaults.menu_option_class)
                    .die("click.tagmate")
                    .live("click.tagmate", function() {
                        var value = $(this).text();
                        var tag = parse_tag(textarea);
                        replace_tag(textarea, tag, value);
                        textarea.keyup();
                    });
            });
        }
    });
})(jQuery);

/* getStyleObject */
(function($){
    $.fn.getStyleObject = function(){
        var dom = this.get(0);
        var style;
        var returns = {};
        if(window.getComputedStyle){
            var camelize = function(a,b){
                return b.toUpperCase();
            };
            style = window.getComputedStyle(dom, null);
            for(var i = 0, l = style.length; i < l; i++){
                var prop = style[i];
                var camel = prop.replace(/\-([a-z])/g, camelize);
                var val = style.getPropertyValue(prop);
                returns[camel] = val;
            };
            return returns;
        };
        if(style = dom.currentStyle){
            for(var prop in style){
                returns[prop] = style[prop];
            };
            return returns;
        };
        return this.css();
    };

    $.fn.copyCSS = function(source){
      var styles = $(source).getStyleObject();
      this.css(styles);
    }
})(jQuery);

/* jQuery selectBox */
if(jQuery) (function($) {

	$.extend($.fn, {

		selectBox: function(method, data) {

			var typeTimer,
				typeSearch = '',
				isMac = navigator.platform.match(/mac/i);


			var init = function(select, data) {
				
				var options;
				
				if( navigator.userAgent.match(/iPad|iPhone|Android|IEMobile|BlackBerry/i) ) return false;

				if( select.tagName.toLowerCase() !== 'select' ) return false;

				select = $(select);
				if( select.data('selectBox-control') ) return false;

				var control = $('<a class="selectBox" />'),
					inline = select.attr('multiple') || parseInt(select.attr('size')) > 1;

				var settings = data || {};
				
				control
					.width(select.outerWidth())
					.addClass(select.attr('class'))
					.attr('title', select.attr('title') || '')
					.attr('tabindex', parseInt(select.attr('tabindex')))
					.css('display', 'inline-block')
					.bind('focus.selectBox', function() {
						if( this !== document.activeElement && document.body !== document.activeElement ) $(document.activeElement).blur();
						if( control.hasClass('selectBox-active') ) return;
						control.addClass('selectBox-active');
						select.trigger('focus');
					})
					.bind('blur.selectBox', function() {
						if( !control.hasClass('selectBox-active') ) return;
						control.removeClass('selectBox-active');
						select.trigger('blur');
					});
				
				if( !$(window).data('selectBox-bindings') ) {
					$(window)
						.data('selectBox-bindings', true)
						.bind('scroll.selectBox', hideMenus)
						.bind('resize.selectBox', hideMenus);
				}
				
				if( select.attr('disabled') ) control.addClass('selectBox-disabled');
				
				select.bind('click.selectBox', function(event) {
					control.focus();
					event.preventDefault();
				});

				if( inline ) {

					options = getOptions(select, 'inline');

					control
						.append(options)
						.data('selectBox-options', options)
						.addClass('selectBox-inline selectBox-menuShowing')
						.bind('keydown.selectBox', function(event) {
							handleKeyDown(select, event);
						})
						.bind('keypress.selectBox', function(event) {
							handleKeyPress(select, event);
						})
						.bind('mousedown.selectBox', function(event) {
							if( $(event.target).is('A.selectBox-inline') ) event.preventDefault();
							if( !control.hasClass('selectBox-focus') ) control.focus();
						})
						.insertAfter(select);

					if( !select[0].style.height ) {

						var size = select.attr('size') ? parseInt(select.attr('size')) : 5;

						var tmp = control
							.clone()
							.removeAttr('id')
							.css({
								position: 'absolute',
								top: '-9999em'
							})
							.show()
							.appendTo('body');
						tmp.find('.selectBox-options').html('<li><a>\u00A0</a></li>');
						var optionHeight = parseInt(tmp.find('.selectBox-options A:first').html('&nbsp;').outerHeight());
						tmp.remove();

						control.height(optionHeight * size);

					}

					disableSelection(control);

				} else {

					var label = $('<span class="selectBox-label" />'),
						arrow = $('<span class="selectBox-arrow" />');
					
					label
						.attr('class', getLabelClass(select))
						.text(getLabelText(select));
					
					options = getOptions(select, 'dropdown');
					options.appendTo('BODY');

					control
						.data('selectBox-options', options)
						.addClass('selectBox-dropdown')
						.append(label)
						.append(arrow)
						.bind('mousedown.selectBox', function(event) {
							if( control.hasClass('selectBox-menuShowing') ) {
								hideMenus();
							} else {
								event.stopPropagation();
								options.data('selectBox-down-at-x', event.screenX).data('selectBox-down-at-y', event.screenY);
								showMenu(select);
							}
						})
						.bind('keydown.selectBox', function(event) {
							handleKeyDown(select, event);
						})
						.bind('keypress.selectBox', function(event) {
							handleKeyPress(select, event);
						})
						.bind('open.selectBox', function(event, triggerData) {
							if(triggerData && triggerData._selectBox === true) return;
							showMenu(select);
						})
						.bind('close.selectBox', function(event, triggerData) {
							if(triggerData && triggerData._selectBox === true) return;
							hideMenus();
						})						
						.insertAfter(select);
					
					var labelWidth = control.width() - arrow.outerWidth() - parseInt(label.css('paddingLeft')) - parseInt(label.css('paddingLeft'));
					label.width(labelWidth);
					
					disableSelection(control);
					
				}

				select
					.addClass('selectBox')
					.data('selectBox-control', control)
					.data('selectBox-settings', settings)
					.hide();
				
			};


			var getOptions = function(select, type) {
				var options;

				var _getOptions = function(select, options) {
					select.children('OPTION, OPTGROUP').each( function() {
						if ($(this).is('OPTION')) {
							if($(this).length > 0) {
								generateOptions($(this), options);
							}
							else {
								options.append('<li>\u00A0</li>');
							}
						}
						else {
							var optgroup = $('<li class="selectBox-optgroup" />');
							optgroup.text($(this).attr('label'));
							options.append(optgroup);
							options = _getOptions($(this), options);
						}
					});
					return options;
				};

				switch( type ) {

					case 'inline':

						options = $('<ul class="selectBox-options" />');
						options = _getOptions(select, options);
						
						options
							.find('A')
								.bind('mouseover.selectBox', function(event) {
									addHover(select, $(this).parent());
								})
								.bind('mouseout.selectBox', function(event) {
									removeHover(select, $(this).parent());
								})
								.bind('mousedown.selectBox', function(event) {
									event.preventDefault();
									if( !select.selectBox('control').hasClass('selectBox-active') ) select.selectBox('control').focus();
								})
								.bind('mouseup.selectBox', function(event) {
									hideMenus();
									selectOption(select, $(this).parent(), event);
								});

						disableSelection(options);

						return options;

					case 'dropdown':
						options = $('<ul class="selectBox-dropdown-menu selectBox-options" />');
						options = _getOptions(select, options);

						options
							.data('selectBox-select', select)
							.css('display', 'none')
							.appendTo('BODY')
							.find('A')
								.bind('mousedown.selectBox', function(event) {
									event.preventDefault(); 
									if( event.screenX === options.data('selectBox-down-at-x') && event.screenY === options.data('selectBox-down-at-y') ) {
										options.removeData('selectBox-down-at-x').removeData('selectBox-down-at-y');
										hideMenus();
									}
								})
								.bind('mouseup.selectBox', function(event) {
									if( event.screenX === options.data('selectBox-down-at-x') && event.screenY === options.data('selectBox-down-at-y') ) {
										return;
									} else {
										options.removeData('selectBox-down-at-x').removeData('selectBox-down-at-y');
									}
									selectOption(select, $(this).parent());
									hideMenus();
								}).bind('mouseover.selectBox', function(event) {
									addHover(select, $(this).parent());
								})
								.bind('mouseout.selectBox', function(event) {
									removeHover(select, $(this).parent());
								});
						
						var classes = select.attr('class') || '';
						if( classes !== '' ) {
							classes = classes.split(' ');
							for( var i in classes ) options.addClass(classes[i] + '-selectBox-dropdown-menu');
						}

						disableSelection(options);

						return options;

				}

			};
			
			
			var getLabelClass = function(select) {
				var selected = $(select).find('OPTION:selected');
				return ('selectBox-label ' + (selected.attr('class') || '')).replace(/\s+$/, '');
			};
			
			
			var getLabelText = function(select) {
				var selected = $(select).find('OPTION:selected');
				return selected.text() || '\u00A0';
			};
			
			
			var setLabel = function(select) {
				select = $(select);
				var control = select.data('selectBox-control');
				if( !control ) return;
				control.find('.selectBox-label').attr('class', getLabelClass(select)).text(getLabelText(select));
			};
			
			
			var destroy = function(select) {

				select = $(select);
				var control = select.data('selectBox-control');
				if( !control ) return;
				var options = control.data('selectBox-options');

				options.remove();
				control.remove();
				select
					.removeClass('selectBox')
					.removeData('selectBox-control').data('selectBox-control', null)
					.removeData('selectBox-settings').data('selectBox-settings', null)
					.show();

			};
			
			
			var refresh = function(select) {
				select = $(select);
				select.selectBox('options', select.html());
			};

			
			var showMenu = function(select) {

				select = $(select);
				var control = select.data('selectBox-control'),
					settings = select.data('selectBox-settings'),
					options = control.data('selectBox-options');
				if( control.hasClass('selectBox-disabled') ) return false;

				hideMenus();

				var borderBottomWidth = isNaN(control.css('borderBottomWidth')) ? 0 : parseInt(control.css('borderBottomWidth'));
				
				options
					.width(control.innerWidth())
					.css({
						top: control.offset().top + control.outerHeight() - borderBottomWidth,
						left: control.offset().left
					});
				
				if( select.triggerHandler('beforeopen') ) return false;
				var dispatchOpenEvent = function() {
					select.triggerHandler('open', { _selectBox: true });
				};
				
				settings.menuSpeed = settings && settings.menuSpeed ? settings.menuSpeed : 100;
				
				switch( settings.menuTransition ) {

					case 'fade':
						options.fadeIn(settings.menuSpeed, dispatchOpenEvent);
						break;

					case 'slide':
						options.slideDown(settings.menuSpeed, dispatchOpenEvent);
						break;

					default:
						options.show(settings.menuSpeed, dispatchOpenEvent);
						break;

				}
				
				if( !settings.menuSpeed ) dispatchOpenEvent();
				
				var li = options.find('.selectBox-selected:first');
				keepOptionInView(select, li, true);
				addHover(select, li);

				control.addClass('selectBox-menuShowing');

				$(document).bind('mousedown.selectBox', function(event) {
					if( $(event.target).parents().andSelf().hasClass('selectBox-options') ) return;
					hideMenus();
				});

			};


			var hideMenus = function() {

				if( $(".selectBox-dropdown-menu:visible").length === 0 ) return;
				$(document).unbind('mousedown.selectBox');

				$(".selectBox-dropdown-menu").each( function() {

					var options = $(this),
						select = options.data('selectBox-select'),
						control = select.data ? select.data('selectBox-control') : {},
						settings = select.data ? select.data('selectBox-settings') : {};
					
					if( select.triggerHandler('beforeclose') ) return false;
					
					var dispatchCloseEvent = function() {
						select.triggerHandler('close', { _selectBox: true });
					};					
					
					try {
						settings.menuSpeed = settings && settings.menuSpeed ? settings.menuSpeed : 100;
					} catch (err) {
						settings = {};
						settings.menuSpeed = 100;
					}
					
					if(settings && settings.menuTransition) {
						switch( settings.menuTransition ) {
	
							case 'fade':
								options.fadeOut(settings.menuSpeed, dispatchCloseEvent);
								break;
	
							case 'slide':
								options.slideUp(settings.menuSpeed, dispatchCloseEvent);
								break;
	
							default:
								options.hide(settings.menuSpeed, dispatchCloseEvent);
								break;
	
						}
					} else {
						options.hide(settings.menuSpeed, dispatchCloseEvent);
					}
					
					if( !settings.menuSpeed ) dispatchCloseEvent();
					if(control && control.removeClass) {
						control.removeClass('selectBox-menuShowing');
					}

				});

			};


			var selectOption = function(select, li, event) {

				select = $(select);
				li = $(li);
				var control = select.data('selectBox-control'),
					settings = select.data('selectBox-settings');

				if( control.hasClass('selectBox-disabled') ) return false;
				if( li.length === 0 || li.hasClass('selectBox-disabled') ) return false;

				if( select.attr('multiple') ) {

					if( event.shiftKey && control.data('selectBox-last-selected') ) {

						li.toggleClass('selectBox-selected');

						var affectedOptions;
						if( li.index() > control.data('selectBox-last-selected').index() ) {
							affectedOptions = li.siblings().slice(control.data('selectBox-last-selected').index(), li.index());
						} else {
							affectedOptions = li.siblings().slice(li.index(), control.data('selectBox-last-selected').index());
						}

						affectedOptions = affectedOptions.not('.selectBox-optgroup, .selectBox-disabled');

						if( li.hasClass('selectBox-selected') ) {
							affectedOptions.addClass('selectBox-selected');
						} else {
							affectedOptions.removeClass('selectBox-selected');
						}

					} else if( (isMac && event.metaKey) || (!isMac && event.ctrlKey) ) {
						li.toggleClass('selectBox-selected');
					} else {
						li.siblings().removeClass('selectBox-selected');
						li.addClass('selectBox-selected');
					}

				} else {
					li.siblings().removeClass('selectBox-selected');
					li.addClass('selectBox-selected');
				}

				if( control.hasClass('selectBox-dropdown') ) {
					control.find('.selectBox-label').text(li.text());
				}

				var i = 0, selection = [];
				if( select.attr('multiple') ) {
					control.find('.selectBox-selected A').each( function() {
						selection[i++] = $(this).attr('rel');
					});
				} else {
					selection = li.find('A').attr('rel');
				}
				
				//control.data('selectBox-last-selected', li);

				if( select.val() !== selection ) {
					select.val(selection);
					setLabel(select);
					select.trigger('change');
				}

				return true;

			};


			var addHover = function(select, li) {
				select = $(select);
				li = $(li);
				var control = select.data('selectBox-control'),
					options = control.data('selectBox-options');

				options.find('.selectBox-hover').removeClass('selectBox-hover');
				li.addClass('selectBox-hover');
			};


			var removeHover = function(select, li) {
				select = $(select);
				li = $(li);
				var control = select.data('selectBox-control'),
					options = control.data('selectBox-options');
				options.find('.selectBox-hover').removeClass('selectBox-hover');
			};


			var keepOptionInView = function(select, li, center) {

				if( !li || li.length === 0 ) return;

				select = $(select);
				var control = select.data('selectBox-control'),
					options = control.data('selectBox-options'),
					scrollBox = control.hasClass('selectBox-dropdown') ? options : options.parent(),
					top = parseInt(li.offset().top - scrollBox.position().top),
					bottom = parseInt(top + li.outerHeight());

				if( center ) {
					scrollBox.scrollTop( li.offset().top - scrollBox.offset().top + scrollBox.scrollTop() - (scrollBox.height() / 2) );
				} else {
					if( top < 0 ) {
						scrollBox.scrollTop( li.offset().top - scrollBox.offset().top + scrollBox.scrollTop() );
					}
					if( bottom > scrollBox.height() ) {
						scrollBox.scrollTop( (li.offset().top + li.outerHeight()) - scrollBox.offset().top + scrollBox.scrollTop() - scrollBox.height() );
					}
				}

			};


			var handleKeyDown = function(select, event) {

				select = $(select);
				var control = select.data('selectBox-control'),
					options = control.data('selectBox-options'),
					settings = select.data('selectBox-settings'),
					totalOptions = 0,
					i = 0;

				if( control.hasClass('selectBox-disabled') ) return;

				switch( event.keyCode ) {

					case 8:
						event.preventDefault();
						typeSearch = '';
						break;

					case 9: 
					case 27: 
						hideMenus();
						removeHover(select);
						break;

					case 13: 
						if( control.hasClass('selectBox-menuShowing') ) {
							selectOption(select, options.find('LI.selectBox-hover:first'), event);
							if( control.hasClass('selectBox-dropdown') ) hideMenus();
						} else {
							showMenu(select);
						}
						break;

					case 38:
					case 37:

						event.preventDefault();

						if( control.hasClass('selectBox-menuShowing') ) {

							var prev = options.find('.selectBox-hover').prev('LI');
							totalOptions = options.find('LI:not(.selectBox-optgroup)').length;
							i = 0;

							while( prev.length === 0 || prev.hasClass('selectBox-disabled') || prev.hasClass('selectBox-optgroup') ) {
								prev = prev.prev('LI');
								if( prev.length === 0 ) {
									if (settings.loopOptions) {
										prev = options.find('LI:last');
									} else {
										prev = options.find('LI:first');
									}
								}
								if( ++i >= totalOptions ) break;
							}

							addHover(select, prev);
							selectOption(select, prev, event);
							keepOptionInView(select, prev);

						} else {
							showMenu(select);
						}

						break;

					case 40: 
					case 39: 

						event.preventDefault();

						if( control.hasClass('selectBox-menuShowing') ) {

							var next = options.find('.selectBox-hover').next('LI');
							totalOptions = options.find('LI:not(.selectBox-optgroup)').length;
							i = 0;

							while( next.length === 0 || next.hasClass('selectBox-disabled') || next.hasClass('selectBox-optgroup') ) {
								next = next.next('LI');
								if( next.length === 0 ) {
									if (settings.loopOptions) {
										next = options.find('LI:first');
									} else {
										next = options.find('LI:last');
									}
								}
								if( ++i >= totalOptions ) break;
							}

							addHover(select, next);
							selectOption(select, next, event);
							keepOptionInView(select, next);

						} else {
							showMenu(select);
						}

						break;

				}

			};


			var handleKeyPress = function(select, event) {

				select = $(select);
				var control = select.data('selectBox-control'),
					options = control.data('selectBox-options');

				if( control.hasClass('selectBox-disabled') ) return;

				switch( event.keyCode ) {

					case 9:
					case 27: 
					case 13: 
					case 38:
					case 37: 
					case 40: 
					case 39:
						break;

					default:

						if( !control.hasClass('selectBox-menuShowing') ) showMenu(select);

						event.preventDefault();

						clearTimeout(typeTimer);
						typeSearch += String.fromCharCode(event.charCode || event.keyCode);

						options.find('A').each( function() {
							if( $(this).text().substr(0, typeSearch.length).toLowerCase() === typeSearch.toLowerCase() ) {
								addHover(select, $(this).parent());
								keepOptionInView(select, $(this).parent());
								return false;
							}
						});

						typeTimer = setTimeout( function() { typeSearch = ''; }, 1000);

						break;

				}

			};


			var enable = function(select) {
				select = $(select);
				select.attr('disabled', false);
				var control = select.data('selectBox-control');
				if( !control ) return;
				control.removeClass('selectBox-disabled');
			};


			var disable = function(select) {
				select = $(select);
				select.attr('disabled', true);
				var control = select.data('selectBox-control');
				if( !control ) return;
				control.addClass('selectBox-disabled');
			};


			var setValue = function(select, value) {
				select = $(select);
				select.val(value);
				value = select.val();
				var control = select.data('selectBox-control');
				if( !control ) return;
				var settings = select.data('selectBox-settings'),
					options = control.data('selectBox-options');

				setLabel(select);
				
				options.find('.selectBox-selected').removeClass('selectBox-selected');
				options.find('A').each( function() {
					if( typeof(value) === 'object' ) {
						for( var i = 0; i < value.length; i++ ) {
							if( $(this).attr('rel') == value[i] ) {
								$(this).parent().addClass('selectBox-selected');
							}
						}
					} else {
						if( $(this).attr('rel') == value ) {
							$(this).parent().addClass('selectBox-selected');
						}
					}
				});

				if( settings.change ) settings.change.call(select);

			};


			var setOptions = function(select, options) {

				select = $(select);
				var control = select.data('selectBox-control'),
					settings = select.data('selectBox-settings');

				switch( typeof(data) ) {

					case 'string':
						select.html(data);
						break;

					case 'object':
						select.html('');
						for( var i in data ) {
							if( data[i] === null ) continue;
							if( typeof(data[i]) === 'object' ) {
								var optgroup = $('<optgroup label="' + i + '" />');
								for( var j in data[i] ) {
									optgroup.append('<option value="' + j + '">' + data[i][j] + '</option>');
								}
								select.append(optgroup);
							} else {
								var option = $('<option value="' + i + '">' + data[i] + '</option>');
								select.append(option);
							}
						}
						break;

				}

				if( !control ) return;

				control.data('selectBox-options').remove();

				var type = control.hasClass('selectBox-dropdown') ? 'dropdown' : 'inline';
				options = getOptions(select, type);
				control.data('selectBox-options', options);

				switch( type ) {
					case 'inline':
						control.append(options);
						break;
					case 'dropdown':
						setLabel(select);
						$("BODY").append(options);
						break;
				}

			};


			var disableSelection = function(selector) {
				$(selector)
					.css('MozUserSelect', 'none')
					.bind('selectstart', function(event) {
						event.preventDefault();
					});
			};

			var generateOptions = function(self, options){
				var li = $('<li />'),
				a = $('<a />');
				li.addClass( self.attr('class') );
				li.data( self.data() );
				a.attr('rel', self.val()).text( self.text() );
				li.append(a);
				if( self.attr('disabled') ) li.addClass('selectBox-disabled');
				if( self.attr('selected') ) li.addClass('selectBox-selected');
				options.append(li);
			};

			switch( method ) {

				case 'control':
					return $(this).data('selectBox-control');

				case 'settings':
					if( !data ) return $(this).data('selectBox-settings');
					$(this).each( function() {
						$(this).data('selectBox-settings', $.extend(true, $(this).data('selectBox-settings'), data));
					});
					break;

				case 'options':
					if( data === undefined ) return $(this).data('selectBox-control').data('selectBox-options');
					$(this).each( function() {
						setOptions(this, data);
					});
					break;

				case 'value':
					if( data === undefined ) return $(this).val();
					$(this).each( function() {
						setValue(this, data);
					});
					break;
				
				case 'refresh':
					$(this).each( function() {
						refresh(this);
					});
					break;

				case 'enable':
					$(this).each( function() {
						enable(this);
					});
					break;

				case 'disable':
					$(this).each( function() {
						disable(this);
					});
					break;

				case 'destroy':
					$(this).each( function() {
						destroy(this);
					});
					break;

				case 'onReady':
					if($.isFunction(data)) {
						data.call(this);
					}
					break;

				default:
					$(this).each( function() {
						init(this, method);
					});
					break;

			}

			return $(this);

		}

	});

})(jQuery);

/* jquery custom fade */
(function ($) {
    $.fn.customFadeIn = function (speed, callback) {
        $(this).fadeIn(speed, function () {
            if (jQuery.browser.msie) $(this).get(0).style.removeAttribute('filter');
            if (callback != undefined) callback();
        });
    };
    $.fn.customFadeOut = function (speed, callback) {
        $(this).fadeOut(speed, function () {
            if (jQuery.browser.msie) $(this).get(0).style.removeAttribute('filter');
            if (callback != undefined) callback();
        });
    };
    $.fn.customFadeTo = function (speed, callback) {
        $(this).fadeTo(speed, function () {
            if (jQuery.browser.msie) $(this).get(0).style.removeAttribute('filter');
            if (callback != undefined) callback();
        });
    };
    $.fn.customToggle = function (speed, callback) {
        $(this).toggle(speed, function () {
            if (jQuery.browser.msie) $(this).get(0).style.removeAttribute('filter');
            if (callback != undefined) callback();
        });
    };
})(jQuery);

/* jquery tooltip */
(function($){
	$.fn.simpletooltip=function(options){
		$('#tooltip').remove();
		$("<div id='tooltip'></div>").appendTo("body");
		
		$(this).each(function(i, element){
			if($(element).attr('title')) {
				jQuery.data(element, 'tip_text', $(element).attr('title'));
				$(element).hover(function(event) {
					rel = $(this).attr("title");
					twidth = parseInt($("#tooltip").width());
					window_width = parseInt($(window).width());
					theight = parseInt($("#tooltip").height());
					window_height = parseInt($(window).height());
					if((event.pageX + twidth) < (window_width-twidth)) {
						tleft = event.pageX +15;
					} else {
						tleft = event.pageX -(twidth+15);
					}
					if((event.pageY + theight + 15) < (window_height-theight)) {
						ttop = event.pageY +15;
					} else {
						ttop = event.pageY -(theight+15);
					}
					$('#tooltip')
					.css("position", "absolute")
					.css("top", ttop)
					.css("left", tleft)
					.css('max-width', 250)
					.show().html(jQuery.data(element, 'tip_text'));
					$(this).removeAttr("title");
				}, function() {
					$("#tooltip").hide();
				});
				
				$(element).mousemove(function(event) {
					twidth = parseInt($("#tooltip").width());
					window_width = parseInt($(window).width());
					theight = parseInt($("#tooltip").height());
					window_height = parseInt($(window).height());
					if((event.pageX + twidth+15) < (window_width-15)) {
						tleft = event.pageX +15;
					} else {
						tleft = event.pageX -(twidth+15);
					}
					if((event.pageY + theight + 15) < (window_height-15)) {
						ttop = event.pageY +15;
					} else {
						ttop = event.pageY -(theight+15);
					}
					$("#tooltip").css("top", ttop).css("left", tleft);
				});
			}
		});
		
	};
})(jQuery);

/* jquery livesearch */
(function($) {  
    var self = null;
     
    $.fn.liveUpdate = function(list, searchin) {        
            return this.each(function() {
                    new $.liveUpdate(this, list, searchin);
            });
    };
    
    $.liveUpdate = function (e, list, searchin) {
            this.field = $(e);
            this.list  = $(list);
            this.searchin = searchin;
            if (this.list.length > 0) {
                    this.init();
            }
    };
    
    $.liveUpdate.prototype = {
            init: function() {
                    var self = this;
                    this.setupCache();
                    this.field.parents('form').submit(function() { return false; });
                    this.field.keyup(function() { self.filter(); });
                    self.filter();
            },
            
            filter: function() {
                    if ($.trim(this.field.val()) == '') { this.list.children('li').show(); return; }
                    this.displayResults(this.getScores(this.field.val().toLowerCase()));
            },
            
            setupCache: function() {
                    var self = this;
                    this.cache = [];
                    this.rows = [];
                    this.list.children('li').each(function() {
                        	if(self.searchin) { 
                        		text = $(self.searchin,this).get(0).innerHTML;
                        	} else {
                        		text = this.innerHTML;
                        	}
                            self.cache.push(new String(text.toLowerCase()));
                            self.rows.push($(this));
                    });
                    this.cache_length = this.cache.length;
            },
            
            displayResults: function(scores) {
                    var self = this;
                    this.list.children('li').hide();
                    $.each(scores, function(i, score) { self.rows[score[1]].show(); });
            },
            
            getScores: function(term) {
                    var scores = [];
                    for (var i=0; i < this.cache_length; i++) {
                    		var score = this.cache[i].search(new RegExp(term, "i"));//this.cache[i].score(term);
                            if (score > -1) { scores.push([score, i]); }
                    }
                    return scores.sort(function(a, b) { return b[0] - a[0]; });
            }
    }
})(jQuery);

/* jquery jNotify */
(function($){

	$.jNotify = {
		defaults: {
			autoHide : true,				
			clickOverlay : false,			
			MinWidth : 200,				
			TimeShown : 1500, 				
			ShowTimeEffect : 200, 			
			HideTimeEffect : 200, 			
			LongTrip : 15,					
			HorizontalPosition : 'right', 	
			VerticalPosition : 'bottom',	
			ShowOverlay : true,				
			ColorOverlay : '#000',			
			OpacityOverlay : 0.3,			
			ShowCloseButton: true,
			
			onClosed : null,
			onCompleted : null
		},

		init:function(msg, options, id) {
			opts = $.extend({}, $.jNotify.defaults, options);
			
			if(opts.ShowCloseButton) {
				opts.autoHide = false;
			}

			if($("#"+id).length == 0)
				$Div = $.jNotify._construct(id, msg);

			WidthDoc = parseInt($(window).width());
			HeightDoc = parseInt($(window).height());

			ScrollTop = parseInt($(window).scrollTop());
			ScrollLeft = parseInt($(window).scrollLeft());

			posTop = $.jNotify.vPos(opts.VerticalPosition);
			posLeft = $.jNotify.hPos(opts.HorizontalPosition);

			if(opts.ShowOverlay && $("#jOverlay").length == 0)
				$.jNotify._showOverlay($Div);

			$.jNotify._show(msg);
		},

		_construct:function(id, msg) {
			$Div = 
			$('<div id="'+id+'" class="notifications"/>')
			.css({opacity : 0,minWidth : opts.MinWidth})
			.html(msg)
			.appendTo('body');
			
			if(opts.ShowCloseButton) {
				$Close = $('<span class="close-button" />').click(function(e){
					e.preventDefault();
					opts.TimeShown = 0;
					$.jNotify._close();
				});
				$Div.append($Close);
			}
			
			return $Div;
		},

		vPos:function(pos) {
			switch(pos) {
				case 'top':
					var vPos = ScrollTop + parseInt($Div.outerHeight(true)/2);
					break;
				case 'center':
					var vPos = ScrollTop + (HeightDoc/2) - (parseInt($Div.outerHeight(true))/2);
					break;
				case 'bottom':
					var vPos = ScrollTop + HeightDoc - parseInt($Div.outerHeight(true));
					break;
			}
			return vPos;
		},

		hPos:function(pos) {
			switch(pos) {
				case 'left':
					var hPos = ScrollLeft;
					break;
				case 'center':
					var hPos = ScrollLeft + (WidthDoc/2) - (parseInt($Div.outerWidth(true))/2);
					break;
				case 'right':
					var hPos = ScrollLeft + WidthDoc - parseInt($Div.outerWidth(true));
					break;
			}
			return hPos;
		},

		_show:function(msg) {
			$Div
			.css({
				top: posTop,
				left : posLeft
			});
			switch (opts.VerticalPosition) {
				case 'top':
					$Div.animate({
						top: posTop + opts.LongTrip,
						opacity:1
					},opts.ShowTimeEffect,function(){
						if(opts.onCompleted) opts.onCompleted();
					});
					if(opts.autoHide)
						$.jNotify._close();
					else
						$Div.css('cursor','pointer').click(function(e){
							$.jNotify._close();
						});
					break;
				case 'center':
					$Div.animate({
						opacity:1
					},opts.ShowTimeEffect,function(){
						if(opts.onCompleted) opts.onCompleted();
					});
					if(opts.autoHide)
						$.jNotify._close();
					else
						$Div.css('cursor','pointer').click(function(e){
							$.jNotify._close();
						});
					break;
				case 'bottom' :
					$Div.animate({
						top: posTop - opts.LongTrip,
						opacity:1
					},opts.ShowTimeEffect,function(){
						if(opts.onCompleted) opts.onCompleted();
					});
					if(opts.autoHide)
						$.jNotify._close();
					else
						$Div.css('cursor','pointer').click(function(e){
							$.jNotify._close();
						});
					break;
			}
		},

		_showOverlay:function(el){
			var overlay = 
			$('<div id="jOverlay" class="notifications" />')
			.css({
				backgroundColor : opts.ColorOverlay,
				opacity: opts.OpacityOverlay
			})
			.appendTo('body')
			.show();

			if(opts.clickOverlay)
			overlay.click(function(e){
				e.preventDefault();
				opts.TimeShown = 0;
				$.jNotify._close();
			});
		},


		_close:function(){
				switch (opts.VerticalPosition) {
					case 'top':
						if(!opts.autoHide)
							opts.TimeShown = 0;
						$Div.stop(true, true).delay(opts.TimeShown).animate({
							top: posTop-opts.LongTrip,
							opacity:0
						},opts.HideTimeEffect,function(){
							$(this).remove();
							if(opts.ShowOverlay && $("#jOverlay").length > 0)
								$("#jOverlay").remove();
								if(opts.onClosed) opts.onClosed();
						});
						break;
					case 'center':
						if(!opts.autoHide)
							opts.TimeShown = 0;
						$Div.stop(true, true).delay(opts.TimeShown).animate({
							opacity:0
						},opts.HideTimeEffect,function(){
							$(this).remove();
							if(opts.ShowOverlay && $("#jOverlay").length > 0)
								$("#jOverlay").remove();
								if(opts.onClosed) opts.onClosed();
						});
						break;
					case 'bottom' :
						if(!opts.autoHide)
							opts.TimeShown = 0;
						$Div.stop(true, true).delay(opts.TimeShown).animate({
							top: posTop+opts.LongTrip,
							opacity:0
						},opts.HideTimeEffect,function(){
							$(this).remove();
							if(opts.ShowOverlay && $("#jOverlay").length > 0)
								$("#jOverlay").remove();
								if(opts.onClosed) opts.onClosed();
						});
						break;
				}
		},

		_isReadable:function(id){
			if($('#'+id).length > 0)
				return false;
			else
				return true;
		}
	};

	jNotify = function(msg,options) {
		if($.jNotify._isReadable('jNotify'))
			$.jNotify.init(msg,options,'jNotify');
	};

	jSuccess = function(msg,options) {
		if($.jNotify._isReadable('jSuccess'))
			$.jNotify.init(msg,options,'jSuccess');
	};

	jError = function(msg,options) {
		if($.jNotify._isReadable('jError'))
			$.jNotify.init(msg,options,'jError');
	};
})(jQuery);

/* jquery AJAX Upload */
(function () {

    function log(){
        try
        {
            if (typeof(console) != 'undefined' && typeof(console.log) == 'function')
            {            
                Array.prototype.unshift.call(arguments, '[Ajax Upload]');
                console.log( Array.prototype.join.call(arguments, ' '));
            }
        }
        catch(e)
        {
            if (window.console // check for window.console not console
                && window.console.log)
            {
                if (typeof(window.console) != 'undefined' && typeof(window.console.log) == 'function')
                {            
                    Array.prototype.unshift.call(arguments, '[Ajax Upload]');
                    window.console.log( Array.prototype.join.call(arguments, ' '));

                }
            }
             
        }
    } 

    function addEvent(el, type, fn){
        if (el.addEventListener) {
            el.addEventListener(type, fn, false);
        } else if (el.attachEvent) {
            el.attachEvent('on' + type, function(){
                fn.call(el);
	        });
	    } else {
            throw new Error('not supported or DOM not loaded');
        }
    }   
    
    function addResizeEvent(fn){
        var timeout;
               
	    addEvent(window, 'resize', function(){
            if (timeout){
                clearTimeout(timeout);
            }
            timeout = setTimeout(fn, 100);                        
        });
    }    
    
    if (document.documentElement.getBoundingClientRect){

        var getOffset = function(el){
            var box = el.getBoundingClientRect();
            var doc = el.ownerDocument;
            var body = doc.body;
            var docElem = doc.documentElement; 
            var clientTop = docElem.clientTop || body.clientTop || 0;
            var clientLeft = docElem.clientLeft || body.clientLeft || 0;
 
            var zoom = 1;            
            if (body.getBoundingClientRect) {
                var bound = body.getBoundingClientRect();
                zoom = (bound.right - bound.left) / body.clientWidth;
            }
            
            if (zoom > 1) {
                clientTop = 0;
                clientLeft = 0;
            }
            
            var top = box.top / zoom + (window.pageYOffset || docElem && docElem.scrollTop / zoom || body.scrollTop / zoom) - clientTop, left = box.left / zoom + (window.pageXOffset || docElem && docElem.scrollLeft / zoom || body.scrollLeft / zoom) - clientLeft;
            
            return {
                top: top,
                left: left
            };
        };        
    } else {

        var getOffset = function(el){
            var top = 0, left = 0;
            do {
                top += el.offsetTop || 0;
                left += el.offsetLeft || 0;
                el = el.offsetParent;
            } while (el);
            
            return {
                left: left,
                top: top
            };
        };
    }
    
    function getBox(el){
        var left, right, top, bottom;
        var offset = getOffset(el);
        left = offset.left;
        top = offset.top;
        
        right = left + el.offsetWidth;
        bottom = top + el.offsetHeight;
        
        return {
            left: left,
            right: right,
            top: top,
            bottom: bottom
        };
    }
    
    function addStyles(el, styles){
        for (var name in styles) {
            if (styles.hasOwnProperty(name)) {
                el.style[name] = styles[name];
            }
        }
    }
        
    function copyLayout(from, to){
	    var box = getBox(from);
        
        addStyles(to, {
	        position: 'absolute',                    
	        left : box.left + 'px',
	        top : box.top + 'px',
	        width : from.offsetWidth + 'px',
	        height : from.offsetHeight + 'px'
	    });        
    }

    var toElement = (function(){
        var div = document.createElement('div');
        return function(html){
            div.innerHTML = html;
            var el = div.firstChild;
            return div.removeChild(el);
        };
    })();
           
    var getUID = (function(){
        var id = 0;
        return function(){
            return 'ValumsAjaxUpload' + id++;
        };
    })();        
 
    function fileFromPath(file){
        return file.replace(/.*(\/|\\)/, "");
    }
     
    function getExt(file){
        return (-1 !== file.indexOf('.')) ? file.replace(/.*[.]/, '') : '';
    }

    function hasClass(el, name){        
        var re = new RegExp('\\b' + name + '\\b');        
        return re.test(el.className);
    }    
    function addClass(el, name){
        if ( ! hasClass(el, name)){   
            el.className += ' ' + name;
        }
    }    
    function removeClass(el, name){
        var re = new RegExp('\\b' + name + '\\b');                
        el.className = el.className.replace(re, '');        
    }
    
    function removeNode(el){
        el.parentNode.removeChild(el);
    }

    window.AjaxUpload = function(button, options){
        this._settings = {
            action: 'upload.php',
            name: 'userfile',
            data: {},
            autoSubmit: true,
            responseType: false,
            hoverClass: 'hover',
            disabledClass: 'disabled',            		
            onChange: function(file, extension){
            },
            onSubmit: function(file, extension){
            },
            onComplete: function(file, response){
            }
        };
                        
        for (var i in options) {
            if (options.hasOwnProperty(i)){
                this._settings[i] = options[i];
            }
        }
        
        if (button.jquery){
            button = button[0];
        } else if (typeof button == "string") {
            if (/^#.*/.test(button)){				
                button = button.slice(1);                
            }
            
            button = document.getElementById(button);
        }
        
        if ( ! button || button.nodeType !== 1){
            throw new Error("Please make sure that you're passing a valid element"); 
        }
                
        if ( button.nodeName.toUpperCase() == 'A'){
            addEvent(button, 'click', function(e){
                if (e && e.preventDefault){
                    e.preventDefault();
                } else if (window.event){
                    window.event.returnValue = false;
                }
            });
        }
                    
        this._button = button;           
        this._input = null;
        this._disabled = false;
        
        this.enable();        
        
        this._rerouteClicks();
    };
    
    AjaxUpload.prototype = {
        setData: function(data){
            this._settings.data = data;
        },
        disable: function(){            
            addClass(this._button, this._settings.disabledClass);
            this._disabled = true;
            
            var nodeName = this._button.nodeName.toUpperCase();            
            if (nodeName == 'INPUT' || nodeName == 'BUTTON'){
                this._button.setAttribute('disabled', 'disabled');
            }            
            
            if (this._input){       
                this._input.parentNode.style.visibility = 'hidden';
            }
        },
        enable: function(){
            removeClass(this._button, this._settings.disabledClass);
            this._button.removeAttribute('disabled');
            this._disabled = false;
            
        },
        _createInput: function(){ 
            var self = this;
                        
            var input = document.createElement("input");
            input.setAttribute('type', 'file');
            input.setAttribute('name', this._settings.name);
            
            addStyles(input, {
                'position' : 'absolute',
                'right' : 0,
                'margin' : 0,
                'padding' : 0,
                'fontSize' : '480px',                
                'cursor' : 'pointer'
            });            

            var div = document.createElement("div");                        
            addStyles(div, {
                'display' : 'block',
                'position' : 'absolute',
                'overflow' : 'hidden',
                'margin' : 0,
                'padding' : 0,                
                'opacity' : 0,
                'direction' : 'ltr',
                'zIndex': 2147483583
            });
                   
            if ( div.style.opacity !== "0") {
                if (typeof(div.filters) == 'undefined'){
                    throw new Error('Opacity not supported by the browser');
                }
                div.style.filter = "alpha(opacity=0)";
            }            
            
            addEvent(input, 'change', function(){
                 
                if ( ! input || input.value === ''){                
                    return;                
                }
                                     
                var file = fileFromPath(input.value);
                                
                if (false === self._settings.onChange.call(self, file, getExt(file))){
                    self._clearInput();                
                    return;
                }
                
                if (self._settings.autoSubmit) {
                    self.submit();
                }
            });            

            addEvent(input, 'mouseover', function(){
                addClass(self._button, self._settings.hoverClass);
            });
            
            addEvent(input, 'mouseout', function(){
                removeClass(self._button, self._settings.hoverClass);
                         
                input.parentNode.style.visibility = 'hidden';

            });   
                        
	        div.appendChild(input);
            document.body.appendChild(div);
              
            this._input = input;
        },
        _clearInput : function(){
            if (!this._input){
                return;
            }            
                                                            
            removeNode(this._input.parentNode);
            this._input = null;                                                                   
            this._createInput();
            
            removeClass(this._button, this._settings.hoverClass);
        },
        _rerouteClicks: function(){
            var self = this;
            
            addEvent(self._button, 'mouseover', function(){
                if (self._disabled){
                    return;
                }
                                
                if ( ! self._input){
	                self._createInput();
                }
                
                var div = self._input.parentNode;                            
                copyLayout(self._button, div);
                div.style.visibility = 'visible';
                                
            });
                 
                                         
        },
        _createIframe: function(){
            var id = getUID();            
                                    
 
            var iframe = toElement('<iframe src="javascript:false;" name="' + id + '" />');     
            iframe.setAttribute('id', id);
            
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
            
            return iframe;
        },
        _createForm: function(iframe){
            var settings = this._settings;
                                           
            var form = toElement('<form method="post" enctype="multipart/form-data"></form>');
                        
            form.setAttribute('action', settings.action);
            form.setAttribute('target', iframe.name);                                   
            form.style.display = 'none';
            document.body.appendChild(form);
            
            for (var prop in settings.data) {
                if (settings.data.hasOwnProperty(prop)){
                    var el = document.createElement("input");
                    el.setAttribute('type', 'hidden');
                    el.setAttribute('name', prop);
                    el.setAttribute('value', settings.data[prop]);
                    form.appendChild(el);
                }
            }
            return form;
        },
        _getResponse : function(iframe, file){    
            var toDeleteFlag = false, self = this, settings = this._settings;   
               
            addEvent(iframe, 'load', function(){                
                
                if (
                    iframe.src == "javascript:'%3Chtml%3E%3C/html%3E';" ||
                    iframe.src == "javascript:'<html></html>';"){                                                                        
                       
                        if (toDeleteFlag) {
                            setTimeout(function(){
                                removeNode(iframe);
                            }, 0);
                        }
                                                
                        return;
                }
                
                var doc = iframe.contentDocument ? iframe.contentDocument : window.frames[iframe.id].document;
                
                if (doc.readyState && doc.readyState != 'complete') {
                   return;
                }
                
                if (doc.body && doc.body.innerHTML == "false") {
                    return;
                }
                
                var response;
                
                if (doc.XMLDocument) {
                    response = doc.XMLDocument;
                } else if (doc.body){
                	if(jQuery) {
                		response = $('body', doc).html();
                	} else {
                		response = doc.body.innerHTML;
                	}
                    
                    if (settings.responseType && settings.responseType.toLowerCase() == 'json') {
                        if (doc.body.firstChild && doc.body.firstChild.nodeName.toUpperCase() == 'PRE') {
                            response = doc.body.firstChild.firstChild.nodeValue;
                        }
                        
                        
                        if (response) {
                        	try {
	                        	if(JSON) {
	                        		response = JSON.parse(response);
	                        	} else if(jQuery) {
	                        		response = jQuery.parseJSON(response);
	                        	} else {
	                        		response = eval("(" + response + ")");
	                        	}
                        	} catch (err) {
                        		jQuery.error( "Invalid JSON: " + err );
                        	}
                        } else {
                            response = {};
                        }
                    }
                } else {
                    response = doc;
                }
                
                settings.onComplete.call(self, file, response);
                toDeleteFlag = true;
                
                iframe.src = "javascript:'<html></html>';";
            });            
        },        
        submit: function(){                        
            var self = this, settings = this._settings;
            
            if ( ! this._input || this._input.value === ''){                
                return;                
            }
                                    
            var file = fileFromPath(this._input.value);
            
            if (false === settings.onSubmit.call(this, file, getExt(file))){
                this._clearInput();                
                return;
            }
              
            var iframe = this._createIframe();
            var form = this._createForm(iframe);
            
            removeNode(this._input.parentNode);            
            removeClass(self._button, self._settings.hoverClass);
                        
            form.appendChild(this._input);
                        
            form.submit();
          
            removeNode(form); form = null;                          
            removeNode(this._input); this._input = null;
            
            this._getResponse(iframe, file);            
  
            this._createInput();
        }
    };
})(); 

/* jquery dragsort */
(function($) {

	$.fn.dragsort = function(options) {
		
		var opts = $.extend({}, $.fn.dragsort.defaults, options);
		var lists = new Array();
		var list = null, lastPos = null;

		this.each(function(i, cont) {
	
			if ($(cont).is("table") && $(cont).children().size() == 1 && $(cont).children().is("tbody"))
				cont = $(cont).children().get(0);
	
			var newList = {
				draggedItem: null,
				placeHolderItem: null,
				pos: null,
				offset: null,
				offsetLimit: null,
				container: cont,
	
				init: function() {
					var lists = new Array();
					
					if(options == 'remove') {
						opts = $(this.container).data('sort_order');
						$(this.container).unbind('mousedown').find(opts.dragSelector).removeClass("drag-enabled");
						return;
					}
					
					$(this.container).data('sort_order', opts).attr("listIdx", i).mousedown(this.grabItem).find(opts.dragSelector)/*.css("cursor", "pointer")*/.addClass("drag-enabled");
				},
	
				grabItem: function(e) {
					
					var elm = e.target;
			
					if(!$(elm).is("[listIdx=" + $(this).attr("listIdx") + "] " + opts.dragSelector)) {
						elm = $(elm).parents(opts.dragSelector).get(0);
					}
					if (elm == this) return;
					
					if (list != null && list.draggedItem != null)
						list.dropItem();
	
					list = lists[$(this).attr("listIdx")];
					list.draggedItem = $(elm).closest(opts.itemSelector);
					var mt = parseInt(list.draggedItem.css("marginTop"));
					var ml = parseInt(list.draggedItem.css("marginLeft"));
					list.offset = list.draggedItem.offset();
					list.offset.top = e.pageY - list.offset.top + (isNaN(mt) ? 0 : mt) - 1;
					list.offset.left = e.pageX - list.offset.left + (isNaN(ml) ? 0 : ml) - 1;
	
					if (!opts.dragBetween) {
						var containerHeight = $(list.container).outerHeight() == 0 ? Math.max(1, Math.round(0.5 + $(list.container).children(opts.itemSelector).size() * list.draggedItem.outerWidth() / $(list.container).outerWidth())) * list.draggedItem.outerHeight() : $(list.container).outerHeight();
						list.offsetLimit = $(list.container).offset();
						list.offsetLimit.right = list.offsetLimit.left + $(list.container).outerWidth() - list.draggedItem.outerWidth();
						list.offsetLimit.bottom = list.offsetLimit.top + containerHeight - list.draggedItem.outerHeight();
					}
	
					list.draggedItem.css({ position: "absolute", opacity: 0.8, "z-index": 999 }).after(opts.placeHolderTemplate);
					list.placeHolderItem = list.draggedItem.next().css("height", list.draggedItem.height()).attr("placeHolder", true);
	
					$(lists).each(function(i, l) { l.ensureNotEmpty(); l.buildPositionTable(); });
	
					list.setPos(e.pageX, e.pageY);
					$(document).bind("selectstart", list.stopBubble); 
					$(document).bind("mousemove", list.swapItems);
					$(document).bind("mouseup", list.dropItem);
					return false; //stop moz text selection
				},
	
				setPos: function(x, y) {
					var top = y - this.offset.top;
					var left = x - this.offset.left;
	
					if (!opts.dragBetween) {
						top = Math.min(this.offsetLimit.bottom, Math.max(top, this.offsetLimit.top));
						left = Math.min(this.offsetLimit.right, Math.max(left, this.offsetLimit.left));
					}
	
					this.draggedItem.parents().each(function() {
						if ($(this).css("position") != "static" && (!$.browser.mozilla || $(this).css("display") != "table")) {
							var offset = $(this).offset();
							top -= offset.top;
							left -= offset.left;
							return false;
						}
					});
	
					this.draggedItem.css({ top: top, left: left });
				},
	
				buildPositionTable: function() {
					var item = this.draggedItem == null ? null : this.draggedItem.get(0);
					var pos = new Array();
					$(this.container).children(opts.itemSelector).each(function(i, elm) {
						if (elm != item) {
							var loc = $(elm).offset();
							loc.right = loc.left + $(elm).width();
							loc.bottom = loc.top + $(elm).height();
							loc.elm = elm;
							pos.push(loc);
						}
					});
					this.pos = pos;
				},
	
				dropItem: function() {
					if (list.draggedItem == null)
						return;
	
					list.placeHolderItem.before(list.draggedItem);
	
					list.draggedItem.css({ position: "", top: "", left: "", opacity: "", "z-index": "" });
					list.placeHolderItem.remove();
	
					$("*[emptyPlaceHolder]").remove();
	
					opts.dragEnd.apply(list.draggedItem);
					list.draggedItem = null;
					$(document).unbind("selectstart", list.stopBubble);
					$(document).unbind("mousemove", list.swapItems);
					$(document).unbind("mouseup", list.dropItem);
					return false;
				},
	
				stopBubble: function() { return false; },
	
				swapItems: function(e) {
					if (list.draggedItem == null)
						return false;
	
					list.setPos(e.pageX, e.pageY);
	
					var ei = list.findPos(e.pageX, e.pageY);
					var nlist = list;
					for (var i = 0; ei == -1 && opts.dragBetween && i < lists.length; i++) {
						ei = lists[i].findPos(e.pageX, e.pageY);
						nlist = lists[i];
					}
	
					if (ei == -1 || $(nlist.pos[ei].elm).attr("placeHolder"))
						return false;
	
					if (lastPos == null || lastPos.top > list.draggedItem.offset().top || lastPos.left > list.draggedItem.offset().left)
						$(nlist.pos[ei].elm).before(list.placeHolderItem);
					else
						$(nlist.pos[ei].elm).after(list.placeHolderItem);
	
					$(lists).each(function(i, l) { l.ensureNotEmpty(); l.buildPositionTable(); });
					lastPos = list.draggedItem.offset();
					return false;
				},
	
				findPos: function(x, y) {
					for (var i = 0; i < this.pos.length; i++) {
						if (this.pos[i].left < x && this.pos[i].right > x && this.pos[i].top < y && this.pos[i].bottom > y)
							return i;
					}
					return -1;
				},
	
				ensureNotEmpty: function() {
					if (!opts.dragBetween)
						return;
	
					var item = this.draggedItem == null ? null : this.draggedItem.get(0);
					var emptyPH = null, empty = true;
	
					$(this.container).children(opts.itemSelector).each(function(i, elm) {
						if ($(elm).attr("emptyPlaceHolder"))
							emptyPH = elm;
						else if (elm != item)
							empty = false;
					});
	
					if (empty && emptyPH == null)
						$(this.container).append(opts.placeHolderTemplate).children(":last").attr("emptyPlaceHolder", true);
					else if (!empty && emptyPH != null)
						$(emptyPH).remove();
				}
			};
	
			newList.init();
			lists.push(newList);
		});
	
		return this;
	};
	
	$.fn.dragsort.defaults = {
		itemSelector: "li",
		dragSelector: "li",
		dragSelectorExclude: "input, a[href]",
		dragEnd: function() { },
		dragBetween: false,
		placeHolderTemplate: "<li>&nbsp;</li>"
	};

})(jQuery);

/* jquery switcherSlider */
var number_id = 1;
(function($){
	
	$.fn.switcherSlider = function(options){
		if(typeof options == 'string') {
			var action = options;
			var options = $.fn.switcherSlider.defaults;
		} else {
			var options = $.extend({}, $.fn.switcherSlider.defaults, options);
			var action = 'init';
		}
		
		var switcherSlider = {
			init: function(element, number){
				var element_id = options.sysId.replace('%d', number);
				var element = $(element);
				if( !element.data('switcherSlider.init') ) 
                                {
					element.data('switcherSlider',options).data('switcherSlider.init', element_id).hide();
                                        /*
                                        if ($.browser.msie)
                                        {
                                            if ($.browser.version<=8)
                                            { 
                                                var stage = $('<span class="stage" id="' + element_id + '"></span>').insertAfter(element);
                                                if(element.is(':checked')) {
                                                        var slider_button = stage.append('<span class="slider-button on">' + options.text.on + '</span>');
                                                } else {
                                                        var slider_button = stage.append('<span class="slider-button">' + options.text.off + '</span>');
                                                }
                                            }       
                                            else
                                            {
                                                var stage = $('<span class="stage" id="' + element_id + '">').insertAfter(element);
                                                if(element.is(':checked')) {
                                                        var slider_button = stage.append('<span class="slider-button on">' + options.text.on + '</span>');
                                                } else {
                                                        var slider_button = stage.append('<span class="slider-button">' + options.text.off + '</span>');
                                                }

                                            }
                                        }
                                        else
                                        {
                                            var stage = $('<span class="stage" id="' + element_id + '">').insertAfter(element);
                                            if(element.is(':checked')) {
                                                    var slider_button = stage.append('<span class="slider-button on">' + options.text.on + '</span>');
                                            } else {
                                                    var slider_button = stage.append('<span class="slider-button">' + options.text.off + '</span>');
                                            }

                                        }
*/
                                        var stage = $('<span class="stage" id="' + element_id + '"></span>').insertAfter(element);
                                        if(element.is(':checked')) {
                                                var slider_button = stage.append('<span class="slider-button on">' + options.text.on + '</span>');
                                        } else {
                                                var slider_button = stage.append('<span class="slider-button">' + options.text.off + '</span>');
                                        }

					var disabled = element.is(':disabled');

					if(disabled) {
						$('.slider-button', slider_button).addClass('disabled');
					} else {
						$('.slider-button', slider_button).click(function(){
							if( $(this).hasClass('on') ) {

								$(this).animate({"left": $(this).width()}, options.animationSpeed, function(){
									$(this).removeClass('on').html(options.text.off);
							        element.attr('checked', false);
							        options.onSwitch.call(this, {element:element, checked:false});
								});
							} else {
								$(this).animate({"left": "0px"}, options.animationSpeed, function(){
									$(this).addClass('on').html(options.text.on);
							        element.attr('checked', true);
							        options.onSwitch.call(this, {element:element, checked:true});
								});
							}
						});
					}
				}
			}, 
			remove: function(element){
				var element = $(element);
				if(element_id = element.data('switcherSlider.init')) {
					element.data('switcherSlider','').data('switcherSlider.init', '');
					$('#' + element_id).remove();
					element.show();
				}
			}, 
			reload: function(element){
				var element = $(element);
				if(element_id = element.data('switcherSlider.init')) {
					options = $.extend({}, $.fn.switcherSlider.defaults, element.data('switcherSlider'));
					var number = element_id.replace( options.sysId.replace('%d',''), '' );
					this.remove(element);
					this.init(element, number);
				}
			},
			on: function(element, callback){
				var element = $(element);
				if(element_id = element.data('switcherSlider.init')) {
					options = $.extend({}, $.fn.switcherSlider.defaults, element.data('switcherSlider'));
					$('#'+element_id+' .slider-button').animate({"left": "0px"}, options.animationSpeed, function(){
						$(this).addClass('on').html(options.text.on);
				        element.attr('checked', true);
				        if(callback) {
				        	options.onSwitch.call(this, {element:element, checked:true});
				        }
					});
				}
			},
			off: function(element, callback) {
				var element = $(element);
				if(element_id = element.data('switcherSlider.init')) {
					options = $.extend({}, $.fn.switcherSlider.defaults, element.data('switcherSlider'));
					$('#'+element_id+' .slider-button').animate({"left": $(this).width()}, options.animationSpeed, function(){
						$(this).removeClass('on').html(options.text.off);
				        element.attr('checked', false);
				        if(callback) {
				        	options.onSwitch.call(this, {element:element, checked:false});
				        }
					});
				}
			},
			getTotalKey: function(){
				return $('#'+options.sysId).size();
			}
		};
		
		return this.each(function(i){
			switch(action) {
				case 'remove':
					switcherSlider.remove(this);
				break;
				case 'reload':
					switcherSlider.reload(this);
				break;
				case 'on':
					switcherSlider.on(this, true);
				break;
				case 'off':
					switcherSlider.off(this, true);
				break;
				case 'on_without_callback':
					switcherSlider.on(this, false);
				break;
				case 'off_without_callback':
					switcherSlider.off(this, false);
				break;
				default:
					switcherSlider.init(this, number_id);
					number_id++;
				break;
			}
		});
	};
	
	$.fn.switcherSlider.defaults = {
		text : {
			on 	: 'ON',
			off	: 'OFF'
		},
		animationSpeed: "slow",
		onSwitch: function(){},
		sysId: 'checkbox_init_%d'
	};
	
})(jQuery);

/* jquery EasyCharCounter */
(function(a) {
	a.fn.extend({
		jqEasyCounter: function(b) {
			return this.each(function() {
				var f = a(this),
	            e = a.extend({
		            maxChars: 100,
		            maxCharsWarning: 80,
		            msgWarning: "warning",
		            template: 'Characters: {length} / {maxChars}',
		            holder: '.jqEasyCounterMsg'
	            }, b);
			    if (e.maxChars <= 0) {
			      return
			    }
			    var d = a(e.holder);
			    
			    g();
			    
		        f.bind("keydown keyup keypress", g).bind("focus paste", function() {
		        	setTimeout(g, 10);
		        });

		        function g() {
		        	var i = f.val(),
		        	h = i.length;
	        		if (h >= e.maxChars) {
        				i = i.substring(0, e.maxChars)
	        		}
	        		if (h > e.maxChars) {
	        			var j = f.scrollTop();
        				f.val(i.substring(0, e.maxChars));
        				f.scrollTop(j)
	        		}
	        		if (h >= e.maxCharsWarning) {
			            d.addClass(e.msgWarning);
	        		} else {
	        			d.removeClass(e.msgWarning);
	        		}
	        		
        			html_text = e.template
	        			.replace(/\{count\}/g, ( e.maxChars - f.val().length ))
	        			.replace(/\{length\}/g, f.val().length)
	        			.replace(/\{maxChars\}/g, e.maxChars);
	        		d.html(html_text);
		        };
			});
		}
	});
})(jQuery);

/* jquery checkAvailable */
(function(a) {
	a.fn.extend({
		checkAvailable: function(b) {
			return this.each(function() {
				var f = a(this),
	            e = a.extend({
		            url: './',
		            holder: '.Msg',
		            method: 'POST',
		            type: 'json',
		            data: {},
		            key: 'raw',
		            cache: false
	            }, b);
	
			    var d = a(e.holder);
			    var original_text = d.html();
			    
			    var load = false;
			    
		        f.bind("keyup", g).bind("focus paste", function() {
		        	setTimeout(g, 10);
		        });

		        function g() {
		        	var i = f.val();
		        
		        	if(load) {
		        		return;
		        	}
		        	
		        	load = true;
		        	
		        	if(i.length < 1) {
		        		d.html(original_text).css('color', '');
		        		load = false;
		        		return;
		        	}
		        	
		        	data = {};
		        	data[e.key] = i;
		        	
		        	$.ajax({
	        			url			: e.url,
	        			data		: a.extend(e.data, data),
	        			type		: e.method,
	        			cache		: e.cache,
	        			dataType	: 'json',
	        			success		: function(json){
		        			if(json.error) {
		        				d.html(json.error).css({color: 'red'});
		        			} else if(json.success) {
		        				d.html(json.success).css({color: 'green'});
		        			}
		        			load = false;
		        		}
	        		});
		        	
		        };
			});
		}
	});
})(jQuery);

/* facebook */
Facebook = {
	_facebookWindow: null,
	_facebookInterval: null,
	startFacebookConnect:function(b,c,d,e,f){
	  e=e==undefined?true:e;
	  var g= (b.indexOf('?') > -1 ? "&" : "?") ;
	  if(c){b+=g+"scope="+c;g="&"}
	  if(d){b+=g+"enable_timeline=1";g="&"}
	  if(f)b+=g+"next="+f;
	  Facebook._facebookWindow=window.open(b,"","location=0,status=0,width=800,height=400");
	  if(e)Facebook._facebookInterval=window.setInterval(Facebook.completeFacebookConnect,1E3)
	},
	completeFacebookConnect:function(){
	  if(Facebook._facebookWindow.closed){
	    window.clearInterval(Facebook._facebookInterval);
	    window.location.reload()
	  }
	}
};

/* resend email verification */
function resend_email_verification(){
	$.post('index.php?controller=users&action=resend&RSP=ajax', function(data){
		if(data.redirect) {
			window.location = data.redirect;
		} else if(data.error) {
			Pins.error(data.error);
		} else if(data.ok) {
			$('#TopNagCallout .LiquidContainer p').html(data.ok);
		}
	}, 'json');
};

/* jquery infinitescroll */
(function (window, $, undefined) {

	$.infinitescroll = function infscr(options, callback, element) {
		
		this.element = $(element);
		this._create(options, callback);
	
	};
	
	$.infinitescroll.defaults = {
		loading: {
			finished: undefined,
			finishedMsg: "",
			img: "data/images/loading.gif",
			msg: null,
			msgText: "",
			selector: null,
			speed: 'fast',
			start: undefined
		},
		state: {
			isDuringAjax: false,
			isInvalidPage: false,
			isDestroyed: false,
			isDone: false, 
			isPaused: false,
			currPage: 1
		},
		callback: undefined,
		debug: false,
		behavior: undefined,
		binder: $(window), 
		nextSelector: "div.navigation a:first",
		navSelector: "div.navigation",
		contentSelector: null, 
		extraScrollPx: 150,
		itemSelector: "div.post",
		animate: false,
		pathParse: undefined,
		dataType: 'html',
		appendCallback: true,
		bufferPx: 40,
		errorCallback: function () { },
		infid: 0, 
		pixelsFromNavToBottom: undefined,
		path: undefined,
		data: null
	};
	
	
	$.infinitescroll.prototype = {
	
	    _binding: function infscr_binding(binding) {
	        var instance = this,
				opts = instance.options;
	
			if (!!opts.behavior && this['_binding_'+opts.behavior] !== undefined) {
				this['_binding_'+opts.behavior].call(this);
				return;
			}
	
			if (binding !== 'bind' && binding !== 'unbind') {
	            this._debug('Binding value  ' + binding + ' not valid');
	            return false;
	        }
	
	        if (binding == 'unbind') {
	
	            (this.options.binder).unbind('smartscroll.infscr.' + instance.options.infid);
	
	        } else {
	
	            (this.options.binder)[binding]('smartscroll.infscr.' + instance.options.infid, function () {
	                instance.scroll();
	            });
	
	        };
	
	        this._debug('Binding', binding);
	
	    },
	
		_create: function infscr_create(options, callback) {
	
	        if (!this._validate(options)) { return false; }
	        var opts = this.options = $.extend(true, {}, $.infinitescroll.defaults, options),
				relurl = /(.*?\/\/).*?(\/.*)/,
				path = $(opts.nextSelector).attr('href');
	        
	        opts.contentSelector = opts.contentSelector || this.element;
	
	        opts.loading.selector = opts.loading.selector || opts.contentSelector;
	
	        if (!path) { this._debug('Navigation selector not found'); return; }
	        
	        opts.path = this._determinepath(path);
			
	        opts.loading.msg = $('<div id="infscr-loading"><img alt="Loading..." src="' + opts.loading.img + '" /><div>' + opts.loading.msgText + '</div></div>');
	
	        (new Image()).src = opts.loading.img;
	
	        opts.pixelsFromNavToBottom = $(document).height() - $(opts.navSelector).offset().top;

	        opts.loading.start = opts.loading.start || function() {
				
				$(opts.navSelector).hide();
				opts.loading.msg
					.appendTo(opts.loading.selector)
					.show(opts.loading.speed, function () {
	                	beginAjax(opts);
	            });
			};
			
			opts.loading.finished = opts.loading.finished || function() {
				opts.loading.msg.fadeOut('normal');
			};
			
	        opts.callback = function(instance,data) {
				if (!!opts.behavior && instance['_callback_'+opts.behavior] !== undefined) {
					instance['_callback_'+opts.behavior].call($(opts.contentSelector)[0], data);
				}
				if (callback) { 
					callback.call($(opts.contentSelector)[0], data);
				}
			};
	
	        this._setup();
	
	    },
	
	    _debug: function infscr_debug() {
	
                if (this.options.debug) 
                {
                            try
                            {
                             return window.console && console.log.call(console, arguments);
                            }
                            catch(e)
                            {
                                if (window.console // check for window.console not console
                                    && window.console.log)
                                {
                                    return window.console && window.console.log.call(window.console, arguments);         
                                }
                            }
	        }
	
	    },
	
	    _determinepath: function infscr_determinepath(path) {
	
	        var opts = this.options;
	
			if (!!opts.behavior && this['_determinepath_'+opts.behavior] !== undefined) {
				this['_determinepath_'+opts.behavior].call(this,path);
				return;
			}
	
	        if (!!opts.pathParse) {
	
	            this._debug('pathParse manual');
	            return opts.pathParse;
	
	        } else if (path.match(/^(.*?)\b2\b(.*?$)/)) {
	            path = path.match(/^(.*?)\b2\b(.*?$)/).slice(1);
	   
	        } else if (path.match(/^(.*?)2(.*?$)/)) {
	
	            if (path.match(/^(.*?page=)2(\/.*|$)/)) {
	                path = path.match(/^(.*?page=)2(\/.*|$)/).slice(1);
	                return path;
	            }
	
	            path = path.match(/^(.*?)2(.*?$)/).slice(1);
	
	        } else {
	
	            if (path.match(/^(.*?page=)1(\/.*|$)/)) {
	                path = path.match(/^(.*?page=)1(\/.*|$)/).slice(1);
	                return path;
	            } else {
	                this._debug('Sorry, we couldn\'t parse your Next (Previous Posts) URL. Verify your the css selector points to the correct A tag. If you still get this error: yell, scream, and kindly ask for help at infinite-scroll.com.');
	                opts.state.isInvalidPage = true;  
	            }
	        }
	        this._debug('determinePath', path);
	        return path;
	
	    },
	
	    _error: function infscr_error(xhr) {
	
	        var opts = this.options;
	
			if (!!opts.behavior && this['_error_'+opts.behavior] !== undefined) {
				this['_error_'+opts.behavior].call(this,xhr);
				return;
			}
	
	        if (xhr !== 'destroy' && xhr !== 'end') {
	            xhr = 'unknown';
	        }
	
	        this._debug('Error', xhr);
	
	        if (xhr == 'end') {
	            this._showdonemsg();
	        }
	
	        opts.state.isDone = true;
	        opts.state.currPage = 1; 
	        opts.state.isPaused = false;
	        this._binding('unbind');
	
	    },
	
	    _loadcallback: function infscr_loadcallback(box, data) {	
	        var opts = this.options,
	    		callback = this.options.callback, 
	    		result = (opts.state.isDone) ? 'done' : (!opts.appendCallback) ? 'no-append' : 'append',
	    		frag;
	
			if (!!opts.behavior && this['_loadcallback_'+opts.behavior] !== undefined) {
				this['_loadcallback_'+opts.behavior].call(this,box,data);
				return;
			}
	
	        switch (result) {
	
	            case 'done':
	
	                this._showdonemsg();
	                return false;
	
	                break;
	
	            case 'no-append':
	
	                if (opts.dataType == 'html') {
	                    data = '<div>' + data + '</div>';
	                    data = $(data).find(opts.itemSelector);
	                };
	
	                break;
	
	            case 'append':
	
	                var children = box.children();
	
	                if (children.length == 0) {
	                    return this._error('end');
	                }
	
	
	                frag = document.createDocumentFragment();
	                while (box[0].firstChild) {
	                    frag.appendChild(box[0].firstChild);
	                }
	
	                this._debug('contentSelector', $(opts.contentSelector)[0]);
	                $(opts.contentSelector)[0].appendChild(frag);
	
	                data = children.get();
	
	
	                break;
	
	        }
	
			opts.loading.finished.call($(opts.contentSelector)[0],opts);
	        
	        if (opts.animate) {
	            var scrollTo = $(window).scrollTop() + $('#infscr-loading').height() + opts.extraScrollPx + 'px';
	            $('html,body').animate({ scrollTop: scrollTo }, 800, function () { opts.state.isDuringAjax = false; });
	        }
	
	        if (!opts.animate) opts.state.isDuringAjax = false;
	
	        callback(this,data);
	
	    },
	
	    _nearbottom: function infscr_nearbottom() {

	        var opts = this.options,
	        	pixelsFromWindowBottomToBottom = 0 + $(document).height() - (opts.binder.scrollTop()) - $(window).height();
				
	
			if (!!opts.behavior && this['_nearbottom_'+opts.behavior] !== undefined) {
				this['_nearbottom_'+opts.behavior].call(this);
				return;
			}
	
			this._debug('math:', pixelsFromWindowBottomToBottom, opts.pixelsFromNavToBottom);
			
	        //return (pixelsFromWindowBottomToBottom - opts.bufferPx < opts.pixelsFromNavToBottom);
			return (pixelsFromWindowBottomToBottom < 750);
	
	    },
	
	    _pausing: function infscr_pausing(pause) {

	        var opts = this.options;
	
			if (!!opts.behavior && this['_pausing_'+opts.behavior] !== undefined) {
				this['_pausing_'+opts.behavior].call(this,pause);
				return;
			}
	
	        if (pause !== 'pause' && pause !== 'resume' && pause !== null) {
	            this._debug('Invalid argument. Toggling pause value instead');
	        };
	
	        pause = (pause && (pause == 'pause' || pause == 'resume')) ? pause : 'toggle';
	
	        switch (pause) {
	            case 'pause':
	                opts.state.isPaused = true;
	                break;
	
	            case 'resume':
	                opts.state.isPaused = false;
	                break;
	
	            case 'toggle':
	                opts.state.isPaused = !opts.state.isPaused;
	                break;
	        }
	
	        this._debug('Paused', opts.state.isPaused);
	        return false;
	
	    },
	
		_setup: function infscr_setup() {
			
			var opts = this.options;
			
			if (!!opts.behavior && this['_setup_'+opts.behavior] !== undefined) {
				this['_setup_'+opts.behavior].call(this);
				return;
			}
			
			this._binding('bind');
			
			return false;
			
		},
	
	    _showdonemsg: function infscr_showdonemsg() {
	
	        var opts = this.options;
	
			if (!!opts.behavior && this['_showdonemsg_'+opts.behavior] !== undefined) {
				this['_showdonemsg_'+opts.behavior].call(this);
				return;
			}
	
	        opts.loading.msg
	    		.find('img')
	    		.hide()
	    		.parent()
	    		.find('div').html(opts.loading.finishedMsg).animate({ opacity: 1 }, 2000, function () {
	    		    $(this).parent().fadeOut('normal');
	    		});
	
	        opts.errorCallback.call($(opts.contentSelector)[0],'done');
	
	    },
	
	    _validate: function infscr_validate(opts) {
	
	        for (var key in opts) {
	            if (key.indexOf && key.indexOf('Selector') > -1 && $(opts[key]).length === 0) {
	                this._debug('Your ' + key + ' found no elements.');
	                return false;
	            }
	            return true;
	        }
	
	    },
	
		bind: function infscr_bind() {
			this._binding('bind');
		},
	
	    destroy: function infscr_destroy() {
	
	        this.options.state.isDestroyed = true;
	        return this._error('destroy');
	
	    },
	
		pause: function infscr_pause() {
			this._pausing('pause');
		},
		
		resume: function infscr_resume() {

			this._pausing('resume');
		},
	
	    retrieve: function infscr_retrieve(pageNum) {
	        var instance = this,
				opts = instance.options,
				path = opts.path,
				box, frag, desturl, method, condition,
	    		pageNum = pageNum || null,
				getPage = (!!pageNum) ? pageNum : opts.state.currPage;
				beginAjax = function infscr_ajax(opts) {
					
	                opts.state.currPage++;
	
	                instance._debug('heading into ajax', path);
	
	                box = $(opts.contentSelector).is('table') ? $('<tbody/>') : $('<div/>');
	
	                desturl = path.join(opts.state.currPage);
	                
					if(desturl.indexOf('?') > -1) {
						desturl += '&RSP=ajax';
					} else {
						desturl += '?RSP=ajax';
					}
	
	                method = (opts.dataType == 'html' || opts.dataType == 'json') ? opts.dataType : 'html+callback';
	                if (opts.appendCallback && opts.dataType == 'html') method += '+callback';
	
	                switch (method) {
	
	                    case 'html+callback':
	
	                        instance._debug('Using HTML via .load() method');
	                        box.load(desturl + ' ' + opts.itemSelector, opts.data, function infscr_ajax_callback(responseText) {
	                            instance._loadcallback(box, responseText);
	                        });
	
	                        break;
	
	                    case 'html':
	                    case 'json':
	
	                        instance._debug('Using ' + (method.toUpperCase()) + ' via $.ajax() method');
	                        $.ajax({
	                            url: desturl,
	                            data: opts.data,
	                            dataType: opts.dataType,
	                            complete: function infscr_ajax_callback(jqXHR, textStatus) {
	                                condition = (typeof (jqXHR.isResolved) !== 'undefined') ? (jqXHR.isResolved()) : (textStatus === "success" || textStatus === "notmodified");
	                                (condition) ? instance._loadcallback(box, jqXHR.responseText) : instance._error('end');
	                            }
	                        });
	
	                        break;
	                }
				};
				
			if (!!opts.behavior && this['retrieve_'+opts.behavior] !== undefined) {
				this['retrieve_'+opts.behavior].call(this,pageNum);
				return;
			}
	
			if (opts.state.isDestroyed) {
	            this._debug('Instance is destroyed');
	            return false;
	        };
	
	        opts.state.isDuringAjax = true;
	
	        opts.loading.start.call($(opts.contentSelector)[0],opts);
	
	    },
	
	    scroll: function infscr_scroll() {
	
	        var opts = this.options,
				state = opts.state;
	
			if (!!opts.behavior && this['scroll_'+opts.behavior] !== undefined) {
				this['scroll_'+opts.behavior].call(this);
				return;
			}
	
			if (state.isDuringAjax || state.isInvalidPage || state.isDone || state.isDestroyed || state.isPaused) return;
	
	        if (!this._nearbottom()) return;
	
	        this.retrieve();
	
	    },
		
		toggle: function infscr_toggle() {
			this._pausing();
		},
		
		unbind: function infscr_unbind() {
			this._binding('unbind');
		},
		
		update: function infscr_options(key) {
			if ($.isPlainObject(key)) {
				this.options = $.extend(true,this.options,key);
			}
		}
	
	};
	
	$.fn.infinitescroll = function infscr_init(options, callback) {
	
	
	    var thisCall = typeof options;
	
	    switch (thisCall) {
	
	        case 'string':
	
	            var args = Array.prototype.slice.call(arguments, 1);
	
	            this.each(function () {
	
	                var instance = $.data(this, 'infinitescroll');
	
	                if (!instance) {
						return false;
	                }
	                if (!$.isFunction(instance[options]) || options.charAt(0) === "_") {
						return false;
	                }
	
	                instance[options].apply(instance, args);
	
	            });
	
	            break;
	
	        case 'object':
	
	            this.each(function () {
	
	                var instance = $.data(this, 'infinitescroll');
	
	                if (instance) {
	
	                    instance.update(options);
	
	                } else {
	
	                    $.data(this, 'infinitescroll', new $.infinitescroll(options, callback, this));
	
	                }
	
	            });
	
	            break;
	
	    }
	
	    return this;
	
	};
	
	var event = $.event,
		scrollTimeout;
	
	event.special.smartscroll = {
	    setup: function () {
	        $(this).bind("scroll", event.special.smartscroll.handler);
	    },
	    teardown: function () {
	        $(this).unbind("scroll", event.special.smartscroll.handler);
	    },
	    handler: function (event, execAsap) {
	        var context = this,
		      args = arguments;
	
	        event.type = "smartscroll";
	
	        if (scrollTimeout) { clearTimeout(scrollTimeout); }
	        scrollTimeout = setTimeout(function () {
	            $.event.handle.apply(context, args);
	        }, execAsap === "execAsap" ? 0 : 100);
	    }
	};
	
	$.fn.smartscroll = function (fn) {
	    return fn ? this.bind("smartscroll", fn) : this.trigger("smartscroll", ["execAsap"]);
	};
	

})(window, jQuery);

/* add menu board fixed */
$(window).load(function(){
	
	var msie6 = $.browser == 'msie' && $.browser.version < 7;
	if (!msie6) {
		
		if($('body').hasClass('users-body')) {
		
			if(!$('#menu, #board, #ContextBar').offset()) {
				return;
			}
			
			var is_board2 = $('#board2').size();
			
			var top = $('#menu, #board, #ContextBar').offset().top;
			$(window).scroll(function (event) {
				var y = $(this).scrollTop();
				if (y >= (top+(is_board2?338:282)) && $('#fancybox-wrap').css('display') === 'none') { $('#menu, #board, #ContextBar').addClass('fixed'); }
				else { $('#menu, #board, #ContextBar').removeClass('fixed'); }
			});
		
		} else if( $('body').hasClass('pin-body') ) {
			
			if($('#top').size() < 1) return;
			
			var msie6 = $.browser == 'msie' && $.browser.version < 7;
			if (!msie6) {
				$(window).scroll(function (event) {
					var y = $(this).scrollTop();
					if (y > 0) { $('#top, #TopNagCallout, #board').addClass('fixed'); }
					else { $('#top, #TopNagCallout, #board').removeClass('fixed'); }
				});
			}
			
		} else if($('#menu').size() > 0) {
			if($('#menu').size() < 1) return;
			
			var msie6 = $.browser == 'msie' && $.browser.version < 7;
			if (!msie6) {
				var top = $('#menu').offset().top || parseInt($('#menu').css('top'));
				$(window).scroll(function (event) {
					var y = $(this).scrollTop();
					if (y >= top) { $('#menu, #board,').addClass('fixed'); }
					else { $('#menu, #board,').removeClass('fixed'); }
				});
			}
		} else {
			if($('#top').size() < 1) return;
			
			var msie6 = $.browser == 'msie' && $.browser.version < 7;
			if (!msie6) { 
				$(window).scroll(function (event) {
					var y = $(this).scrollTop();
					if (y > 0) { $('#top, #board, #TopNagCallout').addClass('fixed'); }
					else { $('#top, #board, #TopNagCallout').removeClass('fixed'); }
				});
			}
		}
	}
});

/* jquery history */
JoGetPinsHistory = function() {
	var isPushed = true;
	var isHtml5 = false;
	
	init();
	function init()
	{
		isHtml5 = !!(window.history && history.pushState && history.replaceState);
	};
	
	this.setPage = function(pageNum, pageUrl)
	{
		this.updateState({page : pageNum}, "", pageUrl);
	};
	
	this.havePage = function()
	{
		return (this.getState() != false);
	};
	
	this.getPage = function()
	{
		if (this.havePage()) {
			stateObj = this.getState();
			return stateObj.page;
		}
		return 1;
	};
	
	this.getState = function()
	{
		if (isHtml5) {
			stateObj = history.state;
			if (stateObj && stateObj.joPagination) return stateObj.joPagination;
		}
		else {
			haveState = (window.location.hash.substring(0, 7) == "#/page/");
			if (haveState) {				
				pageNum = parseInt(window.location.hash.replace("#/page/", ""));
				return { page : pageNum };
			}
		}
		
		return false;
	};
	
	this.updateState = function(stateObj, title, url)
	{
		if (isPushed) {
			this.replaceState(stateObj, title, url);
		}
		else {
			this.pushState(stateObj, title, url);
		}
	};
	
	this.pushState = function(stateObj, title, url) 
	{
		if (isHtml5) {
			history.pushState({ joPagination : stateObj }, title, url);
		}
		else {
			hash = (stateObj.page > 0 ? "#/page/" + stateObj.page : "");
			window.location.hash = hash;
		}
		
		isPushed = true;
	};
	
	this.replaceState = function(stateObj, title, url) 
	{
		if (isHtml5) {
			history.replaceState({ joPagination : stateObj }, title, url);
		}
		else {
			this.pushState(stateObj, title, url);
		}
	};
};

/* jquery contextmenu */
(function($){
	$.fn.contextmenu = function(callback) {
		callback = callback || function(){};
		return $(this).bind("contextmenu", function(e) {
			callback.call(this);
	        e.preventDefault();
	        return false;
	    });
	}
})(jQuery);

/* jquery ba throttle debounce */
(function(window,undefined){
  '$:nomunge'; 
  
  var $ = window.jQuery || window.Cowboy || ( window.Cowboy = {} ), jq_throttle;
  
  $.throttle = jq_throttle = function( delay, no_trailing, callback, debounce_mode ) {
    var timeout_id,
      
      last_exec = 0;
    
    if ( typeof no_trailing !== 'boolean' ) {
      debounce_mode = callback;
      callback = no_trailing;
      no_trailing = undefined;
    }
    
    function wrapper() {
      var that = this,
        elapsed = +new Date() - last_exec,
        args = arguments;
      
      function exec() {
        last_exec = +new Date();
        callback.apply( that, args );
      };
      
      function clear() {
        timeout_id = undefined;
      };
      
      if ( debounce_mode && !timeout_id ) {
        exec();
      }
      
      timeout_id && clearTimeout( timeout_id );
      
      if ( debounce_mode === undefined && elapsed > delay ) {
        exec();
        
      } else if ( no_trailing !== true ) {
        timeout_id = setTimeout( debounce_mode ? clear : exec, debounce_mode === undefined ? delay - elapsed : delay );
      }
    };
    
    if ( $.guid ) {
      wrapper.guid = callback.guid = callback.guid || $.guid++;
    }
    
    return wrapper;
  };
  
  $.debounce = function( delay, at_begin, callback ) {
    return callback === undefined
      ? jq_throttle( delay, at_begin, false )
      : jq_throttle( delay, callback, at_begin !== false );
  };
  
})(this);

/* main */
if(!window.Pins) { window.Pins = {} };

$(window).load(function(){

	$(window).scrollTop(0);
	
	
	$('.silverbox').contextmenu();
	
	var nav = '';
	for(i=2; i<3; i++) {
		nav += '<a class="page_'+i+'" href="'+window.Pins.url+(window.Pins.url.indexOf('?')>-1?'&':'?')+'page='+i+'"></a>';
	}
	
	$('#overflow').append('<div class="navigation hide">'+nav+'</div>');
	$('.navigation.hide a.page_2').addClass('next');
	
	
	$(".easyscroll").easyscroll();
	
	var $container = $('#container');
	
	
	setTimeout(function(){
		
		if($container.find('.box').size() > 0) {
			$container.masonry({
				itemSelector : '.box',
				columnWidth : 135,
				isAnimated: false,
				isFitWidth: true,
				isResizable: true,
				gutterWidth: 12,
				animate: false
			});
			
			var page = Pins.currentPage;
			$container.infinitescroll({
				itemSelector 	: ".box",
				nextSelector	: '<a href="'+window.Pins.url+(window.Pins.url.indexOf('?')>-1?'&':'?')+'page=2"></a>',
				navSelector		: 'div.navigation a.next',
				bufferPx     	: Math.ceil(Math.max($(window).height(),($(document).height()/2))),
				data			: {marker:window.Pins.marker},
				finishedMsg		: window.infiniteFinishedMsg
			},
		
			function( newElements ) { 
				//alert("Carga callback");
				page++;
				var $newElems = $( newElements );
				$container.masonry( "appended", $newElems );
				$newElems.find('.thumb img').LazyLoad();
				//$.ajax({
					//url: (window.Pins.url+(window.Pins.url.indexOf('?')>-1?'&':'?')+'page=' +(page+1)),
					//dataType: "jsonp",
					//jsonp: "jsoncallback"
				//});
				$('.navigation.hide a').removeClass('next').filter('.page_'+page).addClass('next');
				
				Pins.init();
			});
		}
		
	}, 1);
	
});

$(window).load(function(){
	$('#overflow').removeClass('hide');
	
	setTimeout(function() {
	
		var containerwidth = $("#container").width();  
		var max_size = Math.min( Math.max(850, ($(window).width()-10) ), 1170 );
		if(containerwidth > max_size) {
			containerwidth = max_size;
		} 
		if(containerwidth < 850) {
			containerwidth = 850;
		}
		/*$("#topwrapper, #menuwrapper, #category").css({'width' : containerwidth + 'px'});
		
	
		$('#TopNagCallout .LiquidContainer').css({'width' : $("#topwrapper").width() + 'px'});*/
		
	}, 2000);
	
	$('#container .thumb img').LazyLoad();
	
	Pins.init();
	
	$('#menuwrapper .links li, #topwrapper .userbar li').hover(function() {
		$(".dropdown",this).stop(true,true).fadeIn(125);
	}, function() {
		$(".dropdown",this).stop(true,true).fadeOut(125);
	});
	
	$('input#keyword, #invitationBox').clearOnFocus();
	
	$('.buttonswrapper a').click(function(){
		var returns = Pins.controls($(this), $(this).attr('href'), window.location.href);
		return false;
	});
	
	$(window).unbind('.scrolltotop').bind('scroll.scrolltotop',function () {
		if ($(this).scrollTop() > 50 && $('#fancybox-wrap').css('display') === 'none') {
			$('.scrolltotop').stop(true,true).slideDown(500);
		} else {
			$('.scrolltotop').stop(true,true).css('display','none');
		}
	});
});

$(window).load(function(){
	
	var panelwidth = $("#panel").width(); 
	$("#topwrapper, #menuwrapper").css({'width' : panelwidth + 'px'});
	
	$(window).resize(function() {
		var panelwidth = $("#panel").width(); 
		$("#topwrapper, #menuwrapper").css({'width' : panelwidth + 'px'});
	});
	
});

$(window).load(function(){
	
//	var menu_wrapper_links_width = $("#menuwrapper .links").width();
	var menu_wrapper_links_width = 10;
	$("#menuwrapper .links > li").each(function(){
		menu_wrapper_links_width += $(this).innerWidth(true);
	});
	var menu_wrapper_links_diff = menu_wrapper_links_width / 2;

	$("#menuwrapper .links").css({'margin-left' : '-' + menu_wrapper_links_diff + 'px', 'width' : menu_wrapper_links_width + 'px'});
	$('#TopNagCallout .LiquidContainer').css({'width' : $("#topwrapper").width() + 'px'});
});

function fancybox_wrap_mousewheel() {
	$("#fancybox-wrap").unbind('mousewheel').bind("mousewheel",function(ev, delta) {
		var scrollTop = $(".pin-overlay").scrollTop();
		$(".pin-overlay").scrollTop(scrollTop - Math.round(delta * 100));
	}); 
};

/* search autocomplete */
$(document).ready(function(){
	$('#keyword').autoComplete({
		ajax: window.search_autocomplete,
		maxHeight: 331,
		onListFormat: function(event, data) { 
			var container = [], striped = false;
			if(data.list && data.list.items) {
				$(data.list.items).each(function(i, item){
					if(item.search_for) {
						container.push(data.settings.striped && striped ? '<li class="search_for ' + data.settings.striped + '">' : '<li class="search_for">',
								'<a class="items" href="'+item.href+'">'+item.label+'</a>',
								'</li>');
					} else {
						container.push(data.settings.striped && striped ? '<li class="' + data.settings.striped + '">' : '<li>',
								'<a class="items" href="'+item.href+'"><img alt="'+item.label+'" src="'+item.image+'"><span>'+item.label+'</span></a>',
								'</li>');
					}
					striped = ! striped;
				});
			}
			data.ul.html( container.join('') ); 
			
		},
		onSelect: function(event, data) {
			window.location = data.li.find('a').attr('href');
		}
	});
	$('#IntercomDefaultWidget').attr("title","fdfdf");
});
