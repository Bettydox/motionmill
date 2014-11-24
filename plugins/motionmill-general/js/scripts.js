(function($)
{
	$(document).ready(function()
	{
		if ( Boolean( Motionmill.body_class_javascript ) )
		{
			$('body')
				.removeClass( 'no-js' )
				.addClass( 'js' );
		};
	});

})(jQuery)