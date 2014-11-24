(function($)
{	
	Motionmill = $.extend( Motionmill, 
	{
		doAjax : function( method, args, options )
		{
			var options = $.extend(
			{
				dataType : 'json',
				success  : function(){},
				error    : function(){},
				complete : function(){}

			}, options );

			$.post( Motionmill.ajaxurl,
			{
				action : Motionmill.ajaxEvent,
				method : method,
				args   : args

			}, null, options.dataType )

			.fail(function( jqXHR, textStatus, errorThrown )
			{
				options.error.call( this, errorThrown, textStatus, jqXHR );
			})

			.done(function( response, textStatus, jqXHR )
			{
				if ( response.success )
				{
					options.success.call( this, response.data, textStatus, jqXHR );
				}

				else
				{
					options.error.call( this, response.data, textStatus, jqXHR );
				};
			})

			.always(function( jqXHR, textStatus )
			{
				options.complete.call( this, jqXHR, textStatus );
			});
		}
		
	});

})( jQuery )