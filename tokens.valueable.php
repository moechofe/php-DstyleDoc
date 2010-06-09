<?php

require_once 'tokens.php';

// {{{ TokenExtends

class TokenExtends extends Token implements ValueableToken
{
  static function hie( Converter $converter, CustomToken $current, $source, $file, $line )
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
// {{{ TokenImplements

class TokenImplements extends Token implements ValueableToken
{
  static function hie( Converter $converter, CustomToken $current, $source, $file, $line )
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
// {{{ TokenRequireOnce

class TokenRequireOnce extends Token implements ValueableToken
{
  static function hie( Converter $converter, CustomToken $current, $source, $file, $line )
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
    if( ! $this->object instanceof FakeToken )
      return $this->object;
    else
      return $this->open_tag;
  }
}

// }}}
// {{{ Require

class TokenRequire extends TokenRequireOnce {}

// }}}
// {{{ Include Once

class TokenIncludeOnce extends TokenRequireOnce {}

// }}}
// {{{ Include

class TokenInclude extends TokenRequireOnce {}

// }}}
// {{{ Return

class TokenReturn extends Token implements ValueableToken, ExpressionableToken
{
  static function hie( Converter $converter, CustomToken $current, $source, $file, $line )
  {
    $return = new self;
    $return->open_tag = $current;
    $return->object = $current;
    $return->expression = true;

    if( $current instanceof TokenFunction )
      $ref = $current;
    elseif( $current->object instanceof TokenFunction )
      $ref = $current->object;

    $ref->return = true;

    return $return;
  }

  public function rollback( Token $current )
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
    if( $this->object instanceof TokenFunction )
      $current = $this->object;
    elseif( $this->object->object instanceof TokenFunction )
      $current = $this->object->object;

    $current->return = $value;
  }
  public function get_expression_value()
  {
    if( $this->object instanceof TokenFunction )
      $current = $this->object;
    elseif( $this->object->object instanceof TokenFunction )
      $current = $this->object->object;

    return $current->return;
  }
  public function set_value( $value )
  {
    if( $this->object instanceof TokenFunction )
      $current = $this->object;
    elseif( $this->object->object instanceof TokenFunction )
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
    if( $this->object instanceof TokenFunction )
      $current = $this->object;
    elseif( $this->object->object instanceof TokenFunction )
      $current = $this->object->object;

    $returns = array();
    foreach( $current->returns as $return )
//      if( ! preg_match( '/^\\$[_\\w]+$/', $return ) and ! preg_match( '/^(?<!::|->)[_\\w]+\\(\\)$/', $return ) and ! preg_match( '/^\\$[_\\w]+(::|->)\\$?[_\\w]+\(?\)?$/', $return ) )
        $returns[] = $return;

    $current->returns = $returns;

    if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'returns')!==false )
    {
      echo '<div style="background:#ccc">';
      var_dump( $returns );
      echo '</div>';
    }

    return $this->object;
  }
}

// }}}
// {{{ TokenThrow

class TokenThrow extends Token implements ValueableToken
{
  static function hie( Converter $converter, CustomToken $current, $source, $file, $line )
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
    if( ! $this->object instanceof FakeToken )
      $this->object->object->exception = $value;
    else
      $this->open_tag->exception = $value;
  }
  public function get_value()
  {
    if( ! $this->object instanceof FakeToken )
      return $this->object;
    else
      return $this->open_tag;
  }
}

// }}}
// {{{ TokenConst

class TokenConst extends Token implements ValueableToken
{
  static function hie( Converter $converter, CustomToken $current, $source, $file, $line )
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
// {{{ Variable

class TokenVariable extends Token implements ValueableToken, ExpressionableToken
{
  static function hie( Converter $converter, CustomToken $current, $source, $file, $line )
  {
    if( $current instanceof TokenDocComment )
    {
      if( ! $current->object instanceof FakeToken )
        return $current->object;
      else
        return $current->open_tag;
    }
    elseif( $current instanceof self )
      $return = $current;
    elseif( $current instanceof TokenTuple
      or $current instanceof TokenModifier )
    {
      $return = new self;
      $return->file = $file;
      $return->line = $line;
      $return->open_tag = $current;
      $return->object = $current;
      $return->documentation = $current;
      $return->modifier = $current;
      $return->object->var = $return;
      if( $current instanceof TokenModifier )
        $return->expression = true;
    }
    elseif( $current instanceof TokenReturn )
    {
      $current->value = $source;

      if( $current->value instanceof CustomToken )
        return $current->value;
      else
        return new FakeToken;
    }
    else
      return $current;

    $return->name = $source;

    return $return;
  }

  public function rollback( Token $current )
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
