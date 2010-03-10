<?php

/**
 * Script principale de DstyleDoc.
 * Déclare la classe de controle DstyleDoc, l'interface pour les converteurs DstyleDoc_Converter_Convert ainsi que différentes classes d'outils.
 * Ce script est la porte d'entrée de DstyleDoc et il nécessaire d'écrire un outil par dessus pour pouvoir l'utiliser correctement. Cela peut être un outil générique mais la classe DstyleDoc à été écrite pour être directement utilisé par un script léger. Ci-dessous, un exemple d'un script simple qui génere la documentation du script DstyleDoc.php et l'affiche dans le navigateur.
 * ----
 * require_once( 'DstyleDoc.php' );
 * DstyleDoc::hie()->source( 'DstyleDoc.php' )->convert_with( DstyleDoc_Converter_toString::hie() );
 * ----
 * Packages: core
 */

// {{{ properties class

/**
 * Classe de prise en charge de surcharges des membres.
 * Permet d'utiliser facilement des getter, setter, issetter et unsetter.
 * Cette classe doit être étendu.
 */
abstract class DstyleDoc_Properties
{
	/**
	 * Permet d'utiliser des getter.
	 * __get() est appelé automatiquement par PHP lors de l'accès en lecture d'un membre inexistant.
	 * __get() vérifiera au préalable que la méthode "get_"+<nom_du_membre>() existe et quelle est appelable. Dans le cas contraire, l'exception BadPropertyException sera lancé.
	 * Params:
	 *	 string $property = Le nom du membre.
	 *	 Si égale à "__class" alors retourne le nom de la classe dérivé de DstyleDoc_Properties.
	 * Returns:
	 *	 mixed = Retournera la valeur retournée par la méthode "get_"+<nom_du_membre>().
	 *	 get_class() = Le nom de la classe dérivé de DstyleDoc_Properties.
	 * Throws:
	 *	 BadPropertyException = Lancé si la méthode "get_"+<nom_du_membre>() n'est pas disponible.
	 */
	public function __get( $property )
	{
		if( $property === '__class' )
			return get_class( $this );

		elseif( ! method_exists($this,'get_'.(string)$property) or ! is_callable( array($this,'get_'.(string)$property) ) )
			throw new BadPropertyException($this, (string)$property);

		return call_user_func( array($this,'get_'.(string)$property) );
	}

	/**
	 * Permet d'utiliser des setter.
	 * __set() est appelé automatiquement par PHP lors de l'accès en écriture d'un membre inexistant.
	 * __set() vérifiera au préalable que la méthode "set_"+<nom_du_membre>() existe et quelle est appelable. Dans le cas contraire, l'exception BadPropertyException sera lancé.
	 * Params:
	 *	 string $property = Le nom du membre.
	 * Returns:
	 *	 mixed = Retournera la valeur retournée par la méthode "set_"+<nom_du_membre>().
	 * Throws:
	 *	 BadPropertyException = Lancé si la méthode "set_"+<nom_du_membre>() n'est pas disponible.
	 */
	public function __set( $property, $value )
	{
		if( ! method_exists($this,'set_'.(string)$property) or ! is_callable( array($this,'set_'.(string)$property) ) )
			throw new BadPropertyException($this, (string)$property);

		call_user_func( array($this,'set_'.(string)$property), $value );
	}

	/**
	 * Permet d'utiliser des issetter.
	 * __isset() est appelé automatiquement par PHP lors de l'accès d'existance d'un membre inexistant.
	 * __isset() vérifiera au préalable que la méthode "isset_"+<nom_du_membre>() existe et quelle est appelable. Dans le cas contraire, l'exception BadPropertyException sera lancé.
	 * Params:
	 *	 string $property = Le nom du membre.
	 * Returns:
	 *	 mixed = Retournera la valeur retournée par la méthode "isset_"+<nom_du_membre>().
	 * Throws:
	 *	 BadPropertyException = Lancé si la méthode "isset_"+<nom_du_membre>() n'est pas disponible.
	 */
	public function __isset( $property )
	{
		if( ! method_exists($this,'isset_'.(string)$property) or ! is_callable( array($this,'isset_'.(string)$property) ) )
			throw new BadPropertyException($this, (string)$property);

		return call_user_func( array($this,'isset_'.(string)$property) );
	}

	/**
	 * Permet d'utiliser des unsetter.
	 * __unset() est appelé automatiquement par PHP lors de l'accès d'effacement d'un membre inexistant.
	 * __unset() vérifiera au préalable que la méthode "unset_"+<nom_du_membre>() existe et quelle est appelable. Dans le cas contraire, l'exception BadPropertyException sera lancé.
	 * Params:
	 *	 string $property = Le nom du membre.
	 * Returns:
	 *	 mixed = Retournera la valeur retournée par la méthode "isset_"+<nom_du_membre>().
	 * Throws:
	 *	 BadPropertyException = Lancé si la méthode "unset_"+<nom_du_membre>() n'est pas disponible.
	 */
	public function __unset( $property )
	{
		if( ! method_exists($this,'unset_'.(string)$property) or ! is_callable( array($this,'unset_'.(string)$property) ) )
			throw new BadPropertyException($this, (string)$property);

		call_user_func( array($this,'unset_'.(string)$property) );
	}
}

// }}}

require_once 'process.tokens.php';
require_once 'process.elements.php';
require_once 'process.analysers.php';
require_once 'process.descriptables.php';
//require_once 'extension.state_saver.php';
//require_once 'extension.function_return.php';
//require_once 'extension.memcache.php';

/**
 * Classe de control de DstyleDoc.
 * La classe DstyleDoc permet de configurer et de lancer un processus de génération de documentation, en utilisante un syntaxe fluide.
 * ----
 * new DstyleDoc()->source( 'fichier1.php' )->source( 'fichier2.php' );
 * ----
 * Members:
 *  string $source = Fichiers source à analyser.
 *  Accès en écriture : ajoute un fichier source à analyser.
 *  Accès en lecture, isset() et unset() : refusé.
 */
class DstyleDoc extends DstyleDoc_Properties
{
	// {{{ version

	const version = 'DstyleDoc v0.1 2k8-2k9 Martin Mauchauffée';

	// }}}
	// {{{ log()

	/**
	 * Envoie un message de log sur la sortie standard.
	 * Params:
	 *  string,numeric,array ... = Une chaîne de caractère, un nombre ou un tableau de pairs clefs/valeurs a afficher.
	 * Syntax:
	 *  ... = Un nombre infinie de paramètre a afficher.
	 */
	static public function log()
	{
		$args = func_get_args();
		call_user_func( self::$_logger, $args, false );
	}

	static public function warning()
	{
		$args = func_get_args();
		call_user_func( self::$_logger, $args, true );
	}

	static private $_logger = null;

	static public function logger( $callback )
	{
		if( is_callable($callback) )
			self::$_logger = $callback;
		else
			throw new InvalidArgumentException('Invalide callbask for 1st parameter sent to: '.__FUNCTION__);
	}

	static public function default_logger( $args, $warning = false )
	{
		/*if( is_array($args) ) foreach( $args as $arg )
			echo (string)$arg."\n";*/
	}

	// }}}
	// {{{ $source_dir

	protected $_source_dir = '';

	protected function set_source_dir( $source_dir )
	{
		$this->_source_dir = (string)$source_dir;
	}

	protected function get_source_dir()
	{
		return $this->_source_dir;
	}

	// }}}
	// {{{ $sources

	/**
	 * Liste des fichiers sources à analyser.
	 * Type: array(string)
	 */
	protected $_sources = array();

	protected function set_source( $files )
	{
		if( file_exists($this->source_dir.(string)$files) and is_file($this->source_dir.(string)$files) and is_readable($this->source_dir.(string)$files) )
			$this->_sources[] = (string)$files;
		elseif( is_array($files) or $files instanceof Iterator )
			foreach( $files as $file )
				$this->source = $file;
		else
			throw new DstyleDoc_Error_Source( $files );
	}

	protected function get_sources()
	{
		return $this->_sources;
	}

	// }}}
	// {{{ analyse_all()

	protected function analyse_all( DstyleDoc_Converter $converter )
	{
		if( $this->use_temporary_sqlite_database )
		{
			DstyleDoc_State_Saver::start( $this );
		}

		foreach( $this->sources as $file )
		{
			if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'log')!==false )
				self::log( "<span style=\"color: Crimson\">Parsing file: <strong>$file</strong></span>", true );
			$this->analyse_file( $converter, $file );
		}
	}

	// }}}
	// {{{ analyse_file()

	protected function analyse_file( DstyleDoc_Converter $converter, $file )
	{
		$line = 1;
		$current = new DstyleDoc_Token_Fake;
		$doc = '';
		self::log( sprintf("Reading and parsing: %s\n",$file) );
		foreach( token_get_all(file_get_contents($this->source_dir.$file)) as $token )
		{
			if( is_array($token) )
				list( $token, $source, $line ) = $token;
			else
				list( $token, $source, $line ) = array( 0, $token, $line );

			// skip T_WHITESPACE for speed up
			if( $token === T_WHITESPACE )
				continue;

			$call = token_name($token);
			if( substr($call,0,2)==='T_' ) $call = substr($call,2);

			if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'tokens')!==false )
			{
				static $f = 0;
				$ff = (++$f%2)?'BurlyWood':'Goldenrod';
				$s = htmlentities($source); if(!trim($s))$s='&nbsp;'; $c = get_class($current);
	echo <<<HTML
<div style='clear:left;background:black;height:2px;'></div>
<div style='clear:left;float:left;color:white;background:Brown;padding:1px 3px'>{$c}</div>
<div style='float:left;background:Chocolate;color:white;padding:1px 3px'>$line</div>
<div style='float:left;background:Wheat;padding:1px 3px'>$call</div>
<div style='background:{$ff};color:SaddleBrown;padding:1px 3px;'>{$s}</div>
<div style='clear:both'></div>
HTML;
			}

			$save = $current;
			// processing token
			$current = call_user_func( array('DstyleDoc_Token_'.$call,'hie'), $converter, $current, $source, $file, $line );

			if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'tokens')!==false and ( strpos($_REQUEST['debug'],'current')!==false or strpos($_REQUEST['debug'],get_class($current))!==false ) )
				var_dump( $current );

			if( $current instanceof DstyleDoc_Token_Stop )
				break;

			if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'open_tag')!==false )
			{
				$o = $d = '';
				$c = get_class($current);
				if( ! $current instanceof DstyleDoc_Token_Stop )
				{
					$o = get_class($current->open_tag);
					if( $current->open_tag instanceof DstyleDoc_Token_Open_Tag )
						$d = strlen($current->open_tag->documentation)." ".substr($current->open_tag->documentation,0,30);
				}
				if(!trim($d))$d='&nbsp;';
				echo <<<HTML
<div style='clear:left;float:left;color:white;background:OliveDrab;padding:1px 3px'>{$c}</div>
<div style='float:left;color:white;background:DarkOliveGreen;padding:1px 3px'>{$o}</div>
<div style='background:YellowGreen;color:white;padding:1px 3px;'>{$d}</div>
<div style='clear:both'></div>
HTML;
			}

			if( ! $current instanceof DstyleDoc_Token_Custom )
				throw new UnexpectedValueException;
		}

		if( $current instanceof DstyleDoc_Token_Open_Tag or $current instanceof DstyleDoc_Token_Halt_Compiler )
			DstyleDoc_Token_Close_Tag::hie( $converter, $current, $source, $file, $line );
	}

	// }}}
	// {{{ source_dir(), source()

	public function source_dir( $source_dir )
	{
		$this->source_dir = $source_dir;
		return $this;
	}

	public function source()
	{
		$args = func_get_args();
		foreach( $args as $arg )
			$this->source = $arg;
		return $this;
	}

	// }}}
	// {{{ convert_with()

	public function convert_with( DstyleDoc_Converter $converter )
	{
		$converter->dsd = $this;
		$this->analyse_all( $converter );
		if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'hide')!==false )
			null;
		else
			$converter->convert_all();
		return $this;
	}

	// }}}
	// {{{ hie()

	static public function hie()
	{
		self::logger( array('DstyleDoc','default_logger') );
		return new self;
	}

	// }}}
	// {{{ $config

	protected $_config = array(

		'use_temporary_sqlite_database' => false,

		'database_host' => 'localhost',
		'database_user' => 'root',
		'database_pass' => '',
		'database_base' => 'dstyledoc_saved_state',

		'dstyledoc' => true,
		'version' => true,
		'history' => true,
		'params' => true,
		'params_sub' => true,
		'returns' => true,
		'package' => true,
		'throws' => true,
		'syntax' => true,
		'type' => true,
		'since' => true,
		'todo' => true,
		'member' => true,
		'licence' => true,
		'method' => true,

		'element_link' => true,
		'href_link' => true,

		'javadoc' => true,
		'javadoc_version' => true,
		'javadoc_history' => true,
		'javadoc_params' => true,
		'javadoc_returns' => true,
		'javadoc_package' => true,
		'javadoc_subpackage' => true,
		'javadoc_exception' => true,
		'javadoc_syntax' => true,
		'javadoc_var' => true,
		'javadoc_since' => true,
		'javadoc_todo' => true,
		'javadoc_member' => true,
		'javadoc_method' => true,

		'javadoc_link' => true,

		);

	// }}}
	// {{{ __get()

	public function __get( $property )
	{
		if( substr((string)$property,0,7)==='enable_')
		{
			$this->_config[ substr((string)$property,7) ] = true;
			return $this;
		}
		elseif( substr((string)$property,0,8)==='disable_')
		{
			$this->_config[ substr((string)$property,8) ] = false;
			return $this;
		}
		elseif( isset($this->_config[(string)$property]) )
			return $this->_config[(string)$property];
		elseif( substr((string)$property,0,4)!=='get_' )
			return parent::__get( (string)$property );
		else
			throw new BadPropertyException($this, substr((string)$property,4) );
	}

	// }}}
	// {{{ __call()

	public function __call( $method, $params )
	{
		if( substr($method,0,7)==='config_')
		{
			$this->_config[ substr($method,7) ] = array_shift($params);
			return $this;
		}
	 else
			return parent::__get( $property );
	}

	// }}}
}

/**
 * Interface de base pour les converteurs.
 */
interface DstyleDoc_Converter_Convert
{
	// {{{ get_file_interfaces()

	/**
	 * Renvoie la liste des interfaces appartenant a un fichier donnée.
	 * Params:
	 *		$file = L'instance d'un élément de fichier.
	 * Returns:
	 *		array(DstyleDoc_Element_Interface) = Un tableau d'interface.
	 */
	function get_file_interfaces( DstyleDoc_Element_File $file );

	// }}}
	// {{{ get_file_methods()

	/**
	 * Renvoie la liste des m�thodes appartenant � un fichier donn�e.
	 * Params:
	 *		$file = L'instance d'un �l�ment de fichier.
	 * Returns:
	 *		array(DstyleDoc_Element_Method) = Un tableau de m�thodes.
	 */
	function get_file_methods( DstyleDoc_Element_File $file );

	// }}}
	// {{{ get_file_functions()

	/**
	 * Renvoie la liste des functions appartenant à un fichier donnée.
	 * Params:
	 *		$file = L'instance d'un élément de fichier.
	 * Returns:
	 *		array(DstyleDoc_Element_Function) = Un tableau de fonctions.
	 */
	function get_file_functions( DstyleDoc_Element_File $file );

	// }}}
	// {{{ get_file_members()

	/**
	 * Renvoie la liste des membres appartenant � un fichier donn�e.
	 * Params:
	 *		$file = L'instance d'un �l�ment de fichier.
	 * Returns:
	 *		array(DstyleDoc_Element_Member) = Un tableau de membres.
	 */
	function get_file_members( DstyleDoc_Element_File $file );

	// }}}
	// {{{ convert_all()

	/**
	 * Converti tous elements.
	 */
	function convert_all();

	// }}}
	// {{{ convert_file()

	/**
	 * G�n�re la documentation d'un fichier.
	 * Params:
	 *		$file = L'instance du fichier � documenter.
	 * Returns:
	 *		mixed = La documentation du fichier ou pas.
	 */
	function convert_file( DstyleDoc_Element_File $file );

	// }}}
	// {{{ convert_class()

	/**
	 * G�n�re la documentation d'une classe.
	 * Params:
	 *		$class = L'instance de la classe � documenter.
	 * Returns:
	 *		mixed = La documentation de la classe ou pas.
	 */
	function convert_class( DstyleDoc_Element_Class $class );

	// }}}
	// {{{ convert_interface()

	/**
	 * G�n�re la documentation d'un interface.
	 * Params:
	 *		$interface = L'instance de l'interface � documenter.
	 * Returns:
	 *		mixed = La documentation de l'interface ou pas.
	 */
	function convert_interface( DstyleDoc_Element_Interface $interface );

	// }}}
	// {{{ convert_function()

	/**
	 * G�n�re la documentation d'une fonction.
	 * Params:
	 *		$function = L'instance de la fonction � documenter.
	 * Returns:
	 *		mixed = La documentation de la fonction ou pas.
	 */
	function convert_function( DstyleDoc_Element_Function $function );

	// }}}
	// {{{ convert_method()

	/**
	 * G�n�re la documentation d'une m�thode.
	 * Params:
	 *		$method = L'instance de la m�thode � documenter.
	 * Returns:
	 *		mixed = La documentation de la fonction ou pas.
	 */
	function convert_method( DstyleDoc_Element_Method $method );

	// }}}
	// {{{ convert_description()

	/**
	 * Converti la description longue.
	 * Params:
	 *		array(string) $description = Toutes les lignes de la description longue.
	 *		$element = L'élément concerné par la déscription courte.
	 * Returns:
	 *		mixed = Dépends du convertisseur.
	 */
	function convert_description( $description, DstyleDoc_Custom_Element $element );

	// }}}
	// {{{ convert_title()

	/**
	 * Convertie la d�scription courte.
	 * Params:
	 *		string $title = La ligne de description courte.
	 *		$element = L'�l�ment concern� par la d�scription courte.
	 * Returns:
	 *		mixed = D�pends du convertisseur.
	 */
	function convert_title( $title, DstyleDoc_Element $element );

	// }}}
	// {{{ convert_link()

	/**
	 * Converti et renvoie un lien vers un �l�ment.
	 * Params:
	 *		mixed $id = L'identifiant unique de l'�l�ment retourn� par convert_id().
	 *		mixed $name = Le nom d'affichage de l'�l�ment retourn� par convert_name().
	 *		$element = L'�l�ment vers lequel se destine le lien.
	 * Returns:
	 *		mixed = D�pends du convertisseur.
	 */
	function convert_link( $id, $name, DstyleDoc_Element $element );

	// }}}
	// {{{ convert_id()

	/**
	 * Converti et renvoie l'identifiant unique d'un élément.
	 * Params:
	 *		string $id = L'identifiant unique de l'élément.
	 *		array $id = Un tableau contenant la liste des identifiants de l'élément et celui de ses parents.
	 *		$element = L'élément vers lequel se destine le lien.
	 * Returns:
	 *		string = L'identifiant convertie de l'élément.
	 */
	function convert_id( $id, DstyleDoc_Element $element );

	// }}}
	// {{{ convert_display()

	/**
	 * Convertie et renvoie le nom d'affichage d'un élément.
	 * Params:
	 *		string $name = Le nom de l'élément a afficher.
	 *		$element = L'élément vers lequel se destine le lien.
	 * Returns:
	 *		mixed = Dépends du convertisseur.
	 */
	 function convert_display( $name, DstyleDoc_Custom_Element $element );

	// }}}
	// {{{ convert_syntax()

	/**
	 * Génère la documentation d'une syntaxe d'une fonction.
	 * Params:
	 *		$syntax = L'instance de la syntaxe.
	 * Returns:
	 *		mixed = La documentation de la syntaxe ou pas.
	 */
	function convert_syntax( DstyleDoc_Element_Syntax $syntax );

	// }}}
	// {{{ convert_param()

	/**
	 * Génère la documentation d'un paramètre d'une fonction.
	 * Params:
	 *		$param = L'instance du paramètre.
	 * Returns:
	 *		mixed = La documentation de la syntaxe ou pas.
	 */
	function convert_param( DstyleDoc_Element_Param $param );

	// }}}
	// {{{ convert_return()

	/**
	 * Génère la documentation d'une valeur de retour d'une fonction.
	 * Params:
	 *		$param = L'instance de la valeur de retour.
	 * Returns:
	 *		mixed = La documentation de la valeur de retour ou pas.
	 */
	function convert_return( DstyleDoc_Element_Return $param );

	// }}}
	// {{{ convert_type()

	/**
	 * Génère la documentation d'un type de valeur.
	 * Params:
	 *		$type = L'instance du type.
	 * Returns:
	 *		mixed = La documentation du type.
	 */
	function convert_type( DstyleDoc_Element_Type $type );

	// }}}
	// {{{ convert_exception()

	/**
	 * G�n�re la documentation d'un exception lanc� par une fonction.
	 * Params:
	 *		$exception = L'instance de l'exception lanc� par l'exception.
	 * Returns:
	 *		mixed = La documentation de l'exception lanc� par l'exception ou pas.
	 */
	function convert_exception( DstyleDoc_Element_Exception $exception );

	// }}}
	// {{{ convert_member()

	/**
	 * G�n�re la documentation d'un membre d'une classe.
	 * Params:
	 *		$member = L'instance du membre d'une classe.
	 * Returns:
	 *		mixed = La documentation du membre de la classe ou pas.
	 */
	function convert_member( DstyleDoc_Element_Member $member );

	// }}}
	// {{{ convert_text()

	/**
	 * Converti une portion de texte contenu dans une description.
	 * Params:
	 *		string $text = La portion de texte à convertir.
	 */
	function convert_text( $text );

	// }}}
	// {{{ convert_php()

	/**
	 * Converti du code PHP.
	 * Params:
	 *		string $code = Le cde PHP à convertir.
	 */
	function convert_php( $code );

	// }}}
	// {{{ convert_todo()

	/**
	 * Génère la documentation d'un élement de la todolist.
	 * Params:
	 *		array(string) $todo = Toutes les lignes de la description de l'élément de la todolist.
	 * Returns:
	 *		mixed = Dépends du convertisseur.
	 */
	function convert_todo( $todo );

	// }}}
	// {{{ search_element()

	/**
	 * Recherche un �l�ment � partir de sa syntax.
	 * Params:
	 *		string $string = Une syntaxe d'un membre, d'une constante, d'une fonction ou d'un classe.
	 * Returns:
	 *		DstyleDoc_Element = L'instance de l'�l�ment en cas de succ�s.
	 *		false = En cas d'�chec.
	 */
	function search_element( $string );

	// }}}
	// {{{ come_accross_elements()

	/**
	 * Recherche dans un text des �ventuels mots ou expression correspondant � des �lements existants.
	 * Fixme: delete me
	 */
	// function come_accross_elements( $string, DstyleDoc_Custom_Element $element );

	// }}}
	static function hie();
}

class DstyleDoc_Element_Container
{
	// {{{ $extention

	static protected $extension = false;

	/**
	 * Todo: passer par Reflexion
	 */
	static public function set_extension( $extension )
	{
		$this->extension = $extension;
	}

	// }}}
	// {{{ $class

	protected $class = '';

	// }}}
	// {{{ $data

	protected $data = array();

	// }}}
	// {{{ __construct()

	public function __construct( $class )
	{
		if( is_subclass_of( $class, 'DstyleDoc_Custom_Element' ) )
			$this->class = (string)$class;
		else
			throw new InvalidArgumentException(sprintf('Unexcepted (%s), excepted (%s) passed to %s::%s().',(string)$class,'DstyleDoc_Custom_Element',__CLASS__,__FUNCTION__));
	}

	// }}}
	// {{{ put()

	public function put( $data, DstyleDoc_Converter $converter, $cache = true )
	{
		if( $cache and self::$extension and current($this->data) )
			call_user_func( array(self::$extension,'put_element'), current($this->data) );

		$found = false;
		if( ! empty($data) and $converter->dsd->use_temporary_sqlite_database
			and $element = DstyleDoc_State_Saver::get_element( $this->class, $data, $converter ) )
			return $element;
		elseif( ! empty($data) and count($this->data) )
		{
			reset($this->data);
			while( true)
			{
				$current = current($this->data);
				if( $found = ( (is_object($data) and $current === $data)
					or (is_string($data) and strtolower($current->name) === strtolower($data)) ) or false === next($this->data) )
					break;
			}
		}

		if( ! $found )
		{
			if( is_object($data) and ( get_class($data) == $this->class or is_subclass_of($data, $this->class) ) )
				$this->data[] = $data;
			else
			{
				$class_name = $this->class;
				$this->data[] = new $class_name( $converter, $data );
			}
			end($this->data);
		}
	}

	// }}}
	// {{{ get()

	public function get( DstyleDoc_Converter $converter, $cache = true )
	{
		if( $cache and $converter->dsd->use_temporary_sqlite_database and current($this->data) )
			DstyleDoc_State_Saver::put_element( current($this->data) );

		if( ! count($this->data) )
		{
			throw new RuntimeException(sprintf('The container don\'t contain any entry.'));
			$class_name = $this->class;
			return new $class_name( $converter, null );
		}
		else
			return current($this->data);
	}

	// }}}
	// {{{ exists()

	/**
	 * todo: optimiser : si l'object est déjà en current($data) le retourner sans faire d'appel à la base de donnée.
	 */
	public function exists( $data, DstyleDoc_Converter $converter )
	{
		if( $converter->dsd->use_temporary_sqlite_database )
			return DstyleDoc_State_Saver::get_element( $this->class, $data, $converter );

		foreach( $this->data as $value )
		{
			if( strtolower($value->name) === strtolower((string)$data) )
				return $value;
		}
		return false;
	}

	// }}}
	// {{{ get_all()

	public function get_all( DstyleDoc_Converter $converter, $cache = true )
	{
		if( $cache and $converter->dsd->use_temporary_sqlite_database )
			return new DstyleDoc_State_Saver_Iterator( 'DstyleDoc_Element_Function', $converter );
		else
			return $this->data;
	}

	// }}}
}

/**
 * Convertisseur abstrait
 * Todo:
 *	 - reporter set_method() dans les autres methodes de ce genre.
 *   - gérer les constantes
 *   - gérer les membres
 *   - gérer la ligne suivante
 * Members:
 *	 - array(DstyleDoc_Element_Class) $classes = La listes des classes.
 *	 - DstyleDoc_Element_Class $class = Ajoute une nouvelle classe dans la liste ou retourne la dernière ajoutée.
 */
abstract class DstyleDoc_Converter extends DstyleDoc_Properties implements ArrayAccess, DstyleDoc_Converter_Convert
{
	// {{{ $dsd

	/**
	 * L'instance de DstyleDoc associé au converteur.
	 * Type: DstyleDoc
	 * Members:
	 *   (set,get) DstyleDoc $dsd = L'instance de DstyleDoc associé au converteur.
	 */
	protected $_dsd = null;

	/**
	 * Setter pour $_dsd
	 * Met à jour l'instance de DstyleDoc associé au converteur.
	 * Il est préferable d'accèder au membre $_dsd en écriture plutôt que d'appelé cette méthode.
	 * Params:
	 *   DstyleDoc = L'instance de DstyleDoc à associer au converteur.
	 */
	protected function set_dsd( DstyleDoc $dsd )
	{
		$this->_dsd = $dsd;
	}

	/**
	 * Getter pour $_dsd
	 * Retourne l'instance de DstyleDoc associé au converteur.
	 * Il est préferable d'accèder au membre $_dsd en lecture plutôt que d'appelé cette méthode.
	 * Returns:
	 *   DstyleDoc = L'instance de DstyleDoc associé au converteur.
	 */
	protected function get_dsd()
	{
		return $this->_dsd;
	}

	// }}}
	//	{{{ $constants

	protected $_constants = array();

	// }}}
	// {{{ $files

	/**
	 * La liste des fichiers analysés.
	 * Type: DstyleDoc_Element_Container
	 */
	protected $_files = null;

	protected function init_file()
	{
		if( ! $this->_files instanceof DstyleDoc_Element_Container )
			$this->_files = new DstyleDoc_Element_Container( 'DstyleDoc_Element_File' );
	}

	/**
	 * Setter pour $_file
	 */
	protected function set_file( $file )
	{
		$this->init_file();
		$this->_files->put( $file, $this );
	}

	protected function get_file()
	{
		$this->init_file();
		return $this->_files->get( $this );
	}

	protected function get_files()
	{
		$this->init_file();
		return $this->_files->get_all( $this );
	}

	// }}}
	// {{{ $classes

	protected $_classes = null;

	protected function init_class()
	{
		if( ! $this->_classes instanceof DstyleDoc_Element_Container )
			$this->_classes = new DstyleDoc_Element_Container( 'DstyleDoc_Element_Class' );
	}

	protected function set_class( $class )
	{
		$this->init_class();
		$this->_classes->put( $class, $this );
	}

	protected function get_class()
	{
		$this->init_class();
		return $this->_classes->get( $this );
	}

	protected function get_classes()
	{
		$this->init_class();
		return $this->_classes->get_all( $this );
	}

	// }}}
	// {{{ $interfaces

	protected $_interfaces = null;

	protected function init_interface()
	{
		if( ! $this->_interfaces instanceof DstyleDoc_Element_Container )
			$this->_interfaces = new DstyleDoc_Element_Container( 'DstyleDoc_Element_Interface' );
	}

	protected function set_interface( $interface )
	{
		$this->init_interface();
		$this->_interfaces->put( $interface, $this );
	}

	protected function get_interface()
	{
		$this->init_interface();
		return $this->_interfaces->get( $this );
	}

	protected function get_interfaces()
	{
		$this->init_interface();
		return $this->_interfaces->get_all( $this );
	}

	// }}}
	// {{{ $functions

	/**
	 * La listes des instances des fonctions définies.
	 * Types:
	 *		DstyleDoc_Element_Container
	 */
	protected $_functions = null;

	protected function init_function()
	{
		if( ! $this->_functions instanceof DstyleDoc_Element_Container )
			$this->_functions = new DstyleDoc_Element_Container( 'DstyleDoc_Element_Function' );
	}

	protected function set_function( $function )
	{
		$this->init_function();
		$this->_functions->put( $function, $this );
	}

	protected function get_function()
	{
		$this->init_function();
		return $this->_functions->get( $this );
	}

	protected function get_functions()
	{
		$this->init_function();
		return $this->_functions->get_all( $this );
	}

	// }}}
	// {{{ $methods

	/**
	 * fixme Devrait retourner la listes des methodes des classes, ne devrait pas avoir de membre $methods
	 */
	protected $_methods = array();

	protected function set_method( $method )
	{
		$found = false;
		if( ! empty($method) and count($this->_methods) )
		{
			reset($this->_methods);
			while( true)
			{
				$current = current($this->_methods);
				if( $found = ( (is_object($method) and $current === $method)
					or (is_string($method) and $current->name === $method) ) or false === next($this->_methods) )
					break;
			}
		}

		if( ! $found )
		{
			if( $method instanceof DstyleDoc_Element_Method )
				$this->_methods[] = $method;
			else
				$this->_methods[] = new DstyleDoc_Element_Method( $this, $method );
			end($this->_methods);
		}
	}

	protected function get_method()
	{
		if( ! count($this->_methods) )
		{
			$this->_methods[] = new DstyleDoc_Element_Method( $this, null );
			return end($this->_methods);
		}
		else
			return current($this->_methods);
	}

	protected function get_methods()
	{
		return $this->_methods;
	}

	// }}}
	// {{{ $members

	protected $_members = array();

	protected function set_member( $member )
	{
		$found = false;
		if( ! empty($member) and count($this->_members) )
		{
			reset($this->_members);
			while( true)
			{
				$current = current($this->_members);
				if( $found = ( (is_object($member) and $current === $member)
					or (is_string($member) and $current->name === $member) ) or false === next($this->_members) )
					break;
			}
		}

		if( ! $found )
		{
			if( $member instanceof DstyleDoc_Element_Member )
				$this->_members[] = $member;
			else
				$this->_members[] = new DstyleDoc_Element_Member( $this, $member );
			end($this->_members);
		}
	}

	protected function get_member()
	{
		if( ! count($this->_members) )
		{
			$this->_members[] = new DstyleDoc_Element_Member( $this, null );
			return end($this->_members);
		}
		else
			return current($this->_members);
	}

	protected function get_members()
	{
		return $this->_members;
	}

	// }}}
	// {{{ $packages

	protected $_packages = array();

	protected function set_package( $package )
	{
		$found = false;
		if( ! empty($package) and count($this->_packages) )
		{
			reset($this->_packages);
			while( true)
			{
				$current = current($this->_packages);
				if( $found = ( (is_object($package) and $current === $package)
					or (is_string($package) and $current->name === $package) ) or false === next($this->_packages) )
					break;
			}
		}

		if( ! $found )
		{
			if( $package instanceof DstyleDoc_Element_Package )
				$this->_packages[] = $package;
			else
				$this->_packages[] = new DstyleDoc_Element_Package( $this, $package );
			end($this->_packages);
		}
	}

	protected function get_package()
	{
		if( ! count($this->_packages) )
		{
			$this->_packages[] = new DstyleDoc_Element_Package( $this, null );
			return end($this->_packages);
		}
		else
			return current($this->_packages);
	}

	protected function get_packages()
	{
		return $this->_packages;
	}

	// }}}
	// {{{ file_exists()

	/**
	 * Renvoie un fichier si il existe.
	 * Cherche si un fichier a été ajouté dans la liste $_classes. Si il existe, file_exists() retournera l'instance de DstyleDoc_Element_File correspondante, sinon retournera false.
	 * Params:
	 *		string $file = Le chemin du fichier a chercher.
	 * Returns:
	 *		DstyleDoc_Element_File = L'instance du en cas de succès.
	 *		false = En cas d'échec.
	 */
	public function file_exists( $file )
	{
		$this->init_file();
		return $this->_files->exists( $file, $this );
	}

	// }}}
	// {{{ class_exists()

	/**
	 * Renvoie une classe si elle existe.
	 * Params:
	 *		string $class = Le nom de la classe à chercher.
	 * Returns:
	 *		DstyleDoc_Element_Class = L'instance de la classe en cas de succès.
	 *		false = En cas d'échèc.
	 */
	public function class_exists( $class )
	{
		$this->init_class();
		return $this->_classes->exists( $class, $this );
	}

	// }}}
	// {{{ interface_exists()

	/**
	 * Renvoie une interface si elle existe.
	 * Params:
	 *		string $interface = Le nom de la interface à chercher.
	 * Returns:
	 *		DstyleDoc_Element_Interface = L'instance de la interface en cas de succès.
	 *		false = En cas d'échec.
	 */
	public function interface_exists( $interface )
	{
		$this->init_interface();
		return $this->_interfaces->exists( $interface, $this );
	}

	// }}}
	// {{{ method_exists()

	/**
	 * Renvoie une méthode si elle existe.
	 * Params:
	 *		string $class = Le nom de la classe ou de l'interface.
	 *		DstyleDoc_Element_Class, DstyleDoc_Element_Interface $class = L'instance de la classe ou de l'interface.
	 *		string $member = Le nom de la méthode.
	 * Returns:
	 *		DstyleDoc_Element_Function = L'instance de la fonction en cas de succès.
	 *		false = En cas d'échec.
	 */
	public function method_exists( $class, $method )
	{
		$found = false;

		if( is_string($class) )
			$found = $this->class_exists($class);
		elseif( is_string($class) )
			$found = $this->interface_exists($class);

		if( $found )
			$class = $found;
		elseif( $class instanceof DstyleDoc_Element_Member or $class instanceof DstyleDoc_Element_Method )
			$class = $class->class;
		elseif( $class instanceof DstyleDoc_Element_Constant and $class->class )
			$class = $class->class;

		if( $class instanceof DstyleDoc_Element_Class or $class instanceof DstyleDoc_Element_Interface )
		{
			if( ! $class->analysed ) $class->analyse();
			foreach( $this->_methods as $value )
			{
				if( $value->class === $class and strtolower($value->name) === strtolower((string)$method) )
					return $value;
			}
		}

		return false;
	}

	// }}}
	// {{{ function_exists()

	/**
	 * Renvoie une fonction si elle existe.
	 * Params:
	 *		string $function = Le nom de la fonction.
	 * Returns:
	 *		DstyleDoc_Element_Function = L'instance de la fonction en cas de succès.
	 *		false = En cas d'échec.
	 */
	public function function_exists( $function )
	{
		$this->init_function();
		return $this->_functions->exists( $function, $this );
	}

	// }}}
	// {{{ member_exists()

	/**
	 * Renvoie un membre s'il existe.
	 * Params:
	 *		string $class = Le nom de la classe ou de l'interface.
	 *		DstyleDoc_Element_Class, DstyleDoc_Element_Interface $class = L'instance de la classe ou de l'interface.
	 *		string $member = Le nom du membre.
	 *		boolean $analyse = Indique si la documentation de la classe doit être analysé afin de trouver le membre.
	 * Returns:
	 *		DstyleDoc_Element_Member = L'instance du membre en cas de succès.
	 *		false = En cas d'échec.
	 */
	public function member_exists( $class, $member )
	{
		$found = false;

		if( is_string($class) )
			$found = $this->class_exists($class);
		elseif( is_string($class) )
			$found = $this->interface_exists($class);

		if( $found )
			$class = $found;
		elseif( $class instanceof DstyleDoc_Element_Member or $class instanceof DstyleDoc_Element_Method )
			$class = $class->class;
		elseif( $class instanceof DstyleDoc_Element_Constant and $class->class )
			$class = $class->class;

		if( substr((string)$member,0,1)==='$' )
			$member = substr((string)$member,1);

		if( $class instanceof DstyleDoc_Element_Class )
		{
			if( ! $class->analysed ) $class->analyse();
			foreach( $this->_members as $value )
				if( $value->class === $class and strtolower($value->name) === strtolower((string)$member) )
					return $value;
		}

		return false;
	}

	// }}}
	// {{{ constant_exists()

	/**
	 * Renvoie une constante si elle existe.
	 * Params:
	 *		string $class = Le nom de la classe ou de l'interface.
	 *		DstyleDoc_Element_Member, DstyleDoc_Element_Interface $class = L'instance de la classe ou de l'interface.
	 *		null $class = La constante est globale.
	 *		string $constant = Le nom de la constante.
	 * Returns:
	 *		DstyleDoc_Element_Constant = L'instance de la constance en case de succès.
	 *		false = En cas d'échec.
	 */
	public function constant_exists( $class, $constant )
	{
		$found = false;

		if( is_string($class) )
			$found = $this->class_exists($class);
		elseif( is_string($class) )
			$found = $this->interface_exists($class);

		if( $found )
			$class = $found;
		elseif( $class instanceof DstyleDoc_Element_Member or $class instanceof DstyleDoc_Element_Method )
			$class = $class->class;
		elseif( $class instanceof DstyleDoc_Element_Constant and $class->class )
			$class = $class->class;

		if( $class instanceof DstyleDoc_Element_Class )
		{
			if( ! $class->analysed ) $class->analyse();
			foreach( $this->_constants as $value )
				if( $value->class === $class and strtolower($value->name) === strtolower((string)$constant) )
					return $value;
		}
		elseif( $class === null )
		{
			foreach( $this->_constants as $value )
				if( strtolower($value->name) === strtolower((string)$constant) )
					return $value;
		}

		return false;
	}

	// }}}
	// {{{ get_file_classes()

	/**
	 * Renvoie la liste des classes appartenant à un fichier donnée.
	 * Params:
	 *		$file = L'instance d'un élément de fichier.
	 * Return:
	 *		array(DstyleDoc_Element_Class) = Un tableau de classe.
	 */
	public function get_file_classes( DstyleDoc_Element_File $file )
	{
		$classes = array();
		foreach( $this->classes as $class )
			if( $class->file === $file )
				$classes[] = $class;
		return $classes;
	}

	// }}}
	// {{{ get_file_interfaces()

	public function get_file_interfaces( DstyleDoc_Element_File $file )
	{
		$interfaces = array();
		foreach( $this->interfaces as $interface )
			if( $interface->file === $file )
				$interfaces[] = $interface;
		return $interfaces;
	}

	// }}}
	// {{{ get_file_methods()

	public function get_file_methods( DstyleDoc_Element_File $file )
	{
		$methods = array();
		foreach( $this->methods as $method )
			if( $method->file === $file )
				$methods[] = $method;
		return $methods;
	}

	// }}}
	// {{{ get_file_functions()

	public function get_file_functions( DstyleDoc_Element_File $file )
	{
		$functions = array();
		foreach( $this->functions as $function )
			if( $function->file === $file )
				$functions[] = $function;
		return $functions;
	}

	// }}}
	// {{{ get_file_members()

	public function get_file_members( DstyleDoc_Element_File $file )
	{
		$members = array();
		foreach( $this->members as $member )
			if( $member->file === $file )
				$members[] = $member;
		return $members;
	}

	// }}}
	// {{{ search_element()

	public function search_element( $string )
	{
		// un membre
		if( strpos($string, '$') and $part = preg_split('/(::|->)/', $string) and isset($part[1]) and $member = $this->member_exists( $part[0], $part[1] ) )
			return $member;

		// une methode
		elseif( substr($string,-2) == '()' and $part = preg_split('/(::|->)/', substr($string,0,-2)) and isset($part[1]) and $method = $this->method_exists( $part[0], $part[1] ) )
			return $method;

		// une fonction
		elseif( substr($string,-2) == '()' and $function = $this->function_exists( substr($string,0,-2) ) )
			return $function;

		// une classe
		elseif( $class = $this->class_exists( $string ) )
			return $class;

		// une interface
		elseif( $interface = $this->interface_exists( $string ) )
			return $interface;

		// un membre
		elseif( $part = preg_split('/(::|->)/', $string) and isset($part[1])and $member = $this->member_exists( $part[0], $part[1] ) )
			return $member;

		// une constante
		elseif( $part = preg_split('/(::|->)/', $string) and isset($part[1]) and $constant = $this->constant_exists( $part[0], $part[1] ) )
			return $constant;

		// rien
		return false;
	}

	// }}}
	// {{{ offsetExists()

	final public function offsetExists( $offset )
	{
		try
		{
			return $this->$offset ? true : true;
		}
		catch( BadPropertyException $e )
		{
			return false;
		}
	}

	// }}}
	// {{{ offsetGet()

	final public function offsetGet( $offset )
	{
		return $this->$offset;
	}

	// }}}
	// {{{ offsetSet()

	final public function offsetSet( $offset, $value )
	{
		$this->$offset = $value;
	}

	// }}}
	// {{{ offsetUnset()

	final public function offsetUnset( $offset )
	{
	}

	// }}}
}

if( ! class_exists('BadPropertyException') )
{
class BadPropertyException extends LogicException
{
	public function __construct( $class, $member )
	{
		parent::__construct( sprintf('Access denied for %s::$%s.', get_class($class), $member) );
	}
}
}

class DstyleDoc_Error_Source extends RuntimeException
{
	public function __construct( $file )
	{
		parent::__construct( sprintf('Unreadable source file: %s',(string)$file) );
	}
}

