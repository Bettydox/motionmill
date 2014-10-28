<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
------------------------------------------------------------------------------------------------------------------------
 Plugin Name: Motionmill Admin Columns
 Plugin URI:
 Description: Manages post admin columns
 Version: 1.0.0
 Author: Maarten Menten
 Author URI: http://motionmill.com
 License: GPL2
------------------------------------------------------------------------------------------------------------------------
*/

if ( ! class_exists( 'MM_Admin_Columns' ) )
{
	class MM_Admin_Columns
	{
		const FILE = __FILE__;

		protected $columns = array();

		public function __construct()
		{	
			add_filter( 'motionmill_helpers', array( &$this, 'on_helpers' ) );

			add_action( 'motionmill_init', array( &$this, 'initialize' ) );
		}

		public function get_column( $search )
		{
			return MM_Array::get_element_by( $search, $this->columns );
		}

		public function get_columns( $search = null )
		{
			return MM_Array::get_elements_by( $search, $this->columns );
		}

		public function initialize()
		{
			foreach ( apply_filters( 'motionmill_admin_columns_columns', array() ) as $data )
			{
				if ( empty( $data['id'] ) )
				{
					continue;
				}

				$column = array_merge( array
				(
					'id'        => $data['id'],
					'title'     => $data['id'],
					'post_type' => array( 'post' ),
					'cb'        => null,
				), $data );

				// makes sure post_type is array
				if ( ! is_array( $column['post_type'] ) )
				{
					if ( $column['post_type'] != '' )
					{
						$column['post_type'] = array( $column['post_type'] );
					}

					else
					{
						$column['post_type'] = array();
					}
				}
			
				$this->columns[] = $column;
			}

			if ( count( $this->columns ) > 0 )
			{
				add_filter( 'manage_posts_columns' , array( &$this, 'on_manage_columns' ) );
				add_filter( 'manage_pages_columns' , array( &$this, 'on_manage_columns' ) );

				add_action( 'manage_posts_custom_column', array( &$this, 'on_manage_custom_column' ), 10, 2 );
				add_action( 'manage_pages_custom_column', array( &$this, 'on_manage_custom_column' ), 10, 2 );
			}
		}

		public function on_manage_columns( $columns )
		{
			if ( ! empty( $_GET['post_type'] ) )
			{
				$post_type = $_GET['post_type'];
			}

			else
			{
				$post_type = 'post';
			}

			foreach ( $this->get_columns() as $column )
			{
				if ( ! in_array( $post_type , $column['post_type'] ) )
				{
					continue;
				}

				$columns[ $column['id'] ] = $column['title'];
			}

			return $columns;
		}

		public function on_manage_custom_column( $column_id, $post_id )
		{
			$post_type = get_post_type( $post_id );

			$column = $this->get_column( array( 'id' => $column_id ) );

			if ( ! $column || $column['post_type'] != $post_type )
			{
				return;
			}

			if ( ! $column['callback'] )
			{
				return;
			}

			echo call_user_func( $column['callback'], $post_id );
		}

		public function on_helpers( $helpers )
		{
			array_push( $helpers , 'MM_Array' );

			return $helpers;
		}
	}
}

// registers plugin
if ( ! function_exists('motionmill_plugins_add_admin_columns') )
{
	function motionmill_plugins_add_admin_columns( $plugins )
	{
		$plugins[] = 'MM_Admin_Columns';

		return $plugins;
	}

	add_filter( 'motionmill_plugins', 'motionmill_plugins_add_admin_columns' );
}

?>
