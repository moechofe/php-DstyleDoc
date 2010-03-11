<?php

require_once( 'include.properties.php' );

/**
 * Les classes des tokens.
 * Contient les classes de correspondance avec les tokens PHP retournés par la fonction http://php.net/token_get_all. Ces classes analysent le code PHP et instancie les classes d'éléments correspondantes dérivées de Element. Ce script déclare des classe abstraites basé des groupes de token. Certain tokens génére automatiquement de la documentation (ex: TokenReturn, TokenThrow), certain ne font rien et d'autre influence les tokens analysée précédament (ex: TokenOr TokenString). Enfin une derrnère gatégorie sont les tokens qui transmettent la documentation (ex: TokenPublic, TokenAbstract).
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
 * Les tokens comme TokenThrow, TokenReturn ou TokenConst on besoin de connaître le code source des tokens qui les suivent, cette interface déclare 2 fonctions qui permettent de traiter ces codes sources.
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
 * Surement étendu par tous les tokens qui font du travail ou qui influence les tokens courants et suivant.
 * Members:
 *   string $file = Le chemin du fichier d'où proviens le token.
 *     Accès en écriture : change le chemin du fichier grâce à set_file().
 *     Accès en lecture : retourne le chemin du fichier grâce à get_file().
 *     Accès isset() et unset() : refusé.
 *   integer $line = La ligne a laquelle apparait le token.
 *     Accès en écriture : change le numéro de ligne grâce à set_line().
 *     Accès en lecture : retourne le numéro de ligne grâce à get_line().
 *     Accès isset() et unset() : refusé.
 *   TokenOpenTag $open_tag = Le token du tag PHP d'ouverture de code (<?php).
 *     Accès en écriture : change l'instance du token. Récupère l'instance du token du tag PHP à partir du token courrant graĉe à set_open_tag()
 *     Accès en lecture : retourne l'instance du token du tag PHP grâce à get_open_tag().
 *     Accès isset() et unset() : refusé.
 *   Token, string $documentation = Les lignes de la documentation du token.
 *     Accès en écriture : ajoute des lignes ou copie les lignes d'un autre token grâce à set_documentation().
 *     Accès en lecture : retourne les lignes de la documentation grâce à get_documentation().
 *     Accès isset() et unset() : refusé.
 */
abstract class Token extends CustomToken implements WorkToken
{
	// {{{ __construct()

	/**
	 * Contruit un token utile.
	 */
  protected function __construct()
  {
	}

	// }}}
  // {{{ $file

	/**
	 * Le chemin du fichier d'où proviens le token.
	 * Utiliser le membre $file pour accéder au chemin du fichier en lecture et écriture
	 * Type:
	 *   string = Le chemin du fichier.
	 */
  protected $_file = '';

	/**
	 * Setter pour le chemin du fichier d'où proviens le token.
	 * Ne pas utiliser cette méthode, utiliser le membre $file en écriture à la place.
	 * ----
	 * $token->file = __FILE__;
	 * ----
	 * Params:
	 *   string $file = Le chemin du fichier, ne doit pas être vide.
	 */
  protected function set_file( $file )
	{
		assert('(string)$file');
    $this->_file = (string)$file;
  }

	/**
	 * Getter pour le chemin du fichier d'où proviens le token.
	 * Ne pas utiliser cette méthode, utiliser le membre $file en lecture à la place.
	 * ----
	 * echo $token->file;
	 * ----
	 * Return:
	 *   string = Le chemin du fichier, ne doit pas être vide.
	 */
  protected function get_file()
	{
		assert('$this->_file');
    return $this->_file;
  }

  // }}}
  // {{{ $line

	/**
	 * Le numéro de la ligne d'où proviens le token.
	 * Utiliser le membre $line pour accéder au chemin du fichier en lecture et écriture.
	 * Type:
	 *   integer = Le numéro de ligne.
	 */
  protected $_line = 0;

	/**
	 * Setter pour le numéro de la ligne d'où proviens le token.
	 * Ne pas utiliser cette méthode, utiliser le membre $line en écriture à la place.
	 * ----
	 * $token->line = __LINE__;
	 * ----
	 */
  protected function set_line( $line )
	{
		assert('(integer)$line');
    $this->_line = (integer)$line;
  }

	/**
	 * Getter pour le numéro de la ligne d'où proviens le token.
	 * Ne pas utiliser cette méthode, utiliser le membre $line en lecture à la place.
	 * ----
	 * echo $token->line;
	 * ----
	 */
  protected function get_line()
  {
		assert('$this->_line');
    return $this->_line;
  }

  // }}}
  // {{{ $open_tag

	/**
	 * Le tag PHP d'ouverture de code (<?php).
	 * Utiliser le membre $open_tag pour accéder au chemin du fichier en lecture et écriture.
	 * Type:
	 *   TokenOpenTag = Le token du tag PHP d'ouverture de code.
	 *   null = Ne devrait pas arrivé.
	 */
  protected $_open_tag = null;

	/**
	 * Setter pour le tag PHP d'ouverture de code (<?php).
	 * Ne pas utiliser cette méthode, utiliser le membre $open_tag en écriture à la place.
	 * ----
	 * $token = new self;
	 * $token->open_tag = $current;
	 * ----
	 * Params:
	 *   CustomToken $open_tag = L'instance du token courrant.
	 *   TokenOpenTag $open_tag = L'instance du token du tag PHP.
	 */
  protected function set_open_tag( CustomToken $open_tag )
  {
    if( $open_tag instanceof TokenOpenTag )
      $this->_open_tag = $open_tag;
    elseif( $open_tag->open_tag instanceof TokenOpenTag )
      $this->_open_tag = $open_tag->open_tag;
  }

	/**
	 * Getter pour le tag PHP d'ouverture de code (<?php).
	 * Ne pas utiliser cette méthode, utiliser le membre $open_tag en lecture à la place.
	 * ----
	 * var_dump( $token->open_tag );
	 * ----
	 * Return:
	 *   TokenOpenTag = L'instance du token du tag PHP.
	 *   FakeToken = Un faux token, si $_open_tag est vide.
	 */
  protected function get_open_tag()
  {
    if( $this->_open_tag instanceof TokenOpenTag )
      return $this->_open_tag;
    elseif( $this instanceof TokenOpenTag )
      return $this;
    else
      return new FakeToken;
  }

  // }}}
  // {{{ $documentation

	/**
	 * Le documentation capturé pour le token courant.
	 * Utiliser le membre $documentation pour accéder aux lignes de la documentation en lecture et écriture.
	 * Type:
	 *   string = Les lignes de documentation du token.
	 */
  protected $_documentation = '';

	/**
	 * Setter pour les lignes de la documentation du token.
	 * Ne pas utiliser cette méthode, utiliser le membre $documentation en écriture à la place.
	 * Permet de transmettre la documentation d'un token à un autre qui ne s'en est pas servis.
	 * ----
	 * $token = new self;
	 * $token->documentation = $current;
	 * ----
	 * Params:
	 *   TokenOpenTag, TokenClass = La documenation n'est pas capturé si elle proviens d'un token de tag PHP d'ouverture de code (<?php) ou du token de l'instruction de language (class).
	 *   TokenDocComment, Token = La documentation de ce token sera transferé vers le nouveau token.
	 *   string = Une ou plusieurs lignes à ajouter à la documentation du token.
	 */
  protected function set_documentation( $documentation )
	{
		if( $documentation instanceof TokenOpenTag )
			null;
		elseif( $documentation instanceof TokenClass )
			null;
    elseif( $documentation instanceof TokenDocComment or $documentation instanceof Token )
		{
			if( $this instanceof TokenClass or $this instanceof Token_Modifier or $this instanceof Token_Variable or $this instanceof Token_Function or $this instanceof Token_Const )
				$this->set_documentation( $documentation->documentation );
			else
	      $this->open_tag->set_documentation( $documentation->documentation );
		}
    else
		{
			assert('(string)$documentation');
			if( trim((string)$documentation) )
			{
	      if( $this->_documentation )
  	      $this->_documentation .= "\n".(string)$documentation;
    	  else
					$this->_documentation = (string)$documentation;
			}
		}
  }

	/**
	 * Getter pour les lignes de documentation capturées.
	 * Ne pas utiliser cette méthode, utiliser le membre $documentation en lecture à la place.
	 * ----
	 * foreach( explode("\n",$token->documentation) as $ligne );
	 * ----
	 * Return:
	 *   string = Les lignes de documentation séparées par des retours chariots (\n);
	 */
  protected function get_documentation()
  {
    return $this->_documentation;
  }

  // }}}
  // {{{ $name

	/**
	 * Le nom associé au token.
	 * Cela peut être un nom de fonction, de classe, de variable...
	 * Utiliser le membre $name pour accéder au nom en lecture et écriture.
	 * Type:
	 *   string = Le nom associé au token.
	 */
  protected $_name = '';

	/**
	 * Setter pour le nom du token.
	 * Ne pas utiliser cette méthode, utiliser le membre $name en écriture à la place.
	 * ----
	 * $token->name = 'myFunction';
	 * ----
	 * Params:
	 *   string $name = Le nom à associer au token.
	 */
  protected function set_name( $name )
	{
		assert('(string)$name');
    $this->_name = (string)$name;
  }

	/**
	 * Getter pour le nom du token.
	 * Ne pas utiliser cette méthode, utiliser le membre $name en lecture à la place.
	 * ----
	 * echo "function: {$token->name}";
	 * ----
	 * Return:
	 *   string = Le nom associé au token.
	 */
  protected function get_name()
	{
		assert('$this->_name');
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
      or $object instanceof TokenClass
      or $object instanceof Token_Context )
      $this->_object = $object;

    elseif( $object->object instanceof Token_Interface
      or $object->object instanceof Token_Function
      or $object->object instanceof TokenClass
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

require_once( 'dev.documentation.php' );
require_once( 'dev.unittest.php' );
require_once( 'converter.php' );

Mock::generatePartial('Token','MockToken',array('hie'));

class TestToken extends UnitTestCase
{
	protected $token = null;
	function setUp() { $this->token = new MockToken; }
	function tearDown() { unset($this->token); }

	// {{{ testFile(), testLine()

	function testFile()
	{
		$this->token->file = __FILE__;
		$this->assertEqual( $this->token->file, __FILE__ );
	}

	function testLine()
	{
		$this->token->line = $l = __LINE__;
		$this->assertEqual( $this->token->line, $l );
	}

	// }}}
	// {{{ testOpenTag()

	function testOpenTag()
	{
		$this->assertIsA( $this->token->open_tag, 'FakeToken' );
		$this->fail( 'Tester avec TokenOpenTag' );
	}

	// }}}
	// {{{ testDocumentation()

	function testDocumentation()
	{
		$this->assertEqual( $this->token->documentation,'' );
		$this->token->documentation = $c = 'chocolat';
		$this->token->documentation = $p = 'petit suisse';
		$this->assertEqual( $this->token->documentation, "$c\n$p" );
		$this->fail( 'Tester avec TokenOpenTag, TokenClass, TokenDocComment, Token, TokenClass, TokenModifier, TokenVariable, TokenFunction, TokenConst' );
	}

	// }}}
	// {{{ testName()

	function testName()
	{
		$this->token->name = $f = 'frite';
		$this->assertEqual( $this->token->name, $f );
	}

	// }}}
}

