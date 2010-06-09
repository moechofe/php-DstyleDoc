<?php

xdebug_start_code_coverage();

function display_code_coverage()
{
	$coverage = xdebug_get_code_coverage();
	$coverage = $coverage[$_SERVER['SCRIPT_FILENAME']];
	var_dump( $coverage );
	echo '<div id="coverage" class="content-index" style="clear:left;"><h3 class="list-header">Couverture de code du fichier <strong><span class="display display-file">',basename($_SERVER['SCRIPT_FILENAME']),'</span></strong></h3><ol>';
	foreach( file($_SERVER['SCRIPT_FILENAME']) as $index => $line )
		if( trim($line) )
			echo '<li',isset($coverage[$index])?' class="on"':'',' style="white-space:pre">',$line,'</li>';
	echo '</ol></div><style type="text/css">#coverage li.on { background:#cfc }</style>';
}

register_shutdown_function( 'display_code_coverage' );

