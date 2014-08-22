(function($)
{
	$(document).ready(function()
	{
		var button = $('.wrap .mm-media-button');

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
	});

})(jQuery);