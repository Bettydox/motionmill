<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! function_exists('mm_get_geo_info') )
{
	function mm_get_geo_info($address)
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
?>