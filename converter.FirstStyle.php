<?php

require_once( 'converter.template_lite.php' );

/**
 */
class DstyleDoc_Converter_FirstStyle extends DstyleDoc_Converter_TemplateLite
{
  // {{{ convert_all()

  public function convert_all()
  {
    if( ! empty($_GET['file']) and $found = $this->file_exists($_GET['file']) )
      $this->page_file( $found );
    elseif( ! empty($_GET['class']) and @list($file,$class) = explode(',',$_GET['class']) and $found = $this->class_exists($class) )
      $this->page_class( $found );
    elseif( ! empty($_GET['method']) and @list($file,$class,$method) = explode(',',$_GET['method']) and $found = $this->method_exists($class,$method) )
      $this->page_method( $found );
    else
      $this->page_home();
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

  // {{{ hie()

  static function hie()
  {
    return new self;
  }

  // }}}
}

// vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 fileformat=unix foldmethod=marker encoding=utf8 setlocal noendofline binary
?>
