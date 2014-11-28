Motionmill Dashboard Widget
===========================

Displays a widget on the WordPress dashboard page

Usage
-----

### Adds a message to the widget

	function my_plugin_dashboard_widget_messages( $messages )
	{
		$messages[] = array
		(
			'id'   => 'my_plugin_message',
			'text' => __( 'Hello World!' )
		);

		return $messages;
	}

	add_filter( 'motionmill_dashboard_widget_messages', 'my_plugin_dashboard_widget_messages' );

Screenshots
-----------

![Dashboard page](https://raw.githubusercontent.com/addwittz/motionmill/master/plugins/motionmill-dashboard-widget/screenshot-1.png)

Download
--------

This plugin is included in the [Download Motionmill](https://github.com/addwittz/motionmill/releases/latest) plugin.