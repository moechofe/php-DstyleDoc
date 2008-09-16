<?php

/**
 * Script principale de DstyleDoc.
 */

// {{{ properties class

/**
 * Classe de prise en charge des surcharges des membres.
 * Permet d'utiliser facilement des getter, setter, issetter, unsetter et caller.
 */
class DstyleDoc_Properties
{
  protected function __get( $property )
  {
    if( $property === '__class' )
      return get_class( $this );

    elseif( ! method_exists($this,'get_'.(string)$property) or ! is_callable( array($this,'get_'.(string)$property) ) )
      throw new BadPropertyException($this, (string)$property);

    return call_user_func( array($this,'get_'.(string)$property) );
  }

  protected function __set( $property, $value )
  {
    if( ! method_exists($this,'set_'.(string)$property) or ! is_callable( array($this,'set_'.(string)$property) ) )
      throw new BadPropertyException($this, (string)$property);

    call_user_func( array($this,'set_'.(string)$property), $value );
  }

  protected function __isset( $property )
  {
    if( ! method_exists($this,'isset_'.(string)$property) or ! is_callable( array($this,'isset_'.(string)$property) ) )
      throw new BadPropertyException($this, (string)$property);

    return call_user_func( array($this,'isset_'.(string)$property) );
  }

  protected function __unset( $property )
  {
    if( ! method_exists($this,'unset_'.(string)$property) or ! is_callable( array($this,'unset_'.(string)$property) ) )
      throw new BadPropertyException($this, (string)$property);

    call_user_func( array($this,'unset_'.(string)$property) );
  }

  protected function __call( $method, $arguments )
  {
    if( ! method_exists($this,'call_'.(string)$method) or ! is_callable( array($this,'call_'.(string)$method) ) )
      throw new BadMethodCallException;

    return call_user_func_array( array($this,'call_'.(string)$method), $arguments );
  }
}

// }}}

require_once 'process.tokens.php';
require_once 'process.elements.php';
require_once 'process.analysers.php';
require_once 'process.descriptables.php';

/**
 * Classe de control de DstyleDoc.
 * La classe DstyleDoc permet de configurer et de lancer un processus de génération de documentation.
 */
class DstyleDoc extends DstyleDoc_Properties
{
  // {{{ log()

  /**
   * Envoie un message de log sur la sortie standard.
   * Params:
   *    string,numeric,array ... = Une chaîne de caractère, un nombre ou un tableau de pairs clefs/valeurs à afficher.
   * Syntax:
   *    ... = Un nombre infinie de paramètre à afficher.
   */
  static public function log()
  {
    $args = func_get_args();
    foreach( $args as $arg )
      if( is_string($arg) or is_numeric($arg) )
        echo $arg;
      elseif( is_array($arg) )
        foreach( $arg as $key => $value )
          echo "<strong>$key: </strong> $value, ";
    $last = array_pop($args);
    if( is_bool($last) and $last )
      echo "<br />";
  }

  // }}}
  // {{{ $sources

  protected $_sources = array();

  protected function set_source( $files )
  {
    if( file_exists((string)$files) and is_file((string)$files) and is_readable((string)$files) )
      $this->_sources[] = (string)$files;
    elseif( is_array($files) or $files instanceof Iterator )
      foreach( $files as $file )
        $this->source = $file;
  }
  
  protected function get_sources()
  {
    return $this->_sources;
  }

  // }}}
  // {{{ analyse_all()

  protected function analyse_all( DstyleDoc_Converter $converter )
  {
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
    foreach( token_get_all(file_get_contents($file)) as $token )
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
            $d = strlen($current->open_tag->documentation);
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
      {
        var_dump($save);
        throw new UnexpectedValueException;
      }

    }
  }

  // }}}
  // {{{ source()

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
    //    d( $converter )->d6;
    $converter->dsd = $this;
    $this->analyse_all( $converter );
    $converter->convert_all();
    return $this;
  }

  // }}}  // {{{ hie()

  static public function hie()
  {
    return new self;
  }

  // }}}
  // {{{ $config

  protected $_config = array(
   
    'dstyledoc' => true,
    'version' => true,
    'history' => true,
    'params' => true,
    'returns' => true,
    'package' => true,
    'throws' => true,
    'syntax' => true,
    'type' => true,
    'since' => true,

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
    if( substr($method,0,7)==='enable_')
    {
      $this->_config[ substr($property,7) ] = $params;
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
  // {{{ method_exists()

  /**
   * Renvoie une méthode si elle existe.
   * Params:
   *    string $class = Le nom de la classe ou de l'interface.
   *    DstyleDoc_Element_Class, DstyleDoc_Element_Interface $class = L'instance de la classe ou de l'interface.
   *    string $member = Le nom de la méthode.
   * Returns:
   *    DstyleDoc_Element_Function = L'instance de la fonction en cas de succès.
   *    false = En cas d'échèc.
   */
  function method_exists( $class, $method );

  // }}}
  // {{{ function_exists()

  /**
   * Renvoie une fonction si elle existe.
   * Params:
   *    string $function = Le nom de la fonction.
   * Returns:
   *    DstyleDoc_Element_Function = L'instance de la fonction en cas de succès.
   *    false = En cas d'échèc.
   */
  function function_exists( $function );

  // }}}
  // {{{ member_exists()

  /**
   * Renvoie un membre si il existe.
   * Params:
   *    string $class = Le nom de la classe ou de l'interface.
   *    DstyleDoc_Element_Class, DstyleDoc_Element_Interface $class = L'instance de la classe ou de l'interface.
   *    string $member = Le nom du membre.
   * Returns:
   *    DstyleDoc_Element_Member = L'instance du membre en cas de succès.
   *    false = En cas d'échèc.
   */
  function member_exists( $class, $member );

  // }}}
  // {{{ constant_exists()

  /**
   * Renvoie une constante si elle existe.
   * Params:
   *    string $class = Le nom de la classe ou de l'interface.
   *    DstyleDoc_Element_Member, DstyleDoc_Element_Interface $class = L'instance de la classe ou de l'interface.
   *    null $class = La constante est globale.
   *    string $constant = Le nom de la constante.
   * Returns:
   *    DstyleDoc_Element_Constant = L'instance de la constance en case de succès.
   *    false = En cas d'èchec.
   */
  function constant_exists( $class, $constant );

  // }}}
  // {{{ get_classes()

  // }}}
  // {{{ get_all_classes()

  /**
   * Renvoie la liste de toutes les classes.
   * Returns:
   *   array(DstyleDoc_Element_Class) = Un tableau de classe.
   */
  function get_all_classes();

  // }}}
  // {{{ get_file_classes()

  /**
   * Renvoie la liste des classes appartenant à un fichier donnée.
   * Params:
   *    $file = L'instance d'un élément de fichier.
   * Return:
   *    array(DstyleDoc_Element_Class) = Un tableau de classe.
   */
  function get_file_classes( DstyleDoc_Element_File $file );

  // }}}
  // {{{ get_file_interfaces()

  /**
   * Renvoie la liste des interfaces appartenant à un fichier donnée.
   * Params:
   *    $file = L'instance d'un élément de fichier.
   * Returns:
   *    array(DstyleDoc_Element_Interface) = Un tableau d'interface.
   */
  function get_file_interfaces( DstyleDoc_Element_File $file );

  // }}}
  // {{{ get_file_methods()

  /**
   * Renvoie la liste des méthodes appartenant à un fichier donnée.
   * Params:
   *    $file = L'instance d'un élément de fichier.
   * Returns:
   *    array(DstyleDoc_Element_Method) = Un tableau de méthodes.
   */
  function get_file_methods( DstyleDoc_Element_File $file );

  // }}}
  // {{{ get_file_functions()

  /**
   * Renvoie la liste des functions appartenant à un fichier donnée.
   * Params:
   *    $file = L'instance d'un élément de fichier.
   * Returns:
   *    array(DstyleDoc_Element_Function) = Un tableau de fonctions.
   */
  function get_file_functions( DstyleDoc_Element_File $file );

  // }}}
  // {{{ get_file_members()

  /**
   * Renvoie la liste des membres appartenant à un fichier donnée.
   * Params:
   *    $file = L'instance d'un élément de fichier.
   * Returns:
   *    array(DstyleDoc_Element_Member) = Un tableau de membres.
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
   *    $file = L'instance du fichier à documenter.
   * Returns:
   *    mixed = La documentation du fichier ou pas.
   */
  function convert_file( DstyleDoc_Element_File $file );

  // }}}
  // {{{ convert_class()

  /**
   * Génère la documentation d'une classe.
   * Params:
   *    $class = L'instance de la classe à documenter.
   * Returns:
   *    mixed = La documentation de la classe ou pas.
   */
  function convert_class( DstyleDoc_Element_Class $class );

  // }}}
  // {{{ convert_interface()

  /**
   * Génère la documentation d'un interface.
   * Params:
   *    $interface = L'instance de l'interface à documenter.
   * Returns:
   *    mixed = La documentation de l'interface ou pas.
   */
  function convert_interface( DstyleDoc_Element_Interface $interface );

  // }}}
  // {{{ convert_function()

  /**
   * Génère la documentation d'une fonction.
   * Params:
   *    $function = L'instance de la fonction à documenter.
   * Returns:
   *    mixed = La documentation de la fonction ou pas.
   */
  function convert_function( DstyleDoc_Element_Function $function );

  // }}}
  // {{{ convert_method()

  /**
   * Génère la documentation d'une méthode.
   * Params:
   *    $method = L'instance de la méthode à documenter.
   * Returns:
   *    mixed = La documentation de la fonction ou pas.
   */
  function convert_method( DstyleDoc_Element_Method $method );

  // }}}
  // {{{ convert_description()

  /**
   * Converti la description longue.
   * Params:
   *    array(string) $description = Toutes les lignes de la description longue.
   *    $element = L'élément concerné par la déscription courte.
   * Returns:
   *    mixed = Dépends du convertisseur.
   */
  function convert_description( $description, DstyleDoc_Custom_Element $element );

  // }}}
  // {{{ convert_title()

  /**
   * Convertie la déscription courte.
   * Params:
   *    string $title = La ligne de description courte.
   *    $element = L'élément concerné par la déscription courte.
   * Returns:
   *    mixed = Dépends du convertisseur.
   */
  function convert_title( $title, DstyleDoc_Element $element );

  // }}}
  // {{{ convert_link()

  /**
   * Converti et renvoie un lien vers un élément.
   * Params:
   *    mixed $id = L'identifiant unique de l'élément retourné par convert_id().
   *    mixed $name = Le nom d'affichage de l'élément retourné par convert_name().
   *    $element = L'élément vers lequel se destine le lien.
   * Returns:
   *    mixed = Dépends du convertisseur.
   */
  function convert_link( $id, $name, DstyleDoc_Element $element );

  // }}}
  // {{{ convert_id()

  /**
   * Converti et renvoie l'identifiant unique d'un élément.
   * Params:
   *    string $id = L'identifiant unique de l'élément.
   *    array $id = Un tableau contenant la liste des identifiants de l'élément et celui de ses parents.
   *    $element = L'élément vers lequel se destine le lien.
   * Returns:
   *    string = L'identifiant convertie de l'élément.
   */
  function convert_id( $id, DstyleDoc_Element $element );

  // }}}
  // {{{ convert_display()

  /**
   * Convertie et renvoie le nom d'affichage d'un élément.
   * Params:
   *    $name = Le nom de l'élément à afficher.
   *    $element = L'élément vers lequel se destine le lien.
   * Returns:
   *    mixed = Dépends du convertisseur.
   */
   function convert_display( $name, DstyleDoc_Element $element );   

  // }}}
  // {{{ convert_syntax()

  /**
   * Génère la documentation d'une syntaxe d'une fonction.
   * Params:
   *    $syntax = L'instance de la syntaxe.
   * Returns:
   *    mixed = La documentation de la syntaxe ou pas.
   */
  function convert_syntax( DstyleDoc_Element_Syntax $syntax );

  // }}}
  // {{{ convert_param()

  /**
   * Génère la documentation d'un paramètre d'une fonction.
   * Params:
   *    $param = L'instance du paramètre.
   * Returns:
   *    mixed = La documentation de la syntaxe ou pas.
   */
  function convert_param( DstyleDoc_Element_Param $param );

  // }}}
  // {{{ convert_return()

  /**
   * Génère la documentation d'une valeur de retour d'une fonction.
   * Params:
   *    $param = L'instance de la valeur de retour.
   * Returns:
   *    mixed = La documentation de la valeur de retour ou pas.
   */
  function convert_return( DstyleDoc_Element_Return $param );

  // }}}
  // {{{ convert_type()

  /**
   * Génére la documentation d'un type de valeur.
   * Params:
   *    $type = L'instance du type.
   * Returns:
   *    mixed = La documentation du type.
   */
  function convert_type( DstyleDoc_Element_Type $type );

  // }}}
  // {{{ convert_exception()

  /**
   * Génère la documentation d'un exception lancé par une fonction.
   * Params:
   *    $exception = L'instance de l'exception lancé par l'exception.
   * Returns:
   *    mixed = La documentation de l'exception lancé par l'exception ou pas.
   */
  function convert_exception( DstyleDoc_Element_Exception $exception );

  // }}}
  // {{{ convert_member()

  /**
   * Génère la documentation d'un membre d'une classe.
   * Params:
   *    $member = L'instance du membre d'une classe.
   * Returns:
   *    mixed = La documentation du membre de la classe ou pas.
   */
  function convert_member( DstyleDoc_Element_Member $member );

  // }}}
  // {{{ convert_text()

  /**
   * Converti une portion de texte contenu dans une description.
   * Params:
   *    string $text = La portion de texte à convertir.
   */
  function convert_text( $text );

  // }}}
  // {{{ convert_php()

  /**
   * Converti du code PHP.
   * Params:
   *    string $code = Le cde PHP à convertir.
   */
  function convert_php( $code );

  // }}}
  // {{{ search_element()

  /**
   * Recherche un élément à partir de sa syntax.
   * Params:
   *    string $string = Une syntaxe d'un membre, d'une constante, d'une fonction ou d'un classe.
   * Returns:
   *    DstyleDoc_Element = L'instance de l'élément en cas de succès.
   *    false = En cas d'échec.
   */
  function search_element( $string );

  // }}}
  // {{{ come_accross_elements()

  /**
   * Recherche dans un text des éventuels mots ou expression correspondant à des élements existants.
   */
  function come_accross_elements( $string, DstyleDoc_Custom_Element $element );

  // }}}
}

/**
 * Convertisseur abstrait
 * Todo:
 *    - reporter set_method() dans les autres methode de ce genre.
 * Todo: gérer les constantes
 */
abstract class DstyleDoc_Converter extends DstyleDoc_Properties implements ArrayAccess
{
  // {{{ $dsd

  protected $_dsd = null;

  protected function set_dsd( DstyleDoc $dsd )
  {
    $this->_dsd = $dsd;
  }

  protected function get_dsd()
  {
    return $this->_dsd;
  }

  // }}}
  //  {{{ $constants

  protected $_constants = array();

  // }}}
  // {{{ $files

  protected $_files = array();

  protected function set_file( $file )
  {
    $found = false;
    if( ! empty($file) and count($this->_files) )
    {
      reset($this->_files);
      while( true)
      {
        $current = current($this->_files);
        if( $found = ( (is_object($file) and $current === $file)
          or (is_string($file) and $current->file === strtolower($file)) ) or false === next($this->_files) )
          break;
      }
    }

    if( ! $found )
    {
      if( $file instanceof DstyleDoc_Element_File )
        $this->_files[] = $file;
      else
        $this->_files[] = new DstyleDoc_Element_File( $this, $file );
      end($this->_files);
    }
  }

  // Todo: copier sur ce model = pas d'ajout de DstyleDoc_Element_File dans $_files[]
  protected function get_file()
  {
    if( ! count($this->_files) )
      return new DstyleDoc_Element_File( $this, null );
    else
      return current($this->_files);
  }
 
  protected function get_files()
  {
    return $this->_files;
  }

  // }}}
  // {{{ $classes

  protected $_classes = array();

  protected function set_class( $name )
  {
    $found = false;
    if( ! empty($name) and count($this->_classes) )
    {
      reset($this->_classes);
      while( true)
      {
        $class = current($this->_classes);
        if( $found = ($class->name == $name or $class === $name) or false === next($this->_classes) )
          break;
      }
    }

    if( ! $found )
    {
      if( $name instanceof DstyleDoc_Element_Class )
        $this->_classes[] = $name;
      else
        $this->_classes[] = new DstyleDoc_Element_Class( $this, $name );
      end($this->_classes);
    }
  }

  protected function get_class()
  {
    if( ! count($this->_classes) )
    {
      $this->_classes[] = new DstyleDoc_Element_Class( $this, null );
      return end($this->_classes);
    }
    else
      return current($this->_classes);
  }
 
  protected function get_classes()
  {
    return $this->_classes;
  }

  // }}}
  // {{{ $interfaces

  protected $_interfaces = array();

  protected function set_interface( $name )
  {
   $found = false;
    if( ! empty($name) and count($this->_interfaces) )
    {
      reset($this->_interfaces);
      while( true)
      {
        $interface = current($this->_interfaces);
        if( $found = ($interface->name == $name) or false === next($this->_interfaces) )
          break;
      }
    }

    if( ! $found )
    {
      $this->_interfaces[] = new DstyleDoc_Element_Interface( $this, $name );
      end($this->_interfaces);
    }
  }

  protected function get_interface()
  {
    if( ! count($this->_interfaces) )
    {
      $this->_interfaces[] = new DstyleDoc_Element_Interface( $this, null );
      return end($this->_interfaces);
    }
    else
      return current($this->_interfaces);
  }

  protected function get_interfaces()
  {
    return $this->_interfaces;
  }

  // }}}
  // {{{ $functions

  /**
   * La listes des instances des fonctions définies.
   * Types:
   *    array(DstyleDoc_Element_Function) = Un tableau d'instance de DstyleDoc_Element_Function.
   */
  protected $_functions = array();

  protected function set_function( $function )
  {
    $found = false;
    if( ! empty($function) and count($this->_functions) )
    {
      reset($this->_functions);
      while( true)
      {
        $current = current($this->_functions);
        if( $found = ( (is_object($function) and $current === $function)
          or (is_string($function) and $current->name === $function) ) or false === next($this->_functions) )
          break;
      }
    }

    if( ! $found )
    {
      if( $function instanceof DstyleDoc_Element_Function )
        $this->_functions[] = $function;
      else
        $this->_functions[] = new DstyleDoc_Element_Function( $this, $function );
      end($this->_functions);
    }
  }
  
  protected function get_function()
  {
    if( ! count($this->_functions) )
    {
      $this->_functions[] = new DstyleDoc_Element_Function( $this, null );
      return end($this->_functions);
    }
    else
      return current($this->_functions);
  }

  protected function get_functions()
  {
    return $this->_functions;
  }

  // }}}
  // {{{ $methods

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
  // {{{ file_exists()

  /**
   * Renvoie une fonction si elle existe.
   * Params:
   *    string $file = Le nom de la fonction à chercher.
   * Returns:
   *    DstyleDoc_Element_Function = L'instance de la fonction en cas de succès.
   *    false = En cas d'échèc.
   */
  public function file_exists( $file )
  {
    foreach( $this->_files as $value )
    {
      if( strtolower($value->file) === strtolower($file) )
        return $value;
    }
    return false;
  }

  // }}}
  // {{{ class_exists()

  /**
   * Renvoie une classe si elle existe.
   * Params:
   *    string $class = Le nom de la classe à chercher.
   * Returns:
   *    DstyleDoc_Element_Class = L'instance de la classe en cas de succès.
   *    false = En cas d'échèc.
   */
  public function class_exists( $class )
  {
    foreach( $this->_classes as $value )
    {
      if( strtolower($value->name) === strtolower((string)$class) )
        return $value;
    }
    return false;
  }

  // }}}
  // {{{ interface_exists()

  // Todo: propager la vérif strtolower sur les autres fonction de ce type
  /**
   * Renvoie une interface si elle existe.
   * Params:
   *    string $interface = Le nom de la interface à chercher.
   * Returns:
   *    DstyleDoc_Element_Interface= L'instance de la interface en cas de succès.
   *    false = En cas d'échèc.
   */
  public function interface_exists( $interface )
  {
    foreach( $this->_interfaces as $value )
    {
      if( strtolower($value->name) === strtolower((string)$interface) )
        return $value;
    }
    return false;
  }

  // }}}
  // {{{ method_exists()

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

  public function function_exists( $function )
  {
    foreach( $this->_functions as $value )
    {
      if( strtolower($value->name) === strtolower($function) )
        return $value;
    }
    return false;
  }

  // }}}
  // {{{ member_exists()

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

?>
