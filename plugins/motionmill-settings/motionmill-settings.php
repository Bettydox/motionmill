<?php if ( ! defined( 'ABSPATH' ) ) exit; // exits when accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Settings
 Plugin URI:
 Description: Creates admin menu pages.
 Version: 1.0.0
 Author: Maarten Menten
 Author URI: http://maartenmenten.be
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

if ( ! class_exists( 'MM_Settings' ) )
{
	require_once( plugin_dir_path( __FILE__ ) . '/includes/hooks.php' );

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
			add_action( 'motionmill_init', array( &$this, 'initialize' ) );
		}

		public function initialize()
		{
			add_action( 'init', array( &$this, 'on_init' ), 15 );
			add_action( 'admin_init', array( &$this, 'on_admin_init' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'on_admin_enqueue_scripts' ) );
			add_action( 'admin_menu', array( &$this, 'on_admin_menu' ) );
			add_action( 'admin_bar_menu', array( &$this, 'on_admin_bar_menu' ), 100 );

			register_deactivation_hook( self::FILE, array( &$this, 'on_deactivate' ) );
		}

		public function get_pages( $search = '' )
		{
			return MM_Array::get_elements_by( $search, $this->pages );
		}

		public function get_page( $search = '' )
		{
			return MM_Array::get_element_by( $search, $this->pages );
		}

		public function get_sections( $search = '' )
		{
			return MM_Array::get_elements_by( $search, $this->sections );
		}

		public function get_section( $search = '' )
		{
			return MM_Array::get_element_by( $search, $this->sections );
		}

		public function get_fields( $search = '' )
		{
			return MM_Array::get_elements_by( $search, $this->fields );
		}

		public function get_field( $search = '' )
		{
			return MM_Array::get_element_by( $search, $this->fields );
		}

		public function get_field_type( $search = '' )
		{
			return MM_Array::get_element_by( $search, $this->field_types );
		}

		public function get_available_field_types( $page_id )
		{
			$fields = $this->get_fields( array( 'page' => $page_id ) );

			$types = array();

			foreach ( $fields as $field )
			{
				$key = $field['type'];

				if ( isset( $types[ $key ] ) )
				{
					continue;
				}

				$type = $this->get_field_type( array( 'id' => $field['type'] ) );

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
			$page = $this->get_page( array( 'id' => $page_id ), $this->pages );

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
			$fields = $this->get_fields( array( 'page' => $page_id ) );

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

			return $this->get_page( $search );
		}

		public function on_init()
		{
			// sets options
			$this->options = apply_filters( 'motionmill_settings_options', array
			(
				'page_capability'          => 'manage_options',
				'page_parent_slug'         => '',
				'page_option_format'       => '%s',
				'page_admin_bar'     	   => true,
				'page_submit_button' 	   => true,
				'page_title_prefix'        => ''
			));

			// registers pages
			foreach ( apply_filters( 'motionmill_settings_pages', $this->pages ) as $data )
			{
				if ( empty( $data['id'] ) || empty( $data['title'] ) )
				{
					continue;
				}

				// sets default priority
				if ( isset( $data['parent_slug'] ) && $data['parent_slug'] == '' )
				{
					$priority = null;
				}

				else
				{
					$priority = 10;
				}

				$this->pages[] = array_merge( array
				(
					'id'            => $data['id'],
					'title'         => $data['title'],
					'menu_title'    => $data['title'],
					'capability'    => $this->options['page_capability'],
					'menu_slug'     => $data['id'],
					'parent_slug'   => $this->options['page_parent_slug'],
					'description'   => '',
					'option_name'   => sprintf( $this->options['page_option_format'], $data['id'] ),
					'submit_button' => $this->options['page_submit_button'],
					'admin_bar'     => $this->options['page_admin_bar'],
					'priority'      => $priority,
					'styles'   		=> array(),
					'scripts'  		=> array(),
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
					'page'         => ''
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
					'page'         => '',
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
				$page = $this->get_page( array( 'id' => $section['page'] ) );

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
				$page = $this->get_page( array( 'id' => $field['page'] ) );
				$type = $this->get_field_type( array( 'id' => $field['type'] ) );

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
				if ( $page['parent_slug'] != '' )
				{
					continue;
				}

				$page['hook'] = add_menu_page( $page['title'], $page['menu_title'], $page['capability'], $page['menu_slug'], array( &$this, 'on_print_page'), '', $page['priority'] );	
			}

			foreach ( $this->pages as &$page )
			{
				if ( $page['parent_slug'] == '' )
				{
					continue;
				}

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
			$pages = $this->get_pages( array( 'menu_slug' => $page['menu_slug'] ) );

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
		}
	
		public function on_print_page()
		{
			$page = $this->get_current_page();

			if ( $page['parent_slug'] == '' )
			{
				// children of the current page
				$menu_pages = $this->get_pages( array( 'parent_slug' => $page['menu_slug'] ) );
			}

			else
			{
				// siblings of the current page
				$menu_pages = $this->get_pages( array( 'parent_slug' => $page['parent_slug'] ) );
			}

			?>

			<div class="wrap">

				<h2><?php echo esc_html( $this->options['page_title_prefix'] ); ?><?php echo esc_html( $page['title'] ); ?></h2>

				<?php settings_errors(); ?>

				<?php if ( count( $menu_pages ) > 0 ) : ?>
				<h2 class="nav-tab-wrapper">
					<?php foreach ( $menu_pages as $menu_page ) : ?>
					<a href="?page=<?php echo esc_attr( $menu_page['menu_slug'] ); ?>" class="nav-tab<?php echo $menu_page['menu_slug'] == $page['menu_slug'] ? ' nav-tab-active' : ''; ?>"><?php echo esc_html( $menu_page['menu_title'] ); ?></a>
					<?php endforeach; ?>
				</h2><!-- .nav-tab-wrapper -->
				<?php endif; ?>
				
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

	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_settings' );
}

?>