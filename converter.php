<?php

require_once( 'include.properties.php' );

/**
 * Interface de base pour les converteurs.
 */
interface ConverterInterface
{
	// {{{ get_file_interfaces()

	/**
	 * Renvoie la liste des interfaces appartenant a un fichier donnÃ©e.
	 * Params:
	 *		$file = L'instance d'un Ã©lÃ©ment de fichier.
	 * Returns:
	 *		array(DstyleDoc_Element_Interface) = Un tableau d'interface.
	 */
	function get_file_interfaces( DstyleDoc_Element_File $file );

	// }}}
	// {{{ get_file_methods()

	/**
	 * Renvoie la liste des méthodes appartenant à un fichier donnée.
	 * Params:
	 *		$file = L'instance d'un élément de fichier.
	 * Returns:
	 *		array(DstyleDoc_Element_Method) = Un tableau de méthodes.
	 */
	function get_file_methods( DstyleDoc_Element_File $file );

	// }}}
	// {{{ get_file_functions()

	/**
	 * Renvoie la liste des functions appartenant Ã  un fichier donnÃ©e.
	 * Params:
	 *		$file = L'instance d'un Ã©lÃ©ment de fichier.
	 * Returns:
	 *		array(DstyleDoc_Element_Function) = Un tableau de fonctions.
	 */
	function get_file_functions( DstyleDoc_Element_File $file );

	// }}}
	// {{{ get_file_members()

	/**
	 * Renvoie la liste des membres appartenant à un fichier donnée.
	 * Params:
	 *		$file = L'instance d'un élément de fichier.
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
	 * Génère la documentation d'un fichier.
	 * Params:
	 *		$file = L'instance du fichier à documenter.
	 * Returns:
	 *		mixed = La documentation du fichier ou pas.
	 */
	function convert_file( DstyleDoc_Element_File $file );

	// }}}
	// {{{ convert_class()

	/**
	 * Génère la documentation d'une classe.
	 * Params:
	 *		$class = L'instance de la classe à documenter.
	 * Returns:
	 *		mixed = La documentation de la classe ou pas.
	 */
	function convert_class( DstyleDoc_Element_Class $class );

	// }}}
	// {{{ convert_interface()

	/**
	 * Génère la documentation d'un interface.
	 * Params:
	 *		$interface = L'instance de l'interface à documenter.
	 * Returns:
	 *		mixed = La documentation de l'interface ou pas.
	 */
	function convert_interface( DstyleDoc_Element_Interface $interface );

	// }}}
	// {{{ convert_function()

	/**
	 * Génère la documentation d'une fonction.
	 * Params:
	 *		$function = L'instance de la fonction à documenter.
	 * Returns:
	 *		mixed = La documentation de la fonction ou pas.
	 */
	function convert_function( DstyleDoc_Element_Function $function );

	// }}}
	// {{{ convert_method()

	/**
	 * Génère la documentation d'une méthode.
	 * Params:
	 *		$method = L'instance de la méthode à documenter.
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
	 *		$element = L'Ã©lÃ©ment concernÃ© par la dÃ©scription courte.
	 * Returns:
	 *		mixed = DÃ©pends du convertisseur.
	 */
	function convert_description( $description, DstyleDoc_Custom_Element $element );

	// }}}
	// {{{ convert_title()

	/**
	 * Convertie la déscription courte.
	 * Params:
	 *		string $title = La ligne de description courte.
	 *		$element = L'élément concerné par la déscription courte.
	 * Returns:
	 *		mixed = Dépends du convertisseur.
	 */
	function convert_title( $title, DstyleDoc_Element $element );

	// }}}
	// {{{ convert_link()

	/**
	 * Converti et renvoie un lien vers un élément.
	 * Params:
	 *		mixed $id = L'identifiant unique de l'élément retourné par convert_id().
	 *		mixed $name = Le nom d'affichage de l'élément retourné par convert_name().
	 *		$element = L'élément vers lequel se destine le lien.
	 * Returns:
	 *		mixed = Dépends du convertisseur.
	 */
	function convert_link( $id, $name, DstyleDoc_Element $element );

	// }}}
	// {{{ convert_id()

	/**
	 * Converti et renvoie l'identifiant unique d'un Ã©lÃ©ment.
	 * Params:
	 *		string $id = L'identifiant unique de l'Ã©lÃ©ment.
	 *		array $id = Un tableau contenant la liste des identifiants de l'Ã©lÃ©ment et celui de ses parents.
	 *		$element = L'Ã©lÃ©ment vers lequel se destine le lien.
	 * Returns:
	 *		string = L'identifiant convertie de l'Ã©lÃ©ment.
	 */
	function convert_id( $id, DstyleDoc_Element $element );

	// }}}
	// {{{ convert_display()

	/**
	 * Convertie et renvoie le nom d'affichage d'un Ã©lÃ©ment.
	 * Params:
	 *		string $name = Le nom de l'Ã©lÃ©ment a afficher.
	 *		$element = L'Ã©lÃ©ment vers lequel se destine le lien.
	 * Returns:
	 *		mixed = DÃ©pends du convertisseur.
	 */
	 function convert_display( $name, DstyleDoc_Custom_Element $element );

	// }}}
	// {{{ convert_syntax()

	/**
	 * GÃ©nÃ¨re la documentation d'une syntaxe d'une fonction.
	 * Params:
	 *		$syntax = L'instance de la syntaxe.
	 * Returns:
	 *		mixed = La documentation de la syntaxe ou pas.
	 */
	function convert_syntax( DstyleDoc_Element_Syntax $syntax );

	// }}}
	// {{{ convert_param()

	/**
	 * GÃ©nÃ¨re la documentation d'un paramÃ¨tre d'une fonction.
	 * Params:
	 *		$param = L'instance du paramÃ¨tre.
	 * Returns:
	 *		mixed = La documentation de la syntaxe ou pas.
	 */
	function convert_param( DstyleDoc_Element_Param $param );

	// }}}
	// {{{ convert_return()

	/**
	 * GÃ©nÃ¨re la documentation d'une valeur de retour d'une fonction.
	 * Params:
	 *		$param = L'instance de la valeur de retour.
	 * Returns:
	 *		mixed = La documentation de la valeur de retour ou pas.
	 */
	function convert_return( DstyleDoc_Element_Return $param );

	// }}}
	// {{{ convert_type()

	/**
	 * GÃ©nÃ¨re la documentation d'un type de valeur.
	 * Params:
	 *		$type = L'instance du type.
	 * Returns:
	 *		mixed = La documentation du type.
	 */
	function convert_type( DstyleDoc_Element_Type $type );

	// }}}
	// {{{ convert_exception()

	/**
	 * Génère la documentation d'un exception lancé par une fonction.
	 * Params:
	 *		$exception = L'instance de l'exception lancé par l'exception.
	 * Returns:
	 *		mixed = La documentation de l'exception lancé par l'exception ou pas.
	 */
	function convert_exception( DstyleDoc_Element_Exception $exception );

	// }}}
	// {{{ convert_member()

	/**
	 * Génère la documentation d'un membre d'une classe.
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
	 *		string $text = La portion de texte Ã  convertir.
	 */
	function convert_text( $text );

	// }}}
	// {{{ convert_php()

	/**
	 * Converti du code PHP.
	 * Params:
	 *		string $code = Le cde PHP Ã  convertir.
	 */
	function convert_php( $code );

	// }}}
	// {{{ convert_todo()

	/**
	 * GÃ©nÃ¨re la documentation d'un Ã©lement de la todolist.
	 * Params:
	 *		array(string) $todo = Toutes les lignes de la description de l'Ã©lÃ©ment de la todolist.
	 * Returns:
	 *		mixed = DÃ©pends du convertisseur.
	 */
	function convert_todo( $todo );

	// }}}
	// {{{ search_element()

	/**
	 * Recherche un élément à partir de sa syntax.
	 * Params:
	 *		string $string = Une syntaxe d'un membre, d'une constante, d'une fonction ou d'un classe.
	 * Returns:
	 *		DstyleDoc_Element = L'instance de l'élément en cas de succès.
	 *		false = En cas d'échec.
	 */
	function search_element( $string );

	// }}}
	// {{{ come_accross_elements()

	/**
	 * Recherche dans un text des éventuels mots ou expression correspondant à des élements existants.
	 * Fixme: delete me
	 */
	// function come_accross_elements( $string, DstyleDoc_Custom_Element $element );

	// }}}
	// {{{ hie()

	static function hie();

	// }}}
}

/**
 * Classe de base pour les convertisseurs de DstyleDoc.
 * Cette classe doit Ãªtre Ã©tendu pour Ãªtre utilisÃ©e, elle s'occupe de la gestion de donnÃ©e des Elements et permet de chercher un Element en particulier.
 * Todo:
 *	 - reporter set_method() dans les autres methodes de ce genre.
 *   - gÃ©rer les constantes
 *   - gÃ©rer les membres
 *   - gÃ©rer la ligne suivante
 * Members:
 *	 - array(DstyleDoc_Element_Class) $classes = La listes des classes.
 *	 - DstyleDoc_Element_Class $class = Ajoute une nouvelle classe dans la liste ou retourne la derniÃ¨re ajoutÃ©e.
 */
abstract class Converter extends Properties implements ArrayAccess, ConverterInterface
{
	// {{{ $dsd

	/**
	 * L'instance de DstyleDoc associÃ© au converteur.
	 * Type: DstyleDoc
	 * Members:
	 *   (set,get) DstyleDoc $dsd = L'instance de DstyleDoc associÃ© au converteur.
	 */
	protected $_dsd = null;

	/**
	 * Setter pour $_dsd
	 * Met Ã  jour l'instance de DstyleDoc associÃ© au converteur.
	 * Il est prÃ©ferable d'accÃ¨der au membre $_dsd en Ã©criture plutÃ´t que d'appelÃ© cette mÃ©thode.
	 * Params:
	 *   DstyleDoc = L'instance de DstyleDoc Ã  associer au converteur.
	 */
	protected function set_dsd( DstyleDoc $dsd )
	{
		$this->_dsd = $dsd;
	}

	/**
	 * Getter pour $_dsd
	 * Retourne l'instance de DstyleDoc associÃ© au converteur.
	 * Il est prÃ©ferable d'accÃ¨der au membre $_dsd en lecture plutÃ´t que d'appelÃ© cette mÃ©thode.
	 * Returns:
	 *   DstyleDoc = L'instance de DstyleDoc associÃ© au converteur.
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
	 * La liste des fichiers analysÃ©s.
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
	 * La listes des instances des fonctions dÃ©finies.
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
	 * Cherche si un fichier a Ã©tÃ© ajoutÃ© dans la liste $_classes. Si il existe, file_exists() retournera l'instance de DstyleDoc_Element_File correspondante, sinon retournera false.
	 * Params:
	 *		string $file = Le chemin du fichier a chercher.
	 * Returns:
	 *		DstyleDoc_Element_File = L'instance du en cas de succÃ¨s.
	 *		false = En cas d'Ã©chec.
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
	 *		string $class = Le nom de la classe Ã  chercher.
	 * Returns:
	 *		DstyleDoc_Element_Class = L'instance de la classe en cas de succÃ¨s.
	 *		false = En cas d'Ã©chÃ¨c.
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
	 *		string $interface = Le nom de la interface Ã  chercher.
	 * Returns:
	 *		DstyleDoc_Element_Interface = L'instance de la interface en cas de succÃ¨s.
	 *		false = En cas d'Ã©chec.
	 */
	public function interface_exists( $interface )
	{
		$this->init_interface();
		return $this->_interfaces->exists( $interface, $this );
	}

	// }}}
	// {{{ method_exists()

	/**
	 * Renvoie une mÃ©thode si elle existe.
	 * Params:
	 *		string $class = Le nom de la classe ou de l'interface.
	 *		DstyleDoc_Element_Class, DstyleDoc_Element_Interface $class = L'instance de la classe ou de l'interface.
	 *		string $member = Le nom de la mÃ©thode.
	 * Returns:
	 *		DstyleDoc_Element_Function = L'instance de la fonction en cas de succÃ¨s.
	 *		false = En cas d'Ã©chec.
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
	 *		DstyleDoc_Element_Function = L'instance de la fonction en cas de succÃ¨s.
	 *		false = En cas d'Ã©chec.
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
	 *		boolean $analyse = Indique si la documentation de la classe doit Ãªtre analysÃ© afin de trouver le membre.
	 * Returns:
	 *		DstyleDoc_Element_Member = L'instance du membre en cas de succÃ¨s.
	 *		false = En cas d'Ã©chec.
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
	 *		DstyleDoc_Element_Constant = L'instance de la constance en case de succÃ¨s.
	 *		false = En cas d'Ã©chec.
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
	 * Renvoie la liste des classes appartenant Ã  un fichier donnÃ©e.
	 * Params:
	 *		$file = L'instance d'un Ã©lÃ©ment de fichier.
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

