<style type="text/css">
ul { margin: 0px; padding: 0px 0px 0px 18px; }
</style>
<?php

// un dumper/frontend pour xdebug
require_once( 'xdebug.front.end.php' );

// LE script DstylDoc
require_once( 'DstyleDoc.php' );
// LE converteur WEB 1.0
require_once( 'converter.toString.php' );

set_time_limit( 90 );

// Configure et lance l'analyse et la conversion
$d =
DstyleDoc::hie()
  ->config_database_pass( 'SeveuSe' )
  ->enable_dstyledoc
  ->enable_javadoc
  ->enable_come_across_element
  ->enable_href_link
  ->enable_javadoc_link
  ->source( basename(__FILE__) )
  ->convert_with( $c = new DstyleDoc_Converter_toString() );


// Affichage du code source de ce script
echo '<hr />';
echo str_replace($d->database_pass,'*****',highlight_file( __FILE__, true ) );

class c
{
  private $d = null;
  function f()
  {
    return 'string';
  }
  static function e()
  {
    $instance = new self;
    $instance->d = new self;
    return $this->f;
//    return $d->e->f();
  }
}


__halt_compiler();

class ccc
{
  /**
   * Type:
   *	self = L'instance suivante de la classe.
   */
  private $d = null;
  static function e()
  {
    $instance = new self;
    $instance->d = new self;
    return $d->e->f();
  }
  function f()
  {
    return 'string';
  }
}



/**
 * documentation pour aa()
 * Syntax:
 *    false, string (integer $a, [resource $b]) = Call with an integer
 *    and with a documentation on two line
 *    or more
 *    true (string $a) = Call with a string
 * Params:
 *    $a = Description for the 1st parameter $a
 * Returns:
 *    false = Erreur
 *    string = Retourne une chaîne de caractère
 * Throws:
 *    Exception = une exception,
 *    avec une documentation sur deux lignes.
 *    LogicException = une deuxième exception.
 */
function aa( $a, $b = null )
{
  return false;
  return 'bite';
  throw new Exception;
  throw new LogicException;
}

/**
 * Params:
 *   string $string = Une chaine de caractère.
 *   array $array = Un tableau.
 *   integer,float $integer = Un entier ou un flotant.
 *   null $null = NULL.
 * Returns:
 *   true = Succès.
 *   false,null = Echèc.
 * Packages:
 *   core.return
 * Syntax:
 *   object ( test $test ) = example de modification de la syntaxe.
 */
function test_param( $string, $array, $integer, $null, test_float $object )
{
}

/**
 * Returns:
 *   - array Retourne un tableau
 *   - false Ecrasement indirect
 *   - true Ne devrait pas écraser
 * Packages:
 *   - core
 *   - return
 */
function test_indirect_return()
{
}

class test_float
{
}

/**
 * Returns:
 *   test_float = Doc ref
 * Packages: core.return
 */
function test_doc_ref()
{
  return new test_float;
}

/**
 * Returns:
 *   string = Ecrasement direct
 *   true = Se fait-il écrasé ?
 * Packages: core
 * Package: return
 */
function test_return()
{
  return 'string'.'string';
  return false;
  return test_direct_return();
  return test_indirect_return();
  return test_multiple_type();
  return test_doublon();
  return test_doc_ref();
}

/**
 * Returns:
 *   long = Sa marche ?
 *   number = Ecrasement indirect d'un return indirect
 * Packages: core, return
 */
function test_doublon()
{
}

/**
 * Returns:
 *   resource, null = Une ressource ou pas.
 * @package core
 * @subpackage return
 */
function test_multiple_type()
{
}

function test_direct_return( a $a )
{
  return 123;
}

class a
{
  /**
   * Documentation pour un membre : $a ; une methode : a::a() ; une fonction aa() ; une class : a ; un javalink {@link aa() test}.
   * Var:
   *   string = Quand c'est une string, c'est 'test'.
   *   null = Pas de test, pas de string.
   */
  var $a = 'test';

  /**
   * Documentation originale de a::a(), elle devrait être aussi utilisé pour b::a().
   */
  function a()
  {
    return $this->a;
  }
}

class b extends a
{
  function a()
  {
    // parent::a();
  }
}

?>
<html>
<?php

function b( $a = 'test' )
{
?>
<div>
<?php
  return aa();
}

$a = null;

$a = " string {$a} string ${a} ";

__halt_compiler();
?>
