<?php

require_once( 'include.container.php' );

interface MemcacheContainerInterface
{
	static function hie( $servers );
}

class MemcacheContainer extends Container implements MemcacheContainerInterface
{
	protected $memcache = null;

	// {{{ __construct(), hie()

	protected function __construct( $servers )
	{
		assert('is_array($servers) or $servers instanceof Iterator');
		$this->memcache = new Memcache;
		foreach( $servers as $server )
		{
			assert('is_string($server)');
			if( $port = @parse_url($server,PHP_URL_PORT) )
				$memcache->addServer( @parse_url($server,PHP_URL_HOST), $port );
			else
				$memcache->addServer( @parse_url($server,PHP_URL_HOST) );
		}
	}

	static public function hie( $servers )
	{
		assert('is_array($servers) or $servers instanceof Iterator');
		if( ! extension_loaded('memcache') )
			throw new RuntimeException('Extension memcache is\'nt loaded.');
		return new self( $servers );
	}

	// }}}

	protected function hash( $key )
	{
	}

	// {{{ offsetExists(), offsetGet(), offsetSet(), offsetUnset()

	public function offsetExists( $offset )
	{
		return (false !== $this->memcache->get( $this->hash($offset) ) );
	}

	public function offsetGet( $offset )
	{
	}

	public function offsetSet( $offset, $value )
	{
	}

	public function offsetUnset( $offset )
	{
	}

	// }}}
	// {{{ current(), next(), key(), valid(), rewind()

	public function current()
	{
	}

	public function next()
	{
	}

	public function key()
	{
	}

	public function valid()
	{
	}

	public function rewind()
	{
	}

	// }}}
}

require_once( 'dev.documentation.php' );
require_once( 'dev.unittest.php' );

class MemcacheContainerTest extends UnitTestCase
{
	protected $this->container;
	function setUp() { $this->container = new MemcacheContainer('localhost'); }
	function tearDown() { unset($this->container); }

	function testArraySetWithIndex()
	{
		$c = $this->container;
		$c[0] = 0; $c[1] = 1;
		$c['index'] = 'index';
	}

}


