(function($, window, document, undefined)
{
	$.ajaxSetup(
	{
		cache    : false,
		dataType : 'json'
	});

	$(document).ready(function()
	{
		// tooltip
		$('.wrap .description.tooltip-content').each(function(i, elem)
		{
			var $tip = $('<a href="#" class="tooltip"></a>');

			$tip.tipTip(
			{ 
				content        : $(elem).html(),
				defaultPosition: 'top', 
				maxWidth       : '250px'
			})
			.click(function(e)
			{
				e.preventDefault();
			});

			$tip.insertAfter( $('label[for="' + $(this).attr('data-field' ) + '"]') );
		});

		// colorpicker
		$('.wrap .colorpicker').each(function()
		{
			var color = new Color( $(this).val() );

			$(this)
				.data('set', false)
				.css( 'color', color.getMaxContrastColor().toString() )
		    	.css( 'background-color', color.toString() )
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
			$('.wrap .colorpicker').iris('hide');
		});
	});

})(jQuery, window, document);