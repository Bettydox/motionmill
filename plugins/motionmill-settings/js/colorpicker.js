(function($)
{
	$(document).ready(function()
	{
		$('.wrap .mm-colorpicker').each(function()
		{
			var color = new Color( $(this).val() );

			$(this)
				.data('set', false)
				.css( 'color', color.getMaxContrastColor().toString() )
		    	.css( 'background-color', color.toString() )
		    	.attr( 'readonly', 'readonly' )
				.iris(
				{
					hide: true,
					change: function(event, ui)
					{
		        		$(this).css( 'color', ui.color.getMaxContrastColor().toString() );
		    			$(this).css( 'background-color', ui.color.toString() );
		    		}
				})
				.click(function(e)
				{
					$(this).iris('show');

					e.stopPropagation();
				})
		});

		$('body').click(function(e)
		{
			$('.wrap .mm-colorpicker').iris('hide');
		});
	});

})(jQuery);