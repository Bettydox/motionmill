<?php if ( ! defined( 'ABSPATH' ) ) exit; // exits when accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Settings
 Plugin URI:
 Description: Creates admin menu pages.
 Version: 1.0.1
 Author: Maarten Menten
 Author URI: http://maartenmenten.be
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

if ( ! class_exists( 'MM_Settings' ) )
{
	require_once( plugin_dir_path( __FILE__ ) . '/includes/field-types.php' );

	class MM_Settings
	{	
		const FILE = __FILE__;

		protected $motionmill  = null;
		protected $options     = array();
		protected $pages       = array();
		protected $sections    = array();
		protected $fields      = array();
		protected $field_types = array();

		public function __construct()
		{
			$this->motionmill = Motionmill::get_instance();

			add_filter( 'motionmill_helpers', array( &$this, 'on_helpers' ) );
			add_action( 'motionmill_init', array( &$this, 'initialize' ), 5 );
		}

		public function initialize()
		{
			// registers pages
			foreach ( apply_filters( 'motionmill_settings_pages', $this->pages ) as $data )
			{
				if ( empty( $data['id'] ) || empty( $data['title'] ) )
				{
					continue;
				}

				$this->pages[] = array_merge( array
				(
					'id'            => $data['id'],
					'title'         => $data['title'],
					'menu_title'    => $data['title'],
					'capability'    => 'manage_options',
					'menu_slug'     => $data['id'],
					'parent_slug'   => 'motionmill',
					'description'   => '',
					'option_name'   => $data['id'],
					'submit_button' => true,
					'priority'      => 10,
					'admin_bar'     => true,
					'styles'        => array(),
					'scripts'       => array(),
					'localize'      => array(),
					'hook'          => '', // will be set later
				), $data );
			}

			usort( $this->pages, array( &$this, 'on_sort_priority' ) );

			// registers sections
			foreach ( apply_filters( 'motionmill_settings_sections', array() ) as $data )
			{
				if ( empty( $data['id'] ) )
				{
					continue;
				}

				$this->sections[] = array_merge( array
				(
					'id' 		   => $data['id'],
					'title'  	   => '',
					'description'  => '',
					'page'         => 'motionmill'
				), $data );
			}

			// registers fields
			foreach ( apply_filters( 'motionmill_settings_fields', array() ) as $data )
			{
				if ( empty( $data['id'] ) )
				{
					continue;
				}

				$this->fields[] = array_merge( array
				(
					'id' 		   => $data['id'],
					'title'  	   => '',
					'type'         => '',
					'type_args'    => array(),
					'value'        => '',
					'description'  => '',
					'page'         => 'motionmill',
					'section'      => ''
				), $data );
			}

			// registers field types
			foreach ( apply_filters( 'motionmill_settings_field_types', array() ) as $data )
			{
				if ( empty( $data['id'] ) )
				{
					continue;
				}

				$this->field_types[] = array_merge(array
				(
					'id'       => $data['id'],
					'callback' => null,
					'styles'   => array(),
					'scripts'  => array()
				), $data );
			}
			
			add_action( 'admin_init', array( &$this, 'on_admin_init' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'on_admin_enqueue_scripts' ) );
			add_action( 'admin_menu', array( &$this, 'on_admin_menu' ) );
			add_action( 'admin_bar_menu', array( &$this, 'on_admin_bar_menu' ), 100 );

			register_deactivation_hook( self::FILE, array( &$this, 'on_deactivate' ) );
		}

		public function get_available_field_types( $page_id )
		{
			$fields = MM_Array::get_elements_by( array( 'page' => $page_id ), $this->fields );

			$types = array();

			foreach ( $fields as $field )
			{
				$key = $field['type'];

				if ( isset( $types[ $key ] ) )
				{
					continue;
				}

				$type = MM_Array::get_element_by( array( 'id' => $field['type'] ), $this->field_types );

				if ( ! $type )
				{
					continue;
				}

				$types[ $key ] = $type;
			}

			return $types;
		}

		public function get_option( $page_id, $field_id = null, $default = '' )
		{
			$page = MM_Array::get_element_by( array( 'id' => $page_id ), $this->pages );

			if ( $page )
			{
				$options = get_option( $page['option_name'], $this->get_default_options( $page['id'] ) );

				if ( ! $field_id )
				{
					return $options;
				}

				if ( isset( $options[$field_id] ) )
				{
					return $options[$field_id];
				}
			}

			return $default;
		}

		public function get_default_options( $page_id )
		{
			$fields =  MM_Array::get_elements_by( array( 'page' => $page_id ), $this->fields );

			$options = array();

			foreach ( $fields as $field )
			{
				$options[ $field['id'] ] = $field['value'];
			}

			return $options;
		}

		public function get_current_page()
		{
			if ( $screen = get_current_screen() )
			{
				$search = array( 'hook' => $screen->id );
			}

			else if ( isset( $_GET['page'] ) )
			{
				$search = array( 'menu_slug' => $_GET['page'] );
			}

			else
			{
				return null;
			}

			return MM_Array::get_element_by( $search, $this->pages );
		}

		public function on_admin_init()
		{
			// registers one option per page
			foreach ( $this->pages as $page )
			{
				register_setting( $page['id'], $page['option_name'], array( &$this, 'on_sanitize_options' ) );
			}

			// adds sections
			foreach ( $this->sections as $section )
			{
				$page =  MM_Array::get_element_by( array( 'id' => $section['page'] ), $this->pages );

				if ( $section['description'] && is_callable( $section['description'] ) )
				{
					$callback = $section['description'];
				}

				else
				{
					$callback = create_function( '$a' , 'echo"' . $section['description'] . '";' );
				}
				
				add_settings_section( $section['id'], $section['title'], $callback, $page['menu_slug'] );
			}

			// adds fields
			foreach ( $this->fields as $field )
			{
				$page = MM_Array::get_element_by( array( 'id' => $field['page'] ), $this->pages );
				$type = MM_Array::get_element_by( array( 'id' => $field['type'] ), $this->field_types );

				$args = array_merge( (array) $field['type_args'], array
				(
					'id'    	  => sprintf( '%s-%s', $page['id'], $field['id'] ),
					'label_for'   => sprintf( '%s-%s', $page['id'], $field['id'] ), // WordPress needs this for the <label> element
					'name'  	  => sprintf( '%s[%s]', $page['option_name'], $field['id'] ),
					'value' 	  => $this->get_option( $page['id'], $field['id'] ),
					'description' => $field['description']
				));

				add_settings_field( $field['id'], $field['title'], $type['callback'], $page['menu_slug'], $field['section'], $args );
			}
		}

		public function on_admin_menu()
		{
			foreach ( $this->pages as &$page )
			{
				$page['hook'] = add_submenu_page( $page['parent_slug'], $page['title'], $page['menu_title'], $page['capability'], $page['menu_slug'], array( &$this, 'on_print_page') );
			}
		}

		public function on_admin_bar_menu()
		{
			global $wp_admin_bar;
    		
    		if ( ! is_super_admin() || ! is_admin_bar_showing() )
    		{
    			return;
    		}

    		foreach ( $this->pages as $page )
    		{
    			if ( ! $page['admin_bar'] )
    			{
    				continue;
    			}

    			$wp_admin_bar->add_menu(array
    			(
					'id'     => $page['id'],
					'meta'   => array(),
					'title'  => $page['menu_title'],
					'href'   => admin_url( 'admin.php?page=' . $page['menu_slug'] ),
					'parent' => $page['parent_slug']
			    ));
    		}
		}

		public function on_admin_enqueue_scripts()
		{
			$page = $this->get_current_page();

			if ( ! $page )
			{
				return;
			}

			$map = array( 'styles' => 'wp_enqueue_style', 'scripts' => 'wp_enqueue_script' );
			
			// gets all field types for this page
			$types = $this->get_available_field_types( $page['id'] );

			// gets all pages with same slug
			$pages = MM_Array::get_elements_by( array( 'menu_slug' => $page['menu_slug'] ), $this->pages );

			$subjects = array_merge( $types, $pages );

			foreach ( $subjects as $subject )
			{
				foreach ( $map as $key => $callback )
				{
					if ( isset( $subject[$key] ) && is_array( $subject[$key] ) )
					{
						foreach ( $subject[$key] as $args )
						{
							if ( ! is_array( $args ) )
							{
								$args = array( $args );
							}

							call_user_func_array( $callback , $args );
						}
					}
				}
			}

			// localizes scripts
			foreach ( $pages as $page )
			{
				if ( ! is_array( $page['localize'] ) )
				{
					continue;
				}

				foreach ( $page['localize'] as $args )
				{
					call_user_func_array( 'wp_localize_script', $args );
				}
			}
		}
	
		public function on_print_page()
		{
			$page = $this->get_current_page();

			?>

			<div class="wrap">

				<h2><?php _e( 'Motionmill - ', Motionmill::TEXTDOMAIN ); ?><?php echo esc_html( $page['title'] ); ?></h2>

				<?php settings_errors(); ?>

				<h2 class="nav-tab-wrapper">
					<?php foreach ( $this->pages as $menu_page ) : ?>
					<a href="?page=<?php echo esc_attr( $menu_page['menu_slug'] ); ?>" class="nav-tab<?php echo $menu_page['menu_slug'] == $page['menu_slug'] ? ' nav-tab-active' : ''; ?>"><?php echo esc_html( $menu_page['menu_title'] ); ?></a>
					<?php endforeach; ?>
				</h2><!-- .nav-tab-wrapper -->
				
				<?php if ( $page['description'] != '' ) : ?>
				<p><?php echo $page['description']; ?></p>
				<?php endif; ?>

				<form action="options.php" method="post">

					<?php settings_fields( $page['menu_slug'] ); ?>

					<?php do_settings_sections( $page['menu_slug'] ); ?>

					<?php if ( $page['submit_button'] ) : ?>
					<?php submit_button(); ?>
					<?php endif; ?>
					
				</form>

			</div><!-- .wrap -->

			<?php
		}

		public function on_sanitize_options( $options )
		{
			return apply_filters( 'motionmill_settings_sanitize_options', $options, $_POST['option_page'] );
		}

		public function on_deactivate()
		{
			foreach ( $this->pages as $page )
			{
				unregister_setting( $page['id'], $page['option_name'], array( &$this, 'on_sanitize_options' ) );
			}
		}

		public function on_sort_priority( $a, $b )
		{
			if ( $a['priority'] == $b['priority'] )
			{
				return 0;
			}

			return $a['priority'] > $b['priority'] ? 1 : -1;
		}

		public function on_helpers( $helpers )
		{
			array_push( $helpers, 'MM_Array' );

			return $helpers;
		}
	}

	// registers plugin
	function motionmill_plugins_add_settings( $plugins )
	{
		array_push( $plugins , 'MM_Settings' );

		return $plugins;
	}

	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_settings', 999 );
}

?>