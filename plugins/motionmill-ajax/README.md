Motionmill Ajax
===============

Manages Ajax calls

Examples
--------

	### Adds a callback

	function my_plugin_ajax_callback( $args )
	{
		return sprintf( '%s says: Hallo World!', $args['name'] );
	}

	function my_plugin_ajax_methods( $methods )
	{
		$methods[] = array
		(
			'id'       => 'my_plugin_callback',
			'callback' => 'my_plugin_ajax_callback'
		);

		return $methods;
	}

	add_filter( 'motionmill_ajax_methods', 'my_plugin_ajax_methods' );

	### ajax call

	var data =
	{
		action : Motionmill.ajaxEvent,
		method : 'my_plugin_callback',
		args   : { name : 'John' }
	};

	jQuery.post( Motionmill.ajaxurl, data, null, 'json' )

		.done( function( response )
		{
			if ( response.success )
			{
				alert( response.data );
			}
		});

Changelog
---------

_1.0.0_

First release.

Dependencies
------------

[Motionmill](https://github.com/addwittz/motionmill)

