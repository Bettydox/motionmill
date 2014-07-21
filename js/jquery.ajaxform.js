(function($, window, document, undefined)
{
	$.fn.ajaxForm = function(options)
	{
		var options = $.extend(
		{
			ajaxurl  : '',
			dataType : 'json',
			
			elements :
			{
				loader  : '.loader',
				submit  : 'input[type="submit"]',
				success : '.message-success',
				error   : '.message-fail'
			},

			callbacks :
			{
				beforeSend : function(){},
				success    : function(){},
				error      : function(){},
				complete   : function(){}
			}

		}, args );
		
		return this.each(function(i, form)
		{
			form.submit(function(e)
			{
				// shows/hides elements
				form.find( options.elements.submit ).attr('disabled', 'disabled');
				form.find( options.elements.loader ).show();

				$.each(options.messages, function(key, value)
				{
					form.find( value ).hide();
				});

				options.callbacks.beforeSend.call( this );

				// ajax call
				$.post( options.ajaxurl, $(this).serialize(), null, options.dataType )

				.done(function(data, textStatus, jqXHR)
				{
					var args = Array.prototype.slice.call(arguments);

					form.find( options.elements.success ).show();

					options.callbacks.success.apply( this, args );
				})
				
				.fail(function(jqXHR, textStatus, errorThrown)
				{
					var args = Array.prototype.slice.call(arguments);

					form.find( options.elements.error ).show();

					options.callbacks.error.apply( this, args );
				})

				.always(function(jqXHR, textStatus)
				{
					var args = Array.prototype.slice.call(arguments);

					form.find( options.elements.submit ).removeAttr('disabled');
					form.find( options.elements.loader ).hide();

					options.callbacks.complete.apply( this, args );
				});
				
				e.preventDefault();
			});
		});
	};

})(jQuery, window, document);