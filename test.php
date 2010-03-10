<?php
require_once( 'converter.toString.php' );
foreach( get_declared_classes() as $class )
{
	$reflection = new ReflectionClass($class);
	var_dump( $reflection->implementsInterface('DstyleDoc_Converter_Convert') );
	if( $reflection->implementsInterface('DstyleDoc_Converter_Convert') and ! $reflection->isAbstract() )
		echo basename(__FILE__),':',$class;
}
