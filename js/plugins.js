(function($, window, document, undefined)
{
	// based on: http://stackoverflow.com/questions/6361465/how-to-check-if-click-event-is-already-bound-jquery
	$.fn.isBound = function(type, fn)
	{
		if ( this.data('events') == undefined )
		{
			return false;
		};

	    var data = this.data('events')[type];

	    if ( data === undefined || data.length === 0 )
	    {
	        return false;
	    }

	    if ( fn === undefined )
	    {
	    	return true;
	    };

	    return ( $.inArray( fn, data ) !== -1 );
	};

	$.fn.afterResize = function(callback, args)
	{
		var options = $.extend(
		{
			delay : 1000
		}, args);

		var me = $(this), timeout;

		return $(this).resize(function(e)
		{
			if ( typeof timeout != 'undefined' )
			{
				clearTimeout(timeout);
			};
		
			timeout = setTimeout(function()
			{
				callback.apply(me, e);

			}, options.delay);
		});
	}

	$.fn.exists = function(callback)
	{
		var exists = this.length > 0;

		if ( typeof callback == 'undefined' )
		{
			return exists;
		};

		if ( exists )
		{
			callback.apply(this);
		};

		return this;
	};

	$.fn.equalHeight = function(args)
	{
		var options = $.extend({}, args);

		var maxHeight = 0;

		$(this).css( 'height', 'auto' );

		this.each(function()
		{
			if ( $(this).height() > maxHeight  )
			{
				maxHeight = $(this).height();
			};
		});
		
		$(this).css( 'height', maxHeight );

		return this;
	};

	/**
	 * Insert At Carret
	 *
	 * Inserts a given value into a textarea or textfield (at cursor position)
	 *
	 * @link http://stackoverflow.com/questions/11076975/insert-text-into-textarea-at-cursor-position-javascript
	 * @author Maarten Menten
	 * @param value mixed The value to insert.
	 * @return void
	 */
	 
	$.fn.insertAtCaret = function(value)
	{
		return this.each(function()
		{	
			// makes sure we have to DOM element
			var field = $(this).get(0);

		    // IE
		    if ( document.selection )
		    {
		        field.focus();
		        
		        sel = document.selection.createRange();
		        sel.text = value;
		    }

		    // other browsers
		    else if ( field.selectionStart || field.selectionStart == '0' )
		    {
		        var before = field.value.substring( 0, field.selectionStart );
		        var after  = field.value.substring( field.selectionEnd, field.value.length );
		    	
		        $( field ).val( before + value + after );
		    }

		    else
		    {
		        $( field ).val( $( field ).val() + value );
		    }
		});
	};

	/* ------------------------------------------------------------------------------------------ */

	$.getUrlVar = function(key, defaultValue )
	{
		if ( typeof key == 'undefined' ) key = null;
		if ( typeof defaultValue == 'undefined' ) defaultValue = '';
	   
	   	// gets all vars
		var vars = {};

	    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m, k, v)
	    {
	        vars[k] = v;
	    });

	    // gets single var
	    if ( key )
	    {
	    	if ( typeof vars[key] != 'undefined' )
	    	{
	    		return vars[key];
	    	};

	    	return defaultValue;
	    };

	    return vars;
	};

	$.fn.gotoByScroll = function(target, args)
	{
		var options = $.extend(
		{
			offset   : 0,
			duration : 400,
			easing   : 'jswing',
		}, args);

		$(target).stop().animate( { scrollTop: this.offset().top - options.offset }, options );

		return this;
	};

	$.fn.isOnScreen = function()
	{
	    var viewport =
	    {
	        top : win.scrollTop(),
	        left : win.scrollLeft()
	    };

	    viewport.right  = viewport.left + $(window).width();
	    viewport.bottom = viewport.top + $(window).height();
	    
	    var bounds = this.offset();
	    bounds.right  = bounds.left + this.outerWidth();
	    bounds.bottom = bounds.top + this.outerHeight();
	    
	    return ( ! ( viewport.right < bounds.left || viewport.left > bounds.right || viewport.bottom < bounds.top || viewport.top > bounds.bottom ) );
    };

	$.fn.bindAt = function(name, fn, index)
	{
	  // binds as normally
	  this.bind( name, fn );

	  // takes out the handler
	  var handlers = this.data('events')[name];
	  var handler = handlers.splice( handlers.length - 1 )[0];

	  // places it back at the given index
	  handlers.splice(index, 0, handler);
	};

})(jQuery, window, document);