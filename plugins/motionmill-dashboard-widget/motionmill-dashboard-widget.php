<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Dashboard Widget
 Plugin URI:
 Description: Displays a widget on the WordPress dashboard page
 Version: 1.0.0
 Author: Maarten Menten
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

if ( ! class_exists( 'MM_Dashboard_Widget' ) )
{
	class MM_Dashboard_Widget
	{
		const FILE = __FILE__;

		protected $messages = array();

		public function __construct()
		{	
			MM( 'Loader' )->load_class( 'MM_Array' );

			add_action( 'motionmill_init', array( &$this, 'initialize' ) );
		}
		
		public function initialize()
		{
			foreach ( apply_filters( 'motionmill_dashboard_widget_messages', array() ) as $data )
			{
				if ( empty( $data['id'] ) )
				{
					continue;
				}

				$this->messages[] = array_merge( array
				(
					'id'       => '',
					'text'     => ''
				), $data );
			}

			if ( count( $this->messages ) > 0 )
			{
				add_action( 'wp_dashboard_setup', array( &$this,  'on_wp_dashboard_setup' ) );
			}
		}

		public function get_message( $search )
		{
			return MM_Array::get_element_by( $search, $this->messages );
		}

		public function get_messages( $search = null )
		{
			return MM_Array::get_elements_by( $search, $this->messages );
		}

		public function on_wp_dashboard_setup()
		{
			wp_add_dashboard_widget( 'motionmill', __( 'Motionmill', Motionmill::TEXTDOMAIN ), array( &$this, 'on_print_dashboard_widget' ) );
		}

		public function on_print_dashboard_widget()
		{
			?>

			<ul class="motionmill-dashboard-widget-messages">
				<?php foreach ( $this->get_messages() as $message ): ?>
				<li class="motionmill-dashboard-widget-message-<?php echo esc_attr( $message['id'] ); ?>"><?php echo $message['text']; ?></li>
				<?php endforeach ?>
			</ul>

			<?php
		}
	}
}

// registers plugin
if ( ! function_exists('motionmill_plugins_add_dashboard_widget') )
{
	function motionmill_plugins_add_dashboard_widget( $plugins )
	{
		$plugins[] = 'MM_Dashboard_Widget';

		return $plugins;
	}

	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_dashboard_widget' );
}

?>
