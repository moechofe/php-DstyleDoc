<?php

require_once( 'xdebug-frontend.php' );

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
 *   integer $line = La ligne a laquelle apparait le token.
 *  	 Accès en écriture : change le numéro de ligne grâce à set_line().
 *  	 Accès en lecture : retourne le numéro de ligne grâce à get_line().
 *  	 Accès isset() et unset() : refusé.
 *   TokenOpenTag $open_tag = Le token du tag PHP d'ouverture de code (<?php).
 *  	 Accès en écriture : change l'instance du token. Récupère l'instance du token du tag PHP à partir du token courrant graĉe à set_open_tag()
 *  	 Accès en lecture : retourne l'instance du token du tag PHP grâce à get_open_tag().
 *  	 Accès isset() et unset() : refusé.
 *   Token, string $documentation = Les lignes de la documentation du token.
 *  	 Accès en écriture : ajoute des lignes ou copie les lignes d'un autre token grâce à set_documentation().
 *  	 Accès en lecture : retourne les lignes de la documentation grâce à get_documentation().
 *  	 Accès isset() et unset() : refusé.
 *   string $name = Le nom associé au token.
 *  	 Accès en écriture : change le nom du token grâce à set_name().
 *  	 Accès en lecteur : retourne le nom du token grâce à get_name().
 *  	 Accès isset() et unset() : refusé.
 *   string $modifier = Ajoute un modificateur au token
 *  	 Accès en écriture : ajoute un modificateurs de porté ou attribut grâce à set_modifier().
 *  	 Accès lecture, isset() et unset() : refusé.
 *   array $modifiers = Change ou retourne la liste des modificateurs du token.
 *  	 boolean "static" = Indique si la méthode est statique.
 *  	 boolean "abstract" = Indique si la classe ou la méthode est abstraite.
 *  	 boolean "final" = Indique si la classe ou la méthode est finale.
 *  	 boolean "public" = Indique si la méthode est publique.
 *  	 boolean "protected" = Indique si la méthode est protégée.
 *  	 boolean "private" = Indique si la méthode est privée.
 *  	 Accès en écriture : change les modificateurs du token grâce à set_modifiers().
 *  	 Accès en lecture : retourne les modificateurs du token grâce à get_modifiers().
 *  	 Accès isset() et unset() : refusé.
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
	 * Le chemin du fichier
	 * Le chemin du fichier d'où provient le token.
	 * Utiliser le membre $file pour accéder au chemin du fichier en lecture et écriture
	 * Type:
	 *   string = Le chemin du fichier.
	 * Members:
	 *   string $file = Le chemin du fichier d'où provient le token.
	 *  	 Accès en écriture : change le chemin du fichier grâce à set_file().
	 *  	 Accès en lecture : retourne le chemin du fichier grâce à get_file().
	 *  	 Accès isset() et unset() : refusé.
	 */
	protected $_file = '';

	/**
	 * Setter pour le chemin du fichier
	 * Setter pour le chemin du fichier d'où provient le token $_file.
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
	 * Getter pour le chemin du fichier
	 * Getter pour le chemin du fichier d'où provient le token $_file.
	 * Ne pas utiliser cette méthode, utiliser le membre $file en lecture à la place.
	 * ----
	 * echo $token->file;
	 * ----
	 */
	protected function get_file()
	{
		assert('$this->_file');
		return $this->_file;
	}

	// }}}
	// {{{ $line

	/**
	 * Le numéro de la ligne
	 * Le numéro de la ligne d'où provient le token.
	 * Utiliser le membre $line pour accéder au chemin du fichier en lecture et écriture.
	 * Type:
	 *   integer = Le numéro de ligne.
	 */
	protected $_line = 0;

	/**
	 * Setter pour le numéro de la ligne
	 * Setter pour le numéro de la ligne d'où provient le token $_line.
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
	 * Getter pour le numéro de la ligne
	 * Getter pour le numéro de la ligne d'où provient le token $_line.
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
	 * Le tag PHP d'ouverture
	 * Le tag PHP d'ouverture de code (<?php).
	 * Utiliser le membre $open_tag pour accéder au chemin du fichier en lecture et écriture.
	 * Type:
	 *   TokenOpenTag = Le token du tag PHP d'ouverture de code.
	 *   null = Ne devrait pas arrivé.
	 */
	protected $_open_tag = null;

	/**
	 * Setter pour le tag PHP d'ouverture
	 * Setter pour le tag PHP d'ouverture de code (<?php) $_open_tag.
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
	 * Getter pour le tag PHP d'ouverture
	 * Getter pour le tag PHP d'ouverture de code (<?php) $_open_tag.
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
	 * Le documentation
	 * Le documentation capturé pour le token courant.
	 * Utiliser le membre $documentation pour accéder aux lignes de la documentation en lecture et écriture.
	 * Type:
	 *   string = Les lignes de documentation du token.
	 */
	protected $_documentation = '';

	/**
	 * Setter pour la documentation
	 * Setter pour les lignes de la documentation du token $_documentation.
	 * Ne pas utiliser cette méthode, utiliser le membre $documentation en écriture à la place.
	 * Permet de transmettre la documentation d'un token à un autre qui ne s'en est pas servis.
	 * ----
	 * $token = new self;
	 * $token->documentation = $current;
	 * ----
	 * Params:
	 *   TokenOpenTag, TokenClass $documentation = La documenation n'est pas capturé si elle provient d'un token de tag PHP d'ouverture de code (<?php) ou du token de l'instruction de language (class).
	 *   TokenDocComment, Token $documentation = La documentation de ce token sera transferé vers le nouveau token.
	 *   string $documentation = Une ou plusieurs lignes à ajouter à la documentation du token.
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
	 * Getter pour la documentation
	 * Getter pour les lignes de documentation capturées $_documentation.
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
	 * Le nom
	 * Le nom associé au token.
	 * Cela peut être un nom de fonction, de classe, de variable...
	 * Utiliser le membre $name pour accéder au nom en lecture et écriture.
	 * Type:
	 *   string = Le nom associé au token.
	 */
	protected $_name = '';

	/**
	 * Setter pour le nom
	 * Setter pour le nom du token $_name.
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
	 * Getter pour le nom
	 * Getter pour le nom du token $_name.
	 * Ne pas utiliser cette méthode, utiliser le membre $name en lecture à la place.
	 * ----
	 * echo "function: {$token->name}";
	 * ----
	 */
	protected function get_name()
	{
		assert('$this->_name');
		return $this->_name;
	}

	// }}}
	// {{{ $modifiers

	/**
	 * Modificateurs
	 * Liste des modificateurs de porté et autre attributs.
	 * Utiliser le membre $modifier pour accéder au modificateurs en lecture et écriture.
	 * Type:
	 *   array = La liste des modificateurs autorisés tous tokens concernés confondus (TokenClass, TokenFunction)
	 *  	 boolean "static" = Indique si la méthode est statique.
	 *  	 boolean "abstract" = Indique si la classe ou la méthode est abstraite.
	 *  	 boolean "final" = Indique si la classe ou la méthode est finale.
	 *  	 boolean "public" = Indique si la méthode est publique.
	 *  	 boolean "protected" = Indique si la méthode est protégée.
	 *  	 boolean "private" = Indique si la méthode est privée.
	 */
	protected $_modifiers = array(
		'static' => false,
		'abstract' => false,
		'final' => false,
		'public' => false,
		'protected' => false,
		'private' => false );

	/**
	 * Setter pour les modificateurs
	 * Setter pour les modificateurs de porté et autre attributs $_modifiers.
	 * Ajoute un modificateur au token.
	 * Ne pas utiliser cette méthode, utiliser le membre $modifier en écriture à la place.
	 * ----
	 * $token->modifier = 'public'; // Ajoute le modificateur de porté publique.
	 * ----
	 * Params:
	 *   string $modifier = Le nom du modifier à ajouter
	 */
	protected function set_modifier( $modifier )
	{
		if( $modifier instanceof CustomToken )
			$this->modifiers = $modifier->modifiers;
		elseif( is_string($modifier) and isset($this->_modifiers[$modifier]) )
			$this->_modifiers[$modifier] = true;
	}

	/**
	 * Setter pour les modificateurs
	 * Setter pour les modificateurs de porté et autre attributs $_modifiers.
	 * Change les modificateurs du token.
	 * Ne pas utiliser cette méthode, utiliser le membre $modifiers en écriture à la place.
	 * ----
	 * $token->modifiers = array( 'public', 'static' ); // Indique que le token est publique et statique
	 * ----
	 * Params:
	 *   array = La liste des modificateurs autorisés tous tokens concernés confondus (TokenClass, TokenFunction)
	 *  	 boolean "static" = Indique si la méthode est statique.
	 *  	 boolean "abstract" = Indique si la classe ou la méthode est abstraite.
	 *  	 boolean "final" = Indique si la classe ou la méthode est finale.
	 *  	 boolean "public" = Indique si la méthode est publique.
	 *  	 boolean "protected" = Indique si la méthode est protégée.
	 *  	 boolean "private" = Indique si la méthode est privée.
	 */
	protected function set_modifiers( $modifiers )
	{
		foreach( (array)$modifiers as $modifier => $true )
			if( $true and isset($this->_modifiers[$modifier]) )
				$this->_modifiers[$modifier] = true;
			elseif( isset($this->_modifiers[$modifier]) )
				$this->_modifiers[$modifier] = false;
	}

	/**
	 * Getter pour les modificateurs
	 * Getter pour les modificateurs de porté et autre attibuts $_modifiers.
	 * Ne pas utiliser cette méthode, utiliser le membre $modifiers en lecture à la place.
	 * ----
	 * foreach( $token->modifiers as $modifer );
	 * ----
	 */
	protected function get_modifiers()
	{
		return $this->_modifiers;
	}

	// }}}
	// {{{ $methods

	/**
	 * Fonctions
	 * La liste des tokens de fonctions du token de classe.
	 * Utiliser les membres $method et $methodes pour accéder au modificateurs en lecture et écriture.
	 * Type:
	 *   array(TokenFunction) = La liste des tokens de fonction.
	 */
	protected $_methods = array();

	/**
	 * Setter pour les fonctions
	 * Setter pour la liste des fonctions du token $_methods.
	 * Ne pas utiliser cette méthode, utiliser le membre $method en écriture à la place.
	 * ----
	 * $token->method = TokenFunction::hie( ... );
	 * ----
	 * Params:
	 *   TokenFunction $method = Ajout du token de fonction dans la liste.
	 *   CustomToken $method = Pas d'ajout.
	 */
	protected function set_method( CustomToken $method )
	{
		if( $method instanceof TokenFunction )
			$this->_methods[] = $method;
	}

	/**
	 * Getter pour les fonctions
	 * Getter pour la liste des	fonctions du token $_methods.
	 * Ne pas utiliser cette méthode, utiliser le membre $methods en lecture à la place.
	 * ----
	 * foreach( $token->methods as $method );
	 * ----
	 */
	protected function get_methods()
	{
		return $this->_methods;
	}

	// }}}
	// {{{ $vars

	/**
	 * Variables
	 * Liste des tokens de variable.
	 * Utiliser le membre $vars pour accéder à la liste des variable en lecture et écriture.
	 * Type:
	 *   array(TokenVariable) = La liste des variables.
	 */
	protected $_vars = array();

	/**
	 * Setter pour les variables
	 * Setter pour la liste des tokens de variable $_vars.
	 * Ne pas utiliser cette méthode, utiliser le membre $var en écriture à la place.
	 * ----
	 * $token->var = TokenVariable::hie( ... )
	 * ----
	 * Params:
	 *   TokenVariable $var = Ajoute le token de variable dans la liste.
	 *   CustomToken $var = Ne fait rien.
	 */
	protected function set_var( CustomToken $var )
	{
		if( $var instanceof TokenVariable )
			$this->_vars[] = $var;
	}

	/**
	 * Getter pour les variables
	 * Getter pour la liste des tokens de variabl $_vars.
	 * Ne pas utiliser cette méthode, utiliser le membre $vars en lecture à la place.
	 * ----
	 * foreach( $token->vars as $var );
	 * ----
	 */
	protected function get_vars()
	{
		return $this->_vars;
	}

	// }}}
	// {{{ $types

	/**
	 * Types
	 * Liste des types de variable.
	 * Utiliser les membres $type et $types pour accéder à la liste des types de variable en lecture et écriture.
	 * Type:
	 *   array(string) = La liste des types de variable.
	 */
	protected $_types = array();

	/**
	 * Setter pour les types
	 * Setter pour les types de variables $_types.
	 * Ne pas utiliser cette méthode, utiliser le membre $type en écriture à la place.
	 * Ajoute un type dans la listes seulement si celui çi n'est pas déjà présent.
	 * ----
	 * $token->type = 'string'; // Ajoute le type "string" dans la liste.
	 * ----
	 * Params:
	 *   string $type = Ajoute un type à la liste.
	 */
	protected function set_type( $type )
	{
		assert('(string)$type');
		if( ! array_search( (string)$type, $this->_types ) )
			$this->_types[] = (string)$type;
	}

	/**
	 * Getter pour les types
	 * Getter pour les types de variables $_types.
	 * Ne pas utiliser cette méthode, utiliser le membre $types en lecture à la place.
	 * ----
	 * foreach( $token->types as $type );
	 * ----
	 */
	protected function get_types()
	{
		return $this->_types;
	}

	// }}}
	// {{{ $returns

	/**
	 * Valeur de retour
	 * Liste des valeurs de retour d'une fonction.
	 * Utiliser les membres $return et $returns pour accéder à la liste des types de variable en lecture et écriture.
	 * Type:
	 *   array(string) = La liste des valeurs de retour.
	 */
	protected $_returns = array();

	/**
	 * Setter pour les valeurs de retour
	 * Setter pour la liste des valeurs de retour de fonction $_returns.
	 * Ne pas utiliser cette méthode, utiliser le membre $return en écriture à la place.
	 * Utiliser avec une chaine de caractère : modifie le type de la valeur de retour.
	 * Utiliser avec TRUE : ajoute un nouveau type de valeur de retour.
	 * ----
	 * $token->return = 'integer'; // Ajoute le type de valeur de retour "integer"
	 * $token->return = 'string'; // Change le type "integer" en "string"
	 * $token->return = true; // Ajoute un nouveau type vide
	 * $token->return = 'array'; // Change le type vide en "array"
	 * echo $token->returns[0]; // Affichera "string"
	 * ehoc $token->returns[1]; // Affichera "array"
	 * ----
	 * Params:
	 *   string $return = Le type de valeur à ajouter à la liste ou en remplacement du précédent type de valeur.
	 *   true $return = Ajoute un nouveau type de valeur vide à la liste.
	 */
	protected function set_return( $return )
	{
		if( $return === true )
		{
			if( $this->get_return() !== '' )
				$this->_returns[] = '';
		}
		else
		{
			assert('(string)$return');
			$this->_returns[ max(0,count($this->_returns)-1) ] = (string)$return;
		}
	}

	/**
	 * Getter pour les valeurs de retour
	 * Getter pour la liste des valeurs de retour de fonction $_returns.
	 * Retourne le dernier type de valeur de retour ajouté.
	 * Ne pas utiliser cette méthode, utiliser le membre $return en lecture à la place.
	 * ----
	 * $token->return = 'resource';
	 * echo $token->return; // Affichera "resource"
	 * ----
	 * Return:
	 *   string = Le dernier type de valeur de retour ajouté.
	 *   false = Aucun type de valeur de retour ajouté.
	 */
	protected function get_return()
	{
		return end($this->_returns);
	}

	/**
	 * Getter pour les valeurs de retour
	 * Getter pour la liste des valeurs de retour de fonction $_returns.
	 * Ne pas utiliser cette méthode, utiliser le membre $returns en lecture à la place.
	 * ----
	 * foreach( $token->returns as $return );
	 * ----
	 * Return:
	 *   	array(string) = La liste des types de valeur de retour de fonction.
	 */
	protected function get_returns()
	{
		if( $this->get_return() === '' )
			unset( $this->_returns[ count($this->_returns)-1 ] );

		return array_unique($this->_returns);
	}

	/**
	 * Setter pour les valeurs de retour
	 * Setter pour la liste des valeurs de retour de fonction $_returns.
	 * Ne pas utiliser cette méthode, utiliser le membre $returns en lecture à la place.
	 * ----
	 * $token->returns = array('string','integer');
	 * ----
	 * Fixme: Cette méthode est plus restrictive que les autres : set_returns() interdit les array( $object->toString() )
	 */
	protected function set_returns( $returns )
	{
		assert('(array)$returns');
		$this->_returns = array_filter( (array)$returns, 'is_string' );
	}

	// }}}
	// {{{ $default

	/**
	 * Valeur par défaut
	 * Valeur par défaut pour un paramètre de fonction.
	 * Utiliser le membre $default pour accéder à la liste des variable en lecture et écriture.
	 * Type:
	 *   string = La valeur par défault définie dans le code source.
	 */
	protected $_default = '';

	/**
	 * Setter pour la valeur par défaut
	 * Setter pour la valeur par défaut d'un paramètre de fonction $_default.
	 * Ne pas utiliser cette méthode, utiliser le membre $default en écriture à la place.
	 * ----
	 * $token->default = 'null';
	 * ----
	 * Params:
	 *   string = La valeur par défault du paramètre de fonction.
	 */
	protected function set_default( $default )
	{
		assert('(string)$default');
		$this->_default = (string)$default;
	}

	/**
	 * Getter pour la valeur par défaut
	 * Getter pour la valeur par défaut d'un paramètre de fonction $_default.
	 * Ne pas utiliser cette méthode, utiliser le membre $default en lecture à la place.
	 * ----
	 * echo $token->default;
	 * ----
	 */
	protected function get_default()
	{
		return $this->_default;
	}

	// }}}
	// {{{ $object

	/**
	 * Objet parent
	 * L'instance du token de référence.
	 * Utiliser le membre $object pour accéder à la liste des variable en lecture et écriture.
	 * Type:
	 *   CustomToken = L'instance du token de référence.
	 *   null = Ne devrait pas arrivé.
	 */
	protected $_object = null;

	/**
	 * Setter pour l'objet parent.
	 * Setter pour l'instance du token de référence.
	 * Ne pas utiliser cette méthode, utiliser le membre $object en écriture à la place.
	 * ----
	 * $token->object = TokenClass::hie( ... );
	 * ----
	 * Params:
	 *   TokenInterface, TokenFunction, TokenClass, TokenContext $object = Ce token deviens le référent.
	 *   CustomToken $object = Aucun changement.
	 */
	protected function set_object( CustomToken $object )
	{
		if( $object instanceof TokenInterface
			or $object instanceof TokenFunction
			or $object instanceof TokenClass
			or $object instanceof TokenContext )
			$this->_object = $object;

		elseif( $object->object instanceof TokenInterface
			or $object->object instanceof TokenFunction
			or $object->object instanceof TokenClass
			or $object->object instanceof TokenContext )
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
	// {{{ testDocumentation(), testTypes(), testReturns()

	function testDocumentation()
	{
		$this->assertEqual( $this->token->documentation, '' );
		$this->token->documentation = $c = 'chocolat';
		$this->token->documentation = $p = 'petit suisse';
		$this->assertEqual( $this->token->documentation, "$c\n$p" );
		$this->fail( 'Tester avec TokenOpenTag, TokenClass, TokenDocComment, Token, TokenClass, TokenModifier, TokenVariable, TokenFunction, TokenConst' );
	}

	function testTypes()
	{
		$this->assertEqual( $this->token->types, array() );
		$this->token->type = $t = 'tako yaki';
		$this->token->type = $c = 'côte de boeuf';
		$this->assertEqual( $this->token->types, array($t,$c) );
	}

	function testReturns()
	{
		$this->assertEqual( $this->token->returns, array() );
		$this->assertFalse( $this->token->return );
		$this->token->return = 'choux fleur';
		$this->token->return = $b = 'brocoli';
		$this->assertEqual( $this->token->return, $b );
		$this->token->return = true;
		$this->token->return = $c = 'carote';
		$this->assertEqual( $this->token->return, $c );
		$this->assertEqual( $this->token->returns, array($b,$c) );
		$this->token->returns = $e = array('épinards');
		$this->assertEqual( $this->token->returns, $e );
	}

	// }}}
	// {{{ testName(), testDefault()

	function testName()
	{
		$this->token->name = $f = 'frite';
		$this->assertEqual( $this->token->name, $f );
	}

	function testDefault()
	{
		$this->token->default = $c = 'crabe';
		$this->assertEqual( $this->token->default, $c );
	}

	// }}}
	// {{{ testModifiersAppend(), testModifiersSet()

	function testModifiersAppend()
	{
		$keys = array('static','abstract','final','public','protected','private');
		$values = array();
		$this->assertEqual( $this->token->modifiers, array_combine($keys,array_pad($values,count($keys),false)) );
		foreach( $keys as $key )
		{
			$this->token->modifier = $key;
			array_push($values,true);
			$this->assertEqual( $this->token->modifiers, array_combine($keys,array_pad($values,count($keys),false)) );
		}
	}

	function testModifiersSet()
	{
		$ref = array('static','abstract','final','public','protected','private');
		$keys = array('static','abstract','final','public','protected','private');
		while( $keys )
		{
			$key = array_shift($keys);
			$this->token->modifiers = array_merge(array_combine($ref,array_pad(array(),count($ref),false)),$tmp=array_combine($ref,array_pad(array_pad(array(),count($ref)-count($keys),false),count($ref),true)));
			$this->assertEqual( $this->token->modifiers, $tmp );
		}
	}

	// }}}
	// {{{ testVars()

	function testVars()
	{
		$this->assertEqual( $this->token->vars, array() );
		$this->fail( 'Tester avec TokenVariable' );
	}

	// }}}
}

