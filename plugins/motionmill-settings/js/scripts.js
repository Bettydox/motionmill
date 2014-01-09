(function($, window, document, undefined)
{
	$.extend(MM_Settings,
	{
		elem : null,

		initialize : function()
		{
			this.elem = $( '.' + this.page_hook );

			// colorpicker
			this.elem.find('.colorpicker').each(function()
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
				MM_Settings.elem.find('.colorpicker').iris('hide');
			});
		}
	})

	$(document).ready(function()
	{
		MM_Settings.initialize();
	});

})(jQuery, window, document);