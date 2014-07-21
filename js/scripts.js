(function($)
{
	$(document).ready(function()
	{
		// Shows/hides Elements
		$('.hide-if-js').hide();
		$('.hide-if-no-js').show();

		// toggles Elements
		$('a.open, a.close').click(function(e)
		{
			var target = $( $(this).attr('href') );

			if ( target.is(':visible') && $(this).hasClass('close') || target.is(':hidden') && $(this).hasClass('open') )
			{
				target.slideToggle();
			};

			e.preventDefault();
		});
	});

})(jQuery);