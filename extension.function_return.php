<?php

class DstyleDoc_Function_Return extends DstyleDoc_Properties
{
	static protected $phps = array(
		'get_class' => array('string','false'),
		);

	static public function get_return( $function )
	{
		if( isset(self::$phps[(string)$function]) )
			return self::$phps[(string)$function];
		else
			return false;
	}
}
