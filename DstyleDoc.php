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

      static $f = 0;
      $ff = (++$f%2)?'BurlyWood':'Goldenrod';
      $s = htmlentities($source); $c = get_class($current);
      echo <<<HTML
<div style='clear:left;float:left;background:Wheat;padding:1px 3px'>$call</div>
<div style='float:right;color:white;background:Brown;padding:1px 3px'>{$c}</div>
<div style='background:{$ff};color:SaddleBrown;padding:1px 3px;'>{$s}</div>
<div style='clear:both'></div>
HTML;

      // processing token
      $current = call_user_func( array('DstyleDoc_Token_'.$call,'hie'), $converter, $current, $source, $file, $line );

      if( ! $current instanceof DstyleDoc_Token_Custom )
        throw new UnexpectedValueException;
      
      //var_dump( $current );
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
   * Retourne la liste des classes appartenant à un fichier donnée.
   * Return:
   *    array(DstyleDoc_Element_Class) = Un tableau de classe.
   */
  public function get_file_classes( DstyleDoc_Element_File $file );

  // }}}
  // {{{ convert_all()

  /**
   * Converti tous elements.
   */
  function convert_all();

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
   * Convertie un lien vers un element.
   */

  // }}}
}

/**
 * Convertisseur abstrait
 */
abstract class DstyleDoc_Converter extends DstyleDoc_Properties implements DstyleDoc_Converter_Convert
{
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
        if( $found = ($file->name == $name or $file === $name) or false === next($this->_files) )
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

  protected $_functions = array();

  protected function set_function( $name )
  {
    $found = false;
    if( ! empty($name) and count($this->_functions) )
    {
      reset($this->_functions);
      while( true)
      {
        $function = current($this->_functions);
        if( $found = ($function->name == $name or $function === $name) or false === next($this->_functions) )
          break;
      }
    }

    if( ! $found )
    {
      if( $name instanceof DstyleDoc_Element_Function )
        $this->_functions[] = $name;
      else
        $this->_functions[] = new DstyleDoc_Element_Function( $this, $name );
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
        $this->_methods[] = new DstyleDoc_Element_Method( $this, $name );
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
      if( $class->file = $file )
        $classes[] = $class;
    return $classes;
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
