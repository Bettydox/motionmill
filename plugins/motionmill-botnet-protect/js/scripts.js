(function($)
{
	$(document).ready(function()
	{
		var output = $('#mm-botnet-protect-output').hide();
		var inProcess = false;

		var buttons = $('#mm-botnet-protect-add-htaccess-button, #mm-botnet-protect-remove-htaccess-button');

		$(buttons).click(function(e)
		{
			e.preventDefault();

			$(buttons).attr('readonly', 'readonly');
			
			var action = $(this).attr('id') == 'mm-botnet-protect-add-htaccess-button' ? 'mm_botnet_protect_write_to_htaccess' : 'mm_botnet_protect_remove_from_htaccess';

			$.post( ajaxurl, { action: action } )

			.done(function(response)
			{
				output.text(response.message);
			})

			.fail(function( jqXHR, textStatus, errorThrown )
			{
				output.text( 'Error: ' + errorThrown );
			})

			.always(function()
			{
				output.fadeIn();

				$(buttons).removeAttr('readonly');
			});
			

		});
		
	});

})(jQuery);