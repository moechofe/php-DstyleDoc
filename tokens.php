<?php

require_once( 'include.properties.php' );

/**
 * Les classes des tokens.
 * Contient les classes de correspondance avec les tokens PHP retournés par la fonction http://php.net/token_get_all .
 * Ces classes analysent le code PHP et instancie les classes d'éléments correspondantes.
 */

/**
 * Classe de token de base.
 */
abstract class CustomToken extends Properties
{
}

/**
 * Classe de token qui ne fait rien.
 * Transmet la documentation du token courant.
 */
abstract class NoneToken extends CustomToken
{
	// {{{ hie()

	/**
	 * Instancie un token qui ne fait rien.
	 * Cette fonction, bien qu'éqale à WorkToken::hie() n'implémente pas l'interface WorkToken, car les tokens dérivés de NoneToken ne font rien.
	 */
  final static function hie( Converter $converter, CustomToken $current, $source, $file, $line )
  {
    if( $current instanceof TokenDocComment )
    {
      if( ! $current->object instanceof FakeToken )
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

	// }}}
}

/**
 * Interface de travail.
 * Cette interface doit être implémenté par les token qui font du travail.
 */
interface WorkToken
{
	// {{{ hie()

	/**
	 * Instancie un token de travail.
	 * Params:
	 *   $converter = Le convertisseur utilisé pour convertir la documentation.
	 *   $current = Le token courant.
	 *   string $source = Le code source du token.
	 *   string $file = Le chemin du fichier source.
	 *   integer $line = La ligne qui contient le code source du token.
	 * Return:
	 *   WorkToken = L'instance du nouveau token courant.	 *
	 */
	static function hie( Converter $converter, CustomToken $current, $source, $file, $line );

	// }}}
}

/**
 * Classe de token léger.
 */
abstract class LightToken extends CustomToken implements WorkToken
{
	// {{{ __construct()

	/**
	 * Contruit un token léger.
	 */
  final protected function __construct()
  {
	}

	// }}}
}

/**
 * Interface pour les tokens qui peuvent recevoir des valeures des tokens suivant.
 * Les tokens comme TokenThrow, TokenReturn ou TokenConst on besoin de connaitre le code source des tokens qui les suivent, cette interface déclare 2 fonctions qui permet de traiter ses codes sources.
 */
interface ValueableToken
{
  function set_value( $value );
  function get_value();
}

/**
 * Classe de base des tokens qui agisse sur les ValueableToken.
 * Transmet la documentation du token courant.
 * Transmet la valeure du ValueableToken.
 */
abstract class ValueToken extends LightToken
{
	// {{{

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
    elseif( $current instanceof ValueableToken )
    {
      $current->value = $source;

      if( $current->value instanceof CustomToken )
        $return = $current->value;
    }

    return $return;
	}

	// }}}
}

/**
 * Classe de faux token.
 * Le faux token sert de transition entre certain enchainement de tokens.
 */
class FakeToken extends CustomToken
{
}

/**
 * Interface pour les tokens qui se transforme en Element.
 */
interface ElementToken
{
	// {{{ to()

	/**
	 * Convertion du Token en Element.
	 * Instancie les Element correspondant au code source analysé et remplis l'object Converter avec.
	 * Params:
	 *   $converter = Le convertisseur qui sera utilisé par les Element durant la conversion.
	 */
	function to( Converter $converter );

	// }}}
}

/**
 * Classe de token utile.
 * Surement étendu par tous les token qui font du travail ou qui influance les tokens courant et suivant.
 */
abstract class Token extends CustomToken implements WorkToken
{
	// {{{

	/**
	 * Contruit un token utile.
	 */
  final protected function __construct()
  {
	}

	// }}}
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

  protected function set_open_tag( CustomToken $open_tag )
  {
    if( $open_tag instanceof Token_Open_Tag )
      $this->_open_tag = $open_tag;
    elseif( $open_tag->open_tag instanceof Token_Open_Tag )
      $this->_open_tag = $open_tag->open_tag;
  }

  protected function get_open_tag()
  {
    if( $this->_open_tag instanceof Token_Open_Tag )
      return $this->_open_tag;
    elseif( $this instanceof Token_Open_Tag )
      return $this;
    else
      return new FakeToken;
  }

  // }}}
  // {{{ $documentation

  protected $_documentation = '';

  protected function set_documentation( $documentation )
	{
		if( $documentation instanceof Token_Open_Tag )
			null;
		elseif( $documentation instanceof Token_Class )
			null;
    elseif( $documentation instanceof TokenDocComment or $documentation instanceof Token )
		{
			//var_dump( get_class($this) );
			if( $this instanceof Token_Class or $this instanceof Token_Modifier or $this instanceof Token_Variable or $this instanceof Token_Function or $this instanceof Token_Const )
				$this->set_documentation( $documentation->documentation );
			else
	      $this->open_tag->set_documentation( $documentation->documentation );
		}
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
    'public' => true,
    'protected' => false,
    'private' => false );

  protected function set_modifier( $modifier )
	{
    if( $modifier instanceof CustomToken )
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

  protected function set_method( CustomToken $method )
  {
    if( $method instanceof Token_Function )
      $this->_methods[] = $method;
  }

  protected function get_methods()
  {
    return $this->_methods;
  }

  // }}}
  // {{{ $vars

  protected $_vars = array();

  protected function set_var( CustomToken $var )
  {
    if( $var instanceof Token_Variable )
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

  protected function set_object( CustomToken $object )
  {
    if( $object instanceof Token_Interface
      or $object instanceof Token_Function
      or $object instanceof Token_Class
      or $object instanceof Token_Context )
      $this->_object = $object;

    elseif( $object->object instanceof Token_Interface
      or $object->object instanceof Token_Function
      or $object->object instanceof Token_Class
      or $object->object instanceof Token_Context )
      $this->_object = $object->object;
  }

  protected function get_object()
  {
    if( $this->_object instanceof CustomToken )
      return $this->_object;
    else
      return new FakeToken;
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

  protected function set_const( CustomToken $const )
  {
    if( $const instanceof Token_Const )
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
      $this->_expression = new Token_Expression;
  }

  protected function get_expression()
  {
    return $this->_expression;
  }

  // }}}
}

/**
 * Classe de token qui stope l'analyse.
 * Si ce token est instancié, l'analyse du code s'arrète. Normalement, cela survient avec la fonction http://php.net/halt-compiler ou le tag de fermeture de PHP "?>"
 */
abstract class StopToken extends Token
{
}

