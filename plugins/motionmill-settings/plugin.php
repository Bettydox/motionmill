<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists('MM_Settings') )
{
	class MM_Settings extends MM_Plugin
	{
		private $option_name     = '';
		private $page_slug       = '';
		private $page_title      = '';
		private $page_menu_title = '';
		private $page_capability = '';
		private $page_hook       = '';
		private $page_parent     = '';
		private $sections        = array();
		private $fields          = array();
		private $current_section = null;

		public function __construct()
		{
			parent::__construct(array
			(
				'helpers' => array('string', 'array')
			));
		}

		public function initialize()
		{
			$this->page_slug       = 'motionmill_settings';
			$this->option_name     = 'motionmill_options';
			$this->page_title      = __('Motionmill Settings', MM_TEXTDOMAIN);
			$this->page_menu_title = __('Settings', MM_TEXTDOMAIN);
			$this->page_capability = 'manage_options';
			$this->page_parent     = $this->mm->menu_page;

			add_action( 'init', array(&$this, 'on_init'), 100 );
			add_action( 'admin_init', array(&$this, 'on_admin_init') );
			add_action( 'admin_head', array(&$this, 'on_admin_head') );
			add_action( 'admin_enqueue_scripts', array(&$this, 'on_admin_enqueue_scripts') );

			add_action( 'motionmill_admin_menu', array(&$this, 'on_admin_menu') );

			register_deactivation_hook( MM_FILE, array(&$this, 'on_motionmill_deactivate') );
			add_action( 'motionmill_uninstall', array(&$this, 'on_motionmill_uninstall') );
		}

		public function get_option($section = '', $name = null, $default = '')
		{
			$section = mm_clean_path($section);

			$options = get_option( $this->option_name, $this->get_default_options() );

			if ( $section == '' )
			{
				return $options;
			}

			$options = isset($options[$section]) ? $options[$section] : $this->get_default_options($section);

			if ( ! $name )
			{
				return $options;
			}

			if ( isset($options[$name]) )
			{
				return $options[$name];
			}

			return $default;
		}

		public function get_default_options($section = '')
		{
			$options = array();
			
			if ( $section )
			{
				foreach ( mm_get_elements_by( 'section='.mm_clean_path($section), $this->fields ) as $field )
				{
					$options[ $field['name'] ] = $field['value'];
				}
			}

			else
			{
				foreach ( $this->sections as $section )
				{
					$options[ $section['path'] ] = $this->get_default_options( $section['path'] );
				}
			}

			return $options;
		}

		public function on_init()
		{
			// following code is not placed inside admin_init handler cause
			// the default options (set in the field data) won't be accessible in frontend

			// sets settings sections
			foreach( apply_filters( 'motionmill_settings_sections', array() ) as $data )
			{
				if ( empty($data['name']) )
					continue;

				$section = array_merge(array
				(
					'id'            => $this->page_slug . '-section-' . ( count($this->sections) + 1 ),
					'name'          => $data['name'],
					'title'         => $data['name'],
					'description'   => '',
					'path'          => ! empty($data['parent']) ? mm_clean_path($data['parent']) . '/' . $data['name'] : $data['name'],
					'parent'	    => ! empty($data['parent']) ? mm_clean_path($data['parent']) : '',
					'link'          => '',
					'sanitize_cb'   => '',
					'submit_button' => true
				), (array) $data );

				if ( $section['link'] == '' )
					$section['link'] = $section['path'];
				
				// defaults
				$this->sections[] = $section;
			}

			// sets settings fields
			foreach ( apply_filters( 'motionmill_settings_fields', array() ) as $field )
			{
				if ( empty($field['name']) )
					continue;

				// defaults
				$this->fields[] = array_merge(array
				(
					'id'          => $this->page_slug . '-field-' . ( count($this->fields) + 1 ),
					'name'        => $field['name'],
					'title'       => $field['name'],
					'description' => '',
					'type'        => 'textfield',
					'value'       => '',
					'section'     => ! empty($field['section']) ? mm_clean_path($field['section']) : '',
					'tooltip'     => true
				), $field);
			}
		}

		public function on_admin_init()
		{
			// registers a setting for our page
			register_setting( $this->page_slug, $this->option_name, array(&$this, 'on_sanitize_options') );

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
					$callback = create_function('$a', 'echo "' . $section['description'] . '";');
				}

				add_settings_section( $section['id'], $section['title'], $callback, $section['id'] );
			}

			// registers fields
			foreach ( $this->fields as $field )
			{
				$section = mm_get_element_by( 'path='.$field['section'], $this->sections );

				if ( ! $section )
					continue;

				add_settings_field( $field['id'], $field['title'] , array(&$this, 'form_' . $field['type']), $section['id'], $section['id'], array_merge($field, array
				(
					'label_for' => $this->page_slug . '-' . $field['name'],
					'id' 		=> $this->page_slug . '-' . $field['name'],
					'name'      => esc_attr( $this->option_name . '[' . $section['path'] . '][' . $field['name'] . ']' ),
					'value'     => $this->get_option( $section['path'], $field['name'] )
				)));
			}

			// sets current section
			if ( ! empty($_GET['section']) )
			{
				// user defined
				$this->current_section = mm_get_element_by( 'path='.mm_clean_path($_GET['section']), $this->sections );
			}

			else
			{
				// default section (first level 0 section)
				$this->current_section = mm_get_element_by( 'parent=', $this->sections );

				while ( $this->current_section && $this->current_section['path'] != $this->current_section['link'] ) {
				
					$this->current_section = mm_get_element_by( 'path='.$this->current_section['link'], $this->sections );

				}
			}
		}

		public function on_sanitize_options($input)
		{
			$section_id = isset($_POST['section']) ? $_POST['section'] : null;

			$section = mm_get_element_by( 'id='.$section_id, $this->sections );

			if ( $section && is_callable($section['sanitize_cb']) )
			{
				$input = call_user_func( $section['sanitize_cb'], $input );
			}

			return $input;
		}

		public function on_admin_menu()
		{
			$this->page_hook = add_submenu_page( $this->page_parent, $this->page_title, $this->page_menu_title, $this->page_capability, $this->page_slug, array(&$this, 'on_print_menu_page') );
		}

		public function on_admin_head()
		{
			// checks if current screen is our settings page
			$screen = get_current_screen();
			
			if ( $screen->id != $this->page_hook )
				return;

			// notifies observers
			if ( $this->current_section )
			{
				do_action( $this->page_slug . '_head', $this->current_section['path'] );
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
			if ( $this->current_section )
			{
				do_action( $this->page_slug . '_enqueue_scripts', $this->current_section['path'] );
			}
		}

		public function on_print_menu_page()
		{
			// gets navigation items
			$nav_items = array();

			if ( $this->current_section )
			{
				$current_path = explode( '/', $this->current_section['path'] );

				for ( $i = 0; $i < count($current_path); $i++ )
				{ 
					$path = implode( '/', array_slice($current_path, 0, $i + 1) );

					$s = mm_get_element_by( 'path='.$path, $this->sections );
					$siblings = mm_get_elements_by( 'parent='.$s['parent'], $this->sections );
					
					$nav_items[] = array
					(
						'selected' => $s['id'],
						'items'    => $siblings
					);
				}

				$children = mm_get_elements_by('parent='.$this->current_section['path'], $this->sections );
			}
			else
			{
				$children = mm_get_elements_by('parent=', $this->sections );
			}
			
			// adds the child sections of the current section
			if ( count($children) > 0 )
			{
				$nav_items[] = array
				(
					'selected' => '',
					'items'    => $children
				);
			}
			
			?>

			<div class="wrap">

				<!-- heading -->
				<?php screen_icon('options-general'); ?>
				<h2><?php echo esc_html( $this->page_title ); ?></h2>
				
				<?php if ( count($this->sections) == 0 ) : ?>
				<p><?php _e('No settings available', MM_TEXTDOMAIN); ?></p>
				<?php else : ?>

				<!-- navigation -->
				<?php foreach ( $nav_items as $depth => $nav ) : ?>
				<?php if ( $depth == 0 ) : ?>
				<h2 class="nav-tab-wrapper depth-<?php echo $depth; ?>">
					<?php foreach ( $nav['items'] as $item ) : ?>
					<a href="?page=<?php echo esc_attr($this->page_slug); ?>&section=<?php echo esc_attr($item['link']); ?>" class="nav-tab<?php echo $item['id'] == $nav['selected'] ? ' nav-tab-active' : ''; ?>"><?php echo esc_html($item['title']); ?></a>
					<?php endforeach; ?>
				</h2>
				<?php else : ?>
				<ul class="subsubsub depth-<?php echo $depth; ?>">
					<?php $i=0; foreach ($nav['items'] as $item ) : ?>
					<li><a href="?page=<?php echo esc_attr($this->page_slug); ?>&section=<?php echo esc_attr($item['link']); ?>" class="<?php echo $item['id'] == $nav['selected'] ? 'current' : ''; ?>"><?php echo esc_html($item['title']); ?></a></li>
					<?php if ( $i < count($nav['items']) - 1 ) : ?> | <?php endif; ?>
					<?php $i++; endforeach; ?>
				</ul><br class="clear" />
				<?php endif; ?>
				<?php endforeach; ?>

				<!-- content -->
				<?php if ( ! $this->current_section ) : ?>
				<p><?php _e('The requested section could not be found.', MM_TEXTDOMAIN); ?></p>
				<?php else : ?>

				<?php settings_errors( $this->page_slug ); ?>

				<form action="options.php" method="post">

					<input type="hidden" name="section" value="<?php echo esc_attr( $this->current_section['id'] ); ?>">

					<?php settings_fields( $this->page_slug ); ?>
					<?php do_settings_sections( $this->current_section['id'] ); ?>

					<?php if ( $this->current_section['submit_button'] ) : ?>
					<?php submit_button(); ?>
					<?php endif; ?>
					
				</form>

				<?php endif; ?>
				<?php endif; ?>

			</div><!-- .wrap -->

			<?php
		}

		public function on_motionmill_deactivate()
		{
			unregister_setting( $this->page_slug, $this->option_name, array(&$this, 'on_sanitize_options') );
		}

		public function on_motionmill_uninstall()
		{
			// deletes options from database
			delete_option( $this->option_name );
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

	function mm_settings_register($plugins)
	{
		$plugins[] = 'MM_Settings';

		return $plugins;
	}

	add_action( 'motionmill_plugins', 'mm_settings_register', 1 );
}
?>