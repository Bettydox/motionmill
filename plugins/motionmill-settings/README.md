Motionmill Settings
===================

Settings Page
-------------

This example creates a settings page.

	  function my_settings_pages( $pages )
	  {
	    $pages[] = array
	    (
		'id'          	=> 'my_settings_page',
		'title'		=> __( 'My Settings Page' ),
		'description' 	=> __( 'A brief description about this page.' )
	    );
	    
	    return $pages;
	  }
  
  	add_filter( 'motionmill_settings_pages', 'my_settings_pages' );


### Options

__id__

(_String_) (_required_) The id of the page. Must be unique.

__title__

(_String_) (_optional_) The title of the page. default: id

__menu_title__

(_String_) (_optional_) The menu title of the page. default: id

__capability__

(_String_) (_optional_) The capability required for this page to be displayed to the user. default: 'manage_options'

__menu_slug__

(_String_) (_optional_) The slug name to refer to this page

__parent_slug__

(_String_) (_optional_) The slug name for the parent page. default: 'motionmill'

__description__

(_String_) (_optional_) The page description

__option_name__

(_String_) (_optional_) The option name where the field data will be stored into.

__submit_button__

(_Boolean_) (_optional_) Wether to display the submit button. default: true

__priority__

(_Number_) (_optional_) The menu index of this page. default: 10

__admin_bar__

(_Boolean_) (_optional_) Wether to display this page in the admin bar. default: true

__scripts__

(_Array_) (_optional_) The scripts for this page. default: (empty array)

see [wp\_enqueue_script](http://codex.wordpress.org/Function_Reference/wp_enqueue_script)

list of handles

  	array( 'jquery-color', 'jquery-masonry' )

list of arguments

	  array
	  (
		array( 'my-plugins', 'http://domain.com/js/plugins.js', array('jquery'), true ),
		array( 'my-script', 'http://domain.com/js/scripts.js', array('jquery'), true )
	  )
  
combined

  	array
  	(
  		'jquery',
		array( 'my-script', 'http://domain.com/js/scripts.js', null, true )
  	)

__styles__

(_Array_) (_optional_) The styles for this page. default: (empty array)

see [wp\_enqueue_style](http://codex.wordpress.org/Function_Reference/wp_enqueue_style)

same usage as the __scripts__ parameter

Settings Section
----------------

This example creates a settings section.

		function my_settings_sections( $sections )
		{
			$sections[] = array
			(
				'id'          	=> 'my_settings_section',
				'title'		    => __( 'My Section' ),
				'description' 	=> __( 'A brief description about this section.' ),
				'page'          => 'my_settings_page'
			);
		
			return $sections;
		}
		  
		add_filter( 'motionmill_settings_sections', 'my_settings_sections' );
  
### Options

__id__

(_String_) (_required_) The id of the section. Must be unique.

__title__

(_String_) (_optional_) The title of the section. default: id

__description__

(_mixed_) (_optional_) The section description or a callback. default: ''

__page__

(_String_) (_optional_) The page id to which the section belongs. default: 'motionmill'


Settings Fields
---------------

This example creates a settings field.

		function my_settings_fields( $fields )
		{
			$fields[] = array
			(
				'id'          	=> 'my_textfield',
				'title'		    => __( 'My Section' ),
				'description' 	=> __( 'A brief description about this section.' ),
				'type'          => 'textfield',
				'value'         => '',
				'page'          => 'my_settings_page',
				'section'       => 'my_settings_section'
			);
		
			return $fields;
		}
		  
		add_filter( 'motionmill_settings_fields', 'my_settings_fields' );
		
### Options

__id__

(_String_) (_required_) The id. Must be unique per page.

__title__

(_String_) (__optional__) The title of the field. default: id

__description__

(_String_) (__optional__) The description.

__type__

(_String_) (__optional__) The field type. default: 'textfield'

possible values: textfield, textarea, checkbox, dropdown, editor, colorpicker, media or a custom type

__type_args__

(__Array__) (__optional__) Additional arguments voor the field type.

__value__

(_String_) (_required_) The default value. default: ''

__page__

(_String_) (_required_) The id of the page. default: 'motionmill'

__section__

(_String_) (_required_) The id of the section.


Field Types
-----------

This example creates a field type.

		function my_settings_field_types( $types )
		{
			$types[] = array
			(
				'id'       => 'my_type',
				'callback' => 'my_type_callback',
				'styles'   => array(),
				'scripts'  => array()
			);
		
			return $types;
		}
		  
		add_filter( 'motionmill_settings_field_types', 'my_settings_field_types' );
		
		function my_type_callback( $args = array() )
		{
			
			$options = array_merge(array
			(
				'id'          => '',
				'name'        => '',
				'value'       => '',
				'description' => ''
			), $args);
		
			// print the field
		}
		
### Options

__id__

(_String_) (_required_) The id

__callback__

(_String_) (_required_) The callback that prints the field.

__styles__

(_String_) (_optional_) styles to enqueue. default: (empty array)

see page options

__scripts__

(_String_) (_optional_) scripts to enqueue. default: (empty array)

see page options


Download
--------

This plugin is included in the [Motionmill](https://github.com/addwittz/motionmill) plugin.

