<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Status
 Plugin URI: https://github.com/addwittz/motionmill/tree/master/plugins/motionmill-status
 Description: Creates an admin page where messages can be displayed.
 Version: 1.0.0
 Author: Maarten Menten
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

if ( ! class_exists( 'MM_Status' ) )
{
	class MM_Status
	{
		const FILE = __FILE__;

		protected $message_types = array();
		protected $messages      = array();

		public function __construct()
		{	
			require_once( plugin_dir_path( self::FILE ) . 'includes/messages.php' );

			add_filter( 'motionmill_helpers', array( &$this, 'on_helpers' ) );
			add_filter( 'motionmill_settings_pages', array( &$this, 'on_settings_pages' ) );
			add_filter( 'motionmill_settings_sections', array( &$this, 'on_settings_sections' ) );
			add_filter( 'motionmill_dashboard_widget_messages', array( &$this, 'on_dashboard_widget_messages' ) );

			add_action( 'motionmill_init', array( &$this, 'initialize' ), 3 );
		}
		
		public function initialize()
		{
			foreach ( apply_filters( 'motionmill_status_message_types', array() ) as $data )
			{
				if ( empty( $data['id'] ) )
				{
					continue;
				}

				$this->message_types[] = array_merge( array
				(
					'id'          => '',
					'title'       => '',
					'description' => '',
					'icon'        => ''
				), $data );
			}

			foreach ( apply_filters( 'motionmill_status_messages', array() ) as $data )
			{
				if ( empty( $data['id'] ) )
				{
					continue;
				}

				$this->messages[] = array_merge( array
				(
					'id'        => '',
					'text'      => '',
					'type'      => apply_filters( 'motionmill_status_default_message_type', '' ),
					'author'    => null,
					'dashboard' => false
				), $data );
			}
		}

		public function get_message_type( $search )
		{
			return MM_Array::get_element_by( $search, $this->message_types );
		}

		public function get_message_types( $search = '' )
		{
			return MM_Array::get_elements_by( $search, $this->message_types );
		}

		public function get_message( $search )
		{
			return MM_Array::get_element_by( $search, $this->messages );
		}

		public function get_messages( $search = null )
		{
			return MM_Array::get_elements_by( $search, $this->messages );
		}

		public function print_messages()
		{
			?>

			<?php if ( count( $this->get_messages() ) == 0 ) : ?>
			
			<p><?php _e( 'No messages to display.', Motionmill::TEXTDOMAIN ); ?></p>

			<?php else :

			$message_type_current = isset( $_GET['type'] ) ? $_GET['type'] : '';

			if ( $message_type_current )
			{
				$message_search = array( 'type' => $message_type_current );
			}

			else
			{
				$message_search = '';
			}

			$messages = $this->get_messages( $message_search );

			?>

			<ul class="mm-status-messages-menu subsubsub">
				<?php $message_count = count( $this->get_messages() ); ?>
				<li><a href="?page=motionmill_status&type=" class="<?php echo $message_type_current == '' ? ' current' : '' ?>"><?php _e( 'All', Motionmill::TEXTDOMAIN ); ?></a> (<?php echo $message_count; ?>)</li>
				<?php foreach ( $this->get_message_types() as $message_type ) :

				$message_type_messages = $this->get_messages( array( 'type' => $message_type['id'] ) );
				$message_count = count( $message_type_messages );
	
				if ( $message_count === 0 )
				{
					continue;
				}

				?>
				<li><a href="?page=motionmill_status&type=<?php echo urlencode( $message_type['id'] ); ?>" class="<?php echo $message_type_current == $message_type['id'] ? ' current' : '' ?>"><?php echo $message_type['title']; ?></a> (<?php echo $message_count ?>)</li>
				<?php endforeach; ?>
			</ul><!-- .mm-status-messages-menu -->

			<br class="clear">

			<table class="motionmill-status-messages wp-list-table widefat mm-status-current-message-type-<?php echo esc_attr( $message_type_current ); ?>">

				<thead>
					
					<tr>
						<th class="message-icon"></th>
						<th class="message-text"><?php _e( 'Message', Motionmill::TEXTDOMAIN ); ?></th>
						<th class="message-author"><?php _e( 'Author', Motionmill::TEXTDOMAIN ); ?></th>
					</tr>

				</thead>

				<tbody id="the_list">
					
					<?php foreach ( $messages as $message ) :

						$message_type = $this->get_message_type( array( 'id' => $message['type'] ) );

						$css_classes = array();
						$css_classes[] = 'mm-status-message';
						$css_classes[] = sprintf( 'mm-status-message-type-%s', $message_type['id'] );

					?>
						
					<tr class="<?php echo implode( ' ' , $css_classes ); ?>">
						<td class="message-icon" title="<?php echo esc_attr( $message_type['title'] ) ?>"><?php echo MM_Common::get_icon( $message_type['icon'] ); ?></td>
						<td class="message-text"><?php echo $message['text']; ?></td>
						<td class="message-author"><?php echo $message['author']; ?></td>
					</tr>

					<?php endforeach; ?>
					
				</tbody>

			</table><!-- .motionmill-status-messages -->

			<?php endif; ?>

			<?php
		}

		public function on_dashboard_widget_messages( $messages )
		{
			$messages = $this->get_messages( array( 'dashboard' => true ) );

			foreach ( $messages as $message )
			{
				$messages[] = array
				(
					'id' => $message['id'],
					'text' => $message['text']
				);
			}

			return $messages;
		}

		public function on_settings_pages( $pages )
		{
			$messages = $this->get_messages( array( 'type' => array( 'error', 'warning' ) ) );

			$pages[] = array
			(
				'id'            => 'motionmill_status',
				'title'         => __( 'Status', Motionmill::TEXTDOMAIN ),
				'description'   => __( '', Motionmill::TEXTDOMAIN ),
				'submit_button' => false,
				'multilingual'  => false,
				'menu_counter'  => count( $messages ),
				'styles'        => array
				(
					array( 'motionmill-status', plugins_url( 'css/style.css', self::FILE ), array( 'font-awesome' ) )
				),
				'priority' => 1000
			);

			return $pages;
		}

		public function on_settings_sections( $sections )
		{
			$sections[] = array
			(
				'id' 		  => 'motionmill_status_general',
				'title' 	  => __( '', Motionmill::TEXTDOMAIN ),
				'description' => array( &$this, 'print_messages' ),
				'page'        => 'motionmill_status',
			);

			return $sections;
		}

		public function on_helpers( $helpers )
		{
			array_push( $helpers, 'MM_Array' );

			return $helpers;
		}
	}
}

// registers plugin
if ( ! function_exists( 'motionmill_plugins_add_status' ) )
{
	function motionmill_plugins_add_status( $plugins )
	{
		$plugins[] = 'MM_Status';

		return $plugins;
	}

	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_status' );
}

?>
