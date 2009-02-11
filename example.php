<style type="text/css">
ul { margin: 0px; padding: 0px 0px 0px 18px; }
</style>
<?php

require_once( 'xdebug.front.end.php' );

require_once( 'DstyleDoc.php' );
require_once( 'converter.toString.php' );

set_time_limit( 90 );

$d =
DstyleDoc::hie()
  ->config_database_pass( 'SeveuSe' )
  ->enable_dstyledoc
  ->enable_javadoc
  ->enable_come_across_element
  ->enable_href_link
  ->enable_javadoc_link
  ->source( 'example.php' )
  ->convert_with( new DstyleDoc_Converter_toString() );

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
 *   - core.return
 * Syntax:
 *   object ( test $test ) = example de modification de la syntaxe.
 */
function test_param( $string, $array, $integer, $null, test_float $object )
{
}

__halt_compiler();

/**
 * Returns:
 *   array = Retourne un tableau
 *   false = Ecrasement indirect
 *   true = Ne devrait pas écraser
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
 *   string = Auto ecrasement de string
 *   true = Se fait-il écrasé ?
 * Packages: core return
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

function test_direct_return()
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

/**
 * documentation pour aa()
 * Syntax:
 *    false, string = (integer $a, [$b]) = Call with an integer
 *    and with a documentation on two line
 *    or more
 *    string $a = Call with a string
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


?>
