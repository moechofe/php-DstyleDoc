<?php

chdir(__DIR__);

$phar = new Phar($file=__DIR__.'/../dstyledoc.phar');
$phar->addFile('control.php');
$phar->addFile('converter.php');
$phar->addFile('element.all.php');
$phar->addFile('element.php');
$phar->addFile('element.file.php');
$phar->addFile('include.properties.php');
$phar->addFile('include.frontend.php');
$phar->addFile('tokens.all.php');
$phar->addFile('tokens.php');
$phar->addFile('tokens.none.php');
$phar->addFile('tokens.element.php');
$phar->addFile('tokens.value.php');
$phar->addFile('tokens.valueable.php');
$phar->setStub(<<<HTML
<?php
Phar::mapPhar('dstyledoc.phar');
set_include_path('phar://dstyledoc.phar'.PATH_SEPARATOR.get_include_path());require_once 'phar://dstyledoc.phar/include.frontend.php';
__HALT_COMPILER();
HTML
);

$phar = new Phar(__DIR__.'/../dstyledoc.tokyotyrant.phar');
$phar->addFile('container.tokyo-tyrant.php');
$phar->setStub('<?php __HALT_COMPILER();');

$phar = new Phar(__DIR__.'/../dstyledoc.simple.phar');
$phar->addFile('converter.HTML.php');
$phar->addFile('converter.simple.php');
$phar->setStub(<<<HTML
<?php
namespace dstyledoc\converters\simple
function get() { return new \dstyledoc\converters\ConverterSimple; }
namespace
		var_dump( __FILE__.__LINE__ );
Phar::mapPhar('dstyledoc.simple.phar');
		var_dump( __FILE__.__LINE__ );
set_include_path('phar://dstyledoc.simple.phar'.PATH_SEPARATOR.get_include_path());
		var_dump( __FILE__.__LINE__ );
spl_autoload_register( function(\$name)
{
	var_dump( __FILE__.__LINE__,\$name );
	//var_dump( 'spl_autoload_register:', \$name );
	//var_dump( 'declared_classes:', get_declared_classes() );
	switch(\$name)
	{
	case 'dstyledoc\converters\ConverterSimple' :
		var_dump( 'found:',__FILE__.__LINE__ );
		require_once 'phar://dstyledoc.simple.phar/converter.simple.php'; break;
	default:
		var_dump('not found:',__FILE__.__LINE__ );
	}
} );
__HALT_COMPILER();
HTML
);
