<?php
/**
* Plugin Name: Motionmill Roles
* Plugin URI:
* Description: Manages client roles.
* Version: 1.0.0
* Author: Maarten Menten
* Author URI: http://motionmill.com
* License: GPL2
*/

if ( ! class_exists('MM_Roles') )
{
	class MM_Roles
	{
		const FILE = __FILE__;

		protected $roles = array();
		protected $caps  = array();

		protected $motionmill = null;

		public function __construct()
		{
			add_filter( 'motionmill_settings_pages', array(&$this, 'on_settings_pages') );
			add_filter( 'motionmill_settings_sections', array(&$this, 'on_settings_sections') );
			add_filter( 'motionmill_settings_sanitize_options', array(&$this, 'on_sanitize_options'), 10, 2 );
			add_action( 'motionmill_init', array( &$this, 'initialize' ) );
		}

		public function initialize()
		{
			$this->motionmill = Motionmill::get_instance();
			
			add_action( 'init', array( &$this, 'on_init' ) );
			add_action( 'admin_init', array(&$this, 'on_admin_init') );

			register_deactivation_hook( self::FILE, array(&$this, 'on_deactivate') );
		}

		public function on_init()
		{	
			global $wp_roles;
			global $wpdb;
			
			$this->roles = $wp_roles->roles;
			$this->caps  = $this->get_capabilities();

			// creates role 
			if ( ! get_role('motionmill-client') )
			{
				add_role( 'motionmill-client', __( 'Motionmill Client', Motionmill::TEXTDOMAIN ), array
				(
					'edit_files'             => true,
					'moderate_comments'      => true,
					'manage_categories'      => true,
					'manage_links'           => true,
					'upload_files'           => true,
					'unfiltered_html'        => true,
					'edit_posts'             => true,
					'edit_others_posts'      => true,
					'edit_published_posts'   => true,
					'publish_posts'          => true,
					'edit_pages'             => true,
					'read'                   => true,
					'edit_others_pages'      => true,
					'edit_published_pages'   => true,
					'publish_pages'          => true,
					'delete_pages'           => true,
					'delete_others_pages'    => true,
					'delete_published_pages' => true,
					'delete_posts'           => true,
					'delete_others_posts'    => true,
					'delete_published_posts' => true,
					'delete_private_posts'   => true,
					'edit_private_posts'     => true,
					'read_private_posts'     => true,
					'delete_private_pages'   => true,
					'edit_private_pages'     => true,
					'read_private_pages'     => true,
					'create_users'           => true,
					'unfiltered_upload'      => true,
					'edit_dashboard'         => true,
					'list_users'             => true,
					'add_users'              => true,
					'export'                 => true,
					'copy_posts'             => true,
					'edit_theme_options'     => true
				));
			}
		}

		public function get_capabilities()
		{
			$caps = array();

			// gets all capabilities from admin
			foreach ( get_role('administrator')->capabilities as $cap => $value )
			{
				// skip when capability is user level
				if ( stripos( $cap, 'level_' ) === 0 )
					continue;

				$caps[] = $cap;
			}

			sort($caps);

			return $caps;
		}

		public function on_admin_init()
		{
			global $wp_roles;

			$caps = array();

			foreach ( $wp_roles->roles as $role_id => $role )
			{
				foreach ( $role['capabilities'] as $cap => $enabled )
				{
					if ( in_array($cap, $caps) )
						continue;

					$caps[] = $cap;
				}
			}
		}

		public function on_deactivate()
		{
			remove_role( 'motionmill-client' );
		}

		public function on_settings_pages($pages)
		{
			$pages[] = array
			(
				'id'          => 'motionmill_roles',
				'title'       => __('Roles', Motionmill::TEXTDOMAIN),
				'description' => __(' Manages client capabilities.', Motionmill::TEXTDOMAIN),
				'option_name' => 'motionmill_roles'
			);

			return $pages;
		}

		public function on_settings_sections($sections)
		{
			$sections[] = array
			(
				'id' 		  => 'motionmill_roles_client',
				'title' 	  => __('Motionmill Client'),
				'description' => array( &$this, 'on_print_settings_section' ),
				'page'        => 'motionmill_roles',
				'args'        => array( 'role' => 'motionmill-client' )
			);

			return $sections;
		}

		public function on_print_settings_section()
		{
			$role_id = 'motionmill-client';
			$role    = $this->roles[ $role_id ];

			$cols = 4;
			$rows = ceil( count($this->caps) / $cols );

			print( '<table style="width: 100%;">' );

			for ( $row = 0; $row < $rows; $row++ )
			{
				print( '<tr>' );

				for ( $col = 0; $col < $cols; $col++ )
				{
					$i 	 = $row * $cols + $col;
					$cap = $i < count( $this->caps ) ? $this->caps[$i] : null;

					printf( '<td style="width: %s;">', floor( 100 / $cols) .'%' );

					if ( $cap )
					{
						printf( '<label><input type="checkbox" name="motionmill_roles[%s][%s]"%s value="1" />%s</label>', 
							esc_attr( $role_id ), esc_attr( $cap ), checked( ! empty($role['capabilities'][$cap]), true, false ), esc_html( str_replace( '_', ' ', $cap ) ) );
					}

					print( '</td>' );
				}

				print( '</tr>' );
			}

			print( '</table>' );
			
		}

		public function on_sanitize_options($input, $page_id)
		{
			if ( $page_id == 'motionmill_roles' )
			{
				foreach ( $this->roles as $role_id => $role )
				{
					if ( $role_id == 'administrator' )
						continue;

					$caps = isset( $input[$role_id] ) ? $input[$role_id] : null;

					$r = get_role($role_id);

					if ( $r && $caps && is_array($caps) )
					{
						foreach ( $this->caps as $cap )
						{
							if ( ! empty( $caps[$cap] ) )
							{
								$r->add_cap( $cap );
							}
							else
							{
								$r->remove_cap( $cap );
							}
						}
					}
				}
			}

			return $input;
		}
	}

	// registers plugin
	function motionmill_plugins_add_roles($plugins)
	{
		array_push($plugins, 'MM_Roles');

		return $plugins;
	}
	
	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_roles', 5 );
}

?>