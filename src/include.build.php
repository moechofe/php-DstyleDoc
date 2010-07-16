<?php
chdir(__DIR__);
$phar = new Phar(__DIR__.'/../dstyledoc.phar');
$phar->addFile('control.php');
$phar->addFile('converter.php');
$phar->addFile('element.php');
$phar->addFile('include.properties.php');
$phar->addFile('include.frontend.php');
$phar->addFile('tokens.all.php');
$phar->addFile('tokens.php');
$phar->addFile('tokens.none.php');
$phar->addFile('tokens.element.php');
$phar->addFile('tokens.value.php');
$phar->addFile('tokens.valueable.php');
var_dump( $phar->getStub() );
$phar->setStub(<<<HTML
<?php
Phar::mapPhar('dstyledoc.phar');
require_once 'phar://dstyledoc.phar/include.frontend.php';
__HALT_COMPILER();
HTML
);

$phar = new Phar(__DIR__.'/../dstyledoc.tokyotyrant.phar');
$phar->addFile('container.tokyo-tyrant.php');
$phar->setStub('<?php __HALT_COMPILER();');

$phar = new Phar(__DIR__.'/../dstyledoc.converters.phar');
$phar->addFile('converter.HTML.php');
$phar->addFile('converter.simple.php');
$phar->setStub(<<<HTML
<?php
Phar::mapPhar('dstyledoc.converters.phar');
spl_autoload_register( function(\$name)
{
	switch(strtolower(\$name))
	{
	case 'convertersimple' : require_once 'phar://dstyledoc.converters.phar/converter.simple.php'; break;
	}
} );
__HALT_COMPILER();
HTML
);
