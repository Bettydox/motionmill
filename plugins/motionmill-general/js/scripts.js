(function($)
{
	var options = $.extend( {  }, mm_general_options );

	// body class
	$(document).ready(function()
	{
		if ( typeof options.body_class_javascript !== 'undefined' )
		{
			$('body').addClass( 'js' );
		};
		
	});


})(jQuery);