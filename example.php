<?php

/**
 * Doc pour le fichier
 */


/**
 * Test fonction
 * Description longue
 *
 *
 *
 *
 * Sur plusieurs ligne
 * Version: 0.6a
 * History:
 *  - 0.5: C'est la version 0.5
 *  - 0.6  C'est la version 0.6
 *         Mais la description est sur 2 lignes
 *
 *         Avec un paragraphe, mais ça ne marche pas.
 * Params:
 *  - $i La source
 *  integer $a = Un entier
 *  - boolean $b = Un boolean
 *  - $int: Par défault = 0
 * param: yer,type ...: Tout autre paramètre incroyable
 *  Avec une ligne de commentaire en plus.
 *
 *  mais toujours pas de nouveu paragraphe
 *
 * History: 0.7 = Combien vaut la version 7
 * Returns: true = Ok
 *  - false,null: Ko
 * Test
 *
 * Package: test.things
 *
 * Throws: Exception = on failure
 *  - ErrorThings : on what ?
 *
 * Syntax:
 *  - $i, [$a, $int] = syntaxe 
 * de la description
 *  - $i, [$b, ...] : syntaxe 2
 */
function test( source $i, $a = 'b', $int = 0 )
{
  $a = $i;
  throw new ErrorThings();
  throw $a;

  a( $a );

  $a->b();

  trigger_error('test',E_ERROR_USER);
}

define( 'C_DEFAULT', 'heu' );

/**
 * Test classe.
 *
 * Ceci est une description longue.
 *
 * Version: 1.6a
 */
class Test extends customTest implements A, B
{
  /**
   * Static Final Public FUnction A
   */
  static final public function a( $var = C_DEFAULT )
  {
  }
}

/**
 * Docblock for customTest class
 *
 */
abstract class customTest
{
}

/**
 * Interface A
 */
interface A
{
}
interface B
{
}

require_once( 'xdebug.front.end.php' );

require_once( 'DstyleDoc.php' );
require_once( 'converter.toString.php' );

d(

DstyleDoc::hie()
  ->source( 'example.php' )
  ->convert_with( new DstyleDoc_Converter_toString() )

)->fontend;

/**
// recupère la doc des elements parent pour les classe ou les fonction etendu
// forcer public private protected
// see_also
// __get
// __call
// _set
// __unset
// __isset
// comment //
// ignore
**/

?>
