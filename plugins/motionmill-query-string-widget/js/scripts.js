(function($)
{
	MM_Query_Widget =
	{
		initialize : function()
		{
			var widget = $( '.mm-query-string-widget' ).parents('.widget');
		
			// toggles tags
			widget.find('.cats a').click(function(e)
			{
				var selector = '.' + $(this).attr( 'data-tags' );

				var target = widget.find( selector );
				
				if ( target.is(':hidden') )
				{
					widget.find( '.cats a.active' ).removeClass('active');

					$(this).addClass('active');

					widget.find( '.tags:visible' ).slideToggle();
				}

				else
				{
					$(this).removeClass('active');
				}

				target.slideToggle();

				e.preventDefault();
			});

			// inserts tags into template
			widget.find('.tags .button').click(function(e)
			{
				var tag = $(this).attr( 'data-tag' );

				widget.find( '.template' )
					.insertAtCaret( tag )
					.focus();

				e.preventDefault();
			});
		}
	}

	$(document).ready(function()
	{
		MM_Query_Widget.initialize();
	});

})(jQuery);