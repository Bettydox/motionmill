(function($)
{
	Motionmill = $.extend(
	{
		ajaxurl: null,
		lang   : null,

		initialize : function()
		{
			console.log( 'Motionmill initialized' );
		}

	}, Motionmill);
	
	Motionmill.initialize();

	$(document).ready(function()
	{
		$('.hide-if-js').hide();
		$('.hide-if-no-js').show();

		$('a.open').click(function(e)
		{
			var target = $( $(this).attr('href') );

			target.is(':hidden').slideToggle();

			e.preventDefault();
		});

		$('a.close').click(function(e)
		{
			var target = $( $(this).attr('href') );

			target.is(':visible').slideToggle();

			e.preventDefault();
		});

		$('a.toggler').click(function(e)
		{
			var target = $( $(this).attr('href') );

			target.slideToggle();

			e.preventDefault();
		});
	});

})(jQuery);