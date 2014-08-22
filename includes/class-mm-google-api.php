<?php if ( ! defined('ABSPATH') ) exit; // Exits when accessed directly

if ( ! class_exists('MM_Google_Api') )
{
	class MM_Google_Api
	{
		static public function get_geo_info($address)
		{
		    $response = file_get_contents( sprintf('http://maps.google.com/maps/api/geocode/json?sensor=false&address=%s', urlencode($address) ) );
		 
		    if ( $response !== false )
		    {
		        $data = json_decode( $response );
		 
		        if ( $data->status == 'OK' )
		        {
		            return $data->results;
		        }
		    }
		 
		    return false;
		}
	}
}

?>