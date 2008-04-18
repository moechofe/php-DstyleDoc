<?php

interface DstyleDoc_Token_Work
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line );
}

/**
 * Classe de token de base.
 */
abstract class DstyleDoc_Token_Custom extends DstyleDoc_Properties
{
}

/**
 * Classe de token de base.
 */
abstract class DstyleDoc_Token_Light extends DstyleDoc_Token_Custom implements DstyleDoc_Token_Work
{
  final protected function __construct()
  {
  }
}

/**
 * Classe de token fake.
 */
class DstyleDoc_Token_Fake extends DstyleDoc_Token_Custom
{
}

interface DstyleDoc_Token_Valueable
{
  function set_value( $value );
  function get_value();
}

/**
 * Classe de token utilsable.
 */
abstract class DstyleDoc_Token extends DstyleDoc_Token_Custom implements DstyleDoc_Token_Work
{
  final protected function __construct()
  {
  }
  // {{{ $file

  protected $_file = '';

  protected function set_file( $file )
  {
    $this->_file = (string)$file;
  }

  protected function get_file()
  {
    return $this->_file;
  }

  // }}}
  // {{{ $line

  protected $_line = 0;

  protected function set_line( $line )
  {
    $this->_line = (integer)$line;
  }

  protected function get_line()
  {
    return $this->_line;
  }

  // }}}
  // {{{ $open_tag

  protected $_open_tag = null;

  protected function set_open_tag( DstyleDoc_Token_Custom $open_tag )
  {
    if( $open_tag instanceof DstyleDoc_Token_Open_Tag )
      $this->_open_tag = $open_tag;
    elseif( $open_tag->open_tag instanceof DstyleDoc_Token_Open_Tag )
      $this->_open_tag = $open_tag->open_tag;
  }

  protected function get_open_tag()
  {
    if( $this->_open_tag instanceof DstyleDoc_Token_Open_Tag )
      return $this->_open_tag;
    else
      return new DstyleDoc_Token_Fake;
  }

  // }}}
  // {{{ $documentation

  protected $_documentation = '';

  protected function set_documentation( $documentation )
  {
    if( $documentation instanceof DstyleDoc_Token_Doc_Comment
      or $documentation instanceof DstyleDoc_Token_Doc_Comment )
      $this->_documentation = $documentation->documentation;
    else
      $this->_documentation = (string)$documentation;
  }

  protected function get_documentation()
  {
    return $this->_documentation;
  }

  // }}}
  // {{{ $name

  protected $_name = '';

  protected function set_name( $name )
  {
    $this->_name = (string)$name;
  }

  protected function get_name()
  {
    return $this->_name;
  }

  // }}}
  // {{{ $modifiers

  protected $_modifiers = array(
    'static' => false,
    'abstract' => false,
    'final' => false,
    'public' => false,
    'protected' => false,
    'private' => false );

  protected function set_modifier( $modifier )
  {
    if( $modifier instanceof DstyleDoc_Token_Modifier )
      $this->modifiers = $modifier->modifiers;
    elseif( is_string($modifier) and isset($this->_modifiers[$modifier] ) )
      $this->_modifiers[$modifier] = true;
  }

  protected function set_modifiers( $modifiers )
  {
    foreach( (array)$modifiers as $modifier )
      $this->modifier = $modifier;
  }

  protected function get_modifiers()
  {
    return $this->_modifiers;
  }

  // }}}
}

// {{{ Unknown

class DstyleDoc_Token_Unknown extends DstyleDoc_Token_Light
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    switch( $source )
    {
    case '{' :
      return $current;
      break;

    case '(' :
      if( $current instanceof DstyleDoc_Token_Function )
        return DstyleDoc_Token_Tuple::hie( $converter, $current, $source, $file, $line );
      break;
    }
  }
}

// }}}
// {{{ Open Tag

class DstyleDoc_Token_Open_Tag extends DstyleDoc_Token
{
  static public function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    $return = new self;
    $return->file = $file;
    $return->line = $line;

    return $return;
  }
}

// }}}
// {{{ Doc Comment

class DstyleDoc_Token_Doc_Comment extends DstyleDoc_Token
{
  static public function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    $return = new self;
    $return->open_tag = $current;
    $return->line = $line;
    $return->documentation = $source;

    if( $current instanceof DstyleDoc_Token_Doc_Comment )
      $current->open_tag->documentation = $current->documentation;

    return $return;
  }
}

// }}}
// {{{ Interface

class DstyleDoc_Token_Interface extends DstyleDoc_Token implements DstyleDoc_Token_Valueable
{
  static public function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    $return = new self;
    $return->open_tag = $current;
    $return->line = $line;
    $return->documentation = $current->documentation;

    return $return;
  }

  public function set_value( $value )
  {
    $this->name = $value;
  }
  public function get_value()
  {
    return $this;
  }
}

// }}}
// {{{ String

class DstyleDoc_Token_String extends DstyleDoc_Token_Light
{
  static public function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    $return = $current;

    if( $current instanceof DstyleDoc_Token_Valueable )
    {
      $current->value = $source;

      if( $current->value instanceof DstyleDoc_Token_Custom )
        $return = $current->value;
    }

    return $return;
  }
}

// }}}
// {{{ Comment

class DstyleDoc_Token_Comment extends DstyleDoc_Token_Custom
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    return $current;
  }
}

// }}}
// {{{ Static

class DstyleDoc_Token_Static extends DstyleDoc_Token_Custom
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    return DstyleDoc_Token_Modifier::hie( $converter, $current, $source, $file, $line );
  }
}

// }}}
// {{{ Modifier

class DstyleDoc_Token_Modifier extends DstyleDoc_Token
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    $return = new self;
    $return->open_tag = $current;
    $return->modifier = $source;

    if( $current instanceof DstyleDoc_Token_Doc_Comment )
      $return->documentation = $current->documentation;

    return $return;
  }
}

// }}}
// {{{ Function

class DstyleDoc_Token_Function extends DstyleDoc_Token implements DstyleDoc_Token_Valueable
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    $return = new self;
    $return->open_tag = $current;
    $return->modifier = $current;
    $return->line = $line;
    $return->documentation = $current->documentation;

    return $return;      
  }

  public function set_value( $value )
  {
    $this->name = $value;
  }
  public function get_value()
  {
    return $this;
  }
}

// }}}
// {{{ DstyleDoc_Token_Tuple

class DstyleDoc_Token_Tuple extends DstyleDoc_Token_Light
{
  protected $_function = null;
  protected function set_function( DstyleDoc_Token_Function $function )
  {
    $this->_function = $function;
  }
  protected function get_function()
  {
    if( $this->_function instanceof DstyleDoc_Token_Function )
      return $this->_function;
    else
      return new DstyleDoc_Token_Fake;
  }

  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    $return = new self;
    $return->function = $current;

    return $return;
  }
}

// }}}
// {{{ DstyleDoc_Token_Variable


// }}}
// {{{


// }}}
// {{{


// }}}

?>
