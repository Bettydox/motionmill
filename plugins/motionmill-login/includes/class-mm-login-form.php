<?php

class MM_Login_Form
{
	protected $errors = null;

	public function __construct()
	{
		add_filter( 'motionmill_helpers', array( &$this, 'on_helpers' ) );
		
		add_action( 'motionmill_init', array( &$this, 'initialize' ) );
	}

	public function initialize()
	{
		if ( ! MM( 'Login' )->get_option( 'enable' ) )
		{
			return;
		}

		add_filter( 'login_form_top', array(&$this, 'on_login_form_top'), 10, 2 );
		add_filter( 'login_form_middle', array(&$this, 'on_login_form_middle'), 10, 2 );
		add_filter( 'login_form_bottom', array(&$this, 'on_login_form_bottom'), 10, 2 );

		add_shortcode( 'motionmill-login-form', array( &$this, 'get_form' ) );

		add_action( 'init', array( &$this, 'authenticate' ) );
	}

	public function on_login_form_top( $html, $args )
	{
		return $html;
	}

	public function on_login_form_middle( $html, $args )
	{
		$html .= MM( 'Login' )->get_option( 'message' );

		return $html;
	}

	public function on_login_form_bottom( $html, $args )
	{
		$html .= MM( 'Login' )->get_option( 'footer' );

		return $html;
	}

	public function on_helpers( $helpers )
	{
		array_push( $helpers , 'MM_Form' );

		return $helpers;
	}

	public function authenticate()
	{
		if ( empty( $_POST[ Motionmill::NONCE_NAME ] ) )
		{
			return;
		}

		$nonce = $_POST[ Motionmill::NONCE_NAME ];

		if ( ! wp_verify_nonce( $nonce, 'motionmill_login_form_authenticate' ) )
		{
			return;
		}

		$user = wp_authenticate( MM_Form::get_post( 'log' ), MM_Form::get_post( 'pwd' ) );

		if ( ! is_wp_error( $user ) )
		{
			wp_redirect( $this->get_option( 'redirect_url' ) ); exit;
		}

		$this->errors = $user;
	}

	public function get_form( $args = array() )
	{
		$options = shortcode_atts( array
		(
			'echo'           => false,
	        'redirect'       => site_url( $_SERVER['REQUEST_URI'] ), 
	        'form_id'        => 'loginform',
	        'label_username' => __( 'Username' ),
	        'label_password' => __( 'Password' ),
	        'label_remember' => __( 'Remember Me' ),
	        'label_log_in'   => __( 'Log In' ),
	        'id_username'    => 'user_login',
	        'id_password'    => 'user_pass',
	        'id_remember'    => 'rememberme',
	        'id_submit'      => 'wp-submit',
	        'remember'       => true,
	        'value_username' => NULL,
	        'value_remember' => false
		), $args );

		if ( is_wp_error( $this->errors ) && count( $this->errors->get_error_messages() ) > 0 )
		{
			printf( '<div class="error">%s</div>', implode( '<br>', $this->errors->get_error_messages() ) );
		}

		$form = wp_login_form( $options );

		$dom = new DOMDocument();
		$dom->loadHTML( $form );

		foreach ( $dom->getElementsByTagName( 'form' ) as $f )
		{
			$f->setAttribute( 'action', '' );

			$input = $dom->createElement( 'input' );

			$attr = $dom->createAttribute( 'type' );
			$attr->value = 'hidden';

			$input->appendChild( $attr );

			$attr = $dom->createAttribute( 'name' );
			$attr->value = Motionmill::NONCE_NAME;

			$input->appendChild( $attr );

			$attr = $dom->createAttribute( 'value' );
			$attr->value = wp_create_nonce( 'motionmill_login_form_authenticate' );

			$input->appendChild( $attr );

			$f->appendChild( $input );
		}
		
		$dom->removeChild( $dom->firstChild ); // removes <!DOCTYPE 
		$dom->replaceChild( $dom->firstChild->firstChild->firstChild, $dom->firstChild ); // removes <html><body></body></html> 

		return $dom->saveHTML();

	}
}

$mm_login_form = new MM_Login_Form();

?>