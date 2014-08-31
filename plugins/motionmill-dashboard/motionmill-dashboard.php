<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Dashboard
 Plugin URI:
 Description:
 Version: 1.0.0
 Author: Maarten Menten
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

if ( ! class_exists( 'MM_Dashboard' ) )
{
	class MM_Dashboard
	{
		const FILE = __FILE__;

		protected $motionmill = null;
		
		public function __construct()
		{	
			add_filter( 'motionmill_settings_pages', array( &$this, 'on_settings_pages' ) );
			add_filter( 'motionmill_settings_sections', array( &$this, 'on_settings_sections' ) );

			add_action( 'motionmill_init', array( &$this, 'initialize' ) );
		}
		
		public function initialize()
		{
			$this->motionmill = Motionmill::get_instance();
			
			add_action( 'admin_init', array( &$this, 'on_admin_init' ) );
		}

		public function on_settings_pages( $pages )
		{
			$pages[] = array
			(
				'id' 		    => 'motionmill_dashboard',
				'title' 	    => __( 'Dashboard', Motionmill::TEXTDOMAIN ),
				'menu_title'    => __( 'Dashboard', Motionmill::TEXTDOMAIN ),
				'parent_slug'   => 'motionmill',
				'menu_slug'     => 'motionmill',
				'priority'      => 0,
				'styles'   		=> array(),
				'scripts'  		=> array( 'common', 'wp-lists', 'postbox' ),
				'submit_button' => false
			);

			return $pages;
		}

		public function on_settings_sections( $sections )
		{
			$sections[] = array
			(
				'id'          => 'motionmill_dashboard_welcome',
				'title'       => __( '', Motionmill::TEXTDOMAIN ),
				'description' => array( &$this, 'on_print_welcome' ),
				'page'        => 'motionmill_dashboard'
			);

			return $sections;
		}

		public function on_admin_init()
		{
			add_meta_box( 'motionmill-dashboard-overview', __( 'At a glance', Motionmill::TEXTDOMAIN ), array(&$this, 'on_print_plugins'), 'motionmill_dashboard', 'normal', 'default' );
			add_meta_box( 'motionmill-dashboard-updates', __( 'Updates', Motionmill::TEXTDOMAIN ), array(&$this, 'on_print_updates'), 'motionmill_dashboard', 'side', 'default' );
			add_meta_box( 'motionmill-dashboard-docs', __( 'Documentation', Motionmill::TEXTDOMAIN ), array(&$this, 'on_print_documentation'), 'motionmill_dashboard', 'side', 'default' );
		}

		public function on_print_welcome()
		{
			wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
			wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );

			$plugin = get_plugin_data( Motionmill::FILE, false );

			?>
				<div class="welcome-panel">

					<div class="welcome-panel-content">

					<h3><?php printf( __( 'Welcome to %s!',  Motionmill::TEXTDOMAIN ), $plugin['Name'] ); ?></h3>

					<p class="about-description"><?php printf( __( 'v.%s', Motionmill::TEXTDOMAIN ), $plugin['Version'] ); ?></p>

					<p><?php echo $plugin['Description']; ?></p>

					<?php if ( trim( $plugin['PluginURI'] ) != '' ) : ?>
					<p><?php printf( __( 'Click <a href="%s" target="_blank">here</a> to read more.', Motionmill::TEXTDOMAIN ), $plugin['PluginURI'] ); ?></p>
					<?php endif ?>

					</div><!-- .welcome-panel-content -->

				</div><!-- .welcome-panel -->

				<div id="poststuff">

					<div id="post-body" class="metabox-holder columns-2">

						<div id="postbox-container-1" class="postbox-container">
							<?php do_meta_boxes( 'motionmill_dashboard', 'side', null ); ?>
						</div>

						<div id="postbox-container-2" class="postbox-container">
							<?php do_meta_boxes( 'motionmill_dashboard', 'normal', null ); ?>
						</div>

					</div><!-- #post-body -->

				</div><!-- #poststuff -->

				<script type="text/javascript">
					//<![CDATA[
					jQuery(document).ready( function($) {
						// close postboxes that should be closed
						$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
						// postboxes setup
						postboxes.add_postbox_toggles('motionmill_dashboard');
					});
					//]]>
				</script>
			
			<?php
		}

		public function on_print_plugins()
		{
			$plugins = array();

			foreach ( $this->motionmill->get_plugins_data( 'intern', 'extern' ) as $file => $plugin )
			{
				if ( $plugin['Description'] == '' )
				{
					continue;
				}

				$plugins[ $file ] = $plugin;
			}

			if ( count( $plugins ) > 0 )
			{
				print '<ul class="list">';

				foreach ( $plugins as $file => $plugin )
				{
					printf( '<li>%s - <span class="description">%s</span></li>', $plugin['Description'], $plugin['Title'] );
				}

				print '</ul>';
			}

			else
			{
				_e( 'No data available.', Motionmill::TEXTDOMAIN );
			}
		}

		public function on_print_updates()
		{
			$plugin_manager = $this->motionmill->get_plugin( 'MM_Plugins' );

			$plugins = $plugin_manager->get_plugins_to_update();

			if ( count( $plugins ) == 0 )
			{
				_e( 'No updates available.', Motionmill::TEXTDOMAIN );
			}

			else
			{
				print '<ul>';

				foreach ( $plugins as $file )
				{
					$data = get_plugin_data( trailingslashit( WP_PLUGIN_DIR ) . $file );

					printf( '<li><h4>%s</h4></li>', $plugin['Name'] );
				}

				print '</ul>';

				_e( 'Check the <a href="%s">updates section</a> for more information.', '?page=motionmill_plugins', Motionmill::TEXTDOMAIN );
			}
		}

		public function on_print_documentation()
		{
			$plugins = array();

			foreach ( $this->motionmill->get_plugins_data( 'intern', 'extern' ) as $file => $plugin )
			{
				if ( $plugin['PluginURI'] == '' )
				{
					continue;
				}

				$plugins[ $file ] = $plugin;
			}

			if ( count( $plugins ) > 0 )
			{
				print '<ul>';

				foreach ( $plugins as $file => $plugin )
				{
					printf( '<li><h4><a href="%s" target="_blank">%s</a></h4></li>', $plugin['PluginURI'], $plugin['Title'] );
				}

				print '</ul>';
			}

			else
			{
				_e( 'No Documentation available.', Motionmill::TEXTDOMAIN );
			}
		}
	}
}

// registers plugin
if ( ! function_exists('motionmill_plugins_add_dashboard') )
{
	function motionmill_plugins_add_dashboard( $plugins )
	{
		$plugins[] = 'MM_Dashboard';

		return $plugins;
	}

	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_dashboard' );
}

?>