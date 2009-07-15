<?php

require_once( 'prepare.php' );

require_once( 'simpletest/test_case.php' );

define( 'runner', true );

$_REQUEST['nopass'] = true;

$tests = new GroupTest( ' ' );
$tests->addTestFile( 'test.class/token.php' );
$tests->run( new rapporteur() );

// vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 fileformat=unix foldmethod=marker
?>
