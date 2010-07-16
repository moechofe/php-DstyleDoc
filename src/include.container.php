<?php
namespace dstyledoc;

require_once 'include.properties.php';

use \ArrayAccess, \Iterator, \UnexpectedValueException, \InvalidArgumentException;

abstract class Container extends Properties implements ArrayAccess, Iterator
{
	// {{{ $_accept, $_control, $_converter, __construct(), hie_child()

	private $_accept = null;

	private $_control = null;
	private $_converter = null;

	final function __construct( $accept, Converter $converter )
	{
		assert('is_string($accept)');
		assert('is_subclass_of($accept,"CustomElement")');
		$this->_converter = $converter;
		$this->_control = $converter->control;
		$this->_accept = $accept;
	}

	final protected function hie_child( $name )
	{
		$class = $this->_accept;
		return $class($this->_converter,$name);
	}

	// }}}
	// {{{ set(), offsetSet()

	abstract function set( $file, $value );

	final function offsetSet( $offset, $value )
	{
		if( ! is_string($offset) )
			throw new ContainerKeyException;
		if( ! $value instanceof $this->_accept )
			throw new ContainerValueException;
		$this->set($offset,$value);
	}

	// }}}
 	// {{{ get(), offsetGet()

	abstract function get( $file );

	final function offsetGet( $offset )
	{
		if( ! is_string($offset) )
			throw new ContainerKeyException;
		if( ! ($return = $this->get($offset)) instanceof Serializable )
			throw new ContainerReturnException($this);
		return $return;
	}

	// }}}
}

class ContainerReturnException extends UnexpectedValueException
{
	function __construct( Container $class )
	{
		parent::__construct(sprintf("%s::get() mmust return an instance of Serializable",get_class($class)));
	}
}

class ContainerKeyException extends InvalidArgumentException
{
	function __construct()
	{
		parent::__construct(sprintf("Excepted string index"));
	}
}

class ContainerValueException extends InvalidArgumentException
{
	function __construct()
	{
		parent::__construct(sprintf("Excepted object value"));
	}
}

