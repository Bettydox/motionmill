(function($)
{
	$.fn.exists = function(callback)
	{
		return this.length > 0;
	}

	$.fn.equalHeight = function( )
	{
		var maxHeight = 0;

		this.each(function()
		{
			if ( this.height() > maxHeight  )
			{
				maxHeight = this.height();
			};
		});

		this.css( 'height', maxHeight );

		return this;
	}

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
	}

})(jQuery);