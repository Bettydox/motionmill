(function($, window, document, undefined)
{
	$.extend(MM_Settings,
	{
		_elem : null,

		initialize : function()
		{
			this._elem = $( '.' + this.page_hook );

			// colorpicker
			this._elem.find('.mm-colorpicker').each(function()
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
				MM_Settings._elem.find('.mm-colorpicker').iris('hide');
			});

			// media
			var button = this._elem.find('.mm-media-button');

			if ( button.length > 0 )
			{
				button.click(function(e)
				{
					tb_show('','media-upload.php?TB_iframe=true');

					e.preventDefault();
				});

				window.original_tb_remove = window.tb_remove;
		    	window.tb_remove = function()
		    	{
		        	window.original_tb_remove();
		        	button = null;
		    	};

		    	window.original_send_to_editor = window.send_to_editor;
			    window.send_to_editor = function(html)
			    {
			        if (button)
			        {
			        	var field = $( button.attr('href') );
			            var url = $('img',html).attr('src');
			            
			            $(field).val(url);

			            tb_remove();
			        }
			        else
			        {
			            window.original_send_to_editor(html);
			        }
			    };
			};
			
		}
	})

	$(document).ready(function()
	{
		MM_Settings.initialize();
	});

})(jQuery, window, document);