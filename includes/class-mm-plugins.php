<?php if ( ! defined( 'ABSPATH' ) ) exit; // exits when accessed directly

class MM_Plugins
{
	public function __construct()
	{
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	
	public function get_plugins()
	{
		$dir = trailingslashit( dirname( plugin_basename( Motionmill::FILE ) ) ) . 'plugins';
		
		$plugins = array();

		foreach ( get_plugins( '/' . $dir ) as $file => $data )
		{
			if ( ! $this->is_motionmill( $file ) )
			{
				continue;
			}

			// makes file relative to the WordPress plugin directory

			$plugins[] = trailingslashit( $dir ) . $file;
		}

		return $plugins;
	}

	public function get_absolute_path( $plugin )
	{
		return trailingslashit( WP_PLUGIN_DIR ) . plugin_basename( $plugin );
	}

	public function get_active()
	{
		return (array) get_option( 'motionmill_active_plugins' );
	}

	public function is_active( $plugin )
	{
		return in_array( plugin_basename( $plugin ) , $this->get_active() );
	}

	public function is_motionmill( $plugin )
	{
		return ( stripos( plugin_basename( $plugin ), 'motionmill-' ) === 0 );
	}

	public function load( $plugin )
	{
		require_once( $this->get_absolute_path( $plugin ) );
	}

	public function load_textdomain( $plugin )
	{
		$dir = dirname( $this->get_absolute_path( $plugin ) ) . trailingslashit( 'languages' );

		if ( ! file_exists( $dir ) )
		{
			return;
		}

		load_plugin_textdomain( Motionmill::TEXTDOMAIN, false, $dir );
	}

	public function activate( $plugin )
	{
		if ( $this->is_active( $plugin ) )
		{
			return;
		}

		$plugin = plugin_basename( $plugin );

		do_action( 'activate_' . $plugin );

		$active_plugins = $this->get_active();

		$active_plugins[] = $plugin;

		sort( $active_plugins );

		update_option( 'motionmill_active_plugins', $active_plugins );
	}

	public function deactivate( $plugin )
	{
		if ( ! $this->is_active( $plugin ) )
		{
			return;
		}

		$plugin = plugin_basename( $plugin );

		do_action( 'deactivate_' . $plugin );
	
		$index = array_search( $plugin, $this->get_active() );

		$active_plugins = array_splice( $this->get_active(), $index, 1 );

		update_option( 'motionmill_active_plugins', $active_plugins );
	}

	public function uninstall( $plugin )
	{
		// deactivates plugin

		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
		{
			$this->deactivate( $plugin );
		}

		// loads plugin uninstall file

		$file = trailingslashit( dirname( $this->get_absolute_path( $plugin ) ) ) . 'uninstall.php';

		if ( file_exists( $file ) )
		{
			require_once( $file );
		}

		// removes plugin
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
		{
			$this->remove( $plugin );
		}
	}

	public function remove( $plugin )
	{
		$dir = dirname( $this->get_absolute_path( $plugin ) );

		$iterator = new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS );
		$files = new RecursiveIteratorIterator( $iterator, RecursiveIteratorIterator::CHILD_FIRST );
		
		foreach( $files as $file )
		{
		    if ( $file->getFilename() === '.' || $file->getFilename() === '..' )
		    {
		        continue;
		    }
		    
		    if ( $file->isDir() )
		    {
		        rmdir( $file->getRealPath() );
		    }

		    else
		    {
		        unlink( $file->getRealPath() );
		    }
		}

		rmdir( $dir );
	}
}

?>