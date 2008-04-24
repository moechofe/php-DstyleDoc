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

abstract class DstyleDoc_Token_Value extends DstyleDoc_Token_Light
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
  // {{{ $returns

  protected $_returns = array();

  protected function set_return( $return)
  {
    if( $return === true )
    {
      $this->_returns = array_values(array_unique($this->_returns));
      if( $this->get_return() !== '' )
        $this->_returns[] = '';
    }
    else
      $this->_returns[ count($this->_returns)-1 ] = (string)$return;
  }

  protected function get_return()
  {
    return end($this->_returns);
  }

  protected function get_returns()
  {
    if( $this->get_return() === '' )
      unset( $this->_returns[ count($this->_returns)-1 ] );

    return array_unique($this->_returns);
  }

  protected function set_returns( $returns )
  {
    $this->_returns = (array)$returns;
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
  // {{{ $consts

  protected $_consts = array();

  protected function set_const( DstyleDoc_Token_Custom $const )
  {
    if( $const instanceof DstyleDoc_Token_Const )
      $this->_consts[] = $consts;
  }

  protected function get_consts()
  {
    return $this->_consts;
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
      if( $current instanceof DstyleDoc_Token_Tuple
        or $current instanceof DstyleDoc_Token_Context )
        return DstyleDoc_Token_Context::hie( $converter, $current, $source, $file, $line );
      else
        return $current;
      break;

    case '(' :
      if( $current instanceof DstyleDoc_Token_Function )
        return DstyleDoc_Token_Tuple::hie( $converter, $current, $source, $file, $line );
      elseif( $current instanceof DstyleDoc_Token_Return )
      {
        $current->value = $source;
        return $current->value;
      }
      else
        return $current;
      break;

    case ',' :
      if( $current instanceof DstyleDoc_Token_Variable )
        return DstyleDoc_Token_Tuple::hie( $converter, $current, $source, $file, $line );
      else
        return $current;
      break;

    case '=' :
      return $current;
      break;

    case ')' :
      if( $current instanceof DstyleDoc_Token_Variable )
        return DstyleDoc_Token_Tuple::hie( $converter, $current->object, $source, $file, $line );
      elseif( $current instanceof DstyleDoc_Token_Return )
      {
        $current->value = $source;
        return $current->value;
      }
      else
        return $current;
      break;

    case ';' :
      if( $current instanceof DstyleDoc_Token_Tuple )
        return $current->object->object;
      elseif( $current instanceof DstyleDoc_Token_Function
        or $current instanceof DstyleDoc_Token_Context )
        return $current;
      elseif( $current instanceof DstyleDoc_Token_Const
        or $current instanceof DstyleDoc_Token_Variable
        or $current instanceof DstyleDoc_Token_Return )
        return $current->exit;
      break;

    case '}' :
      if( $current instanceof DstyleDoc_Token_Interface )
      {
        $current->to( $converter );
        return $current->open_tag;
      }
      elseif( $current instanceof DstyleDoc_Token_Context )
        return $current->down; 
//      elseif( $current instanceof DstyleDoc_Token_Function )
  //      return $current->object;
      break;

    case '!' :
    case '@' :
    case '?' :
    case ':' :
    case '[' :
    case ']' :
      return $current;
      break;

    case '.' :
    case '+' :
    case '-' :
    case '*' :
    case '/' :
    case '%' :
    case '>' :
    case '<' :
    case '&' :
    case '|' :
    case '^' :
      if( $current instanceof DstyleDoc_Token_Return )
      {
        $current->value = $source;
        return $current->value;
      }
      else
        return $current;
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
    if( $current instanceof DstyleDoc_Token_Modifier )
      $return = $current;
    else
    {
      $return = new self;
      $return->open_tag = $current;
      $return->modifier = $source;
      $return->object = $current;
      $return->documentation = $current;
    }

    $return->modifier = $source;

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
    elseif( $current instanceof DstyleDoc_Token_Tuple
      or $current instanceof DstyleDoc_Token_Modifier )
    {
      $return = new self;
      $return->object = $current;
      $return->object->var = $return;
    }
    elseif( $current instanceof DstyleDoc_Token_Return )
    {
      $current->value = $source;

      if( $current->value instanceof DstyleDoc_Token_Custom )
        return $current->value;
      else
        return new DstyleDoc_Token_Fake;
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
  public function get_exit()
  {
    return $this->object;
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

class DstyleDoc_Token_Lnumber extends DstyleDoc_Token_Value
{
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
    if( $this->_level === 0 )
    {
      if( $this->object instanceof DstyleDoc_Token_Function and $this->object->object instanceof DstyleDoc_Token_Class )
        return $this->object->object;
    }
    else
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
// {{{ Elseif

class DstyleDoc_Token_Elseif extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Else

class DstyleDoc_Token_Else extends DstyleDoc_Token_None
{
}

// }}}
// {{{ InstanceOf

class DstyleDoc_Token_InstanceOf extends DstyleDoc_Token_Value
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

class DstyleDoc_Token_Object_Operator extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Const

class DstyleDoc_Token_Const extends DstyleDoc_Token implements DstyleDoc_Token_Valueable
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    $return = new self;
    $return->open_tag = $current;
    $return->modifier = $current;
    $return->file = $file;
    $return->line = $line;
    $return->object = $current;
    $return->documentation = $current;

    return $return;
  }

  public function set_value( $value )
  {
    if( $this->name )
    {
      $this->name = $value;
      $this->object->const = $this;
    }
    elseif( $this->default )
      $this->default = $value;
  }
  public function get_value()
  {
    return $this;
  }
  public function get_exit()
  {
    return $this->object;
  }
}

// }}}
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
// {{{ Return

class DstyleDoc_Token_Return extends DstyleDoc_Token implements DstyleDoc_Token_Valueable
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    $return = new self;
    $return->open_tag = $current;
    $return->object = $current;

    if( $current instanceof DstyleDoc_Token_Function )
      $ref = $current;
    elseif( $current->object instanceof DstyleDoc_Token_Function )
      $ref = $current->object;

    $ref->return = true;
    $return->brackets = 0;
    $return->rollback = false;

    return $return;
  }

  private $rollback = false;

  private function rollback( $current )
  {
    $current->return = '';
    $this->rollback = true;
    //var_dump( 'ROLLBACK' );
  }

  private $types = array(
    'string', 'number', 'boolean', 'array', 'object', 'null', 'binary' );

  private $brackets = 0;

  public function set_value( $value )
  {
    if( $this->object instanceof DstyleDoc_Token_Function )
      $current = $this->object;
    elseif( $this->object->object instanceof DstyleDoc_Token_Function )
      $current = $this->object->object;

    if( ! $current )
      return;

    //var_dump( "VALUE : ".$value );
    //var_dump( "RETURN : ".$current->return );
    //var_dump( "BRACKETS : ".$this->brackets );

    $r = false;

    if( $this->brackets and $value === ')' )
    {
      //var_dump( __LINE__ );
      $this->brackets--;
    }

    elseif( $this->brackets )
    {
      //var_dump( __LINE__ );
      null;
    }

    elseif( in_array(strtolower($value), array('self','$this')) )
    {
      //var_dump( __LINE__ );
      if( ! $current->return )
        $current->return = $current->return . $this->object->object->object->name;
    }

    elseif( substr($current->return,-1) === '(' and $value !== ')' )
      null;

    elseif( in_array(substr($value,0,1), array('\'','"')) or in_array(strtolower($value), array('(string)','__file__','__function__','__class__','__dir__','__method__','__namespace__')) )
    {
      //var_dump( __LINE__ );
      if( in_array($current->return,array('string','')) )
        $current->return = 'string';
    }

    elseif( $value === '::' or $value === '->' )
    {
      //var_dump( __LINE__ );
      if( $current->return and ! in_array($current->return,$this->types) )
        $current->return = $current->return . $value;
    }

    elseif( $value === '(' )
    {
      //var_dump( __LINE__ );
      if( ! in_array($current->return,$this->types) and $current->return )
        $current->return = $current->return . '()';
      else
         $this->brackets++;
    }

    elseif( in_array($value, array('.','.=')) )
    {
      //var_dump( __LINE__ );
      if( in_array($current->return,array('string','')) or ! in_array($current->return,$this->types) )
        $current->return = 'string';
      else
        return $this->rollback($current);
    }

    elseif( in_array(strtolower($value), array('+','-','*','/','*','%','++','--','(int)','(integer)','(float)','(double)','(real)','>>','<<','&','^','|','+=','-=','*=','/=','%=','__line__')) )
    {
      //var_dump( __LINE__ );
      if( in_array($current->return,array('number','')) or ! in_array($current->return,$this->types) )
        $current->return = 'number';
      else
        return $this->rollback($current);
    }

    elseif( in_array(strtolower($value), array('array','(array)')) )
    {
      //var_dump( __LINE__ );
      if( in_array($current->return,array('array','')) )
        $current->return = 'array';
      else
        return $this->rollback($current);
    }

    elseif( strtolower($value) === '(object)' )
    {
      //var_dump( __LINE__ );
      if( in_array($current->return,array('object','')) )
        $current->return = 'object';
      else
        return $this->rollback($current);
    }

    elseif( strtolower($value) === '(binary)' )
    {
      //var_dump( __LINE__ );
      if( in_array($current->return,array('binary','')) )
        $current->return = 'binary';
      else
        return $this->rollback($current);
    }

    elseif( preg_match('/^\d/', $value) )
    {
      //var_dump( __LINE__ );
      if( in_array($current->return,array('number','')) )
        $current->return = 'number';
    }

    elseif( $current->return and in_array(strtolower($value), array('null','true','false')) )
      null;

    elseif( strtolower($value) === 'null' )
      $current->return = 'null';

    elseif( in_array(strtolower($value), array('&&','||','!','and','or','xor','(bool)','(boolean)','true','false','instanceof','===','==','<=','>=','>','<','!=','!==','<>')) )
    {
      //var_dump( __LINE__ );
      if( in_array($current->return,array('boolean','')) or ! in_array($current->return,$this->types) )
        $current->return = 'boolean';
      else
        return $this->rollback($current);
    }

    elseif( substr($current->return,-2) === '::' or substr($current->return,-2) === '->' )
    {
      //var_dump( __LINE__ );
        $current->return = $current->return . $value;
    }

    elseif( substr($current->return,-1) === ')' )
      null;

    elseif( substr($value,0,1) === '$' )
    {
      //var_dump( __LINE__ );
      if( ! in_array($current->return,$this->types) and substr($current->return,0,1) !== '$' )
        $current->return .= $value;
    }

    elseif( substr($current->return,0,1) !== '$' )
    {
      //var_dump( __LINE__ );
      if( ! in_array($current->return,$this->types) )
        $current->return = $value;
    }

    else
      $r = true;

    //var_dump( $current->return );

    if( $r) 
    {
      $this->rollback( $current );
    }

  }

  public function get_value()
  {
    if( $this->rollback )
      return $this->object;
    else
      return $this;
  }
  public function get_exit()
  {
    if( $this->object instanceof DstyleDoc_Token_Function )
      $current = $this->object;
    elseif( $this->object->object instanceof DstyleDoc_Token_Function )
      $current = $this->object->object;

    $returns = array();
    foreach( $current->returns as $return )
      if( ! preg_match( '/^\\$[_\\w]+$/', $return ) and ! preg_match( '/^(?<!::|->)[_\\w]+\\(\\)$/', $return ) and ! preg_match( '/^\\$[_\\w]+(::|->)\\$?[_\\w]+\(?\)?$/', $return ) )
        $returns[] = $return;

    $current->returns = $returns;

    return $this->object;
  }
}

// }}}
// {{{ Double Colon

class DstyleDoc_Token_Double_Colon extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ DNUmber

class DstyleDoc_Token_DNumber extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Boolean And

class DstyleDoc_Token_Boolean_And extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Inc

class DstyleDoc_Token_Inc extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Dec

class DstyleDoc_Token_Dec extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Int Cast

class DstyleDoc_Token_Int_Cast extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Bool Cast

class DstyleDoc_Token_Bool_Cast extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Double Cast

class DstyleDoc_Token_Double_Cast extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Array Cast

class DstyleDoc_Token_Array_Cast extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Object Cast

class DstyleDoc_Token_Object_Cast extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Sr

class DstyleDoc_Token_Sr extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Sl

class DstyleDoc_Token_Sl extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Is Identical

class DstyleDoc_Token_Is_Identical extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Is Equal

class DstyleDoc_Token_Is_Equal extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Is Greater Or Equal

class DstyleDoc_Token_Is_Greater_Or_Equal extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Is Smaller Or Equal

class DstyleDoc_Token_Is_Smaller_Or_Equal extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Is Not Equal

class DstyleDoc_Token_Is_Not_Equal extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Is Not Identical

class DstyleDoc_Token_Is_Not_Identical extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ String Cast

class DstyleDoc_Token_String_Cast extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Array

class DstyleDoc_Token_Array extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Concat Equal

class DstyleDoc_Token_Concat_Equal extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Plus Equal

class DstyleDoc_Token_Plus_Equal extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Xor Equal

class DstyleDoc_Token_Xor_Equal extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Minus Equal

class DstyleDoc_Token_Minus_Equal extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Mul Equal

class DstyleDoc_Token_Mul_Equal extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Div Equal

class DstyleDoc_Token_Div_Equal extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Mod Equal

class DstyleDoc_Token_Mod_Equal extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ File

class DstyleDoc_Token_File extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Func C

class DstyleDoc_Token_Func_C extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Method C

class DstyleDoc_Token_Method_C extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Line

class DstyleDoc_Token_Line extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Logical Or

class DstyleDoc_Token_Logical_Or extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Logical And

class DstyleDoc_Token_Logical_And extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Logical Xor

class DstyleDoc_Token_Logical_Xor extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Try

class DstyleDoc_Token_Try extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Catch

class DstyleDoc_Token_Catch extends DstyleDoc_Token_None
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

?>
