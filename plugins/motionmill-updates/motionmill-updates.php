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
			add_filter( 'pre_set_site_transient_update_plugins', array( &$this, 'on_update_plugins' ) );
		}

		public function on_update_plugins( $data )
		{
			$plugins = MM()->get_plugins_data('extern');
			$plugins[ plugin_basename( Motionmill::FILE ) ] = get_plugin_data( trailingslashit( WP_PLUGIN_DIR ) . plugin_basename( Motionmill::FILE ) );

			foreach ( $plugins as $file => $plugin )
			{
				$file = trim( $file, '/' );
				$repo = dirname( $file );

				$versions = MM( 'GitHub' )->get_versions( $repo );

				if ( is_wp_error( $versions ) || empty( $versions ) )
				{
					continue;
				}

				$latest_version = $versions[ count( $versions ) - 1 ];

				if ( version_compare( $latest_version,$plugin['Version'], '<=' ) )
				{
					continue;
				}

				$releases = MM( 'GitHub' )->get_releases( $repo );

				if ( is_wp_error( $releases ) || empty( $releases ) || ! isset( $releases[ $latest_version ] ) )
				{
					continue;
				}

				$latest_release = $releases[ $latest_version ];

				$response = new stdClass();
				$response->id          = $file;
				$response->slug        = $file;
				$response->plugin      = $file;
				$response->new_version = $latest_version;
				$response->url         = $plugin['PluginURI'];
				$response->package     = $latest_release;

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
