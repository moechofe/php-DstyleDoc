<?php
/**
 * Classe de token qui stope l'analyse.
 */
abstract class DstyleDoc_Token_Stop extends DstyleDoc_Token
{
}

// {{{ Class C

class DstyleDoc_Token_Class_C extends DstyleDoc_Token_Light
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    if( $current instanceof DstyleDoc_Token_Variable )
      $current->default = $current->object->name;

    return $current;
  }
}

// }}}






