<?php

require_once( 'converter.template_lite.php' );

/**
 */
class DstyleDoc_Converter_FirstStyle extends DstyleDoc_Converter_TemplateLite
{
  // {{{ write_all_files(), display_on_file()

  protected function write_all_files()
  {
    foreach( $this->files as $file )
    {
      $this->tpl->assign( 'file', $file );
      $this->write( 'file.tpl', $tmp = $this->destination_dir.'/'.$file->id.'.html' );
      DstyleDoc::log( sprintf( 'file: %s write to %s', $file->name, $tmp ) );
    }

  }

  protected function display_on_file()
  {
    if( ! empty($_GET['file']) and $found = $this->file_exists($_GET['file']) )
      $this->page_file( $found );
    elseif( ! empty($_GET['class']) and @list($file,$class) = explode(',',$_GET['class']) and $found = $this->class_exists($class) )
      $this->page_class( $found );
    elseif( ! empty($_GET['method']) and @list($file,$class,$method) = explode(',',$_GET['method']) and $found = $this->method_exists($class,$method) )
      $this->page_method( $found );
    elseif( ! empty($_GET['function']) and @list($file,$function) = explode(',',$_GET['function']) and $found = $this->function_exists($function) )
      $this->page_function( $found );
    else
      $this->page_home();
  }

  // }}}

  // {{{ convert_all()

  public function convert_all()
  {
    if( isset($this->destination_dir) )
      $this->write_all_files();
    else
      $this->display_on_file();
  }

  // }}}

  // {{{ page_home()

  protected function page_home()
  {
    $this->write( 'home.tpl' );
  }

  // }}}
  // {{{ page_file()

  protected function page_file( DstyleDoc_Element_File $file )
  {
    $this->tpl->assign( 'file', $file );
    $this->write( 'file.tpl' );
  }

  // }}}
  // {{{ page_class()

  protected function page_class( DstyleDoc_Element_Class $class )
  {
    $this->tpl->assign( 'class', $class );
    $this->write( 'class.tpl' );
  }

  // }}}
  // {{{ page_method()

  protected function page_method( DstyleDoc_Element_Method $method )
  {
    $this->tpl->assign( 'method', $method );
    $this->write( 'method.tpl' );
  }

  // }}}
  // {{{ page_function()

  protected function page_function( DstyleDoc_Element_Function $function )
  {
    $this->tpl->assign( 'function', $function );
    $this->write( 'function.tpl' );
  }

  // }}}

  // {{{ hie()

  static function hie()
  {
    return new self;
  }

  // }}}
}

?>
