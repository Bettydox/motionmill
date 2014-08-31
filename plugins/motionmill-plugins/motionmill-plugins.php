<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Plugins
 Plugin URI:
 Description: Manages Motionmill Plugins
 Version: 1.0.0
 Author: Maarten Menten
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

if ( ! class_exists( 'MM_Plugins' ) )
{
	class MM_Plugins
	{
		const FILE = __FILE__;

		protected $motionmill = null;
		
		public function __construct()
		{	
			$this->motionmill = Motionmill::get_instance();

			add_filter( 'motionmill_helpers', array( &$this, 'on_helpers' ) );
			add_filter( 'motionmill_settings_pages', array( &$this, 'on_settings_pages' ) );
			add_filter( 'motionmill_settings_sections', array( &$this, 'on_settings_sections' ) );
			add_filter( 'motionmill_settings_sanitize_options', array( &$this, 'on_sanitize_options' ) );
			
			add_action( 'motionmill_init', array( &$this, 'initialize' ) );
		}

		public function initialize()
		{
			add_action( 'wp', array( &$this, 'schedule_check_plugins_versions' ) );
			add_action( 'motionmill_plugins_check_versions', array( &$this, 'check_plugin_versions' ) );
		}

		public function on_helpers( $helpers )
		{
			array_push( $helpers , 'MM_GitHub', 'MM_Wordpress' );

			return $helpers;
		}

		public function on_settings_pages( $pages )
		{
			$pages[] = array
			(
				'id'            => 'motionmill_plugins',
				'title'         => __( 'Updates', Motionmill::TEXTDOMAIN ),
				'description'   => __( '', Motionmill::TEXTDOMAIN ),
				'submit_button' => false,
				'menu_counter'  => count( $this->get_plugins_to_update() ),
				'priority'      => 3
			);

			return $pages;
		}

		public function on_settings_sections( $sections )
		{
			$sections[] = array
			(
				'id' 		  => 'motionmill_plugins_general',
				'title' 	  => __( '', Motionmill::TEXTDOMAIN ),
				'description' => array( &$this, 'print_plugins_section' ),
				'page'        => 'motionmill_plugins',
			);

			return $sections;
		}

		public function get_plugins_to_update()
		{
			$versions = $this->motionmill->get_option( 'plugin_versions', array() );

			$plugins = array();

			foreach ( $versions as $file => $version )
			{
				if ( is_wp_error( $version ) )
				{
					continue;
				}

				if ( version_compare( $data['Version'], $version, '<' ) )
				{
					continue;
				}

				$plugins[] = $file;
			}

			return $plugins;
		}

		public function get_plugins_to_check()
		{	
			$motionmill_file = plugin_basename( Motionmill::FILE );

			$plugins = $this->motionmill->get_plugins_data( 'extern' );
			$plugins[ $motionmill_file ] = get_plugin_data( trailingslashit( WP_PLUGIN_DIR ) . $motionmill_file );

			return $plugins;
		}

		public function print_plugins_section()
		{
			$versions    = $this->motionmill->get_option( 'plugin_versions', array() );
			$last_check  = $this->motionmill->get_option( 'plugin_versions_last_check', false );
			$errors      = $this->motionmill->get_option( 'plugin_versions_errors', array() );
			$schedule    = wp_get_schedule( 'motionmill_plugins_check_versions' );
			$updateables = $this->get_plugins_to_update();

			?>

			<table class="widefat p">
				
				<tr>
					<th><?php _e( 'Plugin', Motionmill::TEXTDOMAIN ); ?></th>
					<th><?php _e( 'Version', Motionmill::TEXTDOMAIN ); ?></th>
					<th><?php _e( 'Latest version', Motionmill::TEXTDOMAIN ); ?></th>
					<th><?php _e( 'Up-to-date', Motionmill::TEXTDOMAIN ); ?></th>
					<th><?php _e( '', Motionmill::TEXTDOMAIN ); ?></th>
				</tr>

				<?php foreach ( $this->get_plugins_to_check() as $file => $data ) :

					if ( isset( $versions[ $file ] ) )
					{
						$latest = $versions[ $file ];
					
						if ( ! in_array( $file , $updateables ) )
						{
							$update_to_date = sprintf( '<span class="mm-success">%s</span>', __( 'yes', Motionmill::TEXTDOMAIN ) );
						}

						else
						{
							$update_to_date = sprintf( '<span class="mm-error">%s</span>', __( 'no', Motionmill::TEXTDOMAIN ) );
						}
					}

					else
					{
						$latest = $update_to_date = __( '?', Motionmill::TEXTDOMAIN );
					}

					if ( isset( $errors[$file] ) )
					{
						$comments = $errors[$file];
					}

					else
					{
						$comments = '';
					}
				?>

				<tr>
					<td><?php echo esc_html( $data['Name'] ); ?></td>
					<td><?php echo esc_html( $data['Version'] ); ?></td>
					<td><?php echo esc_html( $latest ); ?></td>
					<td><?php echo $update_to_date; ?></td>
					<td><?php echo esc_html( $comments ); ?></td>
				</tr>

				<?php endforeach; ?>

			</table>
			
			<p><?php printf( __( 'Updates will be automatically checked %s.', Motionmill::TEXTDOMAIN ), $schedule ); ?></p>

			<p>
				<?php if ( $last_check !== false ) : ?>

				<?php printf( __( 'last checked on %s at %s.', Motionmill::TEXTDOMAIN ), 
				date( get_option( 'date_format' ), $last_check ), date( get_option( 'time_format' ), $last_check ) ); ?>
				
				<?php endif; ?>

				<?php submit_button( __( 'Check Again', Motionmill::TEXTDOMAIN ), 'secondary', 'submit', false ); ?>

			</p>

			<?php
		}

		public function on_sanitize_options( $options )
		{
			$this->check_plugin_versions();

			return $options;
		}

		public function check_plugin_versions()
		{
			$checked_versions = $this->motionmill->get_option( 'plugin_versions', array() );

			$errors   = array();
			$versions = array();

			foreach ( $this->get_plugins_to_check() as $file => $data )
			{
				$repo = MM_GitHub::plugin_to_repo( $file );

				$plugin_versions = MM_GitHub::get_versions( $repo );

				if ( is_wp_error( $plugin_versions ) )
				{
					$errors[ $file ] = $plugin_versions->get_error_message();

					continue;
				}

				// gets latest verions
				if ( count( $plugin_versions ) > 0 )
				{
					$version = $plugin_versions[ count( $plugin_versions ) - 1 ];
				}

				// gets current version
				else
				{
					$version = $data[ 'Version' ];
				}

				$versions[ $file ] = $version;
			}

			$this->motionmill->set_option( 'plugin_versions', $versions );
			$this->motionmill->set_option( 'plugin_versions_errors', $errors );
			$this->motionmill->set_option( 'plugin_versions_last_check', time() );		
		}

		public function schedule_check_plugins_versions()
		{
			if ( ! wp_next_scheduled( 'motionmill_plugins_check_versions' ) )
			{
				wp_schedule_event( time(), 'daily', 'motionmill_plugins_check_versions' );
			}
		}
	}
}

// registers plugin
if ( ! function_exists('motionmill_plugins_add_plugins') )
{
	function motionmill_plugins_add_plugins( $plugins )
	{
		$plugins[] = 'MM_Plugins';

		return $plugins;
	}

	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_plugins' );
}

?>