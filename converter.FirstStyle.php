<?php

require_once( 'converter.template_lite.php' );

/**
 * Propose une présentation à base de skins CSS
 */
class DstyleDoc_Converter_FirstStyle extends DstyleDoc_Converter_TemplateLite
{
  // {{{ write_all_files(), display_on_file()

  protected function write_all_files()
  {
    foreach( $this->files as $file )
    {
      $this->tpl->assign( 'file', $file );
      $writed = $this->write( 'file.tpl', $tmp = $this->destination_dir.'/'.$file->id.'.html' );
      if( $writed )
        DstyleDoc::log( sprintf( 'file: %s write to <strong>%s</strong>', $file->name, $tmp ), true );
      else
	DstyleDoc::warning( sprintf( 'cant write documentation file %s to <strong>%s</strong>', $file->name, $tmp ), true );
    }

  }

  protected function display_on_file()
  {
    if( ! empty($_GET['file']) and $found = $this->file_exists($_GET['file']) )
    {
      $this->tpl->assign( 'file', $found );
      $this->write( 'file.tpl' );
    }

    elseif( ! empty($_GET['class']) and @list($file,$class) = explode(',',$_GET['class']) and $found = $this->class_exists($class) )
    {
      $this->tpl->assign( 'class', $found );
      $this->write( 'class.tpl' );
    }

    elseif( ! empty($_GET['method']) and @list($file,$class,$method) = explode(',',$_GET['method']) and $found = $this->method_exists($class,$method) )
    {
      $this->tpl->assign( 'method', $found );
      $this->write( 'method.tpl' );
    }

    elseif( ! empty($_GET['function']) and @list($file,$function) = explode(',',$_GET['function']) and $found = $this->function_exists($function) )
    {
      $this->tpl->assign( 'function', $found );
      $this->write( 'function.tpl' );
    }

    else
      $this->write( 'home.tpl' );
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
  // {{{ $destination_dir

  protected $_destination_dir = null;

  /**
   * Todo:
   *   Prévoir de la création de dossier
   *   et de la coorection de '/' '\'.
   */
  public function destination_dir( $path )
  {
    if( is_readable((string)$path) and is_dir((string)$path) )
    {
      $this->_destination_dir = (string)$path;
    }
    else
      throw new InvalidArgumentException('invalid path for 1st parameter send to: '.__FUNCTION__);

    return $this;
  }

  protected function isset_destination_dir()
  {
    return $this->_destination_dir;
  }

  protected function get_destination_dir()
  {
    return $this->_destination_dir;
  }

  // }}}
  // {{{ write()

  /**
   * Todo: trouver un moyen pour le charset
   * Todo: vérifier le fichier et le dossier.
   */
  protected function write( $template, $to = null )
  {
    if( ! is_null($to) )
    {
      if( ( file_exists($to) and is_writable($to) )
	or is_writable(dirname($to)) )
      {
	file_put_contents( $to, $this->tpl->fetch($template) );
	return true;
      }
      else
	return false;
    }
    else
    {
      if( ! headers_sent() ) header( 'Content-type: text/html; charset=utf-8' );
      $this->tpl->display( $template );
      return true;
    }
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
