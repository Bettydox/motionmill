(function($)
{
	var textFieldSelected = null;

	$(document).ready(function()
	{
		var wrap = $('.wrap');

		wrap.find( 'input[type="text"], textarea' ).each(function(i, textfield)
		{
			var args =
			{
				TB_inline : true,
				width	  : 600,
				height	  : 550,
				inlineId  : 'tag-info'
			};

			var button = $('<a href="#" class="button tag-button thickbox"></a>')
				.attr( 'href', '#' + $.param( args ) )
				.text( 'Insert tag' )
				.click(function(e)
				{
					textFieldSelected = textfield;

					e.preventDefault();
				})
				.insertAfter( this );
		});
		
		wrap.find( '#tag-info .insert-button' ).click(function(e)
		{
			var code = $(this).attr( 'data-code');

			$( textFieldSelected ).insertAtCaret( code );

			tb_remove();

			e.preventDefault();
		});

		wrap.find( '.tag-info' ).tabs();
	});

})(jQuery);