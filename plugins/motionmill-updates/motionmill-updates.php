<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Updates
 Plugin URI: https://github.com/addwittz/motionmill/tree/master/plugins/motionmill-updates
 Description: Checks Updates for Motionmill plugins.
 Version: 1.0.3
 Author: Maarten Menten
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

if ( ! class_exists( 'MM_Updates' ) )
{
	class MM_Updates
	{
		const FILE = __FILE__;

		protected $options = array();

		public function __construct()
		{	
			add_filter( 'motionmill_settings_pages', array( &$this, 'on_settings_pages' ) );
			add_filter( 'motionmill_settings_sections', array( &$this, 'on_settings_sections' ) );
			add_filter( 'motionmill_settings_sanitize_options', array( &$this, 'on_sanitize_options' ) );
			
			add_action( 'motionmill_init', array( &$this, 'initialize' ) );
		}

		public function initialize()
		{
			$this->options = apply_filters( 'motionmill_updates_options', array
			(
				'schedule_interval' => 'daily'
			));

			add_action( 'wp', array( &$this, 'run_check_versions_schedule' ) );
			add_action( 'motionmill_updates_check_versions', array( &$this, 'check_versions' ) );
		}

		public function get_updateables()
		{
			$versions = MM()->get_option( 'versions', array(), 'updates' );

			$plugins = array();

			foreach ( $versions as $file => $version )
			{
				if ( is_wp_error( $version ) )
				{
					continue;
				}

				$data = get_plugin_data( trailingslashit( WP_PLUGIN_DIR ) . $file );

				if ( version_compare( $data['Version'], $version, '<' ) )
				{
					continue;
				}

				$plugins[] = $file;
			}

			return $plugins;
		}

		public function get_subjects()
		{	
			$motionmill_file = plugin_basename( Motionmill::FILE );

			$plugins = MM()->get_plugins_data( 'extern' );
			$plugins[ $motionmill_file ] = get_plugin_data( trailingslashit( WP_PLUGIN_DIR ) . $motionmill_file );

			return $plugins;
		}

		public function print_updates()
		{
			$versions    = MM()->get_option( 'versions', array(), 'updates' );
			$last_check  = MM()->get_option( 'versions_last_check', false, 'updates' );
			$errors      = MM()->get_option( 'versions_errors', array(), 'updates' );
			$schedule    = wp_get_schedule( 'motionmill_updates_check_versions' );
			$updateables = $this->get_updateables();

			?>

			<table class="widefat p">
				
				<tr>
					<th><?php _e( 'Plugin', Motionmill::TEXTDOMAIN ); ?></th>
					<th><?php _e( 'Version', Motionmill::TEXTDOMAIN ); ?></th>
					<th><?php _e( 'Latest version', Motionmill::TEXTDOMAIN ); ?></th>
					<th><?php _e( 'Up-to-date', Motionmill::TEXTDOMAIN ); ?></th>
					<th><?php _e( '', Motionmill::TEXTDOMAIN ); ?></th>
				</tr>

				<?php foreach ( $this->get_subjects() as $file => $data ) :

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
						$latest = $update_to_date = __( '-', Motionmill::TEXTDOMAIN );
					}

					if ( isset( $errors[$file] ) )
					{
						$comments = __( 'Update information not available.', Motionmill::TEXTDOMAIN );
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
			
			<?php if ( $schedule ) : ?>
			<p><?php printf( __( 'Updates will be automatically checked %s.', Motionmill::TEXTDOMAIN ), $schedule ); ?></p>
			<?php endif; ?>

			<p>
				<?php if ( $last_check !== false ) : ?>

				<?php printf( __( 'Last checked on %1$s at %2$s.',  Motionmill::TEXTDOMAIN ), date_i18n( get_option( 'date_format' ), $last_check ), date_i18n( get_option( 'time_format' ), $last_check ) ); ?>
				
				<?php endif; ?>

				<?php submit_button( __( 'Check Again', Motionmill::TEXTDOMAIN ), 'secondary', 'submit', false ); ?>
			</p>

			<?php
		}

		public function on_sanitize_options( $options )
		{
			$this->check_versions();

			return $options;
		}

		public function check_versions()
		{
			$checked_versions = MM()->get_option( 'versions', array(), 'updates' );

			$errors   = array();
			$versions = array();

			foreach ( $this->get_subjects() as $file => $data )
			{
				$repo = MM('GitHub')->plugin_to_repo( $file );

				$plugin_versions = MM('GitHub')->get_versions( $repo );

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

			MM()->set_option( 'versions', $versions, 'updates' );
			MM()->set_option( 'versions_errors', $errors, 'updates' );
			MM()->set_option( 'versions_last_check', time(), 'updates' );		
		}

		public function run_check_versions_schedule()
		{
			if ( empty( $this->options['schedule_interval'] ) )
			{
				if ( wp_next_scheduled( 'motionmill_updates_check_versions' ) )
				{
					wp_clear_scheduled_hook( 'motionmill_updates_check_versions' );
				}
			}

			else
			{
				if ( ! wp_next_scheduled( 'motionmill_updates_check_versions' ) )
				{
					wp_schedule_event( time(), $this->options['schedule_interval'], 'motionmill_updates_check_versions' );
				}
			}
		}

		public function on_settings_pages( $pages )
		{
			$pages[] = array
			(
				'id'            => 'motionmill_updates',
				'title'         => __( 'Updates', Motionmill::TEXTDOMAIN ),
				'description'   => __( '', Motionmill::TEXTDOMAIN ),
				'submit_button' => false,
				'menu_counter'  => count( $this->get_updateables() ),
				'priority'      => 3
			);

			return $pages;
		}

		public function on_settings_sections( $sections )
		{
			$sections[] = array
			(
				'id' 		  => 'motionmill_updates_general',
				'title' 	  => __( '', Motionmill::TEXTDOMAIN ),
				'description' => array( &$this, 'print_updates' ),
				'page'        => 'motionmill_updates',
			);

			return $sections;
		}
	}
}

// registers plugin
if ( ! function_exists('motionmill_updates_add_updates') )
{
	function motionmill_updates_add_updates( $plugins )
	{
		$plugins[] = 'MM_Updates';

		return $plugins;
	}

	add_filter( 'motionmill_plugins', 'motionmill_updates_add_updates' );
}

?>
