<?php
namespace dstyledoc;

require_once 'tokens.php';
require_once 'tokens.none.php';
require_once 'tokens.element.php';
require_once 'tokens.value.php';
require_once 'tokens.valueable.php';

// {{{ TokenOpenTag

class TokenOpenTag extends Token
{
  static public function hie( Converter $converter, CustomToken $current, $source, $file, $line )
  {
    if( $current instanceof TokenCloseTag and ! $current->object instanceof FakeToken )
      return $current->object;
    elseif( $current instanceof TokenCloseTag )
      return $current->open_tag;

    $return = new self;
    $return->file = $file;
    $return->line = $line;

    return $return;
  }
}

// }}}
// {{{ TokenCloseTag

class TokenCloseTag extends Token
{
  static function hie( Converter $converter, CustomToken $current, $source, $file, $line )
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
// {{{ TokenDocComment

class TokenDocComment extends Token
{
  static public function hie( Converter $converter, CustomToken $current, $source, $file, $line )
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
// {{{ TokenMlComment

class TokenMlComment extends TokenDocComment {}

// }}}
// {{{ TokenConstantEncapsedString

class TokenConstantEncapsedString extends LightToken
{
  static function hie( Converter $converter, CustomToken $current, $source, $file, $line )
  {
    $return = $current;

    if( $current instanceof ValueableToken )
    {
      $current->value = $source;

      if( $current->value instanceof CustomToken )
        $return = $current->value;
    }

    return $return;
  }
}

// }}}
// {{{ TokenString

class TokenString extends LightToken
{
  static public function hie( Converter $converter, CustomToken $current, $source, $file, $line )
  {
    $return = $current;

    if( $current instanceof TokenDocComment )
    {
      if( ! $current->object instanceof FakeToken )
        return $current->object;
      else
        return $current->open_tag;
    }
    elseif( $current instanceof TokenTuple )
    {
      $return = TokenVariable::hie( $converter, $current, $source, $file, $line );
      $return->type = $source;
    }
    elseif( $current instanceof ValueableToken )
    {
      $current->value = $source;

      if( $current->value instanceof CustomToken )
        $return = $current->value;
    }

    return $return;
  }
}

// }}}
// {{{ TokenNumString

class TokenNumString extends TokenString {}

// }}}
// {{{ TokenStringVarname

class TokenStringVarname extends TokenString {}

// }}}
// {{{ TokenEncapsedAndWhitespace

class TokenEncapsedAndWhitespace extends TokenString {}

// }}}
// {{{ TokenStartHeredoc

class TokenStartHeredoc extends TokenString {}

// }}}
// {{{ TokenStatic

class TokenStatic extends CustomToken
{
  static function hie( Converter $converter, CustomToken $current, $source, $file, $line )
  {
    return TokenModifier::hie( $converter, $current, $source, $file, $line );
  }
}

// }}}
// {{{ TokenProtected

class TokenProtected extends CustomToken
{
  static function hie( Converter $converter, CustomToken $current, $source, $file, $line )
  {
    return TokenModifier::hie( $converter, $current, $source, $file, $line );
  }
}

// }}}
// {{{ TokenPublic

class TokenPublic extends CustomToken
{
  static function hie( Converter $converter, CustomToken $current, $source, $file, $line )
  {
    return TokenModifier::hie( $converter, $current, $source, $file, $line );
  }
}

// }}}
// {{{ TokenPrivate

class TokenPrivate extends CustomToken
{
  static function hie( Converter $converter, CustomToken $current, $source, $file, $line )
  {
    return TokenModifier::hie( $converter, $current, $source, $file, $line );
  }
}

// }}}
// {{{ TokenFinal

class TokenFinal extends CustomToken
{
  static function hie( Converter $converter, CustomToken $current, $source, $file, $line )
  {
    return TokenModifier::hie( $converter, $current, $source, $file, $line );
  }
}

// }}}
// {{{ TokenAbstract

class TokenAbstract extends CustomToken
{
  static function hie( Converter $converter, CustomToken $current, $source, $file, $line )
	{
    return TokenModifier::hie( $converter, $current, $source, $file, $line );
  }
}
// }}}
// {{{ TokenVar

class TokenVar extends TokenPublic
{
  static function hie( Converter $converter, CustomToken $current, $source, $file, $line )
  {
    return TokenModifier::hie( $converter, $current, 'public', $file, $line );
  }
}

// }}}
// {{{ TokenModifier

class TokenModifier extends Token
{
  static function hie( Converter $converter, CustomToken $current, $source, $file, $line )
  {
    if( $current instanceof TokenModifier )
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
// {{{ TokenHaltCompiler

class TokenHaltCompiler extends StopToken
{
  static function hie( Converter $converter, CustomToken $current, $source, $file, $line )
  {
		$return = new self;
		$return->open_tag = $current->open_tag;
    return $return;
  }
}

// }}}
// {{{ TokenTuple

class TokenTuple extends Token
{
  static function hie( Converter $converter, CustomToken $current, $source, $file, $line )
  {
    $return = new self;
    $return->open_tag = $current;
    $return->object = $current;

    return $return;
  }
}

// }}}
// {{{ TokenContext

class TokenContext extends Token
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
      if( $this->object instanceof TokenFunction and $this->object->object instanceof TokenClass )
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

  static function hie( Converter $converter, CustomToken $current, $source, $file, $line )
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
// {{{ TokenDollarOpenCurlyBraces

class TokenDollarOpenCurlyBraces extends TokenContext {}

// }}}
// {{{ TokenCurlyOpen

class TokenCurlyOpen extends TokenContext {}

// }}}
