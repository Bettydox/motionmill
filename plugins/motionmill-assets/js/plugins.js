(function($, window, document, undefined)
{
	$.fn.exists = function()
	{
		return this.length > 0;
	};

	$.fn.equalHeight = function( )
	{
		var maxHeight = 0;

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

	// http://upshots.org/javascript/jquery-test-if-element-is-in-viewport-visible-on-screen
	$.fn.isOnScreen = function()
	{
	    var win = $(window);
	    
	    var viewport = {
	        top : win.scrollTop(),
	        left : win.scrollLeft()
	    };

	    viewport.right = viewport.left + win.width();
	    viewport.bottom = viewport.top + win.height();
	    
	    var bounds = this.offset();
	    bounds.right = bounds.left + this.outerWidth();
	    bounds.bottom = bounds.top + this.outerHeight();
	    
	    return (!(viewport.right < bounds.left || viewport.left > bounds.right || viewport.bottom < bounds.top || viewport.top > bounds.bottom));
    };

})(jQuery, window, document);
