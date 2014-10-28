<?php

class MM_Settings_Field_types
{
	static public function initialize()
	{
		add_filter( 'motionmill_settings_field_types', array( __CLASS__, 'register' ) , 5 );
	}

	static public function register( $types )
	{
		$types[] = array
		(
			'id'       => 'textfield',
			'callback' => array( __CLASS__, 'type_textfield'),
			'styles'   => array(),
			'scripts'  => array()
		);

		$types[] = array
		(
			'id'       => 'password',
			'callback' => array( __CLASS__, 'type_password'),
			'styles'   => array(),
			'scripts'  => array()
		);

		$types[] = array
		(
			'id'       => 'textarea',
			'callback' => array( __CLASS__, 'type_textarea'),
			'styles'   => array(),
			'scripts'  => array()
		);

		$types[] = array
		(
			'id'       => 'checkbox',
			'callback' => array( __CLASS__, 'type_checkbox'),
			'styles'   => array(),
			'scripts'  => array()
		);

		$types[] = array
		(
			'id'       => 'dropdown',
			'callback' => array( __CLASS__, 'type_dropdown'),
			'styles'   => array(),
			'scripts'  => array()
		);

		$types[] = array
		(
			'id'       => 'media',
			'callback' => array( __CLASS__, 'type_media'),
			'styles'   => array( 'thickbox' ),
			'scripts'  => array
			(
				array( 'media-upload' ),
				array( 'thickbox' ),
				array( 'mm-media', plugins_url('../js/media.js', __FILE__) )
			)
		);

		$types[] = array
		(
			'id'       => 'editor',
			'callback' => array( __CLASS__, 'type_editor'),
			'styles'   => array(),
			'scripts'  => array()
		);

		$types[] = array
		(
			'id'       => 'colorpicker',
			'callback' => array( __CLASS__, 'type_colorpicker'),
			'styles'   => array
			(
				array( 'mm-colorpicker', plugins_url('../css/colorpicker.css', __FILE__) )
			),
			'scripts'  => array
			(
				array( 'iris' ),
				array( 'mm-colorpicker', plugins_url('../js/colorpicker.js', __FILE__ ) )
			)
		);

		$types[] = array
		(
			'id'       => 'code',
			'callback' => array( __CLASS__, 'type_code'),
			'styles'   => array(),
			'scripts'  => array()
		);

		return $types;
	}

	static function description( $value, $args = array() )
	{
		$options = array_merge( array
		(
			'wrapper' => '<p class="description">%s</p>'
		), $args);

		if ( $value == '' )
		{
			return '';
		}

		printf( $options['wrapper'], $value );
	}

	static public function type_textfield( $args = array() )
	{
		$options = array_merge(array
		(
			'id'          => '',
			'class'       => 'regular-text',
			'name'        => '',
			'value'       => '',
			'extra'       => '',
			'description' => ''
		), $args);

		printf( '<input type="text" id="%s" class="%s" name="%s" value="%s" %s />', esc_attr($options['id']), esc_attr($options['class']), esc_attr($options['name']), esc_attr($options['value']), $options['extra'] );
	
		self::description( $options['description'] );
	}

	static public function type_textarea( $args = array() )
	{
		$options = array_merge(array
		(
			'id'          => '',
			'class'       => 'large-text',
			'name'        => '',
			'value'       => '',
			'cols'        => 50,
			'rows'        => get_option( 'default_post_edit_rows', 10 ),
			'extra'       => '',
			'description' => ''
		), $args);

		printf( '<textarea id="%s" class="%s" name="%s" cols="%s" rows="%s" %s>%s</textarea>', esc_attr($options['id']), esc_attr($options['class']), esc_attr($options['name']), esc_attr($options['cols']), esc_attr($options['rows']), esc_html($options['extra']), esc_textarea($options['value']) );
		
		self::description( $options['description'] );
	}

	static public function type_checkbox( $args = array() )
	{
		$options = array_merge(array
		(
			'id'          => '',
			'class'       => '',
			'name'        => '',
			'value'       => '',
			'extra'       => '',
			'description' => ''
		), $args);

		$options['extra'] .= checked( $options['value'], 1, false );

		printf( '<input type="checkbox" id="%s" class="%s" name="%s" value="1" %s />', esc_attr($options['id']), esc_attr($options['class']), esc_attr($options['name']), $options['extra'] );
		
		self::description( $options['description'], array
		(
			'wrapper' => '<span class="description">%s</span>'
		));
	}

	static public function type_dropdown( $args = array() )
	{
		$options = array_merge(array
		(
			'id'          => '',
			'class'       => '',
			'name'        => '',
			'options'     => array(),
			'value'       => '',
			'extra'       => '',
			'multiple'    => false,
			'description' => ''
		), $args);

		if ( $options['multiple'] )
		{
			$options['extra'] .= ' multiple="multiple"';
			$options['name']  .= '[]';
		}

		else
		{
			$options['value'] = array( $options['value'] );
		}

		printf( '<select id="%s" class="%s" name="%s" %s>', esc_attr($options['id']), esc_attr($options['class']), esc_attr($options['name']), $options['extra'] );

		foreach ( $options['options'] as $key => $value )
		{
			if ( in_array( $key, $options['value'] ) )
			{
				$selected = ' selected="selected"';
			}

			else
			{
				$selected = '';
			}

			printf( '<option value="%s"%s>%s</option>', esc_attr($key), $selected, esc_html($value) );
		}

		print( '</select>' );

		self::description( $options['description'] );
	}

	static public function type_password( $args = array() )
	{
		$options = array_merge(array
		(
			'id'          => '',
			'class'       => 'regular-text',
			'name'        => '',
			'value'       => '',
			'extra'       => '',
			'description' => ''
		), $args);

		printf( '<input type="password" id="%s" class="%s" name="%s" value="%s" %s />', esc_attr($options['id']), esc_attr($options['class']), esc_attr($options['name']), esc_attr($options['value']), $options['extra'] );
		
		self::description( $options['description'] );
	}

	static public function type_media( $args = array() )
	{
		$options = array_merge(array
		(
			'id'          => '',
			'class'       => 'regular-text mm-media',
			'name'        => '',
			'value'       => '',
			'extra'       => '',
			'description' => ''
		), $args);

		printf( '<input type="text" id="%s" class="%s" name="%s" value="%s" %s />', esc_attr($options['id']), esc_attr($options['class']), esc_attr($options['name']), esc_attr($options['value']), $options['extra'] );
		printf( '<a href="#%s" class="button mm-media-button">%s</a>', esc_attr($options['id']), __('Insert Media', Motionmill::TEXTDOMAIN) );
		
		self::description( $options['description'] );
	}

	static public function type_colorpicker( $args = array() )
	{
		$options = array_merge(array
		(
			'id'          => '',
			'class'       => 'regular-text mm-colorpicker',
			'name'        => '',
			'value'       => '',
			'extra'       => '',
			'description' => ''
		), $args);

		printf( '<input type="text" id="%s" class="%s" name="%s" value="%s" %s />', esc_attr($options['id']), esc_attr($options['class']), esc_attr($options['name']), esc_attr($options['value']), $options['extra'] );
		
		self::description( $options['description'] );
	}

	static public function type_editor( $args = array() )
	{
		$options = array_merge(array
		(
			'id' 		  	=> '',
			'class' 	  	=> '',
			'name'       	=> '',
			'value'       	=> '',
			'rows'        	=> get_option( 'default_post_edit_rows', 10 ),
			'wpautop' 		=> true,
			'media_buttons' => true,
			'editor_css' 	=> '',
			'teeny' 		=> false,
			'dfw' 			=> false,
			'tinymce' 		=> true,
			'quicktags' 	=> true,
			'description'   => ''
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

		self::description( $options['description'] );
	}

	static public function type_code( $args = array() )
	{
		$options = array_merge(array
		(
			'class'       => 'large-text code',
		), $args);

		self::type_textarea( $options );
	}
}

MM_Settings_Field_types::initialize();

?>