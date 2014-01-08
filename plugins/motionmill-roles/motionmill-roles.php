<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Roles
 Plugin URI: http://motionmill.com
 Description: Customizes the WordPress roles.
 Version: 1.0.0
 Author: Motionmill
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

add_action( 'motionmill_loaded', function(){

if ( ! class_exists('MM_Roles') )
{
	class MM_Roles extends MM_Plugin
	{
		protected $roles = array();
		protected $caps = array();

		public function __construct()
		{
			parent::__construct();
		}

		public function initialize()
		{	
			add_filter( 'motionmill_settings_pages', array(&$this, 'on_settings_pages') );
			add_filter( 'motionmill_settings_sections', array(&$this, 'on_settings_sections') );
			add_filter( 'motionmill_settings_fields', array(&$this, 'on_settings_fields') );
			add_filter( 'motionmill_settings_sanitize_input', array(&$this, 'on_sanitize_input'), 10, 2 );
			add_action( 'motionmill_settings_print_field_type_role_caps', array(&$this, 'on_print_field_type_role_caps') );

			register_deactivation_hook( MM_FILE, array(&$this, 'on_deactivate') );

			global $wp_roles;

			$this->roles = $wp_roles->roles;
			unset( $this->roles['administrator'] );

			$this->caps  = $this->get_capabilities();

			if ( ! get_role('motionmill-client') )
			{
				add_role( 'motionmill-client', __( 'Motionmill Client', MM_TEXTDOMAIN ), array
				(
					'switch_themes'          => false,
					'edit_themes'            => false,
					'activate_plugins'       => false,
					'edit_plugins'           => false,
					'edit_users'             => true,
					'edit_files'             => true,
					'manage_options'         => false,
					'moderate_comments'      => true,
					'manage_categories'      => true,
					'manage_links'           => true,
					'upload_files'           => true,
					'import'                 => true,
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
					'delete_users'           => true,
					'create_users'           => true,
					'unfiltered_upload'      => true,
					'edit_dashboard'         => true,
					'update_plugins'         => false,
					'delete_plugins'         => false,
					'install_plugins'        => false,
					'update_themes'          => false,
					'install_themes'         => false,
					'update_core'            => false,
					'list_users'             => true,
					'remove_users'           => true,
					'add_users'              => true,
					'promote_users'          => false,
					'edit_theme_options'     => false,
					'delete_themes'          => false,
					'export'                 => true,
					'copy_posts'             => true
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

		public function on_deactivate()
		{
			remove_role( 'motionmill-client' );
		}

		public function on_settings_pages($pages)
		{
			$pages[] = array
			(
				'id' 		  => 'motionmill_roles',
				'title' 	  => __( 'Roles', MM_TEXTDOMAIN ),
				'description' => __( '', MM_TEXTDOMAIN )
			);

			return $pages;
		}

		public function on_settings_sections($sections)
		{
			foreach ( $this->roles as $role_id => $role )
			{
				$sections[] = array
				(
					'id' 		  => 'role_' . $role_id,
					'title' 	  => $role['name'],
					'description' => __( '', MM_TEXTDOMAIN ),
					'page'        => 'motionmill_roles'
				);
			}

			return $sections;
		}

		public function on_settings_fields($fields)
		{
			foreach ( $this->roles as $role_id => $role )
			{
				$fields[] = array
				(
					'id' 		  => 'role_' . $role_id . '_caps',
					'title' 	  => __( 'Capabilities', MM_TEXTDOMAIN ),
					'description' => __( '', MM_TEXTDOMAIN ),
					'type'		  => 'role_caps',
					'value'       => array(),
					'page'        => 'motionmill_roles',
					'section'     => 'role_' . $role_id,
					'_role'       => $role_id
				);
			}

			return $fields;
		}

		public function on_print_field_type_role_caps( $field )
		{
			$role_id = $field['_role'];
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
					$cap = $i < count($this->caps) ? $this->caps[$i] : null;

					printf( '<td style="width: %s;">', floor( 100 / $cols) .'%' );

					if ( $cap )
					{
						printf( '<label><input type="checkbox" name="motionmill_roles[%s][%s]"%s />%s</label>', 
							esc_attr( $role_id ), esc_attr( $cap ), checked( ! empty($role['capabilities'][$cap]), true, false ), esc_html( str_replace( '_', ' ', $cap ) ) );
					}

					print( '</td>' );
				}

				print( '</tr>' );
			}

			print( '</table>' );
		}

		public function on_sanitize_input($input, $page_id)
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

});

?>