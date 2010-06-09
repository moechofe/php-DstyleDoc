<?php

require_once 'tokens.php';

/**
 * Les classes des tokens qui deviennent des élements.
 * Contient les classes des tokens qui instancie des instances des classes Element.
 */

// {{{ TokenInterface

class TokenInterface extends Token implements ValueableToken, ElementToken
{
  static public function hie( Converter $converter, CustomToken $current, $source, $file, $line )
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

  public function to( Converter $converter )
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
// {{{ TokenFunction

class TokenFunction extends Token implements ValueableToken, ElementToken
{
  static function hie( Converter $converter, CustomToken $current, $source, $file, $line )
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

    if( ! $return->object instanceof FakeToken )
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

  public function to( Converter $converter )
  {
    if( $this->object instanceof FakeToken )
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

        if( ! is_null($var->default) )
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
// {{{ TokenFunction

/**
 * Todo: test this
 */
class TokenOldFunction extends TokenFunction {}

// }}}
// {{{ TokenOpenTagWithEcho

/**
 * Todo: test this
 */
class TokenOpenTagWithEcho extends TokenFunction {}

// }}}
// {{{ Paamayim Nekudotayim

/**
 * Todo: TokenPaamayimNekudotayim
 */
class TokenPaamayimNekudotayim extends TokenFunction {}

// }}}
// {{{ TokenClass

class TokenClass extends Token implements ValueableToken, ElementToken
{
  static function hie( Converter $converter, CustomToken $current, $source, $file, $line )
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

  public function to( Converter $converter )
  {
    $converter->class = $this->name;
    $class = $converter->class;

    $class->file = $this->file;
    $class->line = $this->line;
    $class->documentation = $this->documentation;
		$class->parent = $this->extend;
		$class->abstract = $this->modifiers['abstract'];
		$class->final = $this->modifiers['final'];

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
