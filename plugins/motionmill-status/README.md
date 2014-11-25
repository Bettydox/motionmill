Motionmill Status
=================

Creates an admin page where messages can be displayed.

Add a message
-------------

		function my_messages( $messages )
		{
			$messages[] = array
			(
				'id'     => 'my_message',
				'text'   => __( 'Hello world!' ),
				'type'   => 'my_type', // warning | success | error | [custom]
				'author' => ''
			);

			return $messages;
		}

		add_filter( 'motionmill_status_messages', 'my_messages' );

Add a message type
------------------

		function my_message_types( $types )
		{
			$types[] = array
			(
				'id'          => 'my_type',
				'title'       => __( 'My Type', Motionmill::TEXTDOMAIN ),
				'description' => __( '' ),
				'icon'        => 'info-circle' // see: http://fortawesome.github.io/Font-Awesome/icons
			);

			return $types;
		}

		add_filter( 'motionmill_status_message_types', 'my_message_types' );

Screenshots
-----------

![Status page](https://raw.githubusercontent.com/addwittz/motionmill/master/plugins/motionmill-status/screenshot-1.png)

Notes
-----

This plugin is included in the [Motionmill](https://github.com/addwittz/motionmill) plugin.
