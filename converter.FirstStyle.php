<?php

require_once( 'converter.template_lite.php' );

/**
 * Propose une présentation à base de skins CSS
 */
class DstyleDoc_Converter_FirstStyle extends DstyleDoc_Converter_TemplateLite
{
	// {{{ $browse

	protected $_browse_mode = true;

	protected function get_browse_mode()
	{
		return (boolean)$this->_browse_mode;
	}

	protected function set_browse_mode( $browse_mode )
	{
		$this->_browse_mode = (boolean)$browse_mode;
		$this->tpl->assign( 'browse_mode', $this->browse_mode );
	}

	// }}}
	// {{{ copy_skin()

	protected function copy_skin()
	{
		$skin_dir = $this->tpl->template_dir.'skins/'.$this->tpl->get_config_vars('skin');
		if( is_dir($skin_dir) and is_readable($skin_dir) )
			$this->copy_dir( $skin_dir, $this->destination_dir );
	}

	// }}}

	// {{{ convert_link()

	public function convert_link( $id, $name, DstyleDoc_Element $element )
	{
		$this->tpl->assign( '_id', $id );
		$this->tpl->assign( '_name', $name );
		$this->tpl->assign( '_element', $element );
		$this->tpl->assign( '_type', strtolower(substr(get_class($element),18)) );
		return $this->tpl->fetch( 'convert_link.tpl' );
	}

	// }}}

	// {{{ convert_all()

	public function convert_all()
	{
		if( ! $this->browse_mode )
		{
			$this->copy_skin();

			$this->write( 'home.tpl', $tmp = $this->destination_dir.'/index' );

			foreach( $this->files as $file )
			{
				$this->tpl->assign( 'file', $file );
				$this->tpl->assign( 'this', $file );
				$this->write( 'file.tpl', $tmp = $this->destination_dir.'/'.$file->id );
			}

			foreach( $this->classes as $class )
			{
				$this->tpl->assign( 'class', $class );
				$this->tpl->assign( 'this', $class );
				$this->write( 'class.tpl', $tmp = $this->destination_dir.'/'.$class->id );
			}
		}
		elseif( ! empty($_GET['file']) and $found = $this->file_exists($_GET['file']) )
		{
			$this->tpl->assign( 'file', $found );
			$this->tpl->assign( 'this', $found );
			$this->tpl->display( 'file.tpl' );
		}
		elseif( ! empty($_GET['class']) and @list($file,$class) = explode(',',$_GET['class']) and $found = $this->class_exists($class) )
		{
			$this->tpl->assign( 'class', $found );
			$this->tpl->assign( 'this', $found );
			$this->tpl->display( 'class.tpl' );
		}
		elseif( ! empty($_GET['method']) and @list($file,$class,$method) = explode(',',$_GET['method']) and $found = $this->method_exists($class,$method) )
		{
			$this->tpl->assign( 'method', $found );
			$this->tpl->assign( 'this', $found );
			$this->tpl->display( 'method.tpl' );
		}
		elseif( ! empty($_GET['function']) and @list($file,$function) = explode(',',$_GET['function']) and $found = $this->function_exists($function) )
		{
			$this->tpl->assign( 'function', $found );
			$this->tpl->assign( 'this', $found );
			$this->tpl->display( 'function.tpl' );
		}
		else
			$this->tpl->display( 'home.tpl' );
	}

	// }}}
	// {{{ $destination_dir

	protected $_destination_dir = null;

	/**
	 * Todo:
	 *	 Prévoir de la création de dossier
	 *	 et de la correction de '/' '\'.
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
	 */
	protected function write( $template, $to = null )
	{
/*		if( ! headers_sent() ) header( 'Content-type: text/html; charset=utf-8' );
		$this->tpl->display( $template );

		if( ! is_null($to) )
		{*/
			if( ( file_exists($to.'.html') and is_writable($to.'.html') )
	or is_writable(dirname($to.'.html')) )
			{
	//
	//throw new RuntimeException('utiliser une fonction smarty ici, compilé ou non, pour allez chercher le chemin');
	//$this->tpl->assign( 'href', '%2$s.html' );

	file_put_contents( $to.'.html', $this->tpl->fetch( $template ) );
	DstyleDoc::log( sprintf( 'Write <strong>%s</strong>', $to.'.html' ), true );
			}
			else
	DstyleDoc::warning( sprintf( 'Write <strong>%s</strong>', $to.'.html' ), true );
		//}
	}

	// }}}
	// {{{ hie()

	static function hie()
	{
		$instance = new self;
		$instance->browse_mode = true;
		return $instance;
	}

	// }}}
}

