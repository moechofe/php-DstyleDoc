<?php

// todo: ajouter $element à tout les appels de des méthodes convert_xxxx()
// todo: ajouter les analyse() a tous les getters

abstract class DstyleDoc_Custom_Element extends DstyleDoc_Properties implements ArrayAccess
{
  // {{{ $converter

  protected $_converter = null;

  protected function set_converter( DstyleDoc_Converter $converter )
  {
    $this->_converter = $converter;
  }

  protected function get_converter()
  {
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
   *   apparement on passe toujours des strings.
   */
  protected function set_description( $description )
  {
    //d( gettype( $description ) );
    $this->_descriptions[] = (string)$description;
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
   *    mixed = Dépends du convertisseur.
   */
  abstract protected function get_display();

  // }}}
  // {{{ __construct()

  public function __construct( DstyleDoc_Converter $converter )
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

  final private function __toString()
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
   *    mixed = La documentation de l'élément ou pas.
   */
  abstract protected function get_convert();

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

/**
 * Classe abstraite d'un Element.
 */
abstract class DstyleDoc_Element extends DstyleDoc_Custom_Element
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
   *    string = L'ID de l'élément.
   */
  abstract protected function get_id();

  // }}}
  // {{{ $link

  /**
   * Renvoie un lien vers l'élément.
   * Returns:
   *    mixed = Dépends du convertisseur.
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
   *    string $text = Le texte du lien.
   * Returns:
   *    mixed = Dépends du convertisseur.
   */
  protected function call_link( $text )
  {
    return $this->converter->convert_link( $this->id, (string)$text, $this );
  }

  // }}}
  // {{{ analyse()

  public function analyse()
  {
    $this->analysed = true;

    $analysers = array();
    foreach( get_declared_classes() as $class )
      if( is_subclass_of( $class, 'DstyleDoc_Analyser' ) )
        $analysers[] = $class;

    $current = null;
    foreach( explode("\n",strtr($this->documentation,array("\r\n"=>"\n","\r"=>"\n"))) as $source )
    {
      if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'doc')!==false )
      {
        $c = htmlentities($source);
        if( ! $c ) $c = '&nbsp;';
        $s = get_class($current);
        if( ! $s ) $s = '&nbsp;';
        $e = get_class($this);
        $ee = $this->name;
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
      foreach( $analysers as $analyser )
      {
          if( call_user_func( array($analyser,'analyse'), $current, $source, &$instance, &$priority, $this->converter->dsd ) )
            $result[$priority] = $instance;
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
 * Classe abstratite d'un Element Contenant un titre.
 */
abstract class DstyleDoc_Element_Titled extends DstyleDoc_Element
{
  // {{{ $descriptions

  protected function get_description()
  {
    if( ! $this->analysed ) $this->analyse();
    $copy = $this->_descriptions;
    if( count($copy) )
      array_shift($copy);
    return $this->converter->convert_description( $copy, $this );
  }

  // }}}
  // {{{ $title

  protected function get_title()
  {
    if( ! $this->analysed ) $this->analyse();
    if( count($this->_descriptions) )
      list($result) = $this->_descriptions;
    else
      $result = '';
    return $this->converter->convert_title( $result, $this );
  }

  // }}}
}

/**
 * Classe abstraite d'un Element possédant un lien dans un fichier.
 */
abstract class DstyleDoc_Element_Filed extends DstyleDoc_Element_Titled
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
}

/**
 * Classe abstraite d'un Element possédant un lien dans un fichier et un nom.
 */
abstract class DstyleDoc_Element_Filed_Named extends DstyleDoc_Element_Filed
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
 * Classe d'un element de type fichier.
 */
class DstyleDoc_Element_File extends DstyleDoc_Element_Titled
{
  // {{{ __sleep()

  public function __sleep()
  {
    return array_merge( parent::__sleep(), array(
      '_file',
    ) );
  }

  // }}}
  // {{{ $file

  protected $_file = '';

  protected function set_file( $file )
  {
    $this->_file = strtolower((string)$file);
  }

  protected function get_file()
  {
    return (string)$this->_file;
  }

  protected function get_name()
  {
    return (string)$this->_file;
  }

  // }}}
  // {{{ $classes

  protected function get_classes()
  {
    $classes = array();

    foreach( $this->converter->classes as $class )
      if( $class->file === $this )
        $classes[] = $class;

    return $classes;
  }

  // }}}
  // {{{ $interfaces

  protected function get_interfaces()
  {
    $interfaces = array();

    foreach( $this->converter->interfaces as $interface )
      if( $interface->file === $this )
        $interfaces[] = $interface;

    return $interfaces;
  }

  // }}}
  // {{{ $functions

  protected function get_functions()
  {
    $functions = array();

    foreach( $this->converter->functions as $function )
      if( $function->file === $this )
        $functions[] = $function;

    return $functions;
  }

  // }}}
  // {{{ $id

  protected function get_id()
  {
    return $this->converter->convert_id( $this->file, $this );
  }

  // }}}
  // {{{ $display

  protected function get_display()
  {
    return $this->converter->convert_display( $this->file, $this );
  }

  // }}}
  // {{{ $convert

  protected function get_convert()
  {
    if( ! $this->analysed ) $this->analyse();
    return $this->converter->convert_file( $this );
  }

  // }}}
  // {{{ __construct()

  public function __construct( DstyleDoc_Converter $converter, $file )
  {
    parent::__construct( $converter );
    $this->file = $file;
  }

  // }}}
}

/**
 * Classe d'un element qui contient des fonctions.
 */
abstract class DstyleDoc_Element_Methoded_Filed_Named extends DstyleDoc_Element_Filed_Named
{
  // {{{ $methods

  /**
   * Contient la listes des méthodes déclarées par la classe.
   * Type:
   *    array(DstyleDoc_Element_Method) = Tableau a clefs numériques contentant des instances de DstyleDoc_Element_Method.
   */
  protected $_methods = array();

  /**
   * Ajoute une méthode a la classe ou selectionne une méthode existante.
   * Si la méthode a été ajoutée elle sera sélectionnée La méthode sera alors renvoyé par la propriété $method.
   * Params:
   *    string = Le nom du membre a lajouter ou a laélectionner.
   *    DstyleDoc_Element_Member = L'instance du membre a lajouter ou a laélectionner.
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

/**
 * Classe d'un element de type classe.
 */
class DstyleDoc_Element_Class extends DstyleDoc_Element_Methoded_Filed_Named
{
  // {{{ $methods

  protected function get_methods()
  {
    $methods = $this->_methods;

    if( $this->parent )
      foreach( $this->parent->heritable_methods as $method )
          // todo: in_array() do not work correctly. see bug #38356 at http://bugs.php.net/bug.php?id=39356
        //        if( ! in_array($method, $methods) )
        foreach( $methods as $value )
          if( $method === $value )
            self::add_uniq_name_methods( $methods, $method );

    return $methods;
  }

  // }}}
  // {{{ add_uniq_name_methods()

  static protected function add_uniq_name_methods( &$methods, $method )
  {
    foreach( $methods as $value )
      if( $value->name == $method->name )
        return null;
    $methods[] = $method;
  }

  // }}}
  // {{{ $heritable_methods

  /**
   * Retourne la liste des méthodes héritables de la classe.
   * Retourne la liste des méthodes de la classe et des méthodes héritées qui ont un accès publiques ou protétéges.
   * Returns:
   *    array(DstyleDoc_Element_Method) = La liste des méthodes héritables de la classe.
   */
  protected function get_heritable_methods()
  {
    $methods = array();

    foreach( $this->_methods as $method )
      if( $method->protected or $method->public )
        $methods[] = $method;

    if( $this->parent )
      foreach( $this->parent->heritable_methods as $method )
        if( ! in_array($method, $methods) )
          $methods[] = $method;

    return $methods;
  }

  // }}}
  // {{{ $abstract

  protected $_abstract = false;

  protected function set_abstract( $abstract )
  {
    $this->_abstract = $abstract;
  }

  protected function get_abstract()
  {
    return $this->_abstract;
  }

  // }}}
  // {{{ $final

  protected $_final = false;

  protected function set_final( $final )
  {
    $this->_final = $final;
  }

  protected function get_final()
  {
    return $this->_final;
  }

  // }}}
  // {{{ $parent

  protected $_parent = null;

  protected function set_parent( $parent )
  {
    if( $parent )
      $this->_parent = (string)$parent;
  }

  protected function get_parent()
  {
    if( $found = $this->converter->class_exists($this->_parent) )
      return $found;
    else
      return $this->_parent;
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
  // {{{ $childs

  protected function get_childs()
  {
    return $this->_childs;
  }

  // }}}
  // {{{ $members

  /**
   * Contient la listes des membres déclaré ppar la classe.
   * Type:
   *    array(DstyleDoc_Element_Member) = Tableau a lalefs numériques contentant des instances de DstyleDoc_Element_Member.
   */
  protected $_members = array();

  /**
   * Ajoute un membre a laa classe ou selectionne un membre existant.
   * Si le membre à été ajouté, il sera sélectionné. Le membre sera alors renvoyé par la propriété $membre.
   * Params:
   *    string = Le nom du membre a lajouter ou a laélectionner.
   *    DstyleDoc_Element_Member = L'instance du membre a lajouter ou a laélectionner.
   */
  protected function set_member( $name )
  {
    $found = false;
    if( ! empty($name) and count($this->_members) )
    {
      reset($this->_members);
      while( true)
      {
        $member = current($this->_members);
        if( $found = ($member->name == $name or $member === $name) or false === next($this->_members) )
          break;
      }
    }

    if( ! $found )
    {
      if( $name instanceof DstyleDoc_Element_Member )
        $this->_members[] = $name;
      else
        $this->_members[] = new DstyleDoc_Element_Member( $this->converter, $name );
      end($this->_members);
    }
  }

  /**
   * Retourne le dernier membre ajouté.
   * Returns:
   *    DstyleDoc_Element_Member = Le dernier membre ajouté.
   */
  protected function get_member()
  {
    if( ! count($this->_members) )
    {
      $this->_members[] = new DstyleDoc_Element_Member( $this->converter, null );
      return end($this->_members);
    }
    else
      return current($this->_members);
  }

  /**
   * Retourne la liste des membres de la classe et des membres hérités.
   * Returns:
   *    array(DstyleDoc_Element_Member) = La liste des membres de la classe et des membres hérités.
   */
  protected function get_members()
  {
    $membres = $this->_members;

    if( $this->parent )
      foreach( $this->parent->heritable_members as $membre )
        if( ! in_array($membre, $membres) )
          $membres[] = $membre;

    return $membres;
  }

  /**
   * Retourne la liste des membres héritables de la classe.
   * Retourne la liste des membres de la classe et des membres hérité pqui ont un accès publique ou protétég.
   * Returns:
   *    array(DstyleDoc_Element_Member) = La liste des membres héritables de la classe.
   */
  protected function get_heritable_members()
  {
    $membres = array();

    foreach( $this->_members as $membre )
      if( $membre->protected or $membre->public )
        $membres[] = $membre;

    if( $this->parent )
      foreach( $this->parent->heritable_members as $membre )
        if( ! in_array($membre, $membres) )
          $membres[] = $membre;

    return $membres;
  }

  // }}}
  // {{{ $id

  protected function get_id()
  {
    return $this->converter->convert_id( array($this->file->file, $this->name), $this );
  }

  // }}}
  // {{{ $display

  protected function get_display()
  {
    return $this->converter->convert_display( $this->name, $this );
  }

  // }}}
  // {{{ $convert

  protected function get_convert()
  {
    if( ! $this->analysed ) $this->analyse();
    return $this->converter->convert_class( $this );
  }

  // }}}
}

/**
 * Classe d'un element de version de l'historique.
 */
class DstyleDoc_Element_History_Version extends DstyleDoc_Custom_Element
{
  // {{{ $version

  protected $_version = '';

  protected function set_version( $version )
  {
    $this->_version = (string)$version;
  }

  protected function get_version()
  {
    return $this->_version;
  }

  // }}}
  // {{{ $display

  protected function get_display()
  {
    return $this->converter->convert_display( $this->version );
  }

  // }}}
  // {{{ $convert

  protected function get_convert()
  {
    return $this->converter->convert_history( $this );
  }

  // }}}
  // {{{ __construct()

  public function __construct( DstyleDoc_Converter $converter, $version )
  {
    parent::__construct( $converter );
    $this->version = $version;
  }

  // }}}
}

/**
 * Classe d'un element de type interface.
 */
class DstyleDoc_Element_Interface extends DstyleDoc_Element_Methoded_Filed_Named
{
  // {{{ $methods

  protected function get_methods()
  {
    return $this->_methods;
  }

  // }}}
  // {{{ $id

  protected function get_id()
  {
    return $this->converter->convert_id( array($this->file->file, $this->name) );
  }

  // }}}
  // {{{ $display

  protected function get_display()
  {
    return $this->converter->convert_display( $this->name );
  }

  // }}}
  // {{{ $convert

  protected function get_convert()
  {
    if( ! $this->analysed ) $this->analyse();
    return $this->converter->convert_interface( $this );
  }

  // }}}
}

/**
 * Classe d'un element de type fonction.
 */
class DstyleDoc_Element_Function extends DstyleDoc_Element_Filed_Named
{
    // {{{ __sleep()

  public function __sleep()
  {
    return array_merge( parent::__sleep(), array(
      '_params', '_returns', '_exceptions', '_syntax',
    ) );
  }

  // }}}
  // {{{ $params

  protected $_params = array();

  protected function set_params( $params )
  {
    $this->_params = (array)$params;
  }

  protected function get_params()
  {
    return $this->_params;
  }

  /**
   * Séléction un paramètre existant ou en crée un nouveau.
   * Le paramètre ainsi séléctionné peut être récupèrer avec get_param().
   * Params:
   *  $param = Le nom de la variable existante ou qui sera créer.
   */
  protected function set_param( $param )
  {
    $found = false;

    if( $param{0} == '$' ) $param = substr($param,1);

    if( ! empty($param) and count($this->_params) )
    {
      reset($this->_params);
      while( true)
      {
	$value = current($this->_params);
        if( strtolower($value->var) == strtolower($param) )
        {
          $found = true;
          break;
        }
        elseif( false === next($this->_params) )
          break;
      }
    }

    if( ! $found )
    {
      $this->_params[] = new DstyleDoc_Element_Param( $this->converter, $param );
      end($this->_params);
    }
  }

  protected function get_param()
  {
    if( ! count($this->_params) )
    {
      $this->_params[] = new DstyleDoc_Element_Param( $this->converter, null );
      return end($this->_params);
    }
    else
      return current($this->_params);
  }

  // }}}
  // {{{ $returns

  /**
   * La liste des valeurs de retour d'une fonction.
   * Types:
   *    array(DstyleDoc_Element_Return) = Un tableau contenant des instances de valeur de retour.
   */
  protected $_returns = array();

  /**
   * Ajoute un nouvelle ou séléctionne une valeur déjà éxistante.
   * La valeur ainsi séléctionné peut être récupèrer avec get_return().
   * Ne pas utiliser directement cette méthode, utiliser la propriété $return à la place.
   * Params:
   *    string $return = Le type de la valeur retourné.
   *    DstyleDoc_Element_Return = Une instance d'une valeur de retour.
   */
  protected function set_return( $return )
  {
    $found = false;
    if( ! empty($return) and count($this->_returns) )
    {
      reset($this->_returns);
      while( true)
      {
	$current = current($this->_returns);
	// ajouter d'autre verif ici ?
        if( $found = ( (is_object($return) and $current === $return)
          or (is_string($return) and is_string($current->type) and strtolower($current->type) === strtolower($return)) ) or false === next($this->_returns) )
          break;
      }
    }

    if( ! $found )
    {
      if( $return instanceof DstyleDoc_Element_Return )
        $this->_returns[] = $return;
      else
        $this->_returns[] = new DstyleDoc_Element_Return( $this->converter, $return );
      end($this->_returns);
    }
  }

  /**
   * Renvoie l'instance de la valeur de retour précedement séléctionner ou ajouté avec set_return().
   * Si aucune valeur de retour n'a été ajouté avant, une fausse valeur de retour sera retournée.
   * Ne pas utiliser directement cette méthode, utiliser la propriété $return à la place.
   * Returns:
   *    DstyleDoc_Element_Return = L'instance de la valeur de retour.
   */
  protected function get_return()
  {
    if( ! count($this->_returns) )
    {
      $this->_returns[] = new DstyleDoc_Element_Return( $this->converter, null );
      return end($this->_returns);
    }
    else
      return current($this->_returns);
  }

  private function returns_types( $returns )
  {
    $result = array();
    foreach( $returns as $return )
    {
      $types = $return->type;
      if( is_array($types) )
      {
        foreach( $this->returns_types($types) as $type => $return )
          $this->smart_add_return_type( $result, $type, $return );
      }
      else
      {
      $this->smart_add_return_type( $result, $types, $return );
      }
    }
    return $result;
  }

  private function smart_add_return_type( &$array, $type, DstyleDoc_Element_Return $return )
  {
    if( $type instanceof DstyleDoc_Element_Class or $type instanceof DstyleDoc_Element_Interface )
      $key = $type->name;
    else
      $key = $type;

    if( ! isset($array[$key]) or ! isset($array[$key]->description) or ($return->from === $this and isset($return->description)) )
      $array[$key] = $return;
  }

  protected function get_returns()
  {
    return array_values( $this->returns_types($this->_returns) );
  }

  // }}}
  // {{{ $exceptions

  /**
   * La liste des exception lancé par une fonction.
   * Types:
   *    array(DstyleDoc_Element_Exception) = Un tableau contenant des instances des exceptions.
   */
  protected $_exceptions = array();

  protected function set_exception( $exception )
  {
    $found = false;
    if( ! empty($exception) and count($this->_exceptions) )
    {
      reset($this->_exceptions);
      while( true)
      {
        $current = current($this->_exceptions);
        if( $found = ( (is_object($exception) and $current === $exception)
          or (is_string($exception) and $current->name === strtolower($exception)) ) or false === next($this->_exceptions) )
          break;
      }
    }

    if( ! $found )
    {
      if( $exception instanceof DstyleDoc_Element_Exception )
        $this->_exceptions[] = $exception;
      else
        $this->_exceptions[] = new DstyleDoc_Element_Exception( $this->converter, $exception );
      end($this->_exceptions);
    }
  }

  protected function get_exception()
  {
    if( ! count($this->_exceptions) )
    {
      $this->_exceptions[] = new DstyleDoc_Element_Exception( $this->converter, null );
      return end($this->_exceptions);
    }
    else
      return current($this->_exceptions);
  }

  protected function get_exceptions()
  {
    return $this->_exceptions;
  }

  // }}}
  // {{{ $syntax

  protected $_syntax = array();

  protected function set_syntax( $syntax )
  {
    $this->_syntax[] = new DstyleDoc_Element_Syntax( $this->converter, $syntax );
  }

  protected function get_syntax()
  {
    if( count($this->_syntax) )
      return end($this->_syntax);
    else
      return new DstyleDoc_Element_Syntax( $this->converter, null );
  }

  protected function get_syntaxs()
  {
    if( ! $this->analysed ) $this->analyse();
    if( ! $this->_syntax )
    {
      $this->_syntax[] = new DstyleDoc_Element_Syntax( $this->converter, $this->params );
      $syntax = end($this->_syntax);
      $syntax->function = $this;
    }
    return $this->_syntax;
  }

  // }}}
  // {{{ $id

  protected function get_id()
  {
    return $this->converter->convert_id( array($this->file->file, $this->name), $this );
  }

  // }}}
  // {{{ $display

  protected function get_display()
  {
    return $this->converter->convert_display( $this->name.'()', $this );
  }

  // }}}
  // {{{ $convert

  protected function get_convert()
  {
    if( ! $this->analysed ) $this->analyse();
    return $this->converter->convert_function( $this );
  }

  // }}}
}

/**
 * Classe d'un element de type fonction.
 */
class DstyleDoc_Element_Method extends DstyleDoc_Element_Function
{
  // {{{ $descriptions

  protected function get_description()
  {
    if( ! $this->analysed ) $this->analyse();
    if( ! $this->_descriptions and $this->parent )
    {
      if( ! $this->parent->analysed ) $this->parent->analyse();
      return $this->parent->description;
    }
    elseif( ! $this->_descriptions )
      return '';
    else
      return parent::get_description();
  }

  // }}}
  // {{{ $title

  protected function get_title()
  {
    if( ! $this->analysed ) $this->analyse();
    if( ! $this->_descriptions and $this->parent )
    {
      if( ! $this->parent->analysed ) $this->parent->analyse();
      return $this->parent->title;
    }
    elseif( ! $this->_descriptions )
      return '';
    else
      return parent::get_title();
  }

  // }}}
  // {{{ $class

  protected $_class = null;

  protected function set_class( DstyleDoc_Element $class )
  {
    if( $class instanceof DstyleDoc_Element_Interface or $class instanceof DstyleDoc_Element_Class )
      $this->_class = $class;
  }

  protected function get_class()
  {
    return $this->_class;
  }

  // }}}
  // {{{ $parent

  protected function get_parent()
  {
    if( $this->class->parent
      and ($found = $this->converter->method_exists($this->class->parent, $this->name))
      and ($found->protected or $found->public and ! $this->private) )
      return $found;
    else
      return null;
  }

  // }}}
  // {{{ $abstract

  protected $_abstract = false;

  protected function set_abstract( $abstract )
  {
    if( $abstract )
      $this->static = false;
    $this->_abstract = (boolean)$abstract;
  }

  protected function get_abstract()
  {
    return $this->_abstract;
  }

  // }}}
  // {{{ $static

  protected $_static = false;

  protected function set_static( $static )
  {
    $this->_static = (boolean)$static;
  }

  protected function get_static()
  {
    return $this->_static;
  }

  // }}}
  // {{{ $public

  protected $_public = false;

  protected function set_public( $public )
  {
    if( $public )
    {
      $this->protected = false;
      $this->private = false;
    }
    $this->_public = (boolean)$public;
  }

  protected function get_public()
  {
    return $this->_public;
  }

  // }}}
  // {{{ $protected

  protected $_protected = false;

  protected function set_protected( $protected )
  {
    if( $protected )
    {
      $this->private = false;
      $this->public = false;
    }
    $this->_protected = (boolean)$protected;
  }

  protected function get_protected()
  {
    return $this->_protected;
  }

  // }}}
  // {{{ $private

  protected $_private = false;

  protected function set_private( $private )
  {
    if( $private )
    {
      $this->protected = false;
      $this->public = false;
    }
    $this->_private = (boolean)$private;
  }

  protected function get_private()
  {
    return $this->_private;
  }

  // }}}
  // {{{ $final

  protected $_final = false;

  protected function set_final( $final )
  {
    if( $final )
      $this->abstract = false;
    $this->_final = (boolean)$final;
  }

  protected function get_final()
  {
    return $this->_final;
  }

  // }}}
  // {{{ $id

  protected function get_id()
  {
    return $this->converter->convert_id( array($this->file->file, $this->class->name, $this->name), $this );
  }

  // }}}
  // {{{ $display

  protected function get_display()
  {
    return $this->converter->convert_display( $this->class->name.($this->static?'::':'->').$this->name.'()', $this );
  }

  // }}}
  // {{{ $convert

  protected function get_convert()
  {
    if( ! $this->analysed ) $this->analyse();
    return $this->converter->convert_method( $this );
  }

  // }}}
}

/**
 * Classe d'un element de type membre.
 */
class DstyleDoc_Element_Member extends DstyleDoc_Element_Filed_Named
{
  // {{{ $name

  protected function set_name( $name )
  {
    if( substr((string)$name,0,1)==='$' )
      $this->_name = substr((string)$name,1);
    else
      $this->_name = (string)$name;
  }

  // }}}
  // {{{ $types

  /**
   * La liste des types d'un membre.
   * Types:
   *    array(DstyleDoc_Element_Type) = Un tableau contenant des instances des types du membre.
   */
  protected $_types = array();

  /**
   * Ajoute un nouvelle ou séléctionne un type d'un 跩stant.
   * Le type ainsi séléctionné peut être récupéré avec get_type().
   * Ne pas utiliser directement cette méthode, utiliser la propriété $type a laa place.
   * Params:
   *    string $type = Le type du membre.
   *    DstyleDoc_Element_Type = Une instance d'un type de membre.
   */
  protected function set_type( $type )
  {
    $found = false;
    if( ! empty($type) and count($this->_types) )
    {
      reset($this->_types);
      while( true)
      {
        $current = current($this->_types);
        if( $found = ( (is_object($type) and $current === $type)
          or (is_string($type) and $current->type === strtolower($type)) ) or false === next($this->_types) )
          break;
      }
    }

    if( ! $found )
    {
      if( $type instanceof DstyleDoc_Element_Type )
        $this->_types[] = $type;
      else
        $this->_types[] = new DstyleDoc_Element_Type( $this->converter, $type );
      end($this->_types);
    }
  }

  /**
   * Renvoie l'instance d'un type d'un membre précédement séléctionner ou ajouté avec set_type().
   * Si aucun type de membre n'a été ajouté avant, une faux type de membre sera retourné.
   * Ne pas utiliser directement cette méthode, utiliser la propriété $type a laa place.
   * Returns:
   *    DstyleDoc_Element_Type = L'instance du type d'un membre.
   */
  protected function get_type()
  {
    if( ! count($this->_types) )
    {
      $this->_types[] = new DstyleDoc_Element_Type( $this->converter, null );
      return end($this->_types);
    }
    else
      return current($this->_types);
  }

  protected function get_types()
  {
    return $this->_types;
  }

  // }}}
  // {{{ $class

  protected $_class = null;

  protected function set_class( DstyleDoc_Element $class )
  {
    if( $class instanceof DstyleDoc_Element_Interface or $class instanceof DstyleDoc_Element_Class )
      $this->_class = $class;
  }

  protected function get_class()
  {
    return $this->_class;
  }

  // }}}
  // {{{ $static

  protected $_static = false;

  protected function set_static( $static )
  {
    $this->_static = $static;
  }

  protected function get_static()
  {
    return $this->_static;
  }

  // }}}
  // {{{ $public

  protected $_public = false;

  protected function set_public( $public )
  {
    $this->_public = $public;
  }

  protected function get_public()
  {
    return $this->_public;
  }

  // }}}
  // {{{ $protected

  protected $_protected = false;

  protected function set_protected( $protected )
  {
    $this->_protected = $protected;
  }

  protected function get_protected()
  {
    return $this->_protected;
  }

  // }}}
  // {{{ $private

  protected $_private = false;

  protected function set_private( $private )
  {
    $this->_private = $private;
  }

  protected function get_private()
  {
    return $this->_private;
  }

  // }}}
  // {{{ $id

  protected function get_id()
  {
    return $this->converter->convert_id( array($this->file->file, $this->class->name, $this->name), $this );
  }

  // }}}
  // {{{ $display

  protected function get_display()
  {
    return $this->converter->convert_display( $this->class->name.($this->static?'::$':'->$').$this->name, $this );
  }

  // }}}
  // {{{ $convert

  protected function get_convert()
  {
    if( ! $this->analysed ) $this->analyse();
    return $this->converter->convert_member( $this );
  }

  // }}}
}

/**
 * Class d'un element de type syntaxe.
 */
class DstyleDoc_Element_Syntax extends DstyleDoc_Custom_Element
{
  // {{{ $function

  protected $_function = null;

  protected function set_function( DstyleDoc_Element_Function $function )
  {
    $this->_function = $function;
  }

  protected function get_function()
  {
    return $this->_function;
  }

  // }}}
  // {{{ $params

  protected $_params = array();

  protected function set_params( $params )
  {
    $this->_params = (array)$params;
  }

  protected function get_params()
  {
    return $this->_params;
  }

  protected function set_param( $param )
  {
    $this->_params[] = (object)$param;
  }

  protected function get_param()
  {
    return end($this->_params);
  }

  // }}}
  // {{{ $display

  protected function get_display()
  {
    return $this->converter->convert_display( (string)$syntax, $this );
  }

  // }}}
  // {{{ $returns

  /**
   * La liste des valeurs de retour d'une syntaxe.
   * Types:
   *    array(DstyleDoc_Element_Return) = Un tableau contenant des instances de valeur de retour.
   */
  protected $_returns = array();

  /**
   * Ajoute un nouvelle ou séléctionne une valeur d'un 跩stante.
   * La valeur ainsi séléctionné peut être récupéré avec get_return().
   * Ne pas utiliser directement cette méthode, utiliser la propriété $return a laa place.
   * Params:
   *    string $return = Le type de la valeur retourné.   *    DstyleDoc_Element_Return = Une instance d'une valeur de retour.
   */
  protected function set_return( $return )
  {
    $found = false;
    if( ! empty($return) and count($this->_returns) )
    {
      reset($this->_returns);
      while( true)
      {
        $current = current($this->_returns);
        if( $found = ( (is_object($return) and $current === $return)
          or (is_string($return) and $current->type === strtolower($return)) ) or false === next($this->_returns) )
          break;
      }
    }

    if( ! $found )
    {
      if( $return instanceof DstyleDoc_Element_Return )
        $this->_returns[] = $return;
      else
        $this->_returns[] = new DstyleDoc_Element_Return( $this->converter, $return );
      end($this->_returns);
    }
  }

  /**
   * Renvoie l'instance de la valeur de retour précédement séléctionner ou ajouté avec set_return().
   * Si aucune valeur de retour n'a été ajouté avant, une fausse valeur de retour sera retournée
   * Ne pas utiliser directement cette méthode, utiliser la propriété $return a laa place.
   * Returns:
   *    DstyleDoc_Element_Return = L'instance de la valeur de retour.
   */
  protected function get_return()
  {
    if( ! count($this->_returns) )
    {
      $this->_returns[] = new DstyleDoc_Element_Return( $this->converter, null );
      return end($this->_returns);
    }
    else
      return current($this->_returns);
  }

  protected function get_returns()
  {
    if( empty($this->_returns) )
      return $this->function->returns;
    else
      return $this->_returns;
  }

  // }}}
  // {{{ __construct()

  public function __construct( DstyleDoc_Converter $converter, $syntax )
  {
    parent::__construct( $converter );
    if( is_array($syntax) or $syntax instanceof ArrayAccess )
      foreach( $syntax as $param )
        $this->param = $param;
  }

  // }}}
  // {{{ $convert

  protected function get_convert()
  {
    return $this->converter->convert_syntax( $this );
  }

  // }}}
}

/**
 * Classe d'un element de type paramètre.
 */
class DstyleDoc_Element_Exception extends DstyleDoc_Custom_Element
{
  // {{{ $name

  protected $_name = '';

  protected function set_name( $name )
  {
    $this->_name = strtolower((string)$name);
  }

  protected function get_name()
  {
    return $this->_name;
  }

  // }}}
  // {{{ $display

  protected function get_display()
  {
    return $this->converter->convert_display( $this->name, $this );
  }

  // }}}
  // {{{ __construct()

  public function __construct( DstyleDoc_Converter $converter, $exception )
  {
    parent::__construct( $converter );
    $this->name = $exception;
  }

  // }}}
  // {{{ $convert

  protected function get_convert()
  {
    return $this->converter->convert_exception( $this );
  }

  // }}}
}

/**
 * Classe d'un element de type paramètre.
 */
class DstyleDoc_Element_Param extends DstyleDoc_Custom_Element
{
  // {{{ $types

  protected $_types = array();

  protected function set_types( $types )
  {
    $this->_types = (array)$types;
  }

  protected function get_types()
  {
    return $this->_types;
  }

  protected function set_type( $type )
  {
    if( ! empty($type) )
    {
      $this->_types[] = strtolower((string)$type);
      $this->_types = array_unique($this->_types);
    }
  }

  // }}}
  // {{{ $var

  protected $_var = '';

  protected function set_var( $var )
  {
    if( substr((string)$var,0,1)=='$') $var = substr((string)$var,1);
    $this->_var = (string)$var;
  }

  protected function get_var()
  {
    return $this->_var;
  }

  // }}}
  // {{{ $default

  protected $_default = null;

  protected function set_default( $default )
  {
    $this->_default = strtolower((string)$default);
  }

  protected function get_default()
  {
    return $this->_default;
  }

  protected function get_optional()
  {
    return (boolean)$this->_default;
  }

  protected function set_optional( $optional )
  {
    if( ! $this->_default and $optional )
      $this->_default = true;
  }

  // }}}
  // {{{ $display

  protected function get_display()
  {
    return $this->converter->convert_display( '$'.$this->var, $this );
  }

  // }}}
  // {{{ __construct()

  /**
   * Construit une instance de DstyleDoc_Element_Param.
   * Params:
   *   $converter = L'instance du convertisseur courant.
   *   string $var = Le nom du paramètre.
   */
  public function __construct( DstyleDoc_Converter $converter, $var )
  {
    parent::__construct( $converter );

    if( $var )
      $this->var = $var;
  }

  // }}}
  // {{{ $convert

  protected function get_convert()
  {
    return $this->converter->convert_param( $this );
  }

  // }}}
}

/**
 * Classe d'un element de type type.
 */
class DstyleDoc_Element_Type extends DstyleDoc_Custom_Element
{
  // {{{ $from

  protected $_from = null;

  protected function set_from( DstyleDoc_Element $from )
  {
    $this->_from = $from;
  }

  protected function get_from()
  {
    return $this->_from;
  }

  // }}}
  // {{{ $type

  private $types = array(
    'string', 'number', 'boolean', 'array', 'object', 'null', 'binary', 'resource', 'false', 'true', 'scalar', 'float', 'long', 'double', 'integer' );

  protected $_type = null;

  protected function set_type( $type )
  {
    //d($type);
    if( $type instanceof DstyleDoc_Element_Interface or $type instanceof DstyleDoc_Element_Class )
      $this->_type = $type;
    else
      $this->_type = (string)$type;
  }

  // todo: virer le $from, il existe dans $fonction
  protected function get_type()
  {
    if( $this->_type instanceof DstyleDoc_Element_Interface or $this->_type instanceof DstyleDoc_Element_Class )
      return $this->_type;

    elseif( ($found = $this->converter->search_element($this->_type)) instanceof DstyleDoc_Element_Function )
    {
      if( ! $found->analysed ) $found->analyse();
      $types = array();
      foreach( $found->returns as $v )
      {
	$v = clone $v;
	$v->from = $found;
        $types[] = $v;
      }
      return $types;
    }

    elseif( $found instanceof DstyleDoc_Element_Interface or $found instanceof DstyleDoc_Element_Class )
    {
      // on met en cache ce qu'on a trouvé
      $this->_type = $found;
      return $found;
    }

    elseif( $found instanceof DstyleDoc_Element_Member )
    {
      if( ! $found->analysed ) $found->analyse();
      $types = array();
      foreach( $found->types as $v )
      {
        $v = clone $v;
        $v->from = $found;
        $types[] = $v;
      }
      return $types;
    }

    elseif( in_array(strtolower($this->_type), $this->types) )
      return strtolower($this->_type);

    else
      return $this->_type;
  }

  // }}}
  // {{{ __construct()

  public function __construct( DstyleDoc_Converter $converter, $type )
  {
    parent::__construct( $converter );
    if( $type )
      $this->type = $type;
  }

  // }}}
  // {{{ $display

  protected function get_display()
  {
    return $this->converter->convert_display( implode(', ', $this->_types), $this );
  }

  // }}}
  // {{{ $convert

  protected function get_convert()
  {
    return $this->converter->convert_type( $this );
  }

  // }}}
}

/**
 * Classe d'un element de type retour.
 */
class DstyleDoc_Element_Return extends DstyleDoc_Element_Type
{
  // {{{ $convert

  protected function get_convert()
  {
    return $this->converter->convert_return( $this );
  }

  // }}}
}

// vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 fileformat=unix foldmethod=marker encoding=utf8 setlocal noendofline binary
?>