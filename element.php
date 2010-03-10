<?php

require_once( 'include.properties.php' );
require_once( 'converter.php' );

/**
 * Classe de base d'un Element.
 * Déclare les informations communes à chaque type d"élements tel que la description et le liens vers le convertisseur.
 * Chaque type d'élement contient des données propre qui utilise le convertisseur pour déterminé comment la documentation doit être génerée.
 */
abstract class CustomElement extends Properties implements ArrayAccess
{
	// {{{ $converter

	/**
	 * L'instance du convertisseur.
	 * Utilisez les setter et getter du membre $converter pour accéder a l'instance du convertisseur.
	 * Type:
	 *   Converter
	 */
	protected $_converter = null;

	/**
	 * Setter pour l'instance du convertisseur.
	 * Ne pas utiliser cette méthode, utiliser le membre $converter en écriture à la place.
	 * ----
	 * $element->converter = new Converter;
	 * ----
	 * Params:
	 *   $converter = L'instance du convertisseur qui sera utilisé pour la génération de la documentation.
	 */
	protected function set_converter( Converter $converter )
	{
		$this->_converter = $converter;
	}

	/**
	 * Getter pour l'instance du convertisseur.
	 * Ne pas utiliser cette méthode, utiliser le membre $converter en lecture à la place.
	 * ----
	 * var_dump( $element->converter );
	 * ----
	 * Return:
	 *   Converter = L'instance du convertisseur utilisé pour la génération de la documentation.
	 */
	protected function get_converter()
	{
		assert( '$this->_converter instanceof Converter' );
		return $this->_converter;
	}

	// }}}
	// {{{ $descriptions

	protected $_descriptions = array();

	protected function get_descriptions()
	{
		return $this->_descriptions;
	}

	protected function set_descriptions( $descriptions )
	{
		$this->_descriptions = (array)$descriptions;
	}

	/**
	 * Todo:
	 *	 apparement on passe toujours des strings.
	 *	 FAUX
	 *	 on passe par un DstyleDoc_Descritable
	 */
	protected function set_description( $description )
	{
		//d( gettype( $description ) );
		/*if( $description instanceof DstyleDoc_Descritable )
			$this->_descriptions = array_merge( $this->_descriptions, $description->descriptions );
		else*/
//			$this->_descriptions[] = (string)$description;
			$this->_descriptions[] = $description;
	}

	protected function get_description()
	{
		return $this->converter->convert_description( $this->_descriptions, $this );
	}

	protected function isset_description()
	{
		return (boolean)$this->_descriptions;
	}

	protected function unset_description()
	{
		$this->_descriptions = array();
	}

	// }}}
	// {{{ $display

	/**
	 * Renvoie la version affichable du nom de l'élément.
	 * Returns:
	 *		mixed = Dépends du convertisseur.
	 */
	abstract protected function get_display();

	// }}}
	// {{{ __get()

	public function __get( $property )
	{
		if( substr((string)$property,0,2)=='is' )
			return substr((string)$property,2)==substr(get_class($this),2-strlen((string)$property));
		else
			return parent::__get( $property );
	}

	// }}}
	// {{{ __construct()

	public function __construct( Converter $converter )
	{
		$this->converter = $converter;
	}

	// }}}
	// {{{ __sleep()

	public function __sleep()
	{
		return array(
			'_descriptions',
		);
	}

	// }}}
	// {{{ __wakeup()

	public function __wakeup()
	{
		return $this->__sleep();
	}

	// }}}
	// {{{ __toString()

	final public function __toString()
	{
		try
		{
			$return = $this->get_convert();
			if( is_string($return) )
				return $return;
			else
				return get_class($this).' '.$this->get_display();
		}
		catch( Exception $e )
		{
			return get_class($this).' '.(string)$e;
		}
	}

	// }}}
	// {{{ $convert

	/**
	 * Renvoie la documentation de l'élément.
	 * Returns:
	 *		mixed = La documentation de l'élément ou pas.
	 */
	abstract protected function get_convert();

	// }}}
	// {{{ offsetExists(), offsetGet(), offsetSet(), offsetUnset()

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

	final public function offsetGet( $offset )
	{
		return $this->$offset;
	}

	final public function offsetSet( $offset, $value )
	{
		$this->$offset = $value;
	}

	final public function offsetUnset( $offset )
	{
	}

	// }}}
	// {{{ put()

	final static public function put( DstyleDoc_Custom_Element $element )
	{
		if( $element->converter->dsd->use_temporary_sqlite_database )
		{
			return $element->converter->dsd->put_element( $element );
		}
		else
			return false;
	}

	// }}}
}

abstract class AnalyseableElement extends CustomElement
{
	// {{{ $analysed

	protected $_analysed = false;

	protected function set_analysed( $analysed )
	{
		$this->_analysed = (boolean)$analysed;
	}

	protected function get_analysed()
	{
		return $this->_analysed;
	}

	// }}}
	// {{{ $description

	protected function get_description()
	{
		$this->analyse();
		return $this->converter->convert_description( $this->_descriptions, $this );
	}

	// }}}
	// {{{ analyse()

	abstract protected function get_analyseable();

	public function analyse()
	{
		if( $this->analysed ) return;
		$this->analysed = true;

		$analysers = array();
		foreach( get_declared_classes() as $class )
			if( is_subclass_of( $class, 'DstyleDoc_Analyser' ) )
				$analysers[] = $class;

		$current = null;
		foreach( (array)$this->analyseable as $source )
		{
			if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'doc')!==false )
			{
				$c = htmlentities($source);
				if( ! $c ) $c = '&nbsp;';
				$s = @get_class($current);
				if( ! $s ) $s = '&nbsp;';
				$e = get_class($this);
				try
				{
					$ee = $this->name;
				}
				catch( BadPropertyException $e )
				{
					$ee = '';
				}
				echo <<<HTML
<div style='clear:left;float:left;color:black;background:PowderBlue;padding:1px 3px'>{$e}</div>
<div style='float:left;color:black;background:LightCyan;padding:1px 3px'>{$ee}</div>
<div style='float:left;color:white;background:SteelBlue;padding:1px 3px'>{$c}</div>
<div style='background:DimGray;color:white;padding:1px 3px;'>{$s}</div>
<div style='clear:left;'></div>
HTML;
			}
			$result = array();
			$source = DstyleDoc_Analyser::remove_stars($source);
			$instance = (object)array('value'=>null);
			$priority = (object)array('value'=>null);
			foreach( $analysers as $analyser )
			{
				if( call_user_func( array($analyser,'analyse'), $current, $source, $instance, $priority, $this->converter->dsd ) )
					$result[$priority->value] = $instance->value;
			}
			if( $result )
			{
				ksort($result);
				$current = current($result);

				if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'doc')!==false )
				{
					foreach( $result as $k => $v )
					{
						$vv = get_class($v);
						echo <<<HTML
<div style='clear:left;float:left;color:white;background:SteelBlue;padding:1px 3px'>{$k}</div>
<div style='float:left;background:MediumPurple;color:white;padding:1px 3px;'>{$vv}</div>
<div style='clear:left;'></div>
HTML;
					}
				}

				if( $current instanceof DstyleDoc_Analyser )
					$current = $current->apply( $this );
			}
		}

		foreach( $analysers as $analyser )
			call_user_func( array($analyser,'finalize'), $this );
	}

	// }}}
}

/**
 * Classe abstraite d'un élement simple.
 * Ces éléments sont instancié lorsque leur élément parent à besoin de les afficher.
 */
abstract class SimpleElement extends AnalyseableElement
{
	protected function get_analyseable()
	{
		$return = $this->descriptions;
		unset($this->description);
		return $return;
	}
}

/**
 * Classe de base d'un élement.
 */
abstract class Element extends AnalyseableElement
{
		// {{{ __sleep()

	public function __sleep()
	{
		return array_merge( parent::__sleep(), array(
			'_historys', '_packages', '_analysed', '_documentation', '_since', '_version',
		) );
	}

	// }}}
	// {{{ $version

	protected $_version = '';

	protected function set_version( $version )
	{
		$this->_version = $version;
	}

	protected function get_version()
	{
		return $this->_version;
	}

	// }}}
	// {{{ $since

	protected $_since = '';

	protected function set_since( $since )
	{
		$this->_since = $since;
	}

	protected function get_since()
	{
		return $this->_since;
	}

	// }}}
	// {{{ $documentation

	protected $_documentation = '';

	protected function set_documentation( $documentation )
	{
		$this->_documentation = (string)$documentation;
	}

	protected function get_documentation()
	{
		return $this->_documentation;
	}

	// }}}
	// {{{ $analysed

	protected $_analysed = false;

	protected function set_analysed( $analysed )
	{
		$this->_analysed = (boolean)$analysed;
	}

	protected function get_analysed()
	{
		return $this->_analysed;
	}

	// }}}
	// {{{ $packages

	protected $_packages = array();

	protected function set_package( $package )
	{
		if( is_array($package) or $package instanceof Iterator )
			foreach( $package as $p )
				$this->_packages[] = (string)$p;
		elseif( $package )
			$this->_packages[] = (string)$package;
	}

	protected function set_packages( $packages )
	{
		$this->_packages = $packages;
	}

	protected function get_packages()
	{
		return $this->_packages;
	}

	// }}}
	// {{{ $historys

	protected $_historys = array();

	protected function set_historys( $versions )
	{
		$this->_historys = (array)$versions;
	}

	protected function get_historys()
	{
		return $this->_historys;
	}

	protected function set_history( $version )
	{
		$this->_historys[] = new DstyleDoc_Element_History_Version( $this->converter, $version );
	}

	protected function get_history()
	{
		if( count($this->_historys) )
		{
			list($version) = array_reverse($this->_historys);
			return $version;
		}
		else
			return new DstyleDoc_Element_History_Version( $this->converter, null );
	}

	// }}}
	// {{{ $id

	/**
	 * Renvoie l'identifiant unique de l'élément basé sur ces décendants.
	 * Returns:
	 *		string = L'ID de l'élément.
	 */
	abstract protected function get_id();

	// }}}
	// {{{ $link

	/**
	 * Renvoie un lien vers l'élément.
	 * Returns:
	 *		mixed = Dépends du convertisseur.
	 */
	protected function get_link()
	{
		return $this->converter->convert_link( $this->id, $this->display, $this );
	}

	// }}}
	// {{{ link()

	/**
	 * Renvoie un lien vers l'élément avec un texte donné en paramètre.
	 * Params:
	 *		string $text = Le texte du lien.
	 * Returns:
	 *		mixed = Dépends du convertisseur.
	 */
	public function link( $text )
	{
		return $this->converter->convert_link( $this->id, (string)$text, $this );
	}

	// }}}
	protected function get_analyseable()
	{
		return explode("\n",strtr($this->documentation,array("\r\n"=>"\n","\r"=>"\n")));
	}
}


/**
 * Classe abstraite d'un élement contenant un titre.
 */
abstract class TitledElement extends Element
{
	// {{{ $descriptions

	protected function get_description()
	{
		$this->analyse();
		$copy = $this->_descriptions;
		if( count($copy) )
			array_shift($copy);
		return $this->converter->convert_description( $copy, $this );
	}

	// }}}
	// {{{ $title

	protected function get_title()
	{
		$this->analyse();
		if( count($this->_descriptions) )
			list($result) = $this->_descriptions;
		else
			$result = '';
		return $this->converter->convert_title( $result, $this );
	}

	// }}}
	// {{{ $todo

	protected $_todos = array();

	protected function get_todo()
	{
		if( ($todo = end($this->_todos)) instanceof DstyleDoc_Element_Todo and ! $todo->descriptions )
			null;
		else
			$this->_todos[] = $todo = new DstyleDoc_Element_Todo( $this->converter );
		return $todo;
	}

	protected function get_todos()
	{
		$this->analyse();
		return $this->_todos;
	}

	// }}}
}

/**
 * Classe abstraite d'un élement possédant un lien dans un fichier.
 */
abstract class FiledElement extends TitledElement
{
		// {{{ __sleep()

	public function __sleep()
	{
		return array_merge( parent::__sleep(), array(
			'_file', '_line',
		) );
	}

	// }}}
	// {{{ $file

	protected $_file = null;

	protected function set_file( $file )
	{
		$this->converter->file = $file;
		$this->_file = $this->converter->file;
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
		$this->_line = abs((integer)$line);
	}

	protected function get_line()
	{
		return (integer)$this->_line;
	}

	// }}}
	// {{{ $package

	protected function get_packages()
	{
		if( ! $this->_packages and $this->file )
		{
			$this->file->analyse();
			return $this->file->packages;
		}
		else
			return parent::get_packages();
	}

	// }}}
}

/**
 * Classe abstraite d'un élement possédant un lien dans un fichier et un nom.
 */
abstract class NamedElement extends FiledElement
{
		// {{{ __sleep()

	public function __sleep()
	{
		return array_merge( parent::__sleep(), array(
			'_name',
		) );
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
		return (string)$this->_name;
	}

	// }}}
	// {{{ __construct()

	public function __construct( DstyleDoc_Converter $converter, $name )
	{
		parent::__construct( $converter );
		if( $name )
			$this->name = $name;
	}

	// }}}
}

/**
 * Classe d'un element qui contient des fonctions.
 */
abstract class MethodedElement extends NamedElement
{
	// {{{ $methods

	/**
	 * Contient la listes des méthodes déclarées par la classe.
	 * Type:
	 *		array(DstyleDoc_Element_Method) = Tableau a clefs numériques contentant des instances de DstyleDoc_Element_Method.
	 */
	protected $_methods = array();

	/**
	 * Ajoute une méthode a la classe ou selectionne une méthode existante.
	 * Si la méthode a été ajoutée elle sera sélectionnée La méthode sera alors renvoyé par la propriété $method.
	 * Params:
	 *		string = Le nom du membre a lajouter ou a laélectionner.
	 *		DstyleDoc_Element_Member = L'instance du membre a lajouter ou a laélectionner.
	 */
	protected function set_method( $name )
	{
		$found = false;
		if( ! empty($name) and count($this->_methods) )
		{
			reset($this->_methods);
			while( true)
			{
				$method = current($this->_methods);
				if( $found = ($method->name == $name or $method === $name) or false === next($this->_methods) )
					break;
			}
		}

		if( ! $found )
		{
			if( $name instanceof DstyleDoc_Element_Method )
				$this->_methods[] = $name;
			else
				$this->_methods[] = new DstyleDoc_Element_Method( $this->converter, $name );
			end($this->_methods);
		}
	}

	protected function get_method()
	{
		if( ! count($this->_methods) )
		{
			$this->_methods[] = new DstyleDoc_Element_Method( $this->converter, null );
			return end($this->_methods);
		}
		else
			return current($this->_methods);
	}

	abstract protected function get_methods();

	// }}}
}

require_once( 'dev.documentation.php' );
require_once( 'dev.unittest.php' );

Mock::generatePartial('CustomElement','MockCustomElement',array('get_display','get_convert'));

class TestCustomElement extends UnitTestCase
{
	protected $element = null;
	function setUp() { $this->element = new MockCustomElement(); }
	function tearDown() { unset($this->element); }

	function testConstructAndConverter()
	{
		$this->element->converter = new MockConverter();
		$this->assertIsA( $this->element->converter, 'Converter' );
		$this->assertIsA( $this->element['converter'], 'Converter' );
	}

	function testDescription()
	{
		$this->element->
	}
}


