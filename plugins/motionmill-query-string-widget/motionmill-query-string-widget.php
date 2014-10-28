<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Query String Widget
 Plugin URI: https://github.com/addwittz/motionmill/tree/master/plugins/motionmill-query-string-widget
 Description: Shows posts by query string in an editable template.
 Version: 1.0.1
 Author: Maarten Menten
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

if ( ! class_exists( 'MM_Query_String_Widget' ) )
{
	class MM_Query_String_Widget extends WP_Widget
	{
		const FILE = __FILE__;

		protected $tags     = array();
		protected $tag_cats = array();

		public function __construct()
		{
			parent::__construct( 'motionmill_query_string_widget', __( 'Motionmill Query String', Motionmill::TEXTDOMAIN ), array
			(
				'description' => __( 'Shows posts by query parameters in an editable template.', Motionmill::TEXTDOMAIN )
			));

			add_action( 'admin_enqueue_scripts', array( &$this, 'on_admin_enqueue_scripts' ) );

			add_filter( 'motionmill_query_string_widget_tag_cats', array( &$this, 'on_tag_cats' ), 5, 1 );
			add_filter( 'motionmill_query_string_widget_tags', array( &$this, 'on_tags' ), 5, 1 );
			add_filter( 'motionmill_query_string_widget_tag_value', array( &$this, 'on_tag_value' ), 5, 3 );

			add_filter( 'motionmill_helpers', array( &$this, 'on_helpers' ) );

			// registers tag groups
			foreach ( apply_filters( 'motionmill_query_string_widget_tag_cats', array() ) as $data )
			{
				if ( ! isset( $data['id'] ) )
				{
					continue;
				}

				$this->tag_cats[] = array_merge( array
				(
					'id'  	    	=> $data['id'],
					'title' 	    => __( 'Untitled' ),
					'description'   => ''
				), $data );
			};

			usort( $this->tag_cats, function( $a, $b )
			{
				return strcmp( $a['id'] , $b['id'] );
			});

			// registers tags
			foreach ( apply_filters( 'motionmill_query_string_widget_tags', array() ) as $data )
			{
				if ( empty( $data['name'] ) )
				{
					continue;
				}

				$this->tags[] = array_merge( array
				(
					'name'  	    => $data['name'],
					'title' 	    => __( 'Untitled' ),
					'description'   => '',
					'category'      => ''
				), $data );
			};

			usort( $this->tags, function( $a, $b )
			{
				return strcmp( $a['title'] , $b['title'] );
			});
		}

		public function get_tag_string( $tag )
		{
			$str = '';

			if ( $tag['category'] )
			{
				$str = $tag['category'] . ':' . $tag['name'];
			}

			else
			{
				$str = $tag['name'];
			}

			return sprintf( '[%s]', $str );
		}

		public function get_tag( $search )
		{
			return MM_Array::get_element_by( $search, $this->tags );
		}

		public function get_tags( $search = null )
		{
			return MM_Array::get_elements_by( $search, $this->tags );
		}

		public function get_tag_category( $search )
		{
			return MM_Array::get_element_by( $search, $this->tag_cats );
		}

		public function get_tag_categories( $search = null )
		{
			return MM_Array::get_elements_by( $search, $this->tag_cats );
		}

		protected function get_post_meta_keys( $post_type = 'post' )
		{
			global $wpdb;

			$query = sprintf("
			SELECT DISTINCT($wpdb->postmeta.meta_key) 
			FROM $wpdb->posts 
			LEFT JOIN $wpdb->postmeta 
			ON $wpdb->posts.ID = $wpdb->postmeta.post_id 
			WHERE $wpdb->posts.post_type = '%s' 
			", esc_sql( $post_type ) );

			return $wpdb->get_col( $query );
		}

		public function widget( $args, $instance )
		{
			// checks language

			if ( $this->is_multilingual() && ! in_array( $instance['language'] , array( '', ICL_LANGUAGE_CODE ) ) )
			{
				return;
			}

			// checks conditions
			$conditions = trim( $instance['conditions'] ) != '' ?  trim( $instance['conditions'] ) : false;
				
			if ( $conditions )
			{
				global $wp_query;

				parse_str( $conditions, $conditions );

				foreach ( $conditions as $key => $value )
				{
					if ( ! isset( $wp_query->query_vars[$key] ) || $wp_query->query_vars[$key] != $value )
					{
						return;
					}
				}
			}

			// template
			$title = apply_filters( 'widget_title', $instance['title'] );

			echo $args['before_widget'];

			printf( '<div id="%s">', esc_attr( $instance['id'] ) );
			
			if ( ! empty( $title ) )
			{
				if ( ! empty( $instance['display_title'] ) )
				{
					echo $args['before_title'] . $title . $args['after_title'];
				}

				else
				{
					echo $args['before_title'] . $args['after_title'];
				}
			}

			$before_template = $instance['before_post_template'];

			if ( ! empty( $instance['wpautop'] ) )
			{
				$before_template = wpautop( $before_template, true );
			}

			printf( '<div class="before-posts">%s</div>', $before_template );

			$the_query = new WP_Query( $instance['query_params'] );

			if ( $the_query->have_posts() )
			{
				echo '<ul class="posts">';

				while ( $the_query->have_posts() )
				{
					$the_query->the_post();
					
					printf( '<li class="%s">', implode( ' ', get_post_class() ) );
					
					$template = $instance['post_template'];

					if ( ! empty( $instance['wpautop'] ) )
					{
						$template = wpautop( $template, true );
					}

					foreach ( $this->tags as $tag )
					{
						$value = apply_filters( 'motionmill_query_string_widget_tag_value', '', $tag, get_the_ID() );

						$template = str_replace( $this->get_tag_string( $tag ), $value, $template );
					}

					echo $template;
					
					echo '</li>';
				}

				echo '</ul>';
			}

			else
			{
				echo $instance['no_results_text'];
			}

			$after_template = $instance['after_post_template'];

			if ( ! empty( $instance['wpautop'] ) )
			{
				$after_template = wpautop( $after_template, true );
			}

			printf( '<div class="after-posts">%s</div>', $after_template );

			wp_reset_postdata();

			echo '</div>';

			echo $args['after_widget'];
		}

		public function is_multilingual()
		{
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			
			return is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' );
		}

		public function get_languages()
		{
			$languages = array();

			foreach ( icl_get_languages('skip_missing=N&orderby=KEY&order=DIR&link_empty_to=str') as $code => $lang )
			{
				$languages[ $code ] = $lang['translated_name'];
			}

			return $languages;
		}

		public function form( $instance )
		{
			// defaults
			$instance = array_merge( array
			(
				'id'                   => '',
				'title'                => __( 'My Posts' ),
				'query_params'         => 'post_type=post',
				'conditions'           => '',
				'no_results_text'      => __( 'No posts found.' ),
				'before_post_template' => '',
				'post_template'        => '<a href="[permalink]">[title]</a>',
				'after_post_template'  => '',
				'wpautop'              => true,
				'display_title'        => true,
				'language'             => '' 
			), $instance );

			$id = 'mm-query-widget-' . rand();

			?>

			<div id="<?php echo $id; ?>" class="mm-query-string-widget">

				<!-- title -->

				<p>
					<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', Motionmill::TEXTDOMAIN ); ?></label><br>
					<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>">
				</p>

				<!-- id -->

				<p>
					<label for="<?php echo $this->get_field_id( 'id' ); ?>"><?php _e( 'ID:', Motionmill::TEXTDOMAIN ); ?></label><br>
					<input type="text" id="<?php echo $this->get_field_id( 'id' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'id' ); ?>" value="<?php echo esc_attr( $instance['id'] ); ?>">
				</p>

				<!-- query -->

				<p>
					<label for="<?php echo $this->get_field_id( 'query-params' ); ?>"><?php _e( 'Query parameters:', Motionmill::TEXTDOMAIN ); ?></label><br>
					<input type="text" id="<?php echo $this->get_field_id( 'query-params' ); ?>" class="widefat code" name="<?php echo $this->get_field_name( 'query_params' ); ?>" value="<?php echo esc_attr( $instance['query_params'] ); ?>">
				</p>

				<!-- conditions -->

				<p>
					<label for="<?php echo $this->get_field_id( 'conditions' ); ?>"><?php _e( 'Conditianal parameters:', Motionmill::TEXTDOMAIN ); ?></label><br>
					<input type="text" id="<?php echo $this->get_field_id( 'conditions' ); ?>" class="widefat code" name="<?php echo $this->get_field_name( 'conditions' ); ?>" value="<?php echo esc_attr( $instance['conditions'] ); ?>">
				</p>

				<!-- no results text -->

				<p>
					<label for="<?php echo $this->get_field_id( 'no-results-text' ); ?>"><?php _e( 'No results text:', Motionmill::TEXTDOMAIN ); ?></label><br>
					<input type="text" id="<?php echo $this->get_field_id( 'no-results-text' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'no_results_text' ); ?>" value="<?php echo esc_attr( $instance['no_results_text'] ); ?>">
				</p>

				<!-- before template -->

				<p>
					<label for="<?php echo $this->get_field_id( 'before_post_template' ); ?>"><?php _e( 'Before template:', Motionmill::TEXTDOMAIN ); ?></label><br>
					<textarea id="<?php echo $this->get_field_id( 'before_post_template' ); ?>" class="widefat code" name="<?php echo $this->get_field_name( 'before_post_template' ); ?>" rows="5"><?php echo esc_html( $instance['before_post_template'] ); ?></textarea>
				</p>

				<!-- template -->

				<p>
					<label for="<?php echo $this->get_field_id( 'post_template' ); ?>"><?php _e( 'Post Template:', Motionmill::TEXTDOMAIN ); ?></label><br>
					<textarea id="<?php echo $this->get_field_id( 'post_template' ); ?>" class="widefat code template" name="<?php echo $this->get_field_name( 'post_template' ); ?>" rows="5"><?php echo esc_html( $instance['post_template'] ); ?></textarea>
				</p>

				<!-- tags -->

				<div class="tag-wrapper">
				
					<?php _e( 'Template tags: ', Motionmill::TEXTDOMAIN ); ?>

					<ul class="cats">

						<?php foreach ( $this->tag_cats  as $cat ) : ?>

						<li><a href="#" data-tags="tags-<?php echo esc_attr( $cat['id'] ); ?>"><?php echo esc_html( $cat['title'] ); ?></a></li>
						
						<?php endforeach; ?>

					</ul><!-- tag-cats -->

					<?php foreach ( $this->tag_cats  as $cat ) : 

					$tags = MM_Array::get_elements_by( 'category='.$cat['id'], $this->tags );

					?>

					<div class="tags tags-<?php echo esc_attr( $cat['id'] ); ?>">

						<?php echo $cat['description']; ?>

						<ul>

							<?php foreach ( $tags as $tag ) : ?>
								
							<li><a href="#" class="button" data-tag="<?php echo esc_attr( $this->get_tag_string( $tag ) ); ?>"><?php echo esc_html( $tag['title'] ); ?></a></li>

							<?php endforeach ?>

						</ul><!-- .tags -->

					</div>
					
					<?php endforeach; ?>

				</div><!-- .tag-wrapper -->

				<!-- after template -->

				<p>
					<label for="<?php echo $this->get_field_id( 'after_post_template' ); ?>"><?php _e( 'After template:', Motionmill::TEXTDOMAIN ); ?></label><br>
					<textarea id="<?php echo $this->get_field_id( 'after_post_template' ); ?>" class="widefat code" name="<?php echo $this->get_field_name( 'after_post_template' ); ?>" rows="5"><?php echo esc_html( $instance['after_post_template'] ); ?></textarea>
				</p>

				<!-- language -->

				<?php if ( $this->is_multilingual() ) :

					$options = array_merge( $this->get_languages(), array( '' => __( '- all -', Motionmill::TEXTDOMAIN ) ) );

					ksort( $options );
				?>
				
				<p>
					<label for="<?php echo $this->get_field_id( 'language' ); ?>"><?php _e( 'Language:', Motionmill::TEXTDOMAIN ); ?></label><br>
					<select id="<?php echo $this->get_field_id( 'language' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'language' ); ?>">
						<?php foreach ( $options as $key => $value ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $instance['language'], $key ); ?>><?php echo esc_html( $value ); ?></option>
						<?php endforeach ?>
					</select>
				</p>

				<?php endif; ?>

				<!-- wpautop -->
				
				<p>
					<label><input type="checkbox" id="<?php echo $this->get_field_id( 'wpautop' ); ?>" name="<?php echo $this->get_field_name( 'wpautop' ); ?>" value="1"<?php checked( $instance['wpautop'], true ); ?>><?php _e( 'automatically add paragraphs.' ); ?></label>
				</p>

				<!-- Display title -->
				
				<p>
					<label><input type="checkbox" id="<?php echo $this->get_field_id( 'display-title' ); ?>" name="<?php echo $this->get_field_name( 'display_title' ); ?>" value="1"<?php checked( $instance['display_title'], true ); ?>><?php _e( 'Display title.' ); ?></label>
				</p>
				
				<script type="text/javascript">
					
					(function($)
					{
						$(document).ready(function()
						{
							var widget = $( '#<?php echo $id; ?>' );

							// toggles tags
							widget.find('.cats a').click(function(e)
							{
								var selector = '.' + $(this).attr( 'data-tags' );

								var target = widget.find( selector );
								
								if ( target.is(':hidden') )
								{
									widget.find( '.cats a.active' ).removeClass('active');

									$(this).addClass('active');

									widget.find( '.tags:visible' ).slideToggle();
								}

								else
								{
									$(this).removeClass('active');
								}

								target.slideToggle();

								e.preventDefault();
							});

							// inserts tags into template
							widget.find('.tags .button').click(function(e)
							{
								var tag = $(this).attr( 'data-tag' );

								widget.find( '.template' )
									.insertAtCaret( tag )
									.focus();

								e.preventDefault();
							});
						});

					})(jQuery);

				</script>

			</div><!-- .mm-query-widget -->

			<?php
		}
		
		public function update( $new_instance, $old_instance )
		{
			$instance = $new_instance;
			
			$instance['wpautop'] = isset( $instance['wpautop'] );
			$instance['display_title'] = isset( $instance['display_title'] );

			return $instance;
		}

		public function on_admin_enqueue_scripts()
		{
			wp_enqueue_style( 'mm-query-widget', plugins_url( 'css/style.css', self::FILE ) );
		}

		public function on_tag_cats( $cats )
		{
			$cats[] = array( 'id' => '', 'title' => __( 'Uncategorized' ), 'description' => __( '' ) );
			$cats[] = array( 'id' => 'meta', 'title' => __( 'Meta' ), 'description' => __( '' ) );
			$cats[] = array( 'id' => 'tax', 'title' => __( 'Taxonomies' ), 'description' => __( '' ) );
			$cats[] = array( 'id' => 'thumb', 'title' => __( 'Thumbnail' ), 'description' => __( '' ) );

			return $cats;
		}

		public function on_tags( $tags )
		{
			$tags[] = array( 'name' => 'title' , 'title' => __( 'title' ) , 'description' => __( 'description' ) , 'category' => '' );
			$tags[] = array( 'name' => 'permalink' , 'title' => __( 'permalink' ) , 'description' => __( 'description' ) , 'category' => '' );
			$tags[] = array( 'name' => 'date' , 'title' => __( 'date' ) , 'description' => __( 'description' ) , 'category' => '' );
			$tags[] = array( 'name' => 'time' , 'title' => __( 'time' ) , 'description' => __( 'description' ) , 'category' => '' );
			$tags[] = array( 'name' => 'excerpt' , 'title' => __( 'excerpt' ) , 'description' => __( 'description' ) , 'category' => '' );
			$tags[] = array( 'name' => 'content' , 'title' => __( 'content' ) , 'description' => __( 'description' ) , 'category' => '' );
			$tags[] = array( 'name' => 'author' , 'title' => __( 'author' ) , 'description' => __( 'description' ) , 'category' => '' );

			// meta

			require_once( plugin_dir_path( Motionmill::FILE ) . 'includes/class-mm-database.php' );

			foreach ( MM_Database::get_post_meta_keys() as $meta_key )
			{
				if ( stripos( $meta_key , '_' ) === 0 )
				{
					continue;
				}

				$tags[] = array
				(
					'name'     => $meta_key,
					'title'    => ucfirst( str_replace( '-', ' ', $meta_key ) ),
					'category' => 'meta',
				);
			}

			// thumb
			foreach ( array( 'thumb', 'medium', 'large', 'full' ) as $size )
			{
				$tags[] = array
				(
					'name'        => $size,
					'title'       => ucfirst( $size ),
					'category'    => 'thumb'
				);
			}

			// tax related tags
			$taxonomies = get_taxonomies( null, 'objects' );

			foreach ( $taxonomies as $tax )
			{
				$tags[] = array( 'name' => $tax->name , 'title' => $tax->label, 'description' => sprintf( __( 'Lists Post %s' ), $tax->label ) , 'category' => 'tax' );
			}

			return $tags;
		}

		public function on_tag_value( $value, $tag, $post_id )
		{
			if ( $tag['category'] )
			{
				if ( $tag['category'] == 'tax' )
				{
					$value = get_the_term_list( 0, $tag['name'], '', ', ', '' );
				}

				else if ( $tag['category'] == 'thumb' )
				{
					$value = get_the_post_thumbnail( null, $tag['name'] );
				}

				else if ( $tag['category'] == 'meta' )
				{
					$value = get_post_meta( get_the_ID(), $tag['name'], true );
				}
			}

			else
			{
				switch ( $tag['name'] )
				{
					case 'title' 	 	: $value = get_the_title(); break;
					case 'permalink' 	: $value = get_the_permalink(); break;
					case 'date'  	 	: $value = get_the_time( get_option('date_format') ); break;
					case 'time'  	 	: $value = get_the_time(); break;
					case 'excerpt'   	: $value = get_the_excerpt(); break;
					case 'content'   	: $value = get_the_content(); break;
					case 'author'   	: $value = get_the_author(); break; 
					case 'authorlink'   : $value = get_the_author_link(); break;
				}
			}

			return $value;
		}

		public function on_helpers( $helpers )
		{
			array_push( $helpers , 'MM_Array', 'MM_Database' );

			return $helpers;
		}
	}

	if ( ! function_exists( 'motionmill_query_string_widget_register' ) )
	{
		function motionmill_query_string_widget_register()
		{
			register_widget( 'MM_Query_String_Widget' );
		}

		add_action( 'widgets_init', 'motionmill_query_string_widget_register' );
	}
}



?>
