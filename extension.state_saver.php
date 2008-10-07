<?php

require_once 'connexion.php';

class DstyleDoc_State_Saver
{
  // {{{ __construct()

  private function __construct()
  {
  }

  // }}}
  // {{{ __clone

  private function __clone()
  {
  }

  // }}}
  // {{{ $cnx

  static protected $cnx = null;

  // }}}
  // {{{ start()

  static public function start( DstyleDoc $dsd )
  {
    self::$cnx = mysql_connexion::get_driver( $dsd->database_host, $dsd->database_user, $dsd->database_pass, $dsd->database_base );
/*
    try
    {
      self::$cnx->query( 'select 1' );
      self::$cnx->query( 'drop database `!`', $dsd->database_base );
    }
    catch( mysql_connexion_connect_error $e )
    {
      d( $e->getMessage() );
      d( preg_match( '/unknown database/i', $e->getMessage() ) );
      if( preg_match( '/unknown database/i', $e->getMessage() ) )
        null;
      else
        throw $e;
    }
*/
//    self::$cnx->query( 'create database `!`', $dsd->database_base );
//    self::$cnx->query( 'use `!`', $dsd->database_base );
    self::$cnx->query( 'drop table if exists elements' );
    self::$cnx->query( "CREATE TABLE `elements` ( `class` varchar(128) character set armscii8 collate armscii8_bin NOT NULL default '', `name` varchar(128) character set armscii8 collate armscii8_bin NOT NULL default '', `state` blob, PRIMARY KEY  (`class`,`name`)) ENGINE=MyISAM DEFAULT CHARSET=armscii8;" );

  }

  // }}}
  // {{{ put_element()

  static public function put_element( DstyleDoc_Custom_Element $element )
  {
    d( $element );
    self::$cnx->query( 'replace into `elements` set `class` = ?, `name` = ?, `state` = ?',
      (string)get_class($element),
      (string)$element->name,
      serialize( $element )
    );
  }

  // }}}
  // {{{ get_element()

  static public function get_element( $class, $name, DstyleDoc_Converter $converter )
  {
    if( $state = self::$cnx->field->query( 'select `state` from `elements` where `class` = ? and `name` = ?', (string)$class, (string)$name ) )
    {
      $element = unserialize( $state );
      $element->converter = $converter;
      d( $element );
      return $element;
    }
    else
      return false;
  }

  // }}}
  // {{{ get_elements()

  static public function get_elements( $class )
  {
    return self::$cnx->col->query( 'select `name` from `elements` where `class` = ?', (string)$class );
  }

  // }}}
}

class DstyleDoc_State_Saver_Iterator implements Iterator
{
  // {{{ $converter

  private $converter = null;

  // }}}
  // {{{ $class

  private $class = null;

  // }}}
  // {{{ $states

  private $states = array();

  // }}}
  // {{{ __construct()

  public function __construct( $class, DstyleDoc_Converter $converter )
  {
    $this->converter = $converter;
    $this->class = $class;
    $this->states = DstyleDoc_State_Saver::get_elements( $class );
  }

  // }}}
  // {{{ current()

  public function current()
  {
    return DstyleDoc_State_Saver::get_element( $this->class, current($this->states), $this->converter );
  }

  // }}}
  // {{{ next()

  public function next()
  {
    next($this->states);
  }

  // }}}
  // {{{ valid()

  public function valid()
  {
    return (boolean)current($this->states);
  }

  // }}}
  // {{{ key()

  public function key()
  {
    return current($this->states);
  }

  // }}}
  // {{{ rewind()

  public function rewind()
  {
    reset($this->states);
  }

  // }}}
}

?>
