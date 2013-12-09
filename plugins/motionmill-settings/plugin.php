<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists('MM_Settings') )
{
	class MM_Settings extends MM_Plugin
	{
		private $page_slug       = '';
		private $page_hook       = '';
		private $pages        	 = array();
		private $sections        = array();
		private $fields          = array();
		private $current_page    = null;

		public function __construct()
		{
			parent::__construct(array
			(
				'helpers' => array('string', 'array')
			));
		}

		public function initialize()
		{
			$this->option_name = 'motionmill_options';
			$this->page_slug   = 'motionmill_settings';

			add_action( 'init', array(&$this, 'on_init'), 100 );
			add_action( 'admin_init', array(&$this, 'on_admin_init') );
			add_action( 'admin_head', array(&$this, 'on_admin_head') );
			add_action( 'admin_enqueue_scripts', array(&$this, 'on_admin_enqueue_scripts') );

			add_action( 'motionmill_admin_menu', array(&$this, 'on_admin_menu') );

			register_deactivation_hook( MM_FILE, array(&$this, 'on_deactivate') );
			add_action( 'motionmill_uninstall', array(&$this, 'on_uninstall') );
		}

		public function get_option($page_id, $name = null, $default = '')
		{
			$page = mm_get_element_by('id='.$page_id, $this->pages);

			if ( $page )
			{
				$options = get_option( $page['option_name'], $this->get_default_options($page_id) );

				if ( ! $name )
				{
					return $options;
				}

				if ( isset($options[$name]) )
				{
					return $options[$name];
				}
			}

			return $default;
		}

		public function get_default_options($page_id)
		{
			$options = array();
			
			foreach ( mm_get_elements_by( 'page='.$page_id, $this->fields ) as $field )
			{
				$options[ $field['id'] ] = $field['value'];
			}
			
			return $options;
		}

		public function on_init()
		{
			// following code is not placed inside admin_init handler cause
			// the default options (set in the field data) won't be accessible in frontend

			// sets settings pages
			foreach ( apply_filters( 'motionmill_settings_pages', array() ) as $data )
			{
				if ( empty($data['id']) )
					continue;

				$this->pages[] = array_merge(array
				(
					'id'            => $data['id'],
					'title'         => $data['id'],
					'description'   => '',
					'option_name'   => $data['id'],
					'sanitize_cb'   => array(&$this, 'on_sanitize_options'),
					'submit_button' => true
				), (array) $data );
			}

			// sets settings sections
			foreach( apply_filters( 'motionmill_settings_sections', array() ) as $data )
			{
				if ( empty($data['id']) )
					continue;

				$this->sections[] = array_merge(array
				(
					'id'            => $data['id'],
					'title'         => $data['id'],
					'description'   => '',
					'page'          => ''
				), (array) $data );
			}

			// sets settings fields
			foreach ( apply_filters( 'motionmill_settings_fields', array() ) as $data )
			{
				if ( empty($data['id']) )
					continue;

				$this->fields[] = array_merge(array
				(
					'id'          => $data['id'],
					'title'       => $data['id'],
					'description' => '',
					'type'        => 'textfield',
					'value'       => '',
					'tooltip'     => true,
					'page'        => '',
					'section'     => ''
				), $data);
			}
		}

		public function on_print_section_description($args)
		{
			$section = mm_get_element_by( 'id='.$args['id'], $this->sections );

			echo $section['description'];
		}

		public function on_admin_init()
		{
			// registers a setting per page
			foreach ( $this->pages as $page )
			{
				register_setting( $page['id'], $page['option_name'], $page['sanitize_cb'] );
			}

			// registers sections
			foreach ( $this->sections as $section )
			{
				if ( is_array($section['description']) )
				{
					$callback = $section['description'];
				}

				else if ( strpos( $section['description'], 'callback_' ) === 0 )
				{
					$callback = substr( $section['description'], strlen('callback_') );
				}
				else
				{
					$callback = array(&$this, 'on_print_section_description');
				}

				add_settings_section( $section['id'], $section['title'], $callback, $section['page'] );
			}

			// registers fields
			foreach ( $this->fields as $field )
			{
				$page = mm_get_element_by( 'id='.$field['page'], $this->pages );

				if ( ! $page )
					continue;

				add_settings_field( $field['id'], $field['title'] , array(&$this, 'form_' . $field['type']), $field['page'], $field['section'], array_merge($field, array
				(
					'label_for' => $this->page_slug . '-' . $field['id'],
					'id' 		=> $this->page_slug . '-' . $field['id'],
					'name'      => esc_attr( $page['option_name'] . '[' . $field['id'] . ']' ),
					'value'     => $this->get_option( $page['id'], $field['id'] )
				)));
			}

			// sets current page
			if ( ! empty($_GET['sub']) )
			{
				// user defined
				$this->current_page = mm_get_element_by( 'id='.$_GET['sub'], $this->pages );
			}

			elseif ( count($this->pages) > 0 )
			{
				$this->current_page = $this->pages[0];
			}

			else
			{
				$this->current_page = null;
			}
		}

		public function on_sanitize_options($input)
		{
			// notifies observers
			if ( $this->current_page )
			{
				$input = apply_filters( 'motionmill_settings_sanitize_options_' . $this->current_page['id'], $input );
			}

			return $input;
		}

		public function on_admin_menu()
		{
			$this->page_hook = add_submenu_page( $this->mm->menu_page, __('Motionmill Settings', MM_TEXTDOMAIN), __('Settings', MM_TEXTDOMAIN), 'manage_options', $this->page_slug, array(&$this, 'on_print_menu_page') );
		}

		public function on_admin_head()
		{
			// checks if current screen is our settings page
			$screen = get_current_screen();
			
			if ( $screen->id != $this->page_hook )
				return;

			// notifies observers
			if ( $this->current_page )
			{
				do_action( 'motionmill_settings_head', $this->current_page['id'] );
			}
		}

		public function on_admin_enqueue_scripts()
		{
			// checks if current screen is our settings page
			$screen = get_current_screen();
			
			if ( $screen->id != $this->page_hook )
				return;

			// tooltip
			wp_enqueue_style( 'tiptip', plugins_url('css/tipTip.css', __FILE__), array(), '1.3', 'all' );
			wp_enqueue_script( 'tiptip', plugins_url('js/jquery.tipTip.minified.js', __FILE__), array('jquery'), '1.3', true );

			// colorpicker
			wp_enqueue_script( 'iris' );

			// general
			wp_enqueue_style( 'motionmill-settings-style',  plugins_url('css/settings.css', __FILE__), array(), '1.0.0', 'all' );
			wp_enqueue_script( 'motionmill-settings-script',  plugins_url('js/settings.js', __FILE__), array('jquery'), '1.0.0', true );

			// notifies observers
			if ( $this->current_page )
			{
				do_action( 'motionmill_settings_enqueue_scripts', $this->current_page['id'] );
			}
		}

		public function on_print_menu_page()
		{
			?>

			<div class="wrap">

				<!-- heading -->
				<?php screen_icon('options-general'); ?>
				<h2><?php _e('Motionmill Settings', MM_TEXTDOMAIN); ?></h2>
				
				<?php if ( count($this->pages) == 0 ) : ?>
				<p><?php _e('No settings available', MM_TEXTDOMAIN); ?></p>
				<?php else : ?>

				<!-- navigation -->
				<h2 class="nav-tab-wrapper">
				<?php foreach ( $this->pages as $page ) : ?>
					<a href="?page=<?php echo esc_attr($this->page_slug); ?>&sub=<?php echo esc_attr($page['id']); ?>" class="nav-tab<?php echo $this->current_page && $page['id'] == $this->current_page['id'] ? ' nav-tab-active' : ''; ?>"><?php echo esc_html($page['title']); ?></a>
				<?php endforeach; ?>
				</h2>
				
				<!-- content -->
				<?php if ( ! $this->current_page ) : ?>
				<p><?php _e('The requested page could not be found.', MM_TEXTDOMAIN); ?></p>
				<?php else : ?>

				<?php settings_errors( $this->current_page['id'] ); ?>

				<form action="options.php" method="post">

					<?php settings_fields( $this->current_page['id'] ); ?>
					<?php do_settings_sections( $this->current_page['id'] ); ?>

					<?php if ( $this->current_page['submit_button'] ) : ?>
					<?php submit_button(); ?>
					<?php endif; ?>
					
				</form>

				<?php endif; ?>
				<?php endif; ?>

			</div><!-- .wrap -->

			<?php
		}

		public function on_deactivate()
		{
			foreach ( $this->pages as $page )
			{
				unregister_setting( $page['id'], $page['option_name'], $page['sanitize_cb'] );
			}
		}

		public function on_uninstall()
		{
			// deletes options from database
			foreach ( $this->pages as $page )
			{
				delete_option( $page['option_name'] );
			}
		}

		private function form_input($args = array())
		{
			$options = array_merge(array
			(
				'type'        => 'text',
				'id' 		  => '',
				'class' 	  => 'regular-text',
				'name'        => '',
				'value'       => '',
				'extra'       => '',
				'description' => '',
				'tooltip'     => false,
				'append'      => '',
				'prepend'     => ''
			), $args);

			printf('%s<input type="%s" id="%s" class="%s" name="%s" value="%s"%s />%s', $options['prepend'], esc_attr($options['type']), esc_attr($options['id']), esc_attr($options['class']), esc_attr($options['name']), esc_attr($options['value']), esc_attr($options['extra']), $options['append'] );

			if ( $options['description'] != '' ) 
			{
				printf( '<div class="description%s" data-field="%s">%s</div>', $options['tooltip'] ? ' tooltip-content hide-if-js' : '', $options['id'], $options['description'] );
			}
		}

		public function form_textfield($args = array())
		{
			$this->form_input(array_merge($args, array
			(
				'type' => 'text'
			)));
		}

		public function form_password($args = array())
		{
			$this->form_input(array_merge($args, array
			(
				'type' => 'password'
			)));
		}

		public function form_image($args = array())
		{
			$this->form_input(array_merge($args, array
			(
				'type' => 'text',
				'append' => ! empty($args['value']) ? sprintf( '<img src="%s" class="motionmill-settings-image" />', $args['value'] ) : ''
			)));
		}

		public function form_checkbox($args = array())
		{
			$this->form_input(array_merge($args, array
			(
				'type'  => 'checkbox',
				'value' => '1',
				'class' => '',
				'extra' => checked( !empty($args['value']), 1, false)
			)));
		}

		public function form_colorpicker($args = array())
		{
			$this->form_input(array_merge($args, array
			(
				'type' => 'text',
				'class' => 'regular-text colorpicker'
			)));
		}

		public function form_textarea($args = array())
		{
			$options = array_merge(array
			(
				'id' 		  => '',
				'class' 	  => 'large-text',
				'name'        => '',
				'value'       => '',
				'rows'        => get_option('default_post_edit_rows', 10),
				'cols'        => '50',
				'description' => '',
				'tooltip'     => false
			), $args);

			printf('<textarea id="%s" class="%s" name="%s" cols="%s" rows="%s">%s</textarea>', esc_attr($options['id']), esc_attr($options['class']), esc_attr($options['name']), esc_attr($options['cols']), esc_attr($options['rows']), esc_html($options['value']) );
			
			if ( $options['description'] != '' ) 
			{
				printf( '<div class="description%s" data-field="%s">%s</div>', $options['tooltip'] ? ' tooltip-content hide-if-js' : '', $options['id'], $options['description'] );
			}
		}

		public function form_editor($args = array())
		{
			$options = array_merge(array
			(
				'id' 		  	=> '',
				'class' 	  	=> '',
				'name'       	=> '',
				'value'       	=> '',
				'rows'        	=> get_option('default_post_edit_rows', 10),
				'wpautop' 		=> true,
				'media_buttons' => true,
				'editor_css' 	=> '',
				'teeny' 		=> false,
				'dfw' 			=> false,
				'tinymce' 		=> true,
				'quicktags' 	=> true,
				'description' => '',
				'tooltip'     => false
			), $args);

			wp_editor( $options['value'], $options['id'], array
			(
				'wpautop' 		=> $options['wpautop'],
				'media_buttons' => $options['media_buttons'],
				'textarea_name' => $options['name'],
				'textarea_rows' => $options['rows'],
				'editor_css' 	=> $options['editor_css'],
				'editor_class' 	=> $options['class'],
				'teeny' 		=> $options['teeny'],
				'dfw' 			=> $options['dfw'],
				'tinymce' 		=> $options['tinymce'],
				'quicktags' 	=> $options['quicktags']
			));

			if ( $options['description'] != '' ) 
			{
				printf( '<div class="description%s" data-field="%s">%s</div>', $options['tooltip'] ? ' tooltip-content hide-if-js' : '', $options['id'], $options['description'] );
			}
		}

		public function form_dropdown($args = array())
		{
			$options = array_merge(array
			(
				'id' 		  => '',
				'class' 	  => 'large-text',
				'name'        => '',
				'value'       => '',
				'options'     => array(),
				'description' => '',
				'tooltip'     => false
			), $args);

			printf( '<select id="%s" class="%s" name="%s">', esc_attr($options['id']), esc_attr($options['class']), esc_attr($options['name']) );
			
			foreach ( $options['options'] as $key => $value )
			{
				printf( '<option value="%s"%s>%s</option>', esc_attr($key), selected($key, $options['value'], false), esc_html($value) );
			}

			echo '</select>';

			if ( $options['description'] != '' )
			{
				printf( '<div class="description%s" data-field="%s">%s</div>', $options['tooltip'] ? ' tooltip-content hide-if-js' : '', $options['id'], $options['description'] );
			}
		}
	}

	function motionmill_settings_register($plugins)
	{
		$plugins[] = 'MM_Settings';

		return $plugins;
	}

	add_action( 'motionmill_plugins', 'motionmill_settings_register', 0 );
}
?>