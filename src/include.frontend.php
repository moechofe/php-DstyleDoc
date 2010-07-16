<?php
namespace dstyledoc;

require_once 'control.php';

use Iterator;

$mode=0; define('cli',1);define('web',2);
if( PHP_SAPI=='cli' ) $mode = cli; else $mode = web;

switch( $mode )
{
case cli:
	$exe=array_shift($argv); $src=$get=array(); $dst='./';
	function err( $str, $code = 1 ) { global $exe; if( $exe ) fwrite(STDERR,"$exe: $str\n"); else fwrite(STDERR,"$str\n"); exit( (int)$code ); }

	$action=0; define('help',1);

	foreach( $argv as $key => $arg )
	{
		switch( array_pop($get) )
		{
/*		case getHost: $host = $arg; unset($argv[$key]); break;
		case getPort: $port = $arg; unset($argv[$key]); break;
		case getScript: $script = $arg; unset($argv[$key]); break;*/
		default:
		if( preg_match('/(^--help$|^-\w*h\w*$)/', $arg) ) { $action = help; unset($argv[$key]); }
		if( isset($argv[$key]) ) $src[] = $arg;
		}
	}

	if( ! $src ) err("No SRC parameter found. \nRead the manual, type: « $exe --help »");

	switch( $action )
	{
	case help:
		echo <<<HELP
DstyleDoc Front-end: documentation generator for PHP
Usage: $exe [OPTION]... SRC...

SRC can be one or more files, folders or PCRE compatible regular expressions.
When using expressions, ensure quoted it with a slash (/), a pipe (|)...

OPTION:
	-h, --help      Display this help page


HELP;
	default:
		$instance = Control::hie();
		$instance->source( $src );
		var_dump($instance);
	}
	exit(0);

	break;

case web:
	break;

default:
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
	break;
}
