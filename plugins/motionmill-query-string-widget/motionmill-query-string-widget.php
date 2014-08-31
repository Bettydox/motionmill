<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Query String Widget
 Plugin URI: 
 Description: Shows posts by query string in an editable template.
 Version: 1.0.0
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
			add_filter( 'motionmill_query_string_widget_tag_value', array( &$this, 'on_tag_value' ), 5, 2 );

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

		public function widget( $args, $instance )
		{
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

			$the_query = new WP_Query( $instance['query_params'] );

			if ( $the_query->have_posts() )
			{
				echo '<ul>';

				while ( $the_query->have_posts() )
				{
					$the_query->the_post();
					
					echo '<li>';
					
					$template = $instance['template'];

					if ( ! empty( $instance['wpautop'] ) )
					{
						$template = wpautop( $template, true );
					}

					foreach ( $this->tags as $tag )
					{
						$value = apply_filters( 'motionmill_query_string_widget_tag_value', '', $tag );

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

			wp_reset_postdata();

			echo $args['after_widget'];
		}

		public function form( $instance )
		{
			// defaults
			$instance = array_merge( array
			(
				'title' 		  => __( 'My Posts' ),
				'query_params' 	  => 'post_type=post',
				'conditions' 	  => '',
				'no_results_text' => __( 'No posts found.' ),
				'template' 	      => '<a href="[permalink]">[title]</a>',
				'wpautop'         => true,
				'display_title'   => true

			), $instance );


			$id = 'mm-query-widget-' . rand();

			?>

			<div id="<?php echo $id; ?>" class="mm-query-string-widget">

				<!-- title -->

				<p>
					<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', Motionmill::TEXTDOMAIN ); ?></label><br>
					<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>">
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

				<!-- template -->

				<p>
					<label for="<?php echo $this->get_field_id( 'template' ); ?>"><?php _e( 'Template:', Motionmill::TEXTDOMAIN ); ?></label><br>
					<textarea id="<?php echo $this->get_field_id( 'template' ); ?>" class="widefat code template" name="<?php echo $this->get_field_name( 'template' ); ?>" rows="5"><?php echo esc_html( $instance['template'] ); ?></textarea>
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

					<ul id="<?php echo esc_attr( $tags_id ); ?>" class="tags tags-<?php echo esc_attr( $cat['id'] ); ?>">

						<?php foreach ( $tags as $tag ) : ?>
							
						<li><a href="#" class="button" data-tag="<?php echo esc_attr( $this->get_tag_string( $tag ) ); ?>"><?php echo esc_html( $tag['title'] ); ?></a></li>

						<?php endforeach ?>

					</ul><!-- .tags -->
					
					<?php endforeach; ?>

				</div><!-- .tag-wrapper -->

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
			$cats[] = array( 'id' => 'tax', 'title' => __( 'Taxonomies' ), 'description' => __( '' ) );

			return $cats;
		}

		public function on_tags( $tags )
		{
			$tags[] = array( 'name' => 'title' , 'title' => __( 'title' ) , 'description' => __( 'description' ) , 'category' => '' );
			$tags[] = array( 'name' => 'permalink' , 'title' => __( 'permalink' ) , 'description' => __( 'description' ) , 'category' => '' );
			$tags[] = array( 'name' => 'date' , 'title' => __( 'date' ) , 'description' => __( 'description' ) , 'category' => '' );
			$tags[] = array( 'name' => 'time' , 'title' => __( 'time' ) , 'description' => __( 'description' ) , 'category' => '' );
			$tags[] = array( 'name' => 'thumbnail' , 'title' => __( 'thumbnail' ) , 'description' => __( 'description' ) , 'category' => '' );
			$tags[] = array( 'name' => 'excerpt' , 'title' => __( 'excerpt' ) , 'description' => __( 'description' ) , 'category' => '' );
			$tags[] = array( 'name' => 'content' , 'title' => __( 'content' ) , 'description' => __( 'description' ) , 'category' => '' );
			$tags[] = array( 'name' => 'author' , 'title' => __( 'author' ) , 'description' => __( 'description' ) , 'category' => '' );

			// tax related tags
			$taxonomies = get_taxonomies( null, 'objects' );

			foreach ( $taxonomies as $tax )
			{
				$tags[] = array( 'name' => $tax->name , 'title' => $tax->label, 'description' => sprintf( __( 'Lists Post %s' ), $tax->label ) , 'category' => 'tax' );
			}

			return $tags;
		}

		public function on_tag_value( $value, $tag )
		{
			if ( $tag['category'] )
			{
				if ( $tag['category'] == 'tax' )
				{
					$value = get_the_term_list( 0, $tag['name'], '', ', ', '' );
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
					case 'thumbnail' 	: $value = get_the_post_thumbnail(); break;
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
			array_push( $helpers , 'MM_Array' );

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