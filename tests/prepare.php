<?php

set_include_path(
  dirname(__FILE__).PATH_SEPARATOR.
  dirname(__FILE__).'/..'.PATH_SEPARATOR.
  '.'
);

@chdir('test.class/');

error_reporting( E_ALL | E_NOTICE & ~E_STRICT );

require_once( 'rapporteur.php' );

function end_run()
{
  if( ! defined( 'runner' ) )
    foreach( array_reverse(get_declared_classes()) as $class )
      if( is_subclass_of( $class, 'UnitTestCase' ) )
      {
        if( $class == 'element_test' )
          continue;
        $test = new $class();
        $test->run( new rapporteur() );
        continue;
      }
}

register_shutdown_function( 'end_run' );

?>
