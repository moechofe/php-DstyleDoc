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
abstract class DstyleDoc_Token_None extends DstyleDoc_Token_Custom
{
  final static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    return $current;
  }
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

interface DstyleDoc_Token_Elementable
{
  function to( DstyleDoc_Converter $converter );
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
    if( $documentation instanceof DstyleDoc_Token_Doc_Comment )
      $this->_documentation = $documentation->documentation;
    elseif( $documentation instanceof DstyleDoc_Token )
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
    if( $modifier instanceof DstyleDoc_Token_Custom )
      $this->modifiers = $modifier->modifiers;
    elseif( is_string($modifier) and isset($this->_modifiers[$modifier]) )
      $this->_modifiers[$modifier] = true;
  }

  protected function set_modifiers( $modifiers )
  {
    foreach( (array)$modifiers as $modifier => $true )
      if( $true and isset($this->_modifiers[$modifier]) )
        $this->_modifiers[$modifier] = true;
  }

  protected function get_modifiers()
  {
    return $this->_modifiers;
  }

  // }}}
  // {{{ $function

  protected $_function = null;

  protected function set__function( DstyleDoc_Token_Custom $function )
  {
    if( $function instanceof DstyleDoc_Token_Function )
      $this->_function = $function;
    elseif( $function->function instanceof DstyleDoc_Token_Function )
      $this->_function = $function->function;
  }

  protected function get__function()
  {
    if( $this->_function instanceof DstyleDoc_Token_Function )
      return $this->_function;
    else
      return new DstyleDoc_Token_Fake;
  }

  // }}}
  // {{{ $methods

  protected $_methods = array();

  protected function set_method( DstyleDoc_Token_Custom $method )
  {
    if( $method instanceof DstyleDoc_Token_Function )
      $this->_methods[] = $method;
  }

  protected function get_methods()
  {
    return $this->_methods;
  }

  // }}}
  // {{{ $vars

  protected $_vars = array();

  protected function set_var( DstyleDoc_Token_Custom $var )
  {
    if( $var instanceof DstyleDoc_Token_Variable )
      $this->_vars[] = $var;
  }

  protected function get_vars()
  {
    return $this->_vars;
  }

  // }}}
  // {{{ $types

  protected $_types = array();

  protected function set_type( $type )
  {
    $this->_types[] = (string)$type;
  }

  protected function get_types()
  {
    return $this->_types;
  }

  // }}}
  // {{{ $default

  protected $_default = '';

  protected function set_default( $default )
  {
    $this->_default = $default;
  }

  protected function get_default()
  {
    return $this->_default;
  }

  // }}}
  // {{{ $object

  protected $_object = null;

  protected function set_object( DstyleDoc_Token_Custom $object )
  {
    if( $object instanceof DstyleDoc_Token_Interface
      or $object instanceof DstyleDoc_Token_Function
      or $object instanceof DstyleDoc_Token_Class
      or $object instanceof DstyleDoc_Token_Context )
      $this->_object = $object;

    elseif( $object->object instanceof DstyleDoc_Token_Interface
      or $object->object instanceof DstyleDoc_Token_Function
      or $object->object instanceof DstyleDoc_Token_Class )
      $this->_object = $object->object;
  }

  protected function get_object()
  {
    if( $this->_object instanceof DstyleDoc_Token_Custom )
      return $this->_object;
    else
      return new DstyleDoc_Token_Fake;
  }

  // }}}
  // {{{ $exceptions

  protected $_exceptions = array();

  protected function set_exception( $exception )
  {
    $this->_exceptions[] = (string)$exception;
  }

  protected function get_exceptions()
  {
    return $this->_exceptions;
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
      if( $current instanceof DstyleDoc_Token_Tuple )
        return DstyleDoc_Token_Context::hie( $converter, $current, $source, $file, $line );
      else
        return $current;
      break;

    case '(' :
      if( $current instanceof DstyleDoc_Token_Function )
        return DstyleDoc_Token_Tuple::hie( $converter, $current, $source, $file, $line );
      if( $current instanceof DstyleDoc_Token_Context )
        return $current;
      break;

    case ',' :
      if( $current instanceof DstyleDoc_Token_Variable )
        return DstyleDoc_Token_Tuple::hie( $converter, $current, $source, $file, $line );
      break;

    case '=' :
      if( $current instanceof DstyleDoc_Token_Variable )
        return $current;
      else
        return $current;
      break;

    case ')' :
      if( $current instanceof DstyleDoc_Token_Tuple )
        return $current;
      elseif( $current instanceof DstyleDoc_Token_Variable )
        return DstyleDoc_Token_Tuple::hie( $converter, $current->object, $source, $file, $line );
      else
        return $current;
      break;

    case ';' :
      if( $current instanceof DstyleDoc_Token_Tuple )
        return $current->object->object;
      elseif( $current instanceof DstyleDoc_Token_Function )
        return $current;
      break;

    case '}' :
      if( $current instanceof DstyleDoc_Token_Interface )
      {
        $current->to( $converter );
        return $current->open_tag;
      }
      elseif( $current instanceof DstyleDoc_Token_Function and ! $current->object instanceof DstyleDoc_Token_Fake )
      {
        $current->to( $converter );
        return $current->open_tag;
      }
//      elseif( $current instanceof DstyleDoc_Token_Function )
  //      return $current->object;
      break;

    case '!' :
      return $current;
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
    $return->object = $current;

    if( $current instanceof self )
      $current->open_tag->documentation = $current;

    return $return;
  }
}

// }}}
// {{{ Interface

class DstyleDoc_Token_Interface extends DstyleDoc_Token implements DstyleDoc_Token_Valueable, DstyleDoc_Token_Elementable
{
  static public function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    $return = new self;
    $return->open_tag = $current;
    $return->file = $file;
    $return->line = $line;
    $return->documentation = $current;

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

  public function to( DstyleDoc_Converter $converter )
  {
    $converter->interface = $this->name;
    $interface = $converter->interface;

    $interface->file = $this->file;
    $interface->line = $this->line;
    $interface->documentation = $this->documentation;

    foreach( $this->methods as $method )
    {
      $interface->method = $method->name;
      $function = $interface->method;

      $function->class = $interface;
      $function->file = $method->file;
      $function->line = $method->line;
      $function->documentation = $method->documentation;
      $function->public = true;
      $function->static = $method->modifiers['static'];

      foreach( $method->vars as $var )
      {
        $function->param = $var->name;
        $param = $function->param;

        foreach( $var->types as $type )
          $param->type = $type;

        $param->default = $var->default;
      }
    }
  }
}

// }}}
// {{{ String

class DstyleDoc_Token_String extends DstyleDoc_Token_Light
{
  static public function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    $return = $current;

    if( $current instanceof DstyleDoc_Token_Tuple )
    {
      $return = DstyleDoc_Token_Variable::hie( $converter, $current, $source, $file, $line );
      $return->type = $source;
    }
    elseif( $current instanceof DstyleDoc_Token_Valueable )
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

class DstyleDoc_Token_Comment extends DstyleDoc_Token_None
{
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
    $return->object = $current;

    $return->documentation = $current;

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
    $return->file = $file;
    $return->line = $line;
    $return->documentation = $current;
    $return->object = $current;

    if( ! $return->object instanceof DstyleDoc_Token_Fake )
      $return->object->method = $return;

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
// {{{ Tuple

class DstyleDoc_Token_Tuple extends DstyleDoc_Token
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    $return = new self;
    $return->object = $current;

    return $return;
  }
}

// }}}
// {{{ Variable

class DstyleDoc_Token_Variable extends DstyleDoc_Token implements DstyleDoc_Token_Valueable
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    if( $current instanceof self )
      $return = $current;
    elseif( $current instanceof DstyleDoc_Token_Tuple )
    {
      $return = new self;
      $return->object = $current;
      $return->object->var = $return;
    }
    else
      return $current;
    
    $return->name = $source;

    return $return;
  }

  public function set_value( $value )
  {
    $this->default = $value;
  }

  public function get_value()
  {
    return $this;
  }
}

// }}}
// {{{ Constant Encapsed String

class DstyleDoc_Token_Constant_Encapsed_String extends DstyleDoc_Token_Light
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
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
// {{{ Lnumber

class DstyleDoc_Token_Lnumber extends DstyleDoc_Token_Light
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
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
// {{{ Class

class DstyleDoc_Token_Class extends DstyleDoc_Token implements DstyleDoc_Token_Valueable
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    $return = new self;
    $return->open_tag = $current;
    $return->modifier = $current;
    $return->file = $file;
    $return->line = $line;
    $return->documentation = $current;

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
// {{{ Context

class DstyleDoc_Token_Context extends DstyleDoc_Token
{
  protected $_level = 1;
  protected function get_up()
  {
    $this->_level += 1;
    return $this;
  }
  protected function get_down()
  {
    $this->_level -= 1;
    return $this;
  }
  protected function get_level()
  {
    return $this->_level;
  }

  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    if( $current instanceof self )
      return $current->up;

    $return = new self;
    $return->open_tag = $current;
    $return->object = $current;

    return $return;
  }
}

// }}}
// {{{ Protected

class DstyleDoc_Token_Protected extends DstyleDoc_Token_Custom
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    return DstyleDoc_Token_Modifier::hie( $converter, $current, $source, $file, $line );
  }
}

// }}}
// {{{ Public

class DstyleDoc_Token_Public extends DstyleDoc_Token_Custom
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    return DstyleDoc_Token_Modifier::hie( $converter, $current, $source, $file, $line );
  }
}

// }}}
// {{{ Private

class DstyleDoc_Token_Private extends DstyleDoc_Token_Custom
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    return DstyleDoc_Token_Modifier::hie( $converter, $current, $source, $file, $line );
  }
}

// }}}
// {{{ Final

class DstyleDoc_Token_Final extends DstyleDoc_Token_Custom
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    return DstyleDoc_Token_Modifier::hie( $converter, $current, $source, $file, $line );
  }
}

// }}}
// {{{ Abstract

class DstyleDoc_Token_Abstract extends DstyleDoc_Token_Custom
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    return DstyleDoc_Token_Modifier::hie( $converter, $current, $source, $file, $line );
  }
}
// }}}
// {{{ If

class DstyleDoc_Token_If extends DstyleDoc_Token_None
{
}

// }}}
// {{{ InstanceOf

class DstyleDoc_Token_InstanceOf extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Throw

class DstyleDoc_Token_Throw extends DstyleDoc_Token implements DstyleDoc_Token_Valueable
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    $return = new self;
    $return->object = $current;

    return $return;
  }

  public function set_value( $value )
  {
    $this->object->exception = $value;
  }
  public function get_value()
  {
    return $this->object;
  }
}

// }}}
// {{{ New

class DstyleDoc_Token_New extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Object Operator

class DstyleDoc_Token_Object_Operator extends DstyleDoc_Token_None
{
}

// }}}
// {{{


// }}}
// {{{


// }}}
// {{{


// }}}
// {{{


// }}}
// {{{


// }}}
// {{{


// }}}
// {{{


// }}}

?>
