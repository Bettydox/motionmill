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

	var args : { name : 'John' }

	Motionmill.doAjax( 'my_plugin_callback', args,
	{
		success : function( data )
		{
			alert( data ); // John says: Hallo World!
		}
		
		error : function( message )
		{
			alert( message );
		}
		
		complete : function()
		{
		
		}
	});

Notes
-----

This plugin is included in the [Motionmill](https://github.com/addwittz/motionmill) plugin.

