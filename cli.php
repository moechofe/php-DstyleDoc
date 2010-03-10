#!/usr/bin/php
<?php

// Set the php cli command
define( 'PHP_CMD','php' );

// Need cli
if( php_sapi_name() != 'cli' ) die("This script must be run with command line.\n");

error_reporting( E_ALL | E_STRICT );
require_once( 'DstyleDoc.php' );
get_available_converter_classname();

// Parse arguments
if( ! isset($_SERVER['argv']) and ! is_array($_SERVER['argv']) ) die("Cannot get the command line options.\n");
else array_shift($_SERVER['argv']);
while( $_SERVER['argv'] )
{
	$arg = array_shift($_SERVER['argv']);
	if( preg_match( '/(^--help$|^-\w*h\w*$)/i', $arg ) ) $display_help = true;
	if( preg_match( '/(^--list$|^-\w*l\w*$)/i', $arg ) ) $list_converter = true;
	if( preg_match( '/(^--output$|^-\w*o\w*$)/i', $arg ) and $_SERVER['argv'] ) { $output_dir = array_shift($_SERVER['argv']); continue; }
  if( $arg[0]!='-' )
	{
		if( preg_match('/^[0-9]+$/', $arg) and (integer)$arg < count($list=get_available_converter_classname()) ) $converter_name = $list[$arg];
		elseif( preg_match('/^[\w-_]+$/', $arg) and in_array($arg,get_available_converter_classname()) ) $converter_name = $arg;
    elseif( ! isset($input_files) ) $input_files = array($arg);
    else $input_files[] = $arg;
  }
}
unset($list, $arg);

// Check output directory
if( ! empty($output_dir) )
  if( ! is_dir($output_dir) or ! is_writable($output_dir) )
    die("Error: output directory: \"$output_dir\" isn't writeable\n\n");

// Check input files
if( ! empty($input_files) and is_array($input_files) )
  foreach( $input_files as $file )
    if( ! file_exists($file) and ! is_readable($file) and ! preg_match('/^\/.*\/$/',$file) )
      die("Error: input file: \"$file\" aren't readable\n\n");

// Display converter list
if( ! empty($list_converter) and empty($display_help) )
{
	echo "List of available converters:\n\n";
	foreach( get_available_converter_classname() as $index => $item )
		echo "\t($index)\t$item\n";
	die("\n");
}

// Display help page
$self = basename(__FILE__);
if( ! empty($display_help) or empty($input_files) or empty($converter_name) )
die( <<<HELP
Usage: php $self [OPTION]... CONVERTER INPUT... [-o OUTPUT]

INPUT is a liste of directory, files or regular expression of the source files.
CONVERTER must be a name or a shortcut of the converter.

Options:
\t-o  --output OUTPUT\tCan be a directory or an URL, depends on the CONVERTER.
\t-l  --list\t\tList available converter.
\t-c  --config FILE\tAppend a config entries file. Accept INI and PHP format.
\t-h  --help\t\tThis help page.

--config:


HELP
);

$dstyledoc = DstyleDoc::hie();
foreach( $input_files as $item )
	add_source( $item, $dstyledoc );
$converter = call_user_func( array($converter_name,'hie') );
if( ! empty($output_dir) )
	$converter->destination_dir( $output_dir );

echo
	"Converter: \t",$converter_name,"\n",
	"Input files: \t",implode(', ',$dstyledoc->sources),"\n",
	"Output dir: \t$output_dir\n",
	"\n";

$dstyledoc->convert_with( $converter );

function add_source( $source, DstyleDoc $instance, $path = '' )
{
	if( is_file($source) )
		$instance->source = $source;
	elseif( is_dir($source) )
	{
		$list = dir($source);
		while( false !== ($item = $list->read()) )
			if( $item != '.' and $item != '..' )
				add_source( $source.$item, $instance );
	}
	elseif( preg_match('/^\/.*\/$/',$source) )
	{
		$list = dir('.');
		while( false !== ($item = $list->read()) )
			if( $item != '.' and $item != '..' )
				if( preg_match( $source, $path.$item ) )
				{
					if( is_file($path.$item) )
						add_source( $path.$item, $instance );
					elseif( is_dir($path.$item) )
						add_source( $source, $instance, $path.$item.'/' );
				}

	}
}

function get_available_converter_classname()
{
	static $result = null;
	if( is_array($result) )
		return $result;
	$result = array();
	$list = dir(dirname(__FILE__));
	while( false !== ($item = $list->read()) )
		if( preg_match( '/^converter\.([-_\w]+)\.php$/', $item ) )
			if( ! shell_exec( "php $item" ) )
				require_once( $item );
	foreach( get_declared_classes() as $class )
	{
		$reflection = new ReflectionClass($class);
		if( $reflection->implementsInterface('DstyleDoc_Converter_Convert') and ! $reflection->isAbstract() )
			$result[] = $class;
	}
	return $result;
}

