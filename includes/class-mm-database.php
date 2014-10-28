<?php if ( ! defined('ABSPATH') ) exit; // Exits when accessed directly

if ( ! class_exists( 'MM_Database' ) )
{
	class MM_Database
	{
	   	static public function get_user_meta_keys( $args = array() )
	   	{	
	   		$options = array_merge( array
	   		(
	   			'exlude_private' => true
	   		), $args );

	   		global $wpdb;

	   		$query = sprintf( 'SELECT DISTINCT meta_key FROM %s', $wpdb->usermeta );

	   		$keys = array();

	   		foreach ( $wpdb->get_col( $query ) as $key )
	   		{
	   			if ( $options['exlude_private'] && stripos( $key , '_') === 0 )
	   			{
	   				continue;
	   			}

	   			$keys[] = $key;
	   		}

	   		return $keys;
	   	}

		static public function get_post_meta_keys( $post_type = null )
		{
			global $wpdb;

			if ( $post_type == null )
			{
				$sql = sprintf( 'SELECT DISTINCT meta_key FROM %s;', $wpdb->postmeta );
			}

			else
			{
				$sql = sprintf("
				SELECT DISTINCT($wpdb->postmeta.meta_key) 
				FROM $wpdb->posts 
				LEFT JOIN $wpdb->postmeta 
				ON $wpdb->posts.ID = $wpdb->postmeta.post_id 
				WHERE $wpdb->posts.post_type = '%s' 
				", esc_sql( $post_type ) );
			}

			return $wpdb->get_col( $sql );
		}

		static public function get_column_names( $table_name )
		{
			global $wpdb;

			if ( strpos( $table_name, $wpdb->prefix ) !== 0 )
			{
				$table_name = $wpdb->prefix . $table_name;
			}

			$columns = array();

			foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name )
			{
				$columns[] = $column_name;
			}

			return $columns;
		}
	}
}

?>