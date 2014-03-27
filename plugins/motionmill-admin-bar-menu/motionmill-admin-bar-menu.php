<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Admin Bar Menu
 Plugin URI: http://motionmill.com
 Description: Creates a Motionmill menu in the Admin bar
 Version: 1.0.0
 Author: Motionmill
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

add_action( 'motionmill_loaded', function(){

if ( ! class_exists('MM_Admin_Bar_Menu') )
{
	class MM_Admin_Bar_Menu extends MM_Plugin
	{
		public function __construct()
		{
			parent::__construct();
		}

		public function initialize()
		{	
			add_action( 'admin_bar_menu', array(&$this, 'on_admin_bar_menu'), 100 );
		}

		public function on_admin_bar_menu()
		{
			global $wp_admin_bar;

		    if ( ! is_super_admin() || ! is_admin_bar_showing() || is_admin() )
		        return;

		    $cap = apply_filters( 'motionmill_admin_bar_menu_cap', 'manage_options' );

		    if ( ! current_user_can($cap) )
		    	return;

		    if ( ! $this->motionmill->page_slug )
		    	return;

		    $wp_admin_bar->add_menu(array
		    (
				'id'     => 'motionmill',
				'meta'   => array(),
				'title'  => __('Motionmill'),
				'href'   => admin_url( 'admin.php?page=' . $this->motionmill->page_slug ),
				'parent' => false
			));

			foreach ( apply_filters( 'motionmill_admin_bar_menu_items', array() ) as $data )
			{
				if ( empty($data['parent']) )
				{
					$data['parent'] = 'motionmill';
				}

				$wp_admin_bar->add_menu($data);
			}
		}
	}

	// registers plugin
	function motionmill_plugins_add_admin_bar_menu($plugins)
	{
		array_push($plugins, 'MM_Admin_Bar_Menu');

		return $plugins;
	}
	
	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_admin_bar_menu', 2 );
}

});

?>