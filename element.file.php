<?php

require_once 'xdebug-frontend.php';
require_once 'element.php';

/**
 * Classe d'un élement de type fichier.
 */
class FileElement extends TitledElement
{
	// {{{ __sleep()

	public function __sleep()
	{
		return array_merge( parent::__sleep(), array(
			'_file',
		) );
	}

	// }}}
	// {{{ $file

	/**
	 * Chemin du fichier
	 * Le chemin relatif du fichier.
	 * Utiliser le membre $file pour accéder au chemin du fichier en lecture et écriture.
	 * Type:
	 *   string = Le chemin relatif de fichier.
	 */
	protected $_file = '';

	protected function set_file( $file )
	{
		assert('(string)$file');
		$this->_file = (string)$file;
	}

	protected function get_file()
	{
		assert('(string)$this->_file');
		return (string)$this->_file;
	}

	protected function get_name()
	{
		assert('(string)$this->_file');
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
		return $this->converter->convert_id( $this->file, $this );
	}

	// }}}
	// {{{ $display

	protected function get_display()
	{
		return $this->converter->convert_display( $this->file, $this );
	}

	// }}}
	// {{{ $convert

	protected function get_convert()
	{
		$this->analyse();
		return $this->converter->convert_file( $this );
	}

	// }}}
	// {{{ __construct()

	public function __construct( Converter $converter, $file )
	{
		parent::__construct( $converter );
		$this->file = $file;
	}

	// }}}
	// {{{ $licence

	protected $_licence = array();

	protected function set_licence( $licence )
	{
		$this->_licence[] = (string)$licence;
	}

	protected function get_licence()
	{
		return $this->converter->convert_licence( $this->_licence, $this );
	}

	protected function isset_licence()
	{
		return (boolean)$this->_licence;
	}

	protected function unset_licence()
	{
		$this->_licence = array();
	}

	// }}}
}

require_once 'dev.documentation.php';
require_once 'dev.unittest.php';
require_once 'converter.php';

class TestFileElement extends TestTitledElement
{
	protected $element = null;
	protected $file = 'figue';
	function setUp() { $this->element = new FileElement( $this->converter = new MockConverter, $this->file ); }
	function tearDown() { unset($this->element); }

	function test__get()
	{
		$this->assertTrue( $this->isFileElement );
	}

	function testFile()
	{
		$this->assertEqual( $this->element->file, $this->file );
		$this->element->file = $f = 'framboise';
		$this->assertEqual( $this->element->file, $f );
	}

	function testName()
	{
		$this->assertEqual( $this->element->file, $this->file );
		$this->element->file = $f = 'fraise';
		$this->assertEqual( $this->element->name, $f );
	}

}
