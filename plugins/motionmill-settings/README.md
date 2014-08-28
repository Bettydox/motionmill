Motionmill Settings
===================

Page
----

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

see [wp\_enqueue_\script](http://codex.wordpress.org/Function_Reference/wp_enqueue_script)

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

see [wp\_enqueue_\style](http://codex.wordpress.org/Function_Reference/wp_enqueue_style)

same usage as the __scripts__ parameter

### Example

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
  
  
