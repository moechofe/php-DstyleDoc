<?php

require_once( 'xdebug.front.end.php' );

class c
{
}

class d
{
  function __sleep()
  {
    return array('a');
  }
  function __wakeup()
  {
    $this->a = 'bbb';
    return $this->__sleep();
  }
}

class a extends d
{
  protected $a = 'aaa';
  function __construct( c $c )
  {
  }
}

class b extends d
{
  protected $a = null;
  function __construct( a $a )
  {
    $this->a = $a;
  }
}

$b = new b( new a( new c ) );

d( $b )->lb;
echo( $s = serialize($b) );

$bb = unserialize($s);
d( $bb )->lbb;

?>
