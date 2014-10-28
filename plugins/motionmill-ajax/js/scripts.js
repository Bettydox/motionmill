(function($)
{	
	Motionmill.ajax =
	{
		sentRequest : function( method, args, options )
		{
			var me = this;

			var options = $.extend(
			{
				error    : function( message ){},
				success  : function( data ){},
				complete : function(){}

			}, options );

			$.post( Motionmill.ajaxurl,
			{
				action : Motionmill.ajaxEvent,
				method : method,
				args   : args

			}, null, 'json' )

			.fail(function( jqXHR, textStatus, errorThrown )
			{
				options.error.call( me, errorThrown );

				console.log( 'error: ' + errorThrown );
			})

			.done(function( response, textStatus, jqXHR )
			{
				if ( response.success )
				{
					options.success.call( me, response.data );

					console.log( 'done (success)' );
					console.log( response.data );
				}

				else
				{
					options.error.call( me, response.data );

					console.log( 'done (error): ' + response.data );
				};
			})

			.always(function( jqXHR, textStatus )
			{
				options.complete.call( me );

				console.log( 'complete' );
			});
		}
	};

	$(document).ready(function()
	{
		$('.mm-ajax-loader').hide();
	});

	Motionmill = $.extend( Motionmill, 
	{
		post : function( method, args, wrapper, options )
		{	
			var options = $.extend(
			{
				button : 'input[type="submit"]'
			}, options );

			var me = this;

			var submit = wrapper.find( options.button )
				.attr( 'disabled', 'disabled' )

			var errors = wrapper.find('.mm-errors');

			if ( errors.length == 0 )
			{
				var errors = $( '<div class="mm-errors"></div>' )
					.insertAfter( submit )
			};

			errors
				.hide()
				.empty();

			var loader = wrapper.find('.mm-loader');

			if ( loader.length == 0 )
			{
				var loader = $( '<div class="mm-loader"></div>' )
					.insertAfter( submit );
			};

			loader.show();

			var args =
			{
				action : Motionmill.ajaxEvent,
				method : method,
				args   : args
			};

			var message = '<span class="mm-message"></span>';

			return $.post( Motionmill.ajaxurl, args, null, 'json' )

			.fail(function( jqXHR, textStatus, errorThrown )
			{
				errors
					.append( $( message ).text( errorThrown ) )
					.show();
			})

			.done(function( response, textStatus, jqXHR )
			{
				if ( ! response.success )
				{
					errors
						.append( $( message ).text( response.data ) )
						.show();
				}
			})

			.always(function( jqXHR, textStatus )
			{
				loader.hide();

				submit.removeAttr( 'disabled' );
			});
		}
		
	});

})( jQuery )