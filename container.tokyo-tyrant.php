<?php
namespace dstyledoc\tokyotyrant;

require_once 'include.container.php';

use dstyledoc\Container;

class TokyoTyrantContainer extends Container
{
	// {{{ $_server

	protected $_server = null;

	function get_server( $member )
	{
		if( ! $this->_server instanceof TokyoTyrant )
		{
			$this->_server = new TokyoTyrant;
			$this->_server->connectUri( $this->control->tokyotyrant_uri );
		}
		return $this->_server;
	}

	// }}}
	// {{{ save()

	private function save( Serializable $value )
	{
	}

	// }}}

	private $current = null;

	function get( $file )
	{
		assert($this->current);
		return $this->current;
	}

	function set( $offset, $value )
	{
		if( $this->current )
			$this->save( $this->current );
		$this->current = $this->hie_child( $value );
	}

	function offsetExists( $offset )
	{
	}

	function offsetUnset( $offset )
	{
	}

	function current()
	{
	}

	function next()
	{
	}

	function key()
	{
	}

	function valid()
	{
	}

	function rewind()
	{
	}
}

