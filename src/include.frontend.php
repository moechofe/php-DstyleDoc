<?php
namespace dstyledoc;

require_once 'control.php';

use Iterator;

$instance = Control::hie();

function auto()
{
	global $instance;
	$sources = func_get_args();
	foreach( $sources as $source )
		if( is_array($source) or $source instanceof Iterator )
			auto( $source );
		elseif( is_file($source) )
			$instance->source( $source );
	return $instance;
}
