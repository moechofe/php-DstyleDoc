<?php

abstract class DstyleDoc_Custom_Element extends DstyleDoc_Properties
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

  protected function set_description( $description )
  {
    $this->_descriptions[] = $description;
  }

  protected function get_description()
  {
    return $this->converter->convert_description( $this->_descriptions );
  }

  // }}}
  // {{{ __construct()

  public function __construct( DstyleDoc_Converter $converter )
  {
    $this->converter = $converter;
  }

  // }}}
  // {{{ __toString()

  /**
   * Renvoie la documentation de l'élément.
   * Returns:
   *    string = La documentation de l'élément.
   */
  abstract public function __toString();

  // }}}
}

/**
 * Classe abstraite d'un Element.
 */
abstract class DstyleDoc_Element extends DstyleDoc_Custom_Element
{
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
  // {{{ $display

  /**
   * Renvoie la version affichable du nom de l'élément.
   * Returns:
   *    string = Le nom affichable de l'élément.
   */
  abstract protected function get_display();

  // }}}
  // {{{ $link

  /**
   * Renvoie un lien vers l'élément.
   * Returns:
   *    mixed = Dépends du convertisseur.
   */
  protected function get_link()
  {
    return $this->converter->convert_link( $this );
  }

  // }}}
  // {{{ analyse()

  public function analyse()
  {
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
        $s = get_class($current);
        echo <<<HTML
<div style='clear:left;float:left;color:white;background:SteelBlue;padding:1px 3px'>{$c}</div>
<div style='background:DimGray;padding:1px 3px;'>{$s}</div>
<div style='clear:left;'></div>
HTML;
      }
      $result = array();
      $source = DstyleDoc_Analyser::remove_stars($source);
      foreach( $analysers as $analyser )
      {
          if( call_user_func( array($analyser,'analyse'), $current, $source, &$instance, &$priority ) )
            $result[$priority] = $instance;
      }
      if( $result )
      {
        ksort($result);
        $current = current($result);

      if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'doc')!==false )
        var_dump( $result );

        if( $current instanceof DstyleDoc_Analyser )
          $current = $current->apply( $this );
      }
    }

    foreach( $analysers as $analyser )
      call_user_func( array($analyser,'finalize'), $this );

    $this->analysed = true;
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
    return $this->converter->convert_description( $copy );
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
    return $this->converter->convert_title( $result );
  }

  // }}}
}

/**
 * Classe abstraite d'un Element possèdant un lien dans un fichier.
 */
abstract class DstyleDoc_Element_Filed extends DstyleDoc_Element_Titled
{
  // {{{ $file

  protected $_file = null;

  protected function set_file( $file )
  {
    if( is_string($file) and ($found = $this->converter->file_exists($file)) )
      $this->_file = $found;
    elseif( $file instanceof DstyleDoc_Element_File )
      $this->_file = $file;
    else
      $this->_file = new DstyleDoc_Element_File( $this->converter, $file );
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
 * Classe abstraite d'un Element possèdant un lien dans un fichier et un nom.
 */
abstract class DstyleDoc_Element_Filed_Named extends DstyleDoc_Element_Filed
{
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
  // {{{ $file

  protected $_file = '';

  protected function set_file( $file )
  {
    $this->_file = (string)$file;
  }

  protected function get_file()
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
    return $this->converter->convert_id( $this->file );
  }

  // }}}
  // {{{ $display

  protected function get_display()
  {
    return $this->converter->convert_display( $this->name );
  }

  // }}}
  // {{{ __toString() 

  public function __toString()
  {
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
 * Classe d'un element qui contient des fonctions
 */
abstract class DstyleDoc_Element_Methoded_Filed_Named extends DstyleDoc_Element_Filed_Named
{
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


  protected function get_methods()
  {
    return $this->_methods;
  }

  // }}}
}

/**
 * Classe d'un element de type classe.
 */
class DstyleDoc_Element_Class extends DstyleDoc_Element_Methoded_Filed_Named
{
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
    $this->_parent = (string)$parent;
  }

  protected function get_parent()
  {
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
  // {{{ $id

  protected function get_id()
  {
    return $this->converter->convert_id( array($this->file->file, $file->name) );
  }

  // }}}
  // {{{ $display

  protected function get_display()
  {
    return $this->converter->conveter_display( $this->name );
  }

  // }}} 
  // {{{ __toString()

  public function __toString()
  {
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
    $this->_version = $version;
  }

  protected function get_version()
  {
    return $this->_version;
  }

  // }}}
  // {{{ __toString()

  public function __toString()
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
  // {{{ $id

  protected function get_id()
  {
    return $this->converter->convert_id( array($this->file->file, $file->name) );
  }

  // }}}
  // {{{ $display

  protected function get_display()
  {
    return $this->converter->conveter_display( $this->name );
  }

  // }}} 
  // {{{ __toString()

  public function __toString()
  {
    return $this->converter->convert_interface( $this );
  }

  // }}}
}

/**
 * Classe d'un element de type fonction.
 */
class DstyleDoc_Element_Function extends DstyleDoc_Element_Filed_Named
{
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
   * Le paramètre ainsi séléctionné peut être récupérer avec get_param().
   * Params:
   *  $param = Le nom de la variable existante ou qui sera créer.
   */
  protected function set_param( $param ) 
  {
    $found = false;
    if( ! empty($param) and count($this->_params) )
    {
      reset($this->_params);
      while( true)
      {
        $value = current($this->_params);
        if( $value->var == $param )
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

  protected $_returns = array();

  protected function set_return( $return )
  {
    if( $return instanceof DstyleDoc_Element_Return )
      $this->_returns[] = $return;
    else
      $this->_returns[] = new DstyleDoc_Element_Return( $this->converter, $return );
  }

  protected function get_return()
  {
    if( ! count($this->_returns) )
      $this->_returns[] = new DstyleDoc_Element_Return( $this->converter, null );

    return end($this->_returns);
  }

  // }}}
  // {{{ $exceptions

  protected $_exceptions = array();

  protected function set_exception( $name )
  {
    $found = false;
    if( ! empty($name) and count($this->_exceptions) )
    {
      reset($this->_exceptions);
      while( true)
      {
        $exception = current($this->_exceptions);
        if( $found = ($exception->name == $name) or false === next($this->_exceptions) )
          break;
      }
    }

    if( ! $found )
    {
      $this->_exceptions[] = new DstyleDoc_Element_Exception( $this->converter, $name );
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

  // }}}
  // {{{ $id

  protected function get_id()
  {
    return $this->converter->convert_id( array($this->file->file, $file->name) );
  }

  // }}}
  // {{{ $display

  protected function get_display()
  {
    return $this->converter->conveter_display( $this->name );
  }

  // }}} 
  // {{{ __toString

  public function __toString()
  {
    return $this->converter->conveter_function( $this );
  } 

  // }}}
}

/**
 * Classe d'un element de type fonction.
 */
class DstyleDoc_Element_Method extends DstyleDoc_Element_Function
{
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
  // {{{ $id

  protected function get_id()
  {
    return $this->converter->convert_id( array($this->file->file, $this->class->name, $this->name) );
  }

  // }}}
  // {{{ $display

  protected function get_display()
  {
    return $this->converter->convert_display( $this->class->name.($this->static?'::':'->').$this->name );
  }

  // }}} 
  // {{{ __toString()

  public function __toString()
  {
    return $this->converter->convert_method( $this );
  }

  // }}}
}

/**
 * Class d'un element de type syntaxe.
 */
class DstyleDoc_Element_Syntax extends DstyleDoc_Custom_Element
{
  // {{{ $syntax

  protected $_syntax = array();

  protected function set_syntax( $syntax ) 
  {
    $this->_syntax = (array)$syntax;
  }

  protected function get_syntax()
  {
    return $this->_syntax;
  }

  // }}}
  // {{{ __construct()

  public function __construct( DstyleDoc_Converter $converter, $syntax )
  {
    parent::__construct( $converter );
    $this->syntax = $syntax;
  }

  // }}}
  // {{{ __toString

  public function __toString()
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
    $this->_name = (string)$name;
  }

  protected function get_name()
  {
    return $this->_name;
  }

  // }}}
  // {{{ __construct()

  public function __construct( DstyleDoc_Converter $converter, $exception )
  {
    parent::__construct( $converter );
    $this->name = $exception;
  }

  // }}}
  // {{{ __toString

  public function __toString()
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

  protected $_types = '';

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
      $this->_types[] = (string)$type;
      $this->_types = array_unique($this->_types);
    }
  }

  // }}}
  // {{{ $var

  protected $_var = '';

  protected function set_var( $var ) 
  {
    $this->_var = (string)$var;
  }

  protected function get_var()
  {
    return $this->_var;
  }

  // }}}
  // {{{ $default

  protected $_default = '';

  protected function set_default( $default ) 
  {
    $this->_default = (string)$default;
  }

  protected function get_default()
  {
    return $this->_default;
  }

  // }}}
  // {{{ __construct()

  public function __construct( DstyleDoc_Converter $converter, $var )
  {
    parent::__construct( $converter );
    if( $var )
      $this->var = $var;
  }

  // }}}
  // {{{ __toString

  public function __toString()
  {
    return $this->converter->convert_param( $this );
  }

  // }}}
}

/**
 * Classe d'un element de type retour.
 */
class DstyleDoc_Element_Return extends DstyleDoc_Custom_Element
{
  // {{{ $type

  protected $_type = '';

  protected function set_type( $type )
  {
    $this->_type = (string)$type;
  }

  protected function get_type()
  {
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
  // {{{ __toString

  public function __toString()
  {
    return $this->converter->convert_return( $this );
  }

  // }}}
}

?>
