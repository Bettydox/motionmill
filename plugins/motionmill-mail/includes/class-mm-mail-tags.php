<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class MM_Mail_Tags
{
	public function __construct()
	{	
		MM( 'Loader' )->load_class( 'MM_Database' );

		add_filter( 'motionmill_mail_tag_categories', array( &$this, 'on_tag_categories' ) );
		add_filter( 'motionmill_mail_tags', array( &$this, 'on_tags' ) );
		add_filter( 'motionmill_mail_parse_tag', array( &$this, 'on_parse_tag' ), 10, 2 );
	}

	public function on_tag_categories( $categories )
	{
		$categories[] = array
		(
			'id'          => 'general',
			'title'       => __( 'General', Motionmill::TEXTDOMAIN ),
			'description' => __( '', Motionmill::TEXTDOMAIN )
		);

		$categories[] = array
		(
			'id'          => 'blog',
			'title'       => __( 'Blog', Motionmill::TEXTDOMAIN ),
			'description' => __( '', Motionmill::TEXTDOMAIN )
		);

		$categories[] = array
		(
			'id'          => 'user',
			'title'       => __( 'User', Motionmill::TEXTDOMAIN ),
			'description' => __( '', Motionmill::TEXTDOMAIN )
		);

		return $categories;
	}

	public function on_tags( $tags )
	{
		// Blog

		$tags[] = array
		(
			'name'        => 'admin_email',
			'title'       => __( 'Admin e-mail', Motionmill::TEXTDOMAIN ),
			'description' => __( 'The "E-mail address" set in Settings > General.', Motionmill::TEXTDOMAIN ),
			'category'    => 'blog'
		);

		$tags[] = array
		(
			'name'        => 'description',
			'title'       => __( 'Description', Motionmill::TEXTDOMAIN ),
			'description' => __( 'The "Tagline" set in Settings > General.', Motionmill::TEXTDOMAIN ),
			'category'    => 'blog'
		);

		$tags[] = array
		(
			'name'        => 'atom_url',
			'title'       => __( 'Atom URL', Motionmill::TEXTDOMAIN ),
			'description' => __( 'Returns the Atom feed URL (/feed/atom).', Motionmill::TEXTDOMAIN ),
			'category'    => 'blog'
		);

		$tags[] = array
		(
			'name'        => 'charset',
			'title'       => __( 'Charset', Motionmill::TEXTDOMAIN ),
			'description' => __( 'Returns the "Encoding for pages and feeds" set in Settings > Reading.', Motionmill::TEXTDOMAIN ),
			'category'    => 'blog'
		);

		$tags[] = array
		(
			'name'        => 'comments_atom_url',
			'title'       => __( 'Comments Atom URL', Motionmill::TEXTDOMAIN ),
			'description' => __( 'Returns the comments Atom feed URL (/comments/feed)', Motionmill::TEXTDOMAIN ),
			'category'    => 'blog'
		);

		$tags[] = array
		(
			'name'        => 'comments_rss2_url',
			'title'       => __( 'Comments rss2 URL', Motionmill::TEXTDOMAIN ),
			'description' => __( 'Returns the comments RSS 2.0 feed URL (/comments/feed).', Motionmill::TEXTDOMAIN ),
			'category'    => 'blog'
		);

		$tags[] = array
		(
			'name'        => 'html_type',
			'title'       => __( 'HTML type', Motionmill::TEXTDOMAIN ),
			'description' => __( 'Returns the Content-Type of WordPress HTML pages.', Motionmill::TEXTDOMAIN ),
			'category'    => 'blog'
		);

		$tags[] = array
		(
			'name'        => 'language',
			'title'       => __( 'Language', Motionmill::TEXTDOMAIN ),
			'description' => __( 'Returns the language of WordPress.', Motionmill::TEXTDOMAIN ),
			'category'    => 'blog'
		);

		$tags[] = array
		(
			'name'        => 'name',
			'title'       => __( 'Name', Motionmill::TEXTDOMAIN ),
			'description' => __( 'Returns the "Site Title" set in Settings > General', Motionmill::TEXTDOMAIN ),
			'category'    => 'blog'
		);

		$tags[] = array
		(
			'name'        => 'pingback_url',
			'title'       => __( 'Pingback URL', Motionmill::TEXTDOMAIN ),
			'description' => __( 'Returns the Pingback XML-RPC file URL.', Motionmill::TEXTDOMAIN ),
			'category'    => 'blog'
		);

		$tags[] = array
		(
			'name'        => 'rdf_url',
			'title'       => __( 'RDF URL', Motionmill::TEXTDOMAIN ),
			'description' => __( 'Returns the RDF/RSS 1.0 feed URL (/feed/rfd).', Motionmill::TEXTDOMAIN ),
			'category'    => 'blog'
		);

		$tags[] = array
		(
			'name'        => 'rss2_url',
			'title'       => __( 'RSS2 URL', Motionmill::TEXTDOMAIN ),
			'description' => __( 'Returns the RSS 2.0 feed URL (/feed).', Motionmill::TEXTDOMAIN ),
			'category'    => 'blog'
		);

		$tags[] = array
		(
			'name'        => 'rss_url',
			'title'       => __( 'RSS URL', Motionmill::TEXTDOMAIN ),
			'description' => __( 'Returns the RSS 0.92 feed URL (/feed/rss).', Motionmill::TEXTDOMAIN ),
			'category'    => 'blog'
		);

		$tags[] = array
		(
			'name'        => 'stylesheet_directory',
			'title'       => __( 'Stylesheet directory', Motionmill::TEXTDOMAIN ),
			'description' => __( 'Returns the stylesheet directory URL of the active theme.', Motionmill::TEXTDOMAIN ),
			'category'    => 'blog'
		);

		$tags[] = array
		(
			'name'        => 'stylesheet_url',
			'title'       => __( 'Stylesheet URL', Motionmill::TEXTDOMAIN ),
			'description' => __( 'Returns the primary CSS (usually style.css) file URL of the active theme.', Motionmill::TEXTDOMAIN ),
			'category'    => 'blog'
		);

		$tags[] = array
		(
			'name'        => 'template_directory',
			'title'       => __( 'Template directory', Motionmill::TEXTDOMAIN ),
			'description' => __( "URL of the active theme's directory", Motionmill::TEXTDOMAIN ),
			'category'    => 'blog'
		);

		$tags[] = array
		(
			'name'        => 'template_url',
			'title'       => __( 'Template url', Motionmill::TEXTDOMAIN ),
			'description' => __( "URL of the active theme's directory", Motionmill::TEXTDOMAIN ),
			'category'    => 'blog'
		);

		$tags[] = array
		(
			'name'        => 'text_direction',
			'title'       => __( 'Text direction', Motionmill::TEXTDOMAIN ),
			'description' => __( 'Returns the Text Direction of WordPress HTML pages.', Motionmill::TEXTDOMAIN ),
			'category'    => 'blog'
		);

		$tags[] = array
		(
			'name'        => 'url',
			'title'       => __( 'URL', Motionmill::TEXTDOMAIN ),
			'description' => __( 'Returns the "Site address (URL)" set in Settings > General', Motionmill::TEXTDOMAIN ),
			'category'    => 'blog'
		);

		$tags[] = array
		(
			'name'        => 'version',
			'title'       => __( 'Version', Motionmill::TEXTDOMAIN ),
			'description' => __( 'The WordPress Version you use.', Motionmill::TEXTDOMAIN ),
			'category'    => 'blog'
		);

		$tags[] = array
		(
			'name'        => 'wpurl',
			'title'       => __( 'URL', Motionmill::TEXTDOMAIN ),
			'description' => __( 'The "WordPress address (URL)" set in Settings > General.', Motionmill::TEXTDOMAIN ),
			'category'    => 'blog'
		);

		// General

		$tags[] = array
		(
			'name'        => 'wp_login_url',
			'title'       => __( 'Login URL', Motionmill::TEXTDOMAIN ),
			'description' => __( 'The URL to the login page.', Motionmill::TEXTDOMAIN ),
			'category'    => 'general'
		);

		$tags[] = array
		(
			'name'        => 'wp_lostpassword_url',
			'title'       => __( 'Lost password URL', Motionmill::TEXTDOMAIN ),
			'description' => __( 'The URL to retrieve the lost password.', Motionmill::TEXTDOMAIN ),
			'category'    => 'general'
		);

		$tags[] = array
		(
			'name'        => 'site_url',
			'title'       => __( 'Site URL', Motionmill::TEXTDOMAIN ),
			'description' => __( 'retrieves the site url for the current site (where the WordPress core files reside).', Motionmill::TEXTDOMAIN ),
			'category'    => 'general'
		);

		$tags[] = array
		(
			'name'        => 'network_site_url',
			'title'       => __( 'Network site URL', Motionmill::TEXTDOMAIN ),
			'description' => __( 'retrieves the site url for the "main" site of the current network.', Motionmill::TEXTDOMAIN ),
			'category'    => 'general'
		);

		// users
		foreach ( MM_Database::get_column_names( 'users' ) as $column_name )
		{
			$tags[] = array
			(
				'name'        => $column_name,
				'title'       => ucfirst( str_replace( '_' , ' ', $column_name ) ),
				'description' => sprintf( __( 'Returns the %s value from the users table.', Motionmill::TEXTDOMAIN ), $column_name ),
				'category'    => 'user'
			);
		}

		foreach ( MM_Database::get_user_meta_keys() as $key )
		{
			$tags[] = array
			(
				'name'        => '~' . $key,
				'title'       => ucfirst( str_replace( '_' , ' ', $key ) ),
				'description' => sprintf( __( 'returns the meta value with key %s.', Motionmill::TEXTDOMAIN ), $key ),
				'category'    => 'user'
			);
		}

		return $tags;
	}

	public function on_parse_tag( $replacement, $tag )
	{
		if ( ! is_null( $tag['value'] ) )
		{
			return $tag['value'];
		}
		
		$key = $tag['name'];

		switch ( $tag['category'] )
		{
			case 'blog':
				
				if ( is_multisite() && $key == 'name' )
				{
					$replacement = $GLOBALS['current_site']->site_name;
				}

				else
				{
					$replacement = get_bloginfo( $key );
				}

				break;

			case 'general':

				if ( function_exists( $key ) )
				{
					$replacement = call_user_func( $key );
				}

				break;

			case 'user':

				$user_id = MM( 'Mail' )->get_tag_category_var( 'user', 'user_id' );

				if ( $user_id )
				{
					$user = get_user_by( 'id', $user_id );

					if ( strpos( $key , '~') !== 0 )
					{
						if ( isset( $user->$key ) )
						{
							$replacement = $user->$key;
						}
					}

					// meta
					else
					{
						$replacement = get_user_meta( $user_id, $key, true );
					}
				}

				break;
		}
		
		return $replacement;
	}
}

$mm_mail_tags = new MM_Mail_Tags();

?>
