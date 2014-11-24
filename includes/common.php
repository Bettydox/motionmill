<?php if ( ! defined( 'ABSPATH' ) ) exit; // exits when accessed directly

function MM( $class = null, $plugin = null )
{
	static $registry = array();

	$motionmill = Motionmill::get_instance();

	if ( $class == null )
	{
		return $motionmill;
	}

	// adds prefix

	$prefix = 'MM_';

	if ( stripos( $class , $prefix ) !== 0 )
	{
		$class = $prefix . $class;
	}

	if ( ! class_exists( $class ) )
	{
		MM( 'Loader' )->load_class( $class, $plugin );
	}

	if ( ! isset( $registry[ $class ] ) )
	{
		$registry[ $class ] = new $class();
	}

	return $registry[ $class ];
}

?>