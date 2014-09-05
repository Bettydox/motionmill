<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Updates
 Plugin URI: https://github.com/addwittz/motionmill/tree/master/plugins/motionmill-updates
 Description: Checks Updates for Motionmill plugins.
 Version: 1.0.4
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

		public function __construct()
		{	
			add_action( 'motionmill_init', array( &$this, 'initialize' ) );
		}

		public function initialize()
		{
			add_filter( 'pre_set_site_transient_update_plugins', array( &$this, 'on_transient_update_plugins' ) );

			foreach ( $this->get_updateable_plugins() as $file => $data )
			{
				add_action( sprintf( 'in_plugin_update_message-%s', $file ), array( &$this, 'on_plugin_update_message' ), 10, 2 ); 	
			}
		}

		public function get_updateable_plugins()
		{	
			$motionmill_file = plugin_basename( Motionmill::FILE );

			$plugins = MM()->get_external_plugins();
			$plugins[ $motionmill_file ] = get_plugin_data( trailingslashit( WP_PLUGIN_DIR ) . $motionmill_file );

			return $plugins;
		}

		public function on_plugin_update_message( $data, $response )
		{
			// changes the details link

			$file = $data['plugin'];
			
			$repo = MM()->get_plugin_repository_name( $file );

			$detail_url = MM('GitHub')->get_release_url( $repo, $response->new_version );

			?>

				<script type="text/javascript">

					jQuery(document).ready(function($)
					{
						var wrap = $('#the-list').find( '#motionmill' );
						
						var message = wrap.next().find('.update-message');

						if ( message.length > 0 )
						{
							message.find( 'a.thickbox' )
								.removeClass( 'thickbox' )
								.attr( 'target', '_blank' )
								.attr( 'href', '<?php echo esc_attr( $detail_url ); ?>' );
						};
					});

				</script>

			<?php
		}

		public function on_transient_update_plugins( $data )
		{
			foreach ( $this->get_updateable_plugins() as $file => $plugin )
			{
				$repo = MM()->get_plugin_repository_name( $file );

				$versions = MM( 'GitHub' )->get_versions( $repo );

				if ( is_wp_error( $versions ) || empty( $versions ) )
				{
					continue;
				}

				$new_version = $versions[ count( $versions ) - 1 ];

				if ( version_compare( $new_version, $plugin['Version'], '<=' ) )
				{
					continue;
				}

				$dir = basename( dirname( $file ) );

				$package = sprintf( 'http://motionmill.com/plugins/releases/%s-%s.zip', $dir, $new_version );

				if ( ! MM_Common::url_exists( $package ) )
				{
					continue;
				}

				$latest_release = $releases[ $new_version ];

				$response = new stdClass();
				$response->id          = $file;  // whatever
				$response->slug        = $file;  // whatever
				$response->plugin      = $file;
				$response->new_version = $new_version;
				$response->url         = 'http://motionmill.com'; // whatever
				$response->package     = $package;

				$data->response[ $file ] = $response;
			}

			return $data;
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
