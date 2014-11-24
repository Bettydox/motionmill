<?php if ( ! defined( 'ABSPATH' ) ) exit; // exits when accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Settings
 Plugin URI: https://github.com/addwittz/motionmill/tree/master/plugins/motionmill-settings
 Description: Creates admin menu pages.
 Version: 1.0.5
 Author: Maarten Menten
 Author URI: http://maartenmenten.be
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

if ( ! class_exists( 'MM_Settings' ) )
{
	class MM_Settings
	{	
		const FILE = __FILE__;

		protected $pages       = array();
		protected $sections    = array();
		protected $fields      = array();
		protected $field_types = array();

		public function __construct()
		{
			MM( 'Loader' )->load_class( 'MM_Array' );
			MM( 'Loader' )->load_class( 'MM_WordPress' );
			MM( 'Loader' )->load_class( 'MM_Settings_Field_Types', self::FILE );

			add_action( 'motionmill_init', array( &$this, 'initialize' ), 5 );
			
			register_deactivation_hook( self::FILE, array( &$this, 'on_deactivate' ) );
		}

		public function initialize()
		{
			// registers pages
			foreach ( apply_filters( 'motionmill_settings_pages', array() ) as $data )
			{
				if ( empty( $data['id'] ) || empty( $data['title'] ) )
				{
					continue;
				}

				if ( ( isset( $data['parent_slug'] ) && $data['parent_slug'] == '' ) )
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
					'description'   => '',
					'capability'    => 'manage_options',
					'menu_slug'     => $data['id'],
					'menu_counter'  => false,
					'parent_slug'   => 'motionmill',
					'option_name'   => sprintf( 'motionmill_settings-%s', $data['id'] ),
					'submit_button' => true,
					'priority'      => $priority,
					'styles'        => array(),
					'scripts'       => array(),
					'localize'      => array(),
					'url'           => null, // will be set later
					'hook'          => null, // will be set later
					'depth'         => null  // will be set later
				), $data);
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
					'id'           => $data['id'],
					'title'        => '',
					'type'         => 'textfield',
					'args'         => array(),
					'value'        => '',
					'description'  => '',
					'rules'        => array( 'trim' ),
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
			
			add_action( 'admin_init', array( &$this, 'on_admin_init' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'on_admin_enqueue_scripts' ) );
			add_action( 'admin_menu', array( &$this, 'on_admin_menu' ) );
			add_action( 'admin_bar_menu', array( &$this, 'on_admin_bar_menu' ), 100 );

			add_filter( 'motionmill_settings_sanitize_option', array( &$this, 'on_sanitize_option' ), 5, 2 );
			add_filter( 'motionmill_settings_page_title', array( &$this, 'on_page_title' ), 5, 2 );
		}

		public function get_option( $page_id, $field_id = null, $default = '', $lang = null )
		{
			$page = $this->get_page( array( 'id' => $page_id ) );

			if ( $page )
			{
				if ( ! $lang )
				{
					$lang = MM_Wordpress::get_language_code();
				}

				$options = get_option( $page['option_name'] );

				if ( ! is_array( $options ) )
				{
					$options = array();
				}

				if ( ! isset( $options[$lang] ) )
				{
					$options[ $lang ] = $this->get_default_options( $page['id'] );
				}

				$options = $options[ $lang ];

				if ( ! $field_id )
				{
					return $options;
				}

				if ( isset( $options[ $field_id ] ) )
				{
					return $options[ $field_id ];
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

		public function get_field( $search )
		{
			return MM_Array::get_element_by( $search, $this->fields );
		}

		public function get_field_name( $field_id, $page_id = null )
		{
			if ( $page_id )
			{
				$page = $this->get_page( array( 'id' => $page_id ) );
			}

			else
			{
				$page = $this->get_page();
			}

			if ( ! $page )
			{
				return false;
			}

			return sprintf( '%s[%s][%s]', $page['option_name'], MM_WordPress::get_language_code(), $field_id );
		}

		public function get_fields( $search = null )
		{
			return MM_Array::get_elements_by( $search, $this->fields );
		}

		public function get_section( $search )
		{
			return MM_Array::get_element_by( $search, $this->sections );
		}

		public function get_sections( $search = '' )
		{
			return MM_Array::get_elements_by( $search, $this->sections );
		}

		public function get_page( $search = null )
		{
			if ( $search == null )
			{
				return $this->get_current_page();
			}

			return MM_Array::get_element_by( $search, $this->pages );
		}

		public function get_page_ancestors( $search = null )
		{
			$page = $this->get_page( $search );

			if ( ! $page )
			{
				return false;
			}

			$ancestors = array();

			while ( $page = $this->get_page( array( 'id' => $page['parent_slug'] ) ) )
			{
				array_unshift( $ancestors, $page );
			}

			return $ancestors;
		}

		public function get_page_trail( $search = null )
		{
			$page = $this->get_page( $search );

			if ( ! $page )
			{
				return false;
			}

			$trail = $this->get_page_ancestors( array( 'id' => $page['id'] ) );
			$trail[] = $page;

			return $trail;
		}

		public function is_page_ancestor( $ancestor_id, $search = null )
		{
			$page = $this->get_page( $search );

			if ( ! $page )
			{
				return null;
			}

			$ancestors = $this->get_page_ancestors( array( 'id' => $page['id'] ) );

			foreach ( $ancestors as $ancestor )
			{
				if ( $ancestor['id'] == $ancestor_id )
				{
					return true;
				}
			}

			return false;
		}

		public function get_current_page()
		{
			$screen = get_current_screen();

			if ( isset( $_GET['page'] ) )
			{
				return $this->get_page( array( 'menu_slug' => $_GET['page'] ) );
			}

			// when saving
			if ( $screen->id == 'options' )
			{
				return $this->get_page( array( 'menu_slug' => $_POST['option_page'] ) );
			}

			return null;
		}

		public function get_pages( $search = null )
		{
			return MM_Array::get_elements_by( $search, $this->pages );
		}

		public function get_field_type( $search )
		{
			return MM_Array::get_element_by( $search, $this->field_types );
		}

		public function get_field_types( $search = null )
		{
			return MM_Array::get_elements_by( $search, $this->field_types );
		}

		public function get_page_field_types( $search = null )
		{	
			$page = $this->get_page( $search );

			$types = array();

			$fields = $this->get_fields( array( 'page' => $page['id'] ) );

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

				$args = array_merge( (array) $field['args'], array
				(
					'id'    	  => sprintf( '%s-%s', $page['id'], $field['id'] ),
					'label_for'   => sprintf( '%s-%s', $page['id'], $field['id'] ), // WordPress needs this for the <label> element
					'name'  	  => $this->get_field_name( $field['id'], $page['id'] ),
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
				if ( $page['menu_counter'] )
				{
					$menu_title = sprintf( '%s <span class="update-plugins count-%s"><span class="plugin-count">%s</span></span>', $page['menu_title'], esc_attr( $page['menu_counter'] ), esc_html( $page['menu_counter'] ) );
				}

				else
				{
					$menu_title = $page['menu_title'];
				}

				if ( $page['parent_slug'] )
				{
					$page['hook'] = add_submenu_page( $page['parent_slug'], $page['title'], $menu_title, $page['capability'], $page['menu_slug'], array( &$this, 'on_print_page') );
				}

				else
				{
					$page['hook'] = add_menu_page( $page['title'], $menu_title, $page['capability'], $page['menu_slug'], array( &$this, 'on_print_page'), null, $page['priority'] );
				}

				$page['url']   = admin_url( 'admin.php?page=' . $page['menu_slug'] );
				$page['depth'] = count( $this->get_page_ancestors( array( 'id' => $page['id'] ) ) );
			}
		}

		public function on_admin_bar_menu()
		{
			global $wp_admin_bar;
    		
    		if ( ! is_super_admin() || ! is_admin_bar_showing() )
    		{
    			return;
    		}

    		$wp_admin_bar->add_menu(array
			(
				'id'     => 'motionmill',
				'meta'   => array(),
				'title'  => __( 'Motionmill' ),
				'href'   => admin_url( 'admin.php?page=motionmill' ),
				'parent' => null
		    ));

    		foreach ( $this->pages as $page )
    		{
    			$wp_admin_bar->add_menu(array
    			(
					'id'     => $page['id'],
					'meta'   => array(),
					'title'  => $page['menu_title'],
					'href'   => $page['url'],
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

			$map = array( 'styles' => 'wp_enqueue_style', 'scripts' => 'wp_enqueue_script', 'localize' => 'wp_localize_script' );

			$subjects = array_merge
			(
				$this->get_page_field_types(),
				$this->get_pages( array( 'menu_slug' => $page['menu_slug'] ) )
			);

			foreach ( $subjects as $subject )
			{
				foreach ( $map as $key => $callback )
				{
					if ( ! isset( $subject[$key] ) || ! is_array( $subject[$key] ) )
					{
						continue;
					}
				
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

			wp_enqueue_style( 'motionmill-settings', plugins_url( 'css/style.css', self::FILE ), array( 'font-awesome' ) );

			do_action( 'motionmill_settings_enqueue_scripts', $page['id'] );
		}

		public function on_page_title( $title, $page )
		{
			$title = __( 'Motionmill - ', Motionmill::TEXTDOMAIN ) . $title;

			if ( MM_Wordpress::is_multilingual() )
			{
				$title .= sprintf( ' (%s)', MM_Wordpress::get_language_code() );
			}

			return $title;
		}

		public function on_print_page()
		{
			$page = $this->get_page();

			$menu_pages = array(); 
			$menu_pages[0] = $this->get_pages( array( 'depth' => 0 ) );
			$menu_pages[1] = $this->get_pages( array( 'parent_slug' => $page['parent_slug'], 'depth' => 1 ) );

			?>

			<div class="wrap">

				<h2 class="page-title"><?php echo apply_filters( 'motionmill_settings_page_title', $page['title'], $page ); ?></h2>

				<?php settings_errors(); ?>
				
				<!-- page navigation -->
				<?php for ( $i = 0; $i < count( $menu_pages ); $i++ ) : ?>

				<?php if ( $i == 0 ) : ?>

				<h2 class="nav-tab-wrapper">
					<?php foreach ( $menu_pages[$i] as $menu_page ) : ?>
					<a href="<?php echo esc_attr( $menu_page['url'] ); ?>" class="nav-tab<?php echo ( $menu_page['menu_slug'] == $page['menu_slug'] || $this->is_page_ancestor( $menu_page['id'] ) ) ? ' nav-tab-active' : ''; ?>"><?php echo $menu_page['menu_title']; ?></a>
					<?php endforeach; ?>
				</h2><!-- .nav-tab-wrapper -->

				<?php else : ?>

				<ul class="subsubsub">
					<?php foreach ( $menu_pages[$i] as $menu_page ) : ?>
					<li><a href="<?php echo esc_attr( $menu_page['url'] ); ?>" class="<?php echo ( $menu_page['menu_slug'] == $page['menu_slug'] || $this->is_page_ancestor( $menu_page['id'] ) ) ? 'current' : ''; ?>"><?php echo $menu_page['menu_title']; ?></a></li>
					<?php endforeach; ?>
				</ul><!-- .subsubsub -->
				<br class="clear">

				<?php endif; ?>

				<?php endfor; ?>

				<!-- page content -->
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
			$options = $options[ MM_WordPress::get_language_code() ];

			$page = $this->get_page();

			$fields = $this->get_fields( array( 'page' => $page['id'] ) );

			foreach ( $fields as $field )
			{
				if ( ! isset( $options[ $field['id'] ] ) )
				{
					continue;
				}

				$option = &$options[ $field['id'] ];

				if ( is_array( $field['rules'] ) )
				{
					foreach ( $field['rules'] as $rule )
					{
						$option = apply_filters( 'motionmill_settings_sanitize_option', $option, $rule );
					}
				}
			}

			$options = apply_filters( 'motionmill_settings_sanitize_options', $options, $page );

			$all_options = get_option( $page['option_name'] );

			if ( ! is_array( $all_options ) )
			{
				$all_options = array();
			}

			$all_options[ MM_Wordpress::get_language_code() ] = $options;

			return $all_options;
		}

		public function on_sanitize_option( $option, $rule )
		{
			switch ( $rule )
			{
				case 'trim'	     			: return trim( $option );
				case 'lowercase' 			: return strtolower( $option );
				case 'upercase' 			: return strtoupper( $option );
				case 'sanitize_email'    	: return sanitize_email( $option );
				case 'sanitize_file_name'	: return sanitize_file_name( $option );
				case 'sanitize_html_class'	: return sanitize_html_class( $option );
				case 'sanitize_mime_type'	: return sanitize_mime_type( $option );
				case 'sanitize_text_field'	: return sanitize_text_field( $option );
				case 'sanitize_user'		: return sanitize_user( $option );
				case 'sanitize_title'		: return sanitize_title( $option );
			}

			return $option;
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
	}

	// registers plugin
	function motionmill_plugins_add_settings( $plugins )
	{
		array_push( $plugins , 'MM_Settings' );

		return $plugins;
	}

	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_settings', 99 );
}

?>
