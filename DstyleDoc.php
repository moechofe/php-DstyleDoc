<?php

// {{{ properties class

class DstyleDoc_Properties
{
  protected function __get( $property )
  {
    if( ! is_callable( array($this,'get_'.(string)$property) ) )
      throw new BadPropertyException($this, (string)$property);

    return call_user_func( array($this,'get_'.(string)$property) );
  }

  protected function __set( $property, $value )
  {
    if( ! is_callable( array($this,'set_'.(string)$property) ) )
      throw new BadPropertyException($this, (string)$property);

    call_user_func( array($this,'set_'.(string)$property), $value );
  }

  protected function __isset( $property )
  {
    if( ! is_callable( array($this,'isset_'.(string)$property) ) )
      throw new BadPropertyException($this, (string)$property);

    return call_user_func( array($this,'isset_'.(string)$property) );
  }

  protected function __unset( $property )
  {
    if( ! is_callable( array($this,'unset_'.(string)$property) ) )
      throw new BadPropertyException($this, (string)$property);

    call_user_func( array($this,'unset_'.(string)$property) );
  }
}

// }}}

require_once 'process.tokens.php';
require_once 'process.elements.php';
require_once 'process.analysers.php';

class DstyleDoc extends DstyleDoc_Properties
{
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
      $this->analyse_file( $converter, $file );
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
<div style='float:left;background:Wheat;padding:1px 3px'>$call</div>
<div style='background:{$ff};color:SaddleBrown;padding:1px 3px;'>{$s}</div>
<div style='clear:both'></div>
HTML;
      }

      $save = $current;
      // processing token
      $current = call_user_func( array('DstyleDoc_Token_'.$call,'hie'), $converter, $current, $source, $file, $line );

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
    $this->analyse_all( $converter );
    $converter->convert_all();
    return $this;
  }

  // }}}
  // {{{ hie()

  static public function hie()
  {
    return new self;
  }

  // }}}
}

/**
 * Interface de base pour les converteurs.
 */
interface DstyleDoc_Converter_Convert
{
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
   *    array(DstyleDoc_Element_Function) = Un tablean de fonctions.
   */
  function get_file_functions( DstyleDoc_Element_File $file );

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
   */
  function convert_description( $description );

  // }}}
  // {{{ convert_title()

  /**
   * Convertie la description courte.
   * Params:
   *    string $title = La ligne de description courte.
   */
  function convert_title( $title );

  // }}}
  // {{{ convert_link()

  /**
   * Converti et renvoie un lien vers un élément.
   * Params:
   *    mixed $id = L'identifiant unique de l'élément retourné par convert_id().
   *    mixed $name = Le nom d'affichage de l'élément retourné par convert_name().
   * Returns:
   *    mixed = Dépends du convertisseur.
   */
  function convert_link( $id, $name );

  // }}}
  // {{{ convert_id()

  /**
   * Converti et renvoie l'identifiant unique d'un élément.
   * Params:
   *    string $id = L'identifiant unique de l'élément.
   *    array $id = Un tableau contenant la liste des identifiants de l'élément et celui de ses parents.
   * Returns:
   *    string = L'identifiant convertie de l'élément.
   */
  function convert_id( $id );

  // }}}
  // {{{ convert_display()

  /**
   * Convertie et renvoie le nom d'affichage d'un élément.
   * Params:
   *    $name = Le nom de l'élément à afficher.
   * Returns:
   *    mixed = Dépends du convertisseur.
   */
   function convert_display( $name );    

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
   *    $return = L'instance de le valeur de retour.
   * Returns:
   *    mixed = La documentation de la valeur de retour ou pas.
   */
  function convert_return( DstyleDoc_Element_Return $param );

  // }}}
}

/**
 * Convertisseur abstrait
 * Todo:
 *    - reporter set_method() dans les autres methode de ce genre.
 */
abstract class DstyleDoc_Converter extends DstyleDoc_Properties implements DstyleDoc_Converter_Convert
{
  // $constants

  protected $_constants = array();

  // }}}
  // {{{ $files

  protected $_files = array();

  protected function set_file( $name )
  {
    $found = false;
    if( ! empty($name) and count($this->_files) )
    {
      reset($this->_files);
      while( true)
      {
        $file = current($this->_files);
        if( $found = ($file->file == $name or $file === $name) or false === next($this->_files) )
          break;
      }
    }

    if( ! $found )
    {
      if( $name instanceof DstyleDoc_Element_File )
        $this->_files[] = $name;
      else
        $this->_files[] = new DstyleDoc_Element_File( $this, $name );
      end($this->_files);
    }
  }

  protected function get_file()
  {
    if( ! count($this->_files) )
    {
      $this->_files[] = new DstyleDoc_Element_File( $this, null );
      return end($this->_files);
    }
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
  // {{{ file_exists()

  public function file_exists( $file )
  {
    foreach( $this->_files as $value )
    {
      if( $value->file === $file )
        return $value;
    }
    return false;
  }

  // }}}
  // {{{ class_exists()

  public function class_exists( $class )
  {
    foreach( $this->_classes as $value )
    {
      if( $value->name === $class )
        return $value;
    }
    return false;
  }

  // }}}
  // {{{ interface_exists()

  public function interface_exists( $interface )
  {
    foreach( $this->_interfaces as $value )
    {
      if( $value->name === $interface )
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
