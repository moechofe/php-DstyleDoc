<?php

// todo: assign $_element for all convert_xxx() template

require_once( 'converter.HTML.php' );
require_once( 'libraries/template_lite/class.template.php' );

/**
 * Convertisseur qui utilise le moteur de template Template_Lite pour générer les pages de la documentation.
 */
abstract class DstyleDoc_Converter_TemplateLite extends DstyleDoc_Converter_HTML
{
  // {{{ convert_file()

  public function convert_file( DstyleDoc_Element_File $file )
  {
    $this->tpl->assign( '_file', $file );
    return $this->tpl->fetch( __CLASS__.':convert_file.tpl' );
  }

  // }}}
  // {{{ convert_class()

  public function convert_class( DstyleDoc_Element_Class $class )
  {
    $this->tpl->assign( '_class', $class );
    return $this->tpl->fetch( __CLASS__.':convert_class.tpl' );
  }

  // }}}
  // {{{ convert_interface()

  public function convert_interface( DstyleDoc_Element_Interface $interface )
  {
  }

  // }}}
  // {{{ convert_function()

  public function convert_function( DstyleDoc_Element_Function $function )
  {
    $this->tpl->assign( '_function', $function );
    return $this->tpl->fetch( __CLASS__.':convert_function.tpl' );
  }

  // }}}
  // {{{ convert_method()

  public function convert_method( DstyleDoc_Element_Method $method )
  {
    $this->tpl->assign( '_method', $method );
    return $this->tpl->fetch( __CLASS__.':convert_method.tpl' );
  }

  // }}}
  // {{{ convert_syntax()

  public function convert_syntax( DstyleDoc_Element_Syntax $syntax )
  {
    $this->tpl->assign( '_syntax', $syntax );
    return $this->tpl->fetch( __CLASS__.':convert_syntax.tpl' );
  }

  // }}}
  // {{{ convert_param()

  public function convert_param( DstyleDoc_Element_Param $param )
  {
  }

  // }}}
  // {{{ convert_return()

  public function convert_return( DstyleDoc_Element_Return $result )
  {
  }

  // }}}
  // {{{ convert_type()

  public function convert_type( DstyleDoc_Element_Type $type )
  {
  }

  // }}}
  // {{{ convert_exception()

  public function convert_exception( DstyleDoc_Element_Exception $exception )
  {
  }

  // }}}
  // {{{ convert_member()

  public function convert_member( DstyleDoc_Element_Member $member )
  {
  }

  // }}}

  // {{{ convert_link()

  public function convert_link( $id, $name, DstyleDoc_Element $element )
  {
    $this->tpl->assign( '_id', $id );
    $this->tpl->assign( '_name', $name );
    $this->tpl->assign( '_element', $element );
    $this->tpl->assign( '_type', strtolower(substr(get_class($element),18)) );
    return $this->tpl->fetch( __CLASS__.':link.tpl' );
  }

  // }}}
  // {{{ convert_title()

  public function convert_title( $title, DstyleDoc_Element $element )
  {
    $this->tpl->assign( '_title', $title );
    $this->tpl->assign( '_type', strtolower(substr(get_class($element),18)) );
    return $this->tpl->fetch( __CLASS__.':title.tpl' );
  }

  // }}}
  // {{{ convert_description()

  public function convert_description( $description, DstyleDoc_Custom_Element $element )
  {
    $this->tpl->assign( '_description', $description );
    $this->tpl->assign( '_type', strtolower(substr(get_class($element),18)) );
    return $this->tpl->fetch( __CLASS__.':description.tpl' );
  }

  // }}}
  // {{{ convert_id()

  public function convert_id( $id, DstyleDoc_Element $element )
  {
    if( is_array($id) )
      $id = implode(',', $id);

    return (string)$id;
  }

  // }}}
  // {{{ convert_display()

  public function convert_display( $name, DstyleDoc_Custom_Element $element )
  {
    $this->tpl->assign( array(
      '_name' => parent::convert_display( $name, $element ),
      '_type' => strtolower(substr(get_class($element),18)),
      '_element' => $element ) );
    return $this->tpl->fetch( __CLASS__.':display.tpl' );
  }

  // }}}

  // {{{ print_files_index()

  static public function print_files_index( $params, $tpl )
  {
    $tpl->assign( '_files', $tpl->_vars['_converter']->files );

    return $tpl->fetch( __CLASS__.':print_files_index.tpl' );
  }

  // }}}
  // {{{ print_classes_index()

  static public function print_classes_index( $params, Template_Lite $tpl )
  {
    if( isset($params['file']) and $params['file'] instanceof DstyleDoc_Element_File )
      $tpl->assign( array(
        '_file' => $params['file'],
        '_classes' => $params['file']->classes ) );
    elseif( isset($tpl->_vars['file']) and $tpl->_vars['file'] instanceof DstyleDoc_Element_File )
      $tpl->assign( array(
        '_file' => $tpl->_vars['file'],
        '_classes' => $tpl->_vars['file']->classes ) );
    else
      $tpl->trigger_error( 'unexists or non DstyleDoc_Element_Class "file" parameter send to {classes_index}' );

    return $tpl->fetch( __CLASS__.':print_classes_index.tpl' );
  }

  // }}}
  // {{{ print_methods_index()

  /**
   * Plug-in Template_Lite pour afficher la listes des méthodes d'une classes.
   * Affiche la liste des classes de la méthode passé en paramètre en utilisant le template "print_methods_index.tpl".
   * Cette fonction ne doit pas être appelé directement, elle doit être enregistrée en temps que plug-in Template_Lite grâce à la méthode Template_Lite::register_function().
   * Si le paramètre "class" n'est pas renseigné il sera determinté automatiquement.
   * Deux variables Template_Lite seront créer et pourront être utilisé dans le template "print_methods_index.tpl" : {$_class} et {$_methods}.
   * Params:
   *   array = Les paramètres envoyés par Template_Lite.
   *     [DstyleDoc_Element_Class "class"] = L'instance de la classe.
   * Returns:
   *   string = Le résultat du template "print_methods_index.tpl".
   */
  static public function print_methods_index( $params, Template_Lite $tpl )
  {
    if( isset($params['class']) and $params['class'] instanceof DstyleDoc_Element_Class )
      $tpl->assign( array(
        '_class' => $params['class'],
        '_methods' => $params['class']->methods ) );
    elseif( isset($tpl->_vars['class']) and $tpl->_vars['class'] instanceof DstyleDoc_Element_Class )
      $tpl->assign( array(
        '_class' => $tpl->_vars['class'],
        '_methods' => $tpl->_vars['class']->methods ) );
    else
      $tpl->trigger_error( 'unexists or non DstyleDoc_Element_Class "class" parameter send to {methods_index}' );

    return $tpl->fetch( __CLASS__.':print_methods_index.tpl' );
  }

  // }}}
  // {{{ print_functions_index()

  /**
   * Plug-in Template_Lite pour afficher la listes des function d'un fichier.
   * Affiche la liste des fonctions déclaré dans le ficher passé en paramètre en utilisant le template "print_functions_index.tpl".
   * Cette fonction ne doit pas être appelé directement, elle doit être enregistrée en temps que plug-in Template_Lite grâce à la méthode Template_Lite::register_function().
   * Si le paramètre "file" n'est pas renseigné il sera determinté automatiquement.
   * Deux variables Template_Lite seront créer et pourront être utilisé dans le template "print_methods_index.tpl" : {$_file} et {$_fonctions}.
   * Params:
   *   array = Les paramètres envoyés par Template_Lite.
   *     [DstyleDoc_Element_Class "class"] = L'instance de la classe.
   * Returns:
   *   string = Le résultat du template "print_methods_index.tpl".
   */
  static public function print_functions_index( $params, Template_Lite $tpl )
  {
    if( isset($params['file']) and $params['file'] instanceof DstyleDoc_Element_File )
      $tpl->assign( array(
        '_file' => $params['file'],
        '_functions' => $params['file']->functions ) );
    elseif( isset($tpl->_vars['file']) and $tpl->_vars['file'] instanceof DstyleDoc_Element_File )
      $tpl->assign( array(
        '_file' => $tpl->_vars['file'],
        '_functions' => $tpl->_vars['file']->functions ) );
    else
      $tpl->trigger_error( 'unexists or non DstyleDoc_Element_Class "file" parameter send to {functions_index}' );

    return $tpl->fetch( __CLASS__.':print_functions_index.tpl' );
  }

  // }}}

  // {{{ template_get_source()

  static public function template_get_source( $template, &$source, $tpl )
  {
    if( ! is_readable( $file = realpath( pathinfo(__FILE__,PATHINFO_FILENAME).'/'.$template ) ) or
      ! is_file( $file ) )
      throw new RuntimeException( "template: $template don't exists" );
    else
      $source = file_get_contents( $file );
    return true;
  }

  // }}}
  // {{{ template_get_timestamp()

  static public function template_get_timestamp( $template, &$timestamp, $tpl )
  {
    $timestamp = time();
    return true;
  }

  // }}}
  // {{{ template_get_secure()

  static public function template_get_secure( $template, $tpl )
  {
    return true;
  }

  // }}}
  // {{{ template_get_trusted()

  static public function template_get_trusted( $templaten, $tpl )
  {
    null;
  }

  // }}}

  // {{{ __construct()

  protected function __construct()
  {
    $this->tpl = new Template_Lite;
    $this->tpl->cache = false;
    // $this->tpl->compile_dir = sys_get_temp_dir();

    $this->tpl->assign_by_ref( '_converter', &$this );

    $this->tpl->register_resource( __CLASS__, array(
      array(__CLASS__, 'template_get_source'),
      array(__CLASS__, 'template_get_timestamp'),
      array(__CLASS__, 'template_get_secure'),
      array(__CLASS__, 'template_get_trusted') ) );

    $this->tpl->register_function( 'files_index', array($this,'print_files_index') );
    $this->tpl->register_function( 'classes_index', array($this,'print_classes_index') );
    $this->tpl->register_function( 'methods_index', array($this,'print_methods_index') );
    $this->tpl->register_function( 'functions_index', array($this,'print_functions_index') );
  }

  // }}}
  // {{{ template_dir

  public function template_dir( $path )
  {
    if( is_readable($path) and is_dir($path) )
    {
      $this->tpl->template_dir = $path;
      $this->tpl->assign( '_template_dir', substr(realpath($path),strlen($_SERVER['DOCUMENT_ROOT'])) );
      $this->tpl->compile_dir = $path.'/compiled';
    }
    else
      throw new InvalidArgumentException('invalid path fot 1st parameter send to: '.__FUNCTION__);

    return $this;
  }

  // }}}
  // {{{ config_dir

  public function config_dir( $path )
  {
    if( is_readable($path) and is_dir($path) )
      $this->tpl->config_dir = $path;
    else
      throw new InvalidArgumentException('invalid path fot 1st parameter send to: '.__FUNCTION__);

    return $this;
  }

  // }}}
  // {{{ config()

  public function config( $config )
  {
    if( is_array($config) )
      $this->tpl->assign_config( $config );

    return $this;
  }

  // }}}
  // {{{ $destination_dir

  protected $_destination_dir = null;

  protected function isset_destination_dir()
  {
    return $this->_destination_dir;
  }

  // }}}
  // {{{ $tpl

  protected $tpl = null;

  // }}}
  // {{{ do()

  // todo: trouver un moyen pour le charset
  protected function write( $template )
  {
    set_error_handler( array($this,'error_config_or_not') );
    if( isset($this->destination_dir) )
    {
    }
    else
    {
      if( ! headers_sent() ) header( 'Content-type: text/html; charset=utf-8' );
      $this->tpl->display( $template );
    }
    restore_error_handler();
  }

  // }}}
  // {{{ error_config_or_not()

  static public function error_config_or_not( $errno, $errstr, $errfile, $errline, $errcontext )
  {
    if( $errno == 8 and substr($errstr,0,15)=='Undefined index' and isset($errcontext['this']) and $errcontext['this'] instanceof Template_Lite and strpos($errfile,$errcontext['this']->compile_dir) !== false )
      echo '#'.substr($errstr,18).'#';
    else
      return false;
  }

  // }}}
}

// vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 fileformat=unix foldmethod=marker encoding=utf8 setlocal noendofline binary
?>
