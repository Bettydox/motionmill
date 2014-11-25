<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Roles
 Plugin URI:
 Description: Manages WordPress roles and capabilities.
 Version: 1.0.2
 Author: Maarten Menten
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

if ( ! class_exists('MM_Roles') )
{
	class MM_Roles
	{
		const FILE = __FILE__;

		public function __construct()
		{
			add_filter( 'motionmill_settings_pages', array( &$this, 'on_settings_pages' ) );
			add_filter( 'motionmill_settings_sections', array( &$this, 'on_settings_sections' ) );
			add_filter( 'motionmill_settings_sanitize_options', array( &$this, 'on_sanitize_options' ), 10, 2 );

			add_action( 'motionmill_init', array( &$this, 'initialize' ) );
		
			register_activation_hook( self::FILE, array( &$this, 'on_activate' ) );
			register_deactivation_hook( self::FILE, array( &$this, 'on_deactivate' ) );
		}

		public function initialize()
		{
			add_action( 'admin_init', array( &$this, 'on_admin_init' ) );
		}

		public function get_roles()
		{
			global $wp_roles;
			
			$roles = $wp_roles->roles;

			unset( $roles['administrator'] );

			return $roles;
		}

		public function get_capabilities()
		{
			$capabilities = array();

			foreach ( get_role( 'administrator' )->capabilities as $key => $value )
			{
				if ( stripos( $key, 'level_' ) === 0 )
				{
					continue;
				}

				$capabilities[] = $key;
			}

			return $capabilities;
		}

		public function on_activate()
		{
			add_role( 'motionmill_client', __( 'Motionmill client', Motionmill::TEXTDOMAIN ), array
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

		public function on_deactivate()
		{
			remove_role( 'motionmill_client' );
		}

		public function on_admin_init()
		{
			foreach ( $this->get_roles() as $role_id => $role )
			{
				add_meta_box( 'motionmill-roles-' . $role_id . '-metabox', $role['name'], array(&$this, 'on_print_capabilities'), 'motionmill_roles', 'normal', 'default', array( 'role_id' => $role_id ) );
			}

			add_meta_box( 'motionmill-roles-create-metabox', __( 'Create role', Motionmill::TEXTDOMAIN ) , array(&$this, 'on_print_metabox_create'), 'motionmill_roles', 'side', 'default' );
			add_meta_box( 'motionmill-roles-delete-metabox', __( 'Delete role', Motionmill::TEXTDOMAIN ) , array(&$this, 'on_print_metabox_delete'), 'motionmill_roles', 'side', 'default' );
			add_meta_box( 'motionmill-roles-save-metabox', __( 'Save changes', Motionmill::TEXTDOMAIN ) , array(&$this, 'on_print_metabox_save'), 'motionmill_roles', 'side', 'default' );
		}

		public function on_print_capabilities( $object, $args )
		{
			$page = MM('Settings')->get_current_page();

			$role = get_role( $args['args']['role_id'] );

			?>

			<ul class="capabilities">
				<?php foreach ( $this->get_capabilities() as $cap ) : 

				$field_check = isset(  $role->capabilities[ $cap ] ) && $role->capabilities[ $cap ];
				$field_title = ucfirst( str_replace( '_' , ' ', $cap ) );
				$field_name  = sprintf( '%s[%s]', $role->name, $cap );

				?>
				<li><label><input type="checkbox" name="<?php echo esc_attr( $field_name ); ?>" value="1"<?php checked( $field_check ); ?>> <?php echo $field_title; ?></label></li>
				<?php endforeach; ?>
			</ul><br class="clear">

			<?php
		}

		public function on_print_metabox_create()
		{
			?>

			<p>
				<label for="role_name"><?php _e( 'Name', Motionmill::TEXTDOMAIN ); ?></label><br>
				<input type="text" id="role_name" class="widefat" name="role_name">
			</p>

			<?php submit_button( __( 'Create', Motionmill::TEXTDOMAIN ), 'primary', 'create_button', false ); ?>

			<?php
		}

		public function on_print_metabox_delete()
		{
			?>

			<p>
				<select id="delete_role_id" class="widefat" name="delete_role_id">
					<?php foreach ( $this->get_roles() as $role_id => $role ) : ?>
					<option value="<?php echo esc_attr( $role_id ); ?>"><?php echo esc_html( $role['name'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>

			<?php submit_button( __( 'Delete', Motionmill::TEXTDOMAIN ), 'primary', 'delete_button', false ); ?>

			<?php
		}

		public function on_print_metabox_save()
		{
			submit_button();
		}

		public function on_print_general_section()
		{
			wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
			wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );

			?>

			<div id="poststuff">

				<div id="post-body" class="metabox-holder columns-2">

					<div id="postbox-container-1" class="postbox-container">
						<?php do_meta_boxes( 'motionmill_roles', 'side', null ); ?>
					</div>

					<div id="postbox-container-2" class="postbox-container">
						<?php do_meta_boxes( 'motionmill_roles', 'normal', null ); ?>
					</div>

					<br class="clear">

				</div><!-- #post-body -->

			</div><!-- #poststuff -->

			<script type="text/javascript">
				//<![CDATA[
				jQuery(document).ready( function($) {
					// close postboxes that should be closed
					$( '.if-js-closed' ).removeClass( 'if-js-closed' ).addClass( 'closed' );
					// postboxes setup
					postboxes.add_postbox_toggles( 'motionmill_roles' );
				});
				//]]>
			</script>

			<?php
		}

		public function on_sanitize_options( $options, $page )
		{
			if ( $page['id'] == 'motionmill_roles' )
			{
				// creates role
				if ( ! empty( $_POST[ 'create_button' ] ) )
				{
					add_role( sanitize_title( $_POST['role_name'] ), $_POST['role_name'] );
				}

				// deletes role
				if ( ! empty( $_POST[ 'delete_button' ] ) )
				{
					remove_role( $_POST['delete_role_id'] );
				}

				if ( ! empty( $_POST[ 'submit' ] ) )
				{
					// saves role capalitiies
					foreach ( $this->get_roles() as $role_id => $data )
					{
						if ( ! isset( $_POST[ $role_id ] ) )
						{
							continue;
						}

						$input = $_POST[ $role_id ];

						$role = get_role( $role_id );

						foreach ( $this->get_capabilities() as $cap )
						{
							if ( isset( $input[ $cap ] ) )
							{
								$role->add_cap( $cap );
							}

							else
							{
								$role->remove_cap( $cap );
							}
						}
					}
				}
			}

			return $options;
		}

		public function on_settings_pages( $pages )
		{
			$pages[] = array
			(
				'id'            => 'motionmill_roles',
				'title'         => __( 'Roles', Motionmill::TEXTDOMAIN ),
				'description'   => __('', Motionmill::TEXTDOMAIN),
				'submit_button' => false,
				'multilingual'  => false,
				'scripts'       => array( 'common', 'wp-lists', 'postbox' ),
				'styles'        => array
				(
					array( 'motionmill-roles', plugins_url( 'css/style.css', self::FILE ) )
				)
			);

			return $pages;
		}

		public function on_settings_sections( $sections )
		{
			$sections[] = array
			(
				'id' 		  => 'motionmill_roles_general',
				'title' 	  => __( 'Roles', Motionmill::TEXTDOMAIN ),
				'description' => array( &$this, 'on_print_general_section' ),
				'page'        => 'motionmill_roles'
			);

			return $sections;
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
