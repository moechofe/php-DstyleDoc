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
 * Classe de token qui stope l'analyse.
 */
class DstyleDoc_Token_Stop extends DstyleDoc_Token_Custom
{
}

/**
 * Classe de token de base.
 */
abstract class DstyleDoc_Token_None extends DstyleDoc_Token_Custom
{
  final static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    if( $current instanceof DstyleDoc_Token_Doc_Comment )
    {
      if( ! $current->object instanceof DstyleDoc_Token_Fake )
        return $current->object;
      else
      {
        $current->open_tag->documentation = $current;
        return $current->open_tag;
      }
    }
    else
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

    if( $current instanceof DstyleDoc_Token_Doc_Comment )
    {
      if( ! $current->object instanceof DstyleDoc_Token_Fake )
        return $current->object;
      else
        return $current->open_tag;
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
    elseif( $this instanceof DstyleDoc_Token_Open_Tag )
      return $this;
    else
      return new DstyleDoc_Token_Fake;
  }

  // }}}
  // {{{ $documentation

  protected $_documentation = '';

  protected function set_documentation( $documentation )
  {
    if( $documentation instanceof DstyleDoc_Token_Doc_Comment or $documentation instanceof DstyleDoc_Token )
      $this->set_documentation( $documentation->documentation );
    elseif( trim((string)$documentation) )
    {
      if( $this->_documentation )
        $this->_documentation .= "\n".(string)$documentation;
      else
        $this->_documentation = (string)$documentation;
    }
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
      or $object->object instanceof DstyleDoc_Token_Class
      or $object->object instanceof DstyleDoc_Token_Context )
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
  // {{{ $dependancies

  protected $_dependancies = array();

  protected function set_dependancie( $dependancie )
  {
    $this->_dependancies[] = (string)$dependancie;
  }

  protected function get_dependancies()
  {
    return $this->_dependancies;
  }

  // }}}
  // {{{ $extend

  protected $_extend = '';

  protected function set_extend( $extend )
  {
    $this->_extend = $extend;
  }

  protected function get_extend()
  {
    return $this->_extend;
  }

  // }}}
  // {{{ $implements

  protected $_implements = array();

  protected function set_implement( $implement )
  {
    $this->_implements[] = (string)$implement;
  }

  protected function get_implements()
  {
    return $this->_implements;
  }

  // }}}
  // {{{ $expression

  protected $_expression = null;

  protected function set_expression( $expression )
  {
    if( $expression )
      $this->_expression = new DstyleDoc_Token_Expression;
  }

  protected function get_expression()
  {
    return $this->_expression;
  }

  // }}}
}

// {{{ Expressionable

interface DstyleDoc_Token_Expressionable
{
  // {{{ Rollback()

  function rollback( DstyleDoc_Token $current );

  // }}}
}

// }}}
// {{{ Expression

class DstyleDoc_Token_Expression extends DstyleDoc_Token_Custom
{
  private $types = array(
    'string', 'number', 'boolean', 'array', 'object', 'null', 'binary', 'resource' );

  private $brackets = 0;

  protected $_rollback = false;

  protected function set_rollback( $rollback )
  {
    $this->_rollback = (boolean)$rollback;
  }

  protected function get_rollback()
  {
    return $this->_rollback;
  }

  public function analyse( DstyleDoc_Token_Expressionable $token, DstyleDoc_Token $current, $value )
  {
    if( $current instanceof DstyleDoc_Token_Class )
      $class = $current;
    elseif( $current->object instanceof DstyleDoc_Token_Class )
      $class = $current->object;
    else
      $class = false;

    if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'returns')!==false )
    {
      if( ! $r = $token->expression_value ) $r = '&nbsp;';
      if( ! $c = $current->name ) $c = '&nbsp;';
      echo <<<HTML
<div style='clear:left;float:left;color:white;background:MediumVioletRed;padding:1px 3px'>{$c}</div>
<div style='float:left;color:white;background:PaleVioletRed;padding:1px 3px'><b>{$value}</b></div>
<div style='float:left;color:white;background:LightPink;color:black;padding:1px 3px'><b>{$r}</b></div>
<div style='background:IndianRed;padding:1px 3px;'><b>{$this->brackets}</b></div>
<div style='clear:both'></div>
HTML;
    }

    $r = false;

    if( $this->brackets and $value === ')' )
    {
      if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'returns')!==false ) var_dump( __LINE__ );
      $this->brackets--;
    }

    elseif( $this->brackets )
    {
      if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'returns')!==false ) var_dump( __LINE__ );
      null;
    }

    elseif( in_array(strtolower($value), array('self','$this')) )
    {
      if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'returns')!==false ) var_dump( __LINE__ );
      if( ! $token->expression_value and $class)
        $token->expression_value = $token->expression_value . $class->name;
    }

    elseif( substr($token->expression_value,-1) === '(' and $value !== ')' )
      null;

    elseif( in_array(substr($value,0,1), array('\'','"')) or in_array(strtolower($value), array('(string)','__file__','__function__','__class__','__dir__','__method__','__namespace__')) )
    {
      if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'returns')!==false ) var_dump( __LINE__ );
      if( in_array($token->expression_value,array('string','')) )
        $token->expression_value = 'string';
    }

    elseif( $value === '::' or $value === '->' )
    {
      if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'returns')!==false ) var_dump( __LINE__ );
      if( $token->expression_value and ! in_array($token->expression_value,$this->types) )
        $token->expression_value = $token->expression_value . $value;
    }

    elseif( $value === '(' )
    {
      if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'returns')!==false ) var_dump( __LINE__ );
      if( ! in_array($token->expression_value,$this->types) and $token->expression_value )
        $token->expression_value = $token->expression_value . '()';
      else
         $this->brackets++;
    }

    elseif( in_array($value, array('.','.=')) )
    {
      if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'returns')!==false ) var_dump( __LINE__ );
      if( in_array($token->expression_value,array('string','')) or ! in_array($token->expression_value,$this->types) )
        $token->expression_value = 'string';
      else
        return $token->rollback($current);
    }

    elseif( in_array(strtolower($value), array('+','-','*','/','*','%','++','--','(int)','(integer)','(float)','(double)','(real)','>>','<<','&','^','|','+=','-=','*=','/=','%=','__line__','<<=','>>=')) )
    {
      if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'returns')!==false ) var_dump( __LINE__ );
      if( in_array($token->expression_value,array('number','')) or ! in_array($token->expression_value,$this->types) )
        $token->expression_value = 'number';
      else
        return $token->rollback($current);
    }

    elseif( in_array(strtolower($value), array('array','(array)')) )
    {
      if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'returns')!==false ) var_dump( __LINE__ );
      if( in_array($token->expression_value,array('array','')) )
        $token->expression_value = 'array';
      else
        return $token->rollback($current);
    }

    elseif( strtolower($value) === '(object)' )
    {
      if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'returns')!==false ) var_dump( __LINE__ );
      if( in_array($token->expression_value,array('object','')) )
        $token->expression_value = 'object';
      else
        return $token->rollback($current);
    }

    elseif( strtolower($value) === '(binary)' )
    {
      if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'returns')!==false ) var_dump( __LINE__ );
      if( in_array($token->expression_value,array('binary','')) )
        $token->expression_value = 'binary';
      else
        return $token->rollback($current);
    }

    elseif( preg_match('/^\d/', $value) )
    {
      if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'returns')!==false ) var_dump( __LINE__ );
      if( in_array($token->expression_value,array('number','')) )
        $token->expression_value = 'number';
    }

    elseif( $token->expression_value and in_array(strtolower($value), array('null','true','false')) )
      null;

    elseif( in_array(strtolower($value), array('null','true','false')) )
      $token->expression_value = strtolower($value);

    elseif( strtolower($value) === 'null' or strtolower($value) === '(unset)' )
      $token->expression_value = 'null';

    elseif( in_array(strtolower($value), array('&&','||','!','and','or','xor','(bool)','(boolean)','instanceof','===','==','<=','>=','>','<','!=','!==','<>')) )
    {
      if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'returns')!==false ) var_dump( __LINE__ );
      if( in_array($token->expression_value,array('boolean','')) or ! in_array($token->expression_value,$this->types) )
        $token->expression_value = 'boolean';
      else
        return $token->rollback($current);
    }

    elseif( substr($token->expression_value,-2) === '::' or substr($token->expression_value,-2) === '->' )
    {
      if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'returns')!==false ) var_dump( __LINE__ );
        $token->expression_value = $token->expression_value . $value;
    }

    elseif( substr($token->expression_value,-1) === ')' )
      null;

    elseif( substr($value,0,1) === '$' )
    {
      if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'returns')!==false ) var_dump( __LINE__ );
      if( ! in_array($token->expression_value,$this->types) and substr($token->expression_value,0,1) !== '$' )
        $token->expression_value .= $value;
    }

    elseif( substr($token->expression_value,0,1) !== '$' )
    {
      if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'returns')!==false ) var_dump( __LINE__ );
      if( ! in_array($token->expression_value,$this->types) )
        $token->expression_value = $value;
    }

    else
      $r = true;

    if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'returns')!==false ) var_dump( __LINE__ );

    if( $r) 
    {
      $token->rollback( $current );
    }
  }

}

// }}}
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
      elseif( $current instanceof DstyleDoc_Token_Implements )
        return $current->object;
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
      {
        if( $current->object instanceof DstyleDoc_Token_Function and ! $current->object->object instanceof DstyleDoc_Token_Fake )
          return $current->object->object;
        elseif( ! $current->object instanceof DstyleDoc_Token_Fake )
          return $current->object;
        else
          return $current->open_tag;
      }
      elseif( $current instanceof DstyleDoc_Token_Function
        or $current instanceof DstyleDoc_Token_Context )
        return $current;
      elseif( $current instanceof DstyleDoc_Token_Const
        or $current instanceof DstyleDoc_Token_Variable
        or $current instanceof DstyleDoc_Token_Return )
        return $current->exit;
      elseif( $current instanceof DstyleDoc_Token_Open_Tag )
        return $current;
      elseif( $current instanceof DstyleDoc_Token_Throw )
      {
        return $current->object;
      }
      break;

    case '}' :
      if( $current instanceof DstyleDoc_Token_Elementable )
        $current->to( $converter );
      if( $current instanceof DstyleDoc_Token_Interface or $current instanceof DstyleDoc_Token_Class )
        return $current->open_tag;
      elseif( $current instanceof DstyleDoc_Token_Context )
      {
        $save = $current->object;
        $return = $current->down; 
        if( $return !== $save and $save instanceof DstyleDoc_Token_Elementable )
          $save->to( $converter );
        return $return;
      }
      elseif( $current instanceof DstyleDoc_Token_Open_Tag )
        return $current;
      break;

    case '!' :
    case '@' :
    case '?' :
    case ':' :
    case '[' :
    case ']' :
    case '"' :
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
    if( $current instanceof DstyleDoc_Token_Close_Tag and ! $current->object instanceof DstyleDoc_Token_Fake )
      return $current->object;
    elseif( $current instanceof DstyleDoc_Token_Close_Tag )
      return $current->open_tag;

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
      $function->abstract = true;
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

      $converter->method = $function;
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

    if( $current instanceof DstyleDoc_Token_Doc_Comment )
    {
      if( ! $current->object instanceof DstyleDoc_Token_Fake )
        return $current->object;
      else
        return $current->open_tag;
    }
    elseif( $current instanceof DstyleDoc_Token_Tuple )
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

class DstyleDoc_Token_Function extends DstyleDoc_Token implements DstyleDoc_Token_Valueable, DstyleDoc_Token_Elementable
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    if( ! array_intersect( $current->modifiers, array_flip(array('public','private','protected')) ) )
      $current->modifier = 'public';

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

  public function to( DstyleDoc_Converter $converter )
  {
    if( $this->object instanceof DstyleDoc_Token_Fake )
    {
      $converter->function = $this->name;
      $function = $converter->function;

      $function->file = $this->file;
      $function->line = $this->line;
      $function->documentation = $this->documentation;

      foreach( $this->vars as $var )
      {
        $function->param = $var->name;
        $param = $function->param;

        foreach( $var->types as $type )
          $param->type = $type;

        $param->default = $var->default;
      }

      foreach( $this->returns as $return )
        $function->return = $return;

      foreach( $this->exceptions as $exception )
        $function->exception = $exception;
    }
  }
}

// }}}
// {{{ Tuple

class DstyleDoc_Token_Tuple extends DstyleDoc_Token
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    $return = new self;
    $return->open_tag = $current;
    $return->object = $current;

    return $return;
  }
}

// }}}
// {{{ Variable

class DstyleDoc_Token_Variable extends DstyleDoc_Token implements DstyleDoc_Token_Valueable, DstyleDoc_Token_Expressionable
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    if( $current instanceof DstyleDoc_Token_Doc_Comment )
    {
      if( ! $current->object instanceof DstyleDoc_Token_Fake )
        return $current->object;
      else
        return $current->open_tag;
    }
    elseif( $current instanceof self )
      $return = $current;
    elseif( $current instanceof DstyleDoc_Token_Tuple
      or $current instanceof DstyleDoc_Token_Modifier )
    {
      $return = new self;
      $return->file = $file;
      $return->line = $line;
      $return->open_tag = $current;
      $return->object = $current;
      $return->documentation = $current;
      $return->modifier = $current;
      if( $current instanceof DstyleDoc_Token_Modifier )
      {
        $return->object->var = $return;
        $return->expression = true;
      }
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

  public function rollback( DstyleDoc_Token $current )
  {
    $current->return = '';
    $this->expression->rollback = true;
    if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'returns')!==false )
      echo <<<HTML
<div style='color:white;background:HotPink;padding:1px 3px'><b>ROLLBACK</b></div>
HTML;
  }
  public function set_expression_value( $value )
  {
    $this->default = $value;
  }
  public function get_expression_value()
  {
    return $this->default;
  }
  public function set_value( $value )
  {
    $current = $this->object;

    if( $this->expression )
      $this->expression->analyse( $this, $current, $value ); 
    else
      $this->default = $value;
  }

  public function get_value()
  {
    if( $this->expression and $this->expression->rollback )
      return $this->object;
    else
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

class DstyleDoc_Token_Class extends DstyleDoc_Token implements DstyleDoc_Token_Valueable, DstyleDoc_Token_Elementable
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

  public function to( DstyleDoc_Converter $converter )
  {
    $converter->class = $this->name;
    $class = $converter->class;

    $class->file = $this->file;
    $class->line = $this->line;
    $class->documentation = $this->documentation;
    $class->parent = $this->extend;

    foreach( $this->vars as $var )
    {
      $class->member = $var->name;
      $member = $class->member;

      $member->class = $class;
      $member->file = $var->file;
      $member->line = $var->line;
      $member->public = $var->modifiers['public'];
      $member->protected = $var->modifiers['protected'];
      $member->private = $var->modifiers['private'];
      $member->documentation = $var->documentation;

      $member->type = $var->default;

      $converter->member = $member;
    }

    foreach( $this->methods as $method )
    {
      $class->method = $method->name;
      $function = $class->method;

      $function->class = $class;
      $function->file = $method->file;
      $function->line = $method->line;
      $function->documentation = $method->documentation;
      $function->public = $method->modifiers['public'];
      $function->protected = $method->modifiers['protected'];
      $function->private = $method->modifiers['private'];
      $function->abstract = $method->modifiers['abstract'];
      $function->final = $method->modifiers['final'];
      $function->static = $method->modifiers['static'];

      foreach( $method->vars as $var )
      {
        $function->param = $var->name;
        $param = $function->param;

        foreach( $var->types as $type )
          $param->type = $type;

        $param->default = $var->default;
      }

      foreach( $method->returns as $return )
        $function->return = $return;

      $converter->method = $function;
    }
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
      else
        return $this->open_tag;
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
// {{{ Endif

class DstyleDoc_Token_Endif extends DstyleDoc_Token_None
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
    $return->open_tag = $current;
    $return->file = $file;
    $return->line = $line;

    return $return;
  }

  public function set_value( $value )
  {
    if( ! $this->object instanceof DstyleDoc_Token_Fake )
      $this->object->object->exception = $value;
    else
      $this->open_tag->exception = $value;
  }
  public function get_value()
  {
    if( ! $this->object instanceof DstyleDoc_Token_Fake )
      return $this->object;
    else
      return $this->open_tag;
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

class DstyleDoc_Token_Return extends DstyleDoc_Token implements DstyleDoc_Token_Valueable, DstyleDoc_Token_Expressionable
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    $return = new self;
    $return->open_tag = $current;
    $return->object = $current;
    $return->expression = true;

    if( $current instanceof DstyleDoc_Token_Function )
      $ref = $current;
    elseif( $current->object instanceof DstyleDoc_Token_Function )
      $ref = $current->object;

    $ref->return = true;

    return $return;
  }

  public function rollback( DstyleDoc_Token $current )
  {
    $current->return = '';
    $this->expression->rollback = true;
    if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'returns')!==false )
      echo <<<HTML
<div style='color:white;background:HotPink;padding:1px 3px'><b>ROLLBACK</b></div>
HTML;

  }

  public function set_expression_value( $value )
  {
    if( $this->object instanceof DstyleDoc_Token_Function )
      $current = $this->object;
    elseif( $this->object->object instanceof DstyleDoc_Token_Function )
      $current = $this->object->object;

    $current->return = $value;
  }
  public function get_expression_value()
  {
    if( $this->object instanceof DstyleDoc_Token_Function )
      $current = $this->object;
    elseif( $this->object->object instanceof DstyleDoc_Token_Function )
      $current = $this->object->object;

    return $current->return;
  }
  public function set_value( $value )
  {
    if( $this->object instanceof DstyleDoc_Token_Function )
      $current = $this->object;
    elseif( $this->object->object instanceof DstyleDoc_Token_Function )
      $current = $this->object->object;

    $this->expression->analyse( $this, $current, $value );
  }
  public function get_value()
  {
    if( $this->expression->rollback )
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
//      if( ! preg_match( '/^\\$[_\\w]+$/', $return ) and ! preg_match( '/^(?<!::|->)[_\\w]+\\(\\)$/', $return ) and ! preg_match( '/^\\$[_\\w]+(::|->)\\$?[_\\w]+\(?\)?$/', $return ) )
        $returns[] = $return;

    $current->returns = $returns;

    if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'returns')!==false ) var_dump( $returns );

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
// {{{ Unset

class DstyleDoc_Token_Unset extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Isset

class DstyleDoc_Token_Isset extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Require Once

class DstyleDoc_Token_Require_Once extends DstyleDoc_Token implements DstyleDoc_Token_Valueable
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    $return = new self;
    $return->file = $file;
    $return->line = $line;
    $return->object = $current;
    $return->open_tag = $current;
    $return->documentation = $current;

    return $return;
  }

  public function set_value( $value )
  {
    if( ( substr($value,0,1) === '\'' or substr($value,0,1) === '"' ) )
    {
      $file = stripslashes(substr($value,1,-1));
      if( strpos($file,'$') === false or preg_match( '/(?<=\\\)\\$/', $file ) )
        $this->open_tag->dependancie = $file;
    }
  }
  public function get_value()
  {
    if( ! $this->object instanceof DstyleDoc_Token_Fake )
      return $this->object;
    else
      return $this->open_tag;
  }
}

// }}}
// {{{ Require

class DstyleDoc_Token_Require extends DstyleDoc_Token_Require_Once
{
}

// }}}
// {{{ Include Once

class DstyleDoc_Token_Include_Once extends DstyleDoc_Token_Require_Once
{
}

// }}}
// {{{ Include

class DstyleDoc_Token_Include extends DstyleDoc_Token_Require_Once
{
}

// }}}
// {{{ While

class DstyleDoc_Token_While extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Endwhile 

class DstyleDoc_Token_Endwhile extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Foreach

class DstyleDoc_Token_Foreach extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Endforeach

class DstyleDoc_Token_Endforeach extends DstyleDoc_Token_None
{
}

// }}}
// {{{ As

class DstyleDoc_Token_As extends DstyleDoc_Token_None
{
}

// }}}
// {{{ For

class DstyleDoc_Token_For extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Endfor

class DstyleDoc_Token_Endfor extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Switch


class DstyleDoc_Token_Switch extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Endswitch

class DstyleDoc_Token_Endswitch extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Case

class DstyleDoc_Token_Case extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Break

class DstyleDoc_Token_Break extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Continue

class DstyleDoc_Token_Continue extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Do

class DstyleDoc_Token_Do extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Default

class DstyleDoc_Token_Default extends DstyleDoc_Token_None
{
}

// }}}
// {{{ List

class DstyleDoc_Token_List extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Bad Character

class DstyleDoc_Token_Bad_Character extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Character

class DstyleDoc_Token_Character extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Clone

class DstyleDoc_Token_Clone extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Curly Open

class DstyleDoc_Token_Curly_Open extends DstyleDoc_Token_Context
{
}

// }}}
// {{{ Declare

class DstyleDoc_Token_Declare extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Double Arrow

class DstyleDoc_Token_Double_Arrow extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Empty

class DstyleDoc_Token_Empty extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Echo

class DstyleDoc_Token_Echo extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Encapsed And Whitespace

class DstyleDoc_Token_Encapsed_And_Whitespace extends DstyleDoc_Token_String
{
}

// }}}
// {{{ End Declare

class DstyleDoc_Token_End_Declare extends DstyleDoc_Token_None
{
}

// }}}
// {{{ End Heredoc

class DstyleDoc_Token_End_Heredoc extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Start Heredoc

class DstyleDoc_Token_start_heredoc extends DstyleDoc_Token_String
{
}

// }}}
// {{{ Eval

class DstyleDoc_Token_Eval extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Exit

class DstyleDoc_Token_Exit extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Global

class DstyleDoc_Token_Global extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Halt Compiler

class DstyleDoc_Token_Halt_Compiler extends DstyleDoc_Token_Stop
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    DstyleDoc_Token_Close_Tag::hie( $converter, $current, $source, $file, $line );
    return new self;
  }
}

// }}}
// {{{ Inline Html

class DstyleDoc_Token_Inline_Html extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Ml Comment

class DstyleDoc_Token_Ml_Comment extends DstyleDoc_Token_Doc_Comment
{
}

// }}}
// {{{ Ns C

class DstyleDoc_Token_Ns_C extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Namespace

class DstyleDoc_Token_Namespace extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Num String

class DstyleDoc_Token_Num_String extends DstyleDoc_Token_String
{
}

// }}}
// {{{ Old Function

class DstyleDoc_Token_Old_Function extends DstyleDoc_Token_Function
{
}

// }}}
// {{{ Open Tag With Echo

class DstyleDoc_Token_Open_Tag_With_Echo extends DstyleDoc_Token_Function
{
}

// }}}
// {{{ Paamayim Nekudotayim

class DstyleDoc_Token_Paamayim_Nekudotayim extends DstyleDoc_Token_Function
{
}

// }}}
// {{{ Print

class DstyleDoc_Token_Print extends DstyleDoc_Token_None
{
}

// }}}
// {{{ Sl Equal

class DstyleDoc_Token_Sl_Equal extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Sr Equal

class DstyleDoc_Token_Sr_Equal extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ String Varname

class DstyleDoc_Token_String_Varname extends DstyleDoc_Token_String
{
}

// }}}
// {{{ Unset Cast

class DstyleDoc_Token_Unset_Cast extends DstyleDoc_Token_Value
{
}

// }}}
// {{{ Var

class DstyleDoc_Token_Var extends DstyleDoc_Token_Public
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    return DstyleDoc_Token_Modifier::hie( $converter, $current, 'public', $file, $line );
  }
}

// }}}
// {{{ Dollar Open Curly Braces

class DstyleDoc_Token_Dollar_Open_Curly_Braces extends DstyleDoc_Token_Context
{
}

// }}}
// {{{ Extends

class DstyleDoc_Token_Extends extends DstyleDoc_Token implements DstyleDoc_Token_Valueable
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    $return = new self;
    $return->open_tag = $current;
    $return->object = $current;

    return $return;
  }

  public function set_value( $value )
  {
    $this->object->extend = $value;
  }
  public function get_value()
  {
    return $this->object;
  }
}

// }}}
// {{{ Implements

class DstyleDoc_Token_Implements extends DstyleDoc_Token implements DstyleDoc_Token_Valueable
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    $return = new self;
    $return->open_tag = $current;
    $return->object = $current;

    return $return;
  }

  public function set_value( $value )
  {
    $this->object->implement = $value;
  }
  public function get_value()
  {
    return $this;
  }
}

// }}}
// {{{ Close Tag

class DstyleDoc_Token_Close_Tag extends DstyleDoc_Token
{
  static function hie( DstyleDoc_Converter $converter, DstyleDoc_Token_Custom $current, $source, $file, $line )
  {
    $return = new self;
    $return->open_tag = $current;
    $return->object = $current;

    $converter->file = $file;
    $element = $converter->file;
    $element->documentation = $current->open_tag->documentation;

    return $return;
  }
}

// }}}

?>
