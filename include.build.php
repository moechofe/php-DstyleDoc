<?php
$phar = new Phar(__DIR__.'/dstyledoc.phar');
$phar->addFile('control.php');
$phar->addFile('include.properties.php');
$phar->addFile('include.frontend.php');
$phar->addFile('tokens.all.php');
$phar->addFile('tokens.php');
$phar->addFile('tokens.none.php');
$phar->addFile('tokens.element.php');
$phar->addFile('tokens.value.php');
$phar->addFile('tokens.valueable.php');
var_dump( $phar->getStub() );
$phar->setStub(<<<PHP
<?php
Phar::mapPhar('dstyledoc.phar');
require_once 'include.frontend.php';
__HALT_COMPILER();
PHP
);

$phar = new Phar(__DIR__.'/dstyledoc.tokyotyrant.phar');
$phar->addFile('container.tokyo-tyrant.php');
$phar->setStub('<?php __HALT_COMPILER();');

