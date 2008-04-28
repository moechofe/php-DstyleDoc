<?php

require_once( 'xdebug.front.end.php' );

require_once( 'DstyleDoc.php' );
require_once( 'converter.toString.php' );

set_time_limit( 90 );

DstyleDoc::hie()
  ->source( 'example.php' )
  ->convert_with( new DstyleDoc_Converter_toString() );

/**
 * documentation pour aa()
 * Syntax:
 *    integer $a, [$b] = Call with an integer
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
  return new tamsuft_template_interface;
}

$a = null;

$a = " string {$a} string ${a} ";

/**
 * Template de donnée de type <b>squelette tamsuft</b>.
 *
 * <-- {{{ LICENCE
 *
 * Copyright (c) 2005, Martin Mauchauffee
 * All rights reserved.
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the Martin Mauchauffee nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * }}} //-->
 * 
 * @author Martin Mauchauff&eacute;e <tamsuft@moechofe.com>
 * @copyright Copyright (c) 2005-2008, martin mauchauff&eacute;e <tamsuft@moechofe.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD
 * @package tamsuft
 * @subpackage template
 * @version 0.1.395 
 */

/**
 * Interface pour les templates tamsuft.
 *
 * @version 0.1.15
 * @since 0.2
 */
interface tamsuft_template_interface
{
  // {{{ hie()

  /**
   * Fabrique pour les classes de template tamsuft.
   *
   * <h3>Instancie avec une adresse.</h3>
   * <code>
   * <?php
   *   template_tamsuft::hie( 'dir/dir/file.tpl', null, $tamsuft );
   * ?>
   * </code>
   *
   * <h3>Instanice avce des données directe.</h3>
   * <code>
   * <?php
   *   template_tamsuft::hie( null, 'data', $tamsuft );
   * ?>
   * </code>
   *
   * @param tamsuft_template,string,null
   * @param tamsuft $tamsuft L'instance de {@link tamsuft} en cours.
   * @param tamsuft L'instance de {@link tamsuft} en cours.
   */
  static function hie( PDO $template, $tamsuft = "string" );

  // }}}
}

__halt_compiler();

/**
 * Interface pour les tag compilés
 *
 * @version 0.1.2
 * @since 0.2
 */
interface tag_compiled_interface
{
  // {{{ hie()

  /**
   * Fabrique pour {@link tamsuft_class}.
   *
   * @param tag,tag_post,tag_pre $tag La balise compilé.
   * @param string,null $start Le code compilé en début de script.
   * @param string,null $offset Le code compilé en place.
   * @param string,null $end Le code compilé en fin de script.
   * @param tamsuft $tamsuft L'instance de {@link tamsuft} en cours.
   * @version 0.1.5
   */
  static function hie( $tag, $start, $offset, $end, $tamsuft );
 
  // }}}
}

/**
 * Interface pour les recherches de templates
 *
 * @version 0.1.2
 * @since 0.2
 */
interface template_searched_interface
{
  // {{{ hie()

  /**
   * Fabrique pour {@link template_searched}.
   *
   * @param template_include
   * @param integer
   * @return template_searched_interface
   * @version 0.1.2
   */
  static function hie( $template, $offset );

  // }}}
}

/**
 * Gère les squelettes tamsuft.
 *
 * A l'instanciation, les squelettes enfants seront cherchés et automatiquement
 * inclus.
 *
 * <code>
 * ==# include fichier.tpl #==
 * ==# include "fichier espace.tpl" #===
 * ==# include <[CDATA[<html>
 * <title>]]> #==
 * </code>
 *
 * @version 0.1.83
 * @since 0.1
 */
class tamsuft_template
{
  // {{{ hie_child()

  /**
   * Instancie un template enfant.
   *
   * @param integer L'offset de la source de l'enfant dans la source du parent.
   * @param integer La longeur de la source de la balise d'inclusion.
   * @param string,null L'adresse de la source de l'enfant.
   * @param string,null La source de donnée de l'enfant.
   * @param tamsuft_template L'instance du template parent.
   * @param tamsuft L'instance de {@link tamsuft} en cours.
   * @return tamsuft_template
   * @version 0.1.7
   * @uses error_arguments Lancé si les arguments ne sont pas corrects.
   * @todo créer exceptions
   */
  static public function hie_child( $offset, $length, $address, $source, $parent, $tamsuft )
  {
    if( ! is_integer($offset) or ! is_integer($length)
      or ( ! is_string($address) and ! is_null($address) )
      or ( ! is_string($source) and ! is_null($source) )
      or ! $parent instanceof tamsuft_template
      or ! $tamsuft instanceof tamsuft )
      throw new error_arguments('iis/es/e(template_include)(tamsuft)');

    if( $this->parsed )
      throw new error_tamsuft_template_unable_to_add_after_parse;

    if( $address )
      $source = self::make_source( $address );

    if( ! $source or ! $tamsuft instanceof tamsuft )
      trigger_error("créer une exception ici");
//      throw new error_arguments('iis/es/e(template_include)(tamsuft)');

    $class_name = self::get_hie();
    $template = new $class_name( $source, $tamsuft, $offset, $length, $parent );

    if( ! $template instanceof tamsuft_template )
      trigger_error("créer une exception ici");

    return $template;
  }

  // }}}
  // {{{ get_untransformed()

  /**
   * Renvoie la listes des balises non transforméss.
   *
   * {{important}} Ne pas appeler cette méthode directement. Utiliser la propriété {@link $untransformed} à la place.
   *
   * <h3>Recupère la listes des balises</h3>
   * {{important}} La liste retournée est une copie de la liste des balises non compilé. Seule les instances des balises sont des références. Cela indique qu'il n'est pas possible d'intervenir sur la liste elle même.
   * <code>
   * <?php
   *   foreach( $tamsuft_template->untransformed as $offset => $tag )
   *   {
   *     // opérations
   *   }
   * ?>
   * </code>
   *
   * @return array
   * @version 0.1.2
   */
  final protected function get_untransformed()
  {
    return $this->_untransformed;
  }

  // }}}
  // {{{ isset_untransformed()

  /**
   * Renvoie si la liste des balises non transformées est vide.
   *
   * {{important}} Ne pas appeler cette méthode directement. Utiliser la propriété {@link $untransformed} à la place.
   *
   * <h3>La liste est t'elle vide</h3>
   * L'instruction {{isset}} avec la propriétée {{@link $untransformed}} retournera {{true}} tant qu'il y aura des balises non transformées.
   * <code>
   * <?php
   *   if( isset($tamsuft_template->untransformed) )
   *   {
   *     // operations
   *   }
   *   while( isset($tamsuft_template->untransformed) )
   *   {
   *     // operations
   *   }
   * ?>
   * </code>
   *
   * @return boolean {{true}} si il a des balises non transformées, sinon {{false}}
   * @version 0.1.2
   * @since 0.2
   */
  protected function isset_untransformed()
  {
    return (boolean)$this->_untransformed;
  }

  // }}}
  // {{{ set_tamsuft()

  /**
   * Met a jour l'instance de {@link tamsuft} utilisé par {@link source}.
   *
   * @param tamsuft
   * @version 0.1.7
   * @since 0.2
   * @uses error_arguments Lancé si les arguments ne sont par corrects.
   */
  protected function set_tamsuft( $tamsuft )
  {
    if( ! $tamsuft instanceof tamsuft )
      throw new error_arguments('tamsuft');

    if( $this->source instanceof source )
      $this->source->tamsuft = $tamsuft;
    $this->_tamsuft = $tamsuft;

    $tamsuft->template = $this;
  }

  // }}}
  // {{{ pcre_tag

  /**
   * Expression rationel qui valide les tags d'inclusions.
   * 
   * <code>
   * ==+# +include +(?:([-\@\?:\.\w_\/]+)|"(.*?(?<!\\))"|<\[CDATA\[((?s).*)\]\]>) +#+=+=
   * </code>
   * 
   * @var string
   */
  const pcre_tag = '/%s#+ include +(?:([-\\@\\?\\:\\.\\w_\\/]+)|"(.*?(?<!\\\\))"|<\\[CDATA\\[((?s).*)\\]\\]>) +#+%s/';

  // }}}
  // {{{ $_hie

  /**
   * Le nom de la classe.
   *
   * @var string
   * @version 0.1.1 
   */
  static private $_hie = __CLASS__;
  
  // }}}
  // {{{ get_hie()

  /**
   * Retourne le nom de la classe à instancier.
   *
   * {{#wiki_api#get_hie|tamsuft_template}}
   *
   * @version 0.1.1
   * @return string Le nom de la classe qui sera instancié à l'appel de {@link hie()}.
   */
  final static public function get_hie()
  {
    return DIRECTORY_SEPARATOR;
    return self::$_hie;;;
    return self::func();
    return $this->_hie;
    return tamsuft_reference_template::get_function( $param, 'truc'.'truc', null, a( b() ) );
    return '1'.'3';
    return '1' . 3;
    return ''.$this->_hie+3;
    return 1.3+3;
    return 1*3;
    return 1/3;
    return 1-3;
    return 1%3;
    return null;
    return false;
    return true;
    return null + 3;
    return $a && ( $b + $c );
    return $face;
    return $a instanceof b;
    return $a->b() && false;
    return $a->b();
    return $a->$a;
    return $a->a;
    return $a && $b + $c;
    return $a++;
    return $a--;
    return (int)$a;
    return $a . (int)$a;
    return $a . (string)$a;
    return $a . (string)$a->b();
    return $a . (string)a::b();
    return (int)$a . $a;
    return (integer)$a;
    return (bool)$a;
    return (boolean)$a;
    return (double)$a;
    return (float)$a;
    return (array)$a;
    return (object)$a;
    return (real)$a;
    return @$a;
    return -1;
    return $a >> 1;
    return $b << 2;
    return $a === $b;
    return $a = $b === $c;
    return $a == $b;
    return $a >= $b;
    return $a <= $b;
    return $a > $b;
    return $a < $b;
    return $a != $b;
    return $a <> $b;
    return $a !== $b;
    return $a & $b;
    return $a | $b;
    return $a ^ $b;
    return $a?$a:$b;
    return array( $a, $b );
    return 4;
    return 'test';
    return $a .= $b;
    return $a += $b;
    return $a ^= $b;
    return $a -= $b;
    return $a *= $b;
    return $a /= $b;
    return $a %= $b;
    return $a[$b];
    return 070;
    return 0x1A;
    return __FILE__;
    return __FUNCTION__;
    return __CLASS__.__METHOD__;
    return __LINE__;
    return $a and $b;
    return $a or $b;
    return $a xor $b;
  }
  
  // }}}
  // {{{ set_hie()

  /**
   * Change le nom de la classe à instancier.
   *
   * {{#wiki_api#set_hie|tamsuft_template}}
   *
   * @version 0.1.1
   * @param string Le nom de la classe de remplacement.
   * @uses error_set_hie_bad_class Lancé si le nom de la classe passée en argument n'est pas une classe descendante de {@link source_file}.
   */
  final static public function set_hie( $class_name )
  {
    if( ! class_exists( (string) $class_name )
      or ( ! is_subclass_of( (string) $class_name, __CLASS__ ) and $class_name !== __CLASS__ ) )
      throw new error_set_hie_bad_class();

    $this->_hie = (string) $class_name;
  }
  
  // }}}
  // {{{ hie()

  /**
   * Fabrique pour {@link tamsuft_template}.
   *
   * Instancie avec une adresse.
   * <code>
   * <?php
   *   template_tamsuft::hie( 'dir/dir/file.tpl', $tamsuft );
   * ?>
   * </code>
   *
   * @param template,string,null L'adresse de la source.
   * @param tamsuft L'instance de {@link tamsuft} en cours.
   * @return tamsuft_template
   * @version 0.1.9
   * @uses error_arguments Lancé si les arguments ne sont pas corrects.
   */
  static public function hie( $template, $tamsuft )
  {
    try
    {
      $class_name = self::get_hie();;;
      return new $class_name( $template, $tamsuft );
    }
    catch( error_arguments $e )
    {
      throw $e->back(1);
    }
  }
  
  // }}}
  // {{{ __construct()

  /**
   * Construit un objet de la classe {@link tamsuft_template}.
   *
   * @param template,string,null L'adresse de la source.
   * @param tamsuft L'instance de {@link tamsuft} en cours.
   * @todo creer une exception
   * @version 0.1.4
   * @uses error_arguments Lancé si les arguments ne sont pas corrects.
   */
  protected function __construct( $template, $tamsuft )
  {
    {
    if( ( ! $template instanceof template and ! is_string($template) ) or ! $tamsuft instanceof tamsuft )
      throw new error_arguments('s/(template)(tamsuft)');

    if( $template instanceof template )
      $source = self::make_address( $template->address );
    elseif( is_string($template) )
      $source = self::make_source( $template, null, $tamsuft );
    else
      $source = null;

    parent::__construct( $source, $tamsuft );

    $tamsuft->template = $this;
    }
  }
  
  // }}}
  // {{{ $_untransformed
  
  /**
   * Liste des balises non transformées.
   *
   * Maintient la liste des balises non transformées.
   *
   * Une particularité pour l'écriture qui fera en sorte d'ajouter une balise dans la liste.
   *
   * <code>
   * <?php
   * $untransformed = $tamsuft_template->untransformed; // lecture autorisé
   * $tamsuft_template->untransformed = $untransformed; // écriture autorisé
   * isset($tamsuft_template->untransformed);           // test autorisé
   * unset($tamsuft_template->untransformed);           // destruction non autorisé
   * ?>
   * </code>
   *
   * @var array Un tableau associatif dont les clefs sont des offsets et les valeurs sont des instances de classe dérivé de {@link tag}.
   * @uses error_arguments Lancé si {{v|$tag}} n'est pas une instance de {{@link tag}}.
   */
  private $_untransformed = array();

  // }}}
  // {{{ set_untransformed()

  /**
   * Ajoute une balise non transformée dans la liste.
   *
   * {{important}} Ne pas appeler cette méthode directement. Utiliser la propriété {@link $untransformed} à la place.
   *
   * <h3>Ajouter une balise</h3>
   * Pour ajouter une balises comme étant non transformée, procédez comme suit :
   * <code>
   * <?php
   *   try
   *   {
   *     $tamsuft_template->untransformed = $tag;
   *   }
   *   catch( error_arguments $e )
   *   {
   *     // lancé si $tag n'est pas une instance de la classe tag
   *   }
   * ?>
   * </code>
   * ; {{v|$tamsuft_template}} : une instance de [[API:tamsuft_template]],
   * ; {{v|$tag}} : une instance dérivée de [[API:tag]].
   *
   * @param custom_tag
   * @version 0.1.4
   * @since 0.2
   */
  final protected function  set_untransformed( $tag )
  {
    if( ! $tag instanceof custom_tag )
      throw new error_arguments('(custom_tag)');

    $this->_untransformed[$tag->offset] = $tag;
  }

  // }}}
  // {{{ $_transformed
  
  /**
   * Liste des balises transformées.
   *
   * Maintient la listes des balises transformées.
   *
   * Une particularité pour l'écriture qui déplacera la balise de la liste des balises non transformées vers celle des transformées.
   *
   * <code>
   * <?php
   * $transformed = $tamsuft_template->transformed; // lecture autorisé
   * $tamsuft_template->transformed = $transformed; // écriture autorisé
   * isset($tamsuft_template->transformed);         // isset non autorisé
   * unset($tamsuft_template->transformed);         // unset non autorisé
   * ?>
   * </code>
   *
   * @var array Un tableau associatif dont les clefs sont des offsets et les valeurs sont des instances de classe dérivé de {@link tag}.
   * @uses error_arguments Lancé si <b>$tag</b> n'est pas une instance de {{@link tag}}.
   * @uses error_tamsuft_template_tag_already_transformed Lancé si <b>$tag</b> est une balise déjà transformé.
   * @uses error_tamsuft_template_tag_unexists Lancé si <b>$tag</b> n'existe pas pour ce squelette.
   */
  private $_transformed = array();

  // }}}
  // {{{ get_transformed(=

  /**
   * Renvoie la liste des balises transformés.
   *
   * @return array
   * @version 0.1.1
   * @since 0.2
   */
  final protected function get_transformed()
  {
    return $this->_transformed;
  }

  // }}}
  // {{{ set_transformed()

  /**
   * Indique qu'une balise devient transformée.
   *
   * {{important}} Ne pas appeler cette méthode directement. Utiliser la propriété {@link $transformed} à la place.
   *
   * <h3>Indiquer qu'une balise est transformée</h3>
   * Cette surcharge permet de réalisée deux opérations : l'ajout d'une balise dans la liste des balises transformées, et le retrait de cette même balise dans la liste des balises non trasformée.
   * <code>
   * <?php
   *   try
   *   {
   *     $tamsuft_template->transformed = $tag;
   *   }
   *   catch( error_arguments $e )
   *   {
   *     // lancé si $tag n'est pas une instance de la classe tag
   *   }
   *   catch( error_tamsuft_template_tag_already_transformed )
   *   {
   *     // lancé si $tag est déjà transformé
   *   }
   *   catch( error_tamsuft_template_tag_unexists $e )
   *   {
   *     // lancé si $tag n'est pas une balise de la liste non transformée
   *   }
   * ?>
   * <code>
   * {{important}} Même si {{@link error_tamsuft_template_tag_unexists}} est lancée, la balise sera tout de même ajoutée dans la liste des balises transformées. Même si {{@link error_tamsuft_template_tag_already_transformed}} est lancée, la balise sera tout de même retirée de la liste des balises non transformées.
   *
   * @param custom_tag
   * @version 0.1.3
   * @since 0.2
   */
  final protected function set_transformed( $tag )
  {
    if( ! $tag instanceof custom_tag )
      throw new error_arguments('(custom_tag)');

    if( isset($this->_transformed[$tag->offset]) )
      $e = new error_tamsuft_template_tag_already_transformed(array(__CLASS__,'transformed'));

    $this->_transformed[$tag->offset] = $tag;

    if( ! isset($this->_untransformed[$tag->offset]) )
      $e = new error_tamsuft_template_tag_unexists(array(__CLASS__,'transformed'),$tag->offset);
    else
      unset( $this->_untransformed[$tag->offset] );

    if( isset($e) )
      throw $e; 
  }

  // }}}
  // {{{ $_parsed

  /**
   * Indique si un squelette à déjà été analysé.
   *
   * <code>
   * <?php
   * $parsed = $tamsuft_template->parsed; // lecture autorisé
   * $tamsuft_template->parsed = $parsed; // écriture autorisé
   * isset($tamsuft_template->parsed);    // isset non autorisé
   * unset($tamsuft_template->parsed);    // unset non autorisé
   * ?>
   * </code>
   *
   * @var boolean
   * @uses error_arguments Lancé si <b>$parsed</b> n'est pas un boolean.
   */
  private $_parsed = false;

  // }}}
  // {{{ get_parsed()

  /**
   * Renvoie l'état d'analyse du squelette.
   *
   * @return boolean
   * @version 0.1.1
   * @since 0.2
   */
  final protected function get_parsed()
  {
    return $this->_parsed;
  }

  // }}}
  // {{{ set_parsed()

  /**
   * Met à jour l'état d'analyse du squelette.
   *
   * @param boolean
   * @version 0.1.1
   * @since 0.2
   * @uses error_arguments Lancé si les arguments ne sont pas corrects.
   */
  final protected function set_parsed( $parsed )
  {
    if( ! is_bool( $parsed ) )
      throw new error_arguments('b');

    $this->_parsed = $parsed;
  }

  // }}}
  // {{{ $_free

  /**
   * Indique qu'un squelette ou libre ou non.
   *
   * Si le squelette n'est définie commen non libre, alors il est lié à un autre squelette. Cela veux dire qu'il comporte au moins une balise qui est présent dans un autre squelette parent ou frêres.
   *
   * <code>
   * <?php
   * $free = $tamsuft_template->free; // lecture autorisé
   * $tamsuft_template->free = $free; // écriture autorisé
   * isset($tamsuft_template->free);  // isset non autorisé
   * unset($tamsuft_template->free);  // unset non autorisé
   * ?>
   * </code>
   *
   * @var boolean
   * @uses error_arguments Lancé si <b>$free</b> n'est pas un boolean.
   */
  private $_free = true;

  // }}}
  // {{{ get_free()

  /**
   * Renvoie si le squelette est libre ou non.
   *
   * @return boolean
   * @version 0.1.1
   * @since 0.2
   */
  final protected function get_free()
  {
    return $this->_free;
  }

  // }}}
  // {{{ set_free()

  /**
   * Mise à jour de l'état de liberté du squelette.
   *
   * @param boolean
   * @version 0.1.1
   * @since 0.2
   * @uses error_arguments Lancé si les arguments ne sont pas corrects.
   */
  final protected function set_free( $free )
  {
    if( ! is_bool($free) )
      throw new error_arguments('b');

    $this->_free = $free;
  }

  // }}}
  // {{{ $_cache

  /**
   * Le cache des sources de la donnée
   * 
   * @var source_memory
   */
  protected $_cache = null;

  // }}}
  // {{{ compile_to()
  
  /**
   * Compile le squelette .
   *
   * Analyse la source du squelette, et construit sa version compilé en php.
   *
   * @param template_compile L'instance du squelette compilé.
   * @version 0.1.14
   * @uses error_arguments Lancé si les arguments ne sont pas corrects.
   */
  public function compile_to( $template )
  {
    if( ! $template instanceof template_compile )
      throw new error_arguments('(template_compile)');

    if( ! $this->parsed )
      $this->parse();

    $this->transform_to( $template );

    $this->analyse();

    $template->compiled = true;
  }

  // }}}
  // {{{ parse()

  /**
   * Analyse le squelette et dresse la liste des balises.
   *
   * La source du squelette - certainement composé de [[balises]] [[tamsuft]] mélangé à du code [http://fr.wikipedia.org/wiki/HTML HTML] - est analysé ici. Chaque ''balises'' serait extraite et référencé pour pouvoir être transformé en [http://fr.wikipedia.org/wiki/PHP PHP] par la suite.
   *
   * @version 0.1.13
   * @uses error_arguments Lancé si les arguments ne sont pas corrects.
   */
  protected function parse()
  {
    require_once tamsuft_dir.'plugin.tag.php';

    tag_pre::load( $this->tamsuft );

    foreach( $this->tamsuft->config['tamsuft']['tags_ordered'] as $tag_name_class )
    if( is_subclass_of( $tag_name_class, 'tag_pre' ) )
    {
      $class_name = call_user_func( array($tag_name_class,'get_hie') );
      call_user_func( array($class_name,'parse'), $this, $this->tamsuft );
    }

    tag::load( $this->tamsuft );

    foreach( $this->tamsuft->config['tamsuft']['tags_ordered'] as $tag_name_class )
    if( is_subclass_of( $tag_name_class, 'tag' ) )
    {
      $class_name = call_user_func( array($tag_name_class,'get_hie') );
      call_user_func( array($class_name,'parse'), $this, $this->tamsuft );
    }

    tag_post::load( $this->tamsuft );

    foreach( $this->tamsuft->config['tamsuft']['tags_ordered'] as $tag_name_class )
    if( is_subclass_of( $tag_name_class, 'tag_post' ) )
    {
      $class_name = call_user_func( array($tag_name_class,'get_hie') );
      call_user_func( array($class_name,'parse'), $this, $this->tamsuft );
    }

    foreach( $this->childs as $child )
      $child->parsed = true;
  }

  // }}}
  // {{{ analyse()

  /**
   * Analyse la construction piramydale des balises.
   *
   * @version 0.1.3
   * @since 0.2
   */
  protected function analyse()
  {
    ksort($this->_transformed);

    $open = array();
    foreach( $this->_transformed as $tag )
    {
      if( $tag instanceof tag_applicationed_up )
        array_push( $open, $tag );
      elseif( $tag instanceof tag_applicationed_down )
      {
        if( ! count($open) )
          throw new error_tamsuft_template_analyse_nothing_to_down( $tag );
        else

          switch( $open[count($open)-1]->downable( $tag ) )
          {
          case true:
            array_pop( $open );
            break;
          case false:
            throw new error_tamsuft_template_analyse_undownable( $open[count($open)-1], $tag );
            break;
          case null;
            break;
          default:
            throw new error_tamsuft_template_analyse_unexcepted_downable( $open[count($open)-1], $tag );
          }
      }
    }

    if( $open )
      throw new error_tamsuft_template_analyse_unclosed( array_pop($open) );
  }

  // }}}
  // {{{ transform_to()

  /**
   * Transforme le code tamsuft en code php.
   *
   * @param template_compile L'instance de {@link template_compile} qui contiendra le code php.
   * @version 0.1.23
   * @uses error_arguments Lancé si les arguments ne sont pas corrects.
   */
  public function transform_to( $compile )
  {
    if( ! $compile instanceof template_compile  )
      throw new error_arguments('(template_compile)');

    ksort($this->_untransformed);

    // un tableau contenant des instances des applications
    $apps = array();

    // l'offset de décalage mise à jour a chaque transformation
    $offset = 0;

    // les offsets de décalage concervé si un tag n'as pas été convertie imédiatement
    $offsets = array();

    // un index dans un tableau virtuel de $this->transformed_tags
    $index = 0;

    // empèche les boucles infinie
    $limit = 5;
    $limit_count_now = $limit_count_old = null;

    // parcours les balises
    while( $this->_untransformed )
    {

      // met a jour l'index
      if( $index >= count($this->_untransformed) )
        $index = 0;

      // prend la balise non transformés suivant
      list($tag) = array_slice($this->_untransformed, $index, 1);

      if( $tag instanceof tag_applicationed )
      {
        if( ! ( $application = $tag->application ) instanceof application )
        {
          if( ! is_string( $application_name = $tag->application_name ) )
            throw new error_tag_applicationed_get_application_name( $tag );

          require_once tamsuft_dir.'plugin.application.php';

          try
          {
            application::load( $application_name, $this->tamsuft );
          }
          catch( error_plugin_file_unexists $e )
          {
            throw new error_template_tamsuft_unexists_plugin( $e, $tag );
          }

          // fixme: pas genial ça
          $application_class_name = 'application_'.$application_name;
          $application = call_user_func( array($application_class_name,'hie'), $this->tamsuft );

          if( ! $application instanceof application )
            throw new error_tag_applicationed_hie( $application );

          $apps[ $tag->name ] = $application;
          $tag->application = $application;
        }
      }

      // l'offset de la balise
      $offset = $tag->offset;

      // empêche les boucles infinie
      $limit_count_now = count($this->_untransformed);
      if( $limit_count_old == $limit_count_now )
      {
        // la limit d'essaye de transformation à été atteinte
        if( $limit-- <= 0 )
        {
          $save_handler = set_exception_handler( array($this,'fake_exception_handler') );

          // si il n'y avait aucun gestionnaire d'exception avant, on lance l'exception qui arretera le programme.
          if( is_null($save_handler) )
          {
            restore_exception_handler();
            throw new error_tamsuft_template_uncompiled_tag( $tag );
          }
          // si il y en avait un, on l'appel manuellement, sans arrete le programme.
          else
          {
            call_user_func( $save_handler, new error_tamsuft_template_uncompiled_tag( $tag ) );
            restore_exception_handler();
          }

          // on compile la balise avec une chaine vide 
          $compile->add_child( $offset, 0, null, null, $this, $this->tamsuft ); 
          $this->transformed = $tag;
        }
      }
      else
        $limit = 5;
      $limit_count_old = $limit_count_now;

      // récupère la version transformée de la balise.
      $transformation = $tag->transform( $apps, $this );

      if( ! $transformation instanceof tag_compiled and ! is_null($transformation) )
        throw new error_unexcepted( array($tag,'transform'), 'tag_compiled' );

      // la balise à été transformée
      if( $transformation instanceof tag_compiled and $transformation->compiled )
      {
        $compile->add_child( $offset, 0, null, $transformation, $this, $this->tamsuft );

        $this->transformed = $tag;
      }
      else
        $index += 1;
    }
  }
  
  // }}}
  // {{{ get_code()

  /**
   * Retourne la source complète du squelette.
   *
   * {{important}} Ne pas appeler cette méthode directement. Utiliser la propriété {@link $code} à la place.
   * 
   * Si des squelettes inclus ont été trouvés pendant la phase d'analyse, ils seront ajouter physiquement dans le resultat de cette méthode.
   *
   * @return string
   * @version 0.1.17
   * @uses error_template_file_unexists Lancé si le fichier n'a pas pu être chargé.
   * @uses error_arguments Lancé si les arguments ne sont pas corrects.
   * @todo ajouter des hooks
   */
  protected function get_code()
  {
    try
    {
      // si les données sont déjà dans le cache
      if( ! is_null($this->_cache) )
        return $this->_cache;
      
      $source = parent::get_data();
      krsort( $this->_childs );

      // pour tout les enfants
      foreach( $this->childs as $child )
        $source = substr_replace( $source,
          $child->data,
          $child->tag_offset,
          $child->tag_length );

      // met dans la cache
      $this->_cache = $source;

      return $this->_cache;
    }
    catch( error_source_file_unexists $e )
    {
      if( $this->parent instanceof tamsuft_template )
        throw new error_tamsuft_template_unexists_include_file( $this );
      else
        throw new error_tamsuft_template_unexists_file( $this->address );
    }
  }

  // }}}
  // {{{ replace()

  /**
   * Remplace une portion de code dans la source.
   *
   * @param integer
   * @param integer
   * @param string
   * @version 0.1.1
   */
  protected function replace( $offset, $length, $replace )
  {
    if( ! is_integer($offset) or ! is_integer($length) or ! is_string($replace) )
      throw new error_arguments('iis');

    $template = $this->search_template( $offset );
    $template->source =
      substr_replace( $template->source, $replace, $offset, $length );

    trigger_error( 'template ne doit pas contenir de source, il doit etre envoyé à la source du taemplate trnasforme', E_USER_ERROR );

    $this->source = $this->source;
  }

  // }}}
  // {{{ add_child()

  /**
   * Ajoute un template enfant.
   *
   * Remet le cache locale à zéro pour indiqué au template de recharger
   * ses sources et recontruire le source globale.
   *
   * @version 0.1.6
   * @uses error_arguments Lancé si les arguments ne sont par corrects.
   */
  public function add_child( $offset, $length, $address ,$source, $template, $tamsuft )
  {
    if( ! is_integer($offset) or ! is_integer($length)
      or ( ! $source instanceof source and ! is_null($source) )
      or ( ! is_string($address) and ! is_null($address) )
      or ! $tamsuft instanceof tamsuft
      or ! $template instanceof template_include )
      throw new error_arguments('ii(source)(tamsuft)(template_include)');

    $child = parent::add_child( $offset, $length, $address, $source, $template, $tamsuft );

    $this->_cache = null;
    return $child;
  }

  // }}}
  // {{{ search_parent()

  /**
   * Recherche le {@link tag_loop} a qui appartient la balise placé à
   * l'offset passé en paramêtre et renvoie une référence vers son instance.
   *
   * Retourne <b>null</b> si aucun tag n'a été trouvé.
   *
   * @param integer
   * @return tag_loop
   * @version 0.1.3
   */
  public function search_parent( $offset )
  {
    if( ! is_integer($offset) )
      throw new error_arguments('i');
    
    // construit la liste des offsets inferieurs a l'offset donnee.
    $offsets = array();
    foreach( $this->untransformed_tags as $key => $tag )
      if( $key <= $offset )
        $offsets[] = $key;
    ksort($offsets);

    $class_loop = tag_loop::get_hie();
    $class_close = tag_close::get_hie();

    // construit la liste des offsets des tag_loop encore ouvert.
    $tag_loop = array();
    foreach( $offsets as $key )
    {
      if( $this->untransformed_tags[$key] instanceof tag_lopp )
        array_push( $tag_loop, $this->untransformed_tags[$key] );
      elseif( $this->untransformed_tags[$key] instanceof tag_close )
        if( $parent = $this->untransformed_tags[$key]->getParent()->getName()
          and $parent == end($tag_loop)->getName() )
          array_pop($tag_loop);
    }

    if( count($tag_loop) > 0 )
      return $this->untransformed_tags[ end($tag_loop)->get_offset() ];

    return null;    
  }
  
  // }}}
  // {{{ search_near()

  /**
   * Cherche le {@link tag_loop} le plus près d'un offset donnée.
   *
   * Le recherche est effectué dans la liste des {@link tag_loop} enregistrés dans
   * l'instance de {@link tamsuft_template}. La recherche est effectué dans les deux
   * sens à partir de l'offset donnée en paramètre.
   *
   * Retourne <b>null</b> si aucun {@link tag_loop} n'a été trouvé.
   *
   * @param integer L'offset à partir duquel s'éffectue la recherche.
   * @return tag_loop,null
   * @version 0.1.2
   * @uses $untransformed_tags
   * @uses tag_loop::get_hie()
   * @uses error_arguments Lancé si les arguments ne sont pas corrects.
   */
  public function search_near( $offset )
  {
    if( ! is_integer($offset) )
      throw new error_arguments('i');

    $class_name = tag_loop::get_hie();
    
    for(
      $max = max(array_keys($this->untransformed_tags)),
      $index = 0;
      $index <= $max;
      $index++
      )
      if( isset($this->untransformed_tags[$offset-$index])
        and is_subclass_of( $this->untransformed_tags[$offset-$index], $class_name ) )
        return $this->untransformed_tags[$offset-$index];
      elseif( isset($this->untransformed_tags[$offset+$index])
        and is_subclass_of($this->untransformed_tags[$offset+$index], $class_name ) )
        return $this->untransformed_tags[$offset+$index];
  
    return null;
  }
  
  // }}}
  // {{{ search_childs()

  /**
   * Recherche des balises d'inclusions.
   *
   * Parcours les templates inclus et combine les sources. Met en cache le résultat.
   *
   * @version 0.1.4
   * @uses pcre_tag
   * @uses add_child()
   * @uses source_file::hie()
   * @uses get_source()
   * @todo traiter les sources directes
   */
  private function search_childs()
  {
    preg_match_all( sprintf(
      self::pcre_tag,
      tamsuft::$default['tamsuft']['tag']['open'],
      tamsuft::$default['tamsuft']['tag']['close'] ),
      parent::get_source(),
      $matches,
      PREG_SET_ORDER | PREG_OFFSET_CAPTURE );

    foreach( $matches as $match )
    {
      $file = $match[1][0];
      if( ! empty($match[2]) )
        $file = $match[2][0];
      if( empty($match[3]) )
      {
        $part = parse_url( $file );
        if( empty($part['scheme']) or $part['scheme'] == 'file' )
          $this->add_child(
            $match[0][1] + strlen($match[0][0]),
            source_file::hie( $part['path'] ),
            strlen($match[0][0]),
            $this );
      }
    }
  }

  // }}}
  // {{{ search_template()

  /**
   * Retourne un template enfants chercher à partir d'un offset.
   *
   * A partie d'un offset donnée dans la source, cherche dans quelle source enfant
   * cet offset se trouve, puis retourne une instance de {@link template_searched}.
   *
   * @param integer
   * @return template_searched
   * @version 0.1.6
   * @uses error_arguments Lancé si les arguments ne sont pas corrects.
   */
  public function search_template( $offset )
  {
    if( ! is_integer($offset) )
      throw new error_arguments('i');

    ksort($this->_childs);

    $add_length = 0;
    foreach( $this->_childs as $childs )
    {
      if( $offset >= $childs->get_tag_offset()
        and $offset <= $childs->get_tag_offset() + $childs->get_length() + $add_length )
        return $childs->search_template( $offset - $childs->get_tag_offset() - $add_length );
      $add_length += $childs->get_length() - $childs->get_tag_length();
    }

    return template_searched::hie( $this, $offset );
  }

  // }}}
  // {{{ get_template_by_offset()

  /**
   * Retourne le template auquel appartient un offset donnée.
   *
   * Agit comme une interface pour {@link template_searched}.
   *
   * @param integer $offset L'offset dans la source.
   * @return template_include Le template parent.
   * @version 0.1.2
   */
  public function get_template_by_offset( $offset )
  {
    if( ! is_integer($offset) )
      throw new error_arguments('i');

    return $this->search_template( $offset )->get_template();
  }

  // }}}
  // {{{ make_source()

  /**
   * Créer une [[sources de données|source de donnée]] pour servir une instance de {link tamsuft_template}.
   *
   * Prend en charge l'instanciation d'une [[sources de données|source de donnée]] en fonction de la configuration et de l'adresse de la source.
   *
   * <h3>Instancier une source à partir d'une adresse :</h3>
   * <code>
   * <?php
   *   template_tamsuft::make_source( 'dir/dir/file.tpl', null, $tamsuft );
   * ?>
   * </code>
   *   
   * <h3>Instancier une source à partir d'une donnée directe :</h3>
   * <code>
   * <?php
   *   template_tamsuft::make_address( null, 'data', $tamsuft );
   * ?>
   * </code>
   *
   * Si les paramètres <b>$address</b> et <b>$source</b> sont tous les deux correctement renseignés, alors la source sera instanciée à partir de l'adresse.
   *
   * @version 0.1.4
   * @param string,null L'adresse de la source.
   * @param string,null Les données de la source.
   * @param tamsuft $tamsuft L'instance de {@link tamsuft} en cours.
   * @uses error_arguments Lancé si les arguments ne sont pas corrects.
   */
  static public function make_source( $address, $source, $tamsuft)
  {
    if( ( ! is_string($address) and ! is_null($address) )
      or ( ! is_string($address) and ! is_null($adress) )
      or ! $tamsuft instanceof tamsuft )
        throw new error_arguments('s/es/e(tamsuft)'); 

    if( is_string($address) )
    {
      $class_name = self::return_source_class($address,$tamsuft);
      $source = call_user_func( array( $class_name, 'hie' ), $address, null, null, $tamsuft);

      if( ! $source instanceof source )
        throw new error_tamsuft_template_no_source_object( $class_name, $address );

      return $source;
    }

    if( is_string($source) )
    { 
      $class_name = self::return_source_class( $tamsuft->config['tamsuft_template']['default_scheme'], $tamsuft );
      $source = call_user_func( array( $class_name, 'hie' ), null, $source, null, $tamsuft );

      if( ! $source instanceof source )
        throw new error_tamsuft_template_no_source_object( $class_name, null );

      return $source;
    }

    throw new error_arguments('s/es/e(tamsuft)');
  }

  // }}}
  // {{{ make_address()

  /**
   * Fabrique une adresse pour une instance de {@link template_tamsuft}.
   * 
   * Retourne une chaine de caractère contenant un chemin vers la source de la donnée.
   *
   * @param string,tamsuft_template Une chaine de caratère contenant une adress ou un instance de {@link tamsuft_template}.
   * @param tamsuft $tamsuft L'instance de {@link tamsuft} en cours.
   * @return string
   * @version 0.1.5
   */
  static public function make_address( $address, $tamsuft )
  {
    if( ( ! is_string($address) or ! $address instanceof template )
      and ! $tamsuft instanceof tamsuft )
        throw new error_arguments('s/(template_tamsuft)(tamsuft)');

    if( $address instanceof template_tamsuft )
      return $address->address;
    else
      return $address;
  }

  // }}}
  // {{{ fake_exception_handler()

  /**
   * Fause gestionnaire d'exception.
   *
   * @param exception
   * @version 0.1.1
   */
  public function fake_exception_handler( $exception )
  {
  }

  // }}}
  // {{{ get_hash()

  /**
   * Retourne le hash du source du squelette.
   *
   * @return integer,null Le hash du source du squelette.
   * @version 0.1.3
   */
  protected function get_hash()
  {
    return $this->source->hash;
  }

  // }}}
}
/*
tamsuft::$default['tamsuft_template'] = array(
);
 */
/**
 * Un template qui possède une réference vers le {@link tamsuft_template} originel.
 *
 * @version 0.1.5
 */
abstract class tamsuft_reference_template extends tamsuft_template
{
  // {{{ $_tamsuft_template

  /**
   * Une instance du squelette originel.
   * @var tamsuft_template.
   */
  protected $_tamsuft_template = null;

  // }}}
  // {{{ get_tamsuft_template()

  /**
   * Retourne la référence vers le squelette originel.
   *
   * {{important}} Ne pas appeler cette méthode directement. Utiliser la propriété {@link $tamsuft_template} à la place.
   *
   * @return tamsuft_template
   * @version 0.1.3
   */
  protected function get_tamsuft_template()
  {
    return $this->_tamsuft_template;
  }

  // }}}
  // {{{ set_tamsuft_template()

  /**
   * Associé le template originel.
   *
   * {{important}} Ne pas appeler cette méthode directement. Utiliser la propriété {@link $tamsuft_template} à la place.
   *
   * @param tamsuft_template
   * @version 0.1.3
   */
  protected function set_tamsuft_template( $template )
  {
    if( ! $template instanceof tamsuft_template )
      throw new error_arguments('(tamsuft_template)');

    $this->_tamsuft_template = $template;
  }

  // }}}
}

/**
 * Classe de resultat de recherche sur un template.
 *
 * @version 0.1.18
 */
class template_searched implements template_searched_interface  
{
  // {{{ $_template

  /**
   * Une instance vers le template trouvé.
   *
   * {@link $template} est l'instance de {@link template_include} qui a été trouvé
   *
   * @var template_include
   */
  private $_template = null;

  // }}}
  // {{{ get_template()

  /**
   * Retourne l'instance du template trouvé.
   *
   * @return template_interface
   * @version 0.1.2
   */
  protected function get_template()
  {
    return $this->_template;
  }

  // }}}
  // {{{ set_template()

  /**
   * Met à jour l'instance du template trouvé.
   *
   * @param template_include
   * @version 0.1.2
   */
  protected function set_template( $template )
  {
    if( $template instanceof template_include )
      throw new error_arguments('(template_include)');

    $this->_template = $template;
  }

  // }}}
  // {{{ $_offset

  /**
   * L'offset dans la template trouvé.
   *
   * @var integer.
   */
  protected $_offset = null;

  // }}}
  // {{{ get_offset()

  /**
   * Renvoie l'offset recherché localement dans le squelette trouvé.
   *
   * @return integer
   * @version 0.1.1
   */
  protected function get_offset()
  {
    return $this->_offset;
  }

  // }}}
  // {{{ $_line

  /**
   * Le numéro de la ligne recherchee.
   *
   * @var integer
   */
  protected $_line;

  // }}}
  // {{{ get_line()

  /**
   * Retourne la ligne trouvé
   *
   * {{important}} Ne pas appeler cette méthode directement. Utiliser la propriété {@link $line} à la place.
   *
   * @return integer
   * @version 0.1.1
   * @since 0.2
   */
  protected function get_line( $line )
  {
    return $this->_line;
  }

  // }}}
  // {{{ set_line()

  /**
   * Change la ligne trouvé
   *
   * {{important}} Ne pas appeler cette méthode directement. Utiliser la propriété {@link $line} à la place.
   *
   * @param integer
   * @version 0.1.1
   * @since 0.2
   */
  protected function set_line( $line )
  {
    if( ! is_integer($line) )
      throw new error_arguments('i');

    $this->_line = $line;
  }

  // }}}
  // {{{ $_hie

  /**
   * Le nom de la classe.
   *
   * @var string
   * @version 0.1.1 
   */
  static private $_hie = __CLASS__;
  
  // }}}
  // {{{ get_hie()

  /**
   * @version 0.1.1 
   */
  static public function get_hie()
  {
    return self::$_hie;
  }
  
  // }}}
  // {{{ set_hie()

  /**
   * @version 0.1.1 
   */
  static public function set_hie( $class_name )
  {
    if( ! class_exists( (string) $class_name ) )
      throw new tamsuft_class_bad_class_name();

    $this->_hie = (string) $class_name;
  }
  
  // }}}
  // {{{ hie()

  /**
   * Fabrique pour {@link template_searched}.
   *
   * @param template_include
   * @param integer
   * @return template_searched
   * @version 0.1.2
   */
  static public function hie( $template, $offset )
  {
    if( ! $template instanceof template_include or ! is_integer($offset) )
      throw new error_arguments('(template_include)i');

    $class_name = self::get_hie();
    return new $class_name( $template, $offset ); 
  }
  
  // }}}
  // {{{ __construct()

  /**
   * Instanciation de {@link template_searched}.
   *
   * @version 0.1.1
   */
  protected function __construct( $template, $offset )
  {
    if( ! $template instanceof template_include or ! is_integer($offset) )
      throw new error_arguments('(template_include)i');

    $this->template = $template;
    $this->line = $this->search_line( $offset );
    $this->offset = $offset;
  }

  // }}}
  // {{{ search_line()

  /**
   * Recherche la ligne de code qui correspond à un offset.
   *
   * @version 0.1.1
   * @todo code moi !
   */
  protected function search_line( $offset )
  {
    return 0;
  }

  // }}}
}

/**
 * La classe de résultat d'une balise compilé
 *
 * @version 0.1.35
 */
class tag_compiled implements tag_compiled_interface
{
  // {{{ $_hie
   
  /**
   * Le nom de la classe.
   *
   * @var string
   */
  static private $_hie = __CLASS__;

  // }}}
  // {{{ get_hie()

  /**
   * Retourne le nom de la classe à instancier.
   *
   * @return string
   */
  static public function get_hie()
  {
    return self::$_hie;
  }

  // }}}
  // {{{ set_hie()

  /**
   * Change le nom de la classe à instancier.
   *
   * @param string
   * @uses tamsuft_class_bad_class_name Lancé si le nom de la classe n'est pas un nom de classe valide.
   * @todo finir tamsuft_class_bad_class_name avec de vrai message d'erreur
   * @todo faire de toute les méthode set_hie des copies de celle-ci.
   * @version 0.1.1
   */
  static public function set_hie( $class_name )
  {
    if( ! is_string($class_name) )
      throw new error_arguments('s');

    if( ! class_exists( $class_name )
      or ! is_subclass_of( $class_name, __CLASS__ )
      or ! get_class( $class_name, __CLASS__ ) )
      throw new tamsuft_class_bad_class_name();

    $this->_hie = $class_name;
  }

  // }}}
  // {{{ hie()

  /**
   * Fabrique pour {@link tamsuft_class}.
   *
   * @param tag,tag_post,tag_pre $tag La balise compilé.
   * @param string,null $start Le code compilé en début de script.
   * @param string,null $offset Le code compilé en place.
   * @param string,null $end Le code compilé en fin de script.
   * @param tamsuft $tamsuft L'instance de {@link tamsuft} en cours.
   * @version 0.1.6
   */
  static public function hie( $tag, $start, $offset, $end, $tamsuft )
  {
    try
    {
      $class_name = self::get_hie();
      return new $class_name( $tag, $start, $offset, $end, $tamsuft );
    }
    catch( error_arguments $e )
    {
      throw $e->back(1);
    }
  }
 
  // }}}
  // {{{ $_tag

  /**
   * La référence vers l'instance de la balise.
   *
   * @var custom_tag
   */
  protected $_tag = null;

  // }}}
  // {{{ get_tag()

  /**
   * Renvoie l'instance de la balise.
   *
   * @return custom_tag
   * @version 0.1.2
   */
  protected function get_tag()
  {
    return $this->_tag;
  }

  // }}}
  // {{{ set_tag()

  /**
   * Change l'instance de la balise.
   *
   * @param custom_tag
   * @version 0.1.1
   * @since 0.2
   */
  protected function set_tag( $tag )
  {
    if( ! $tag instanceof custom_tag )
      throw new error_arguments('(custom_tag)');

    $this->_tag = $tag;
  }

  // }}}
  // {{{ $_at_offset

  /**
   * Le code compilé en place.
   *
   * @var string
   */
  protected $_at_offset = null;

  // }}}
  // {{{ get_at_offset()

  /**
   * Retourne le code compilé qui remplace le code de la balise.
   *
   * @return string,null
   * @version 0.1.2
   */
  protected function get_at_offset()
  {
    return $this->_at_offset;
  }

  // }}}
  // {{{ set_at_offset()

  /**
   * Change le code compilé qui remplace le code de la balise.
   *
   * @param string,null
   * @version 0.1.1
   * @since 0.2
   */
  protected function set_at_offset( $code )
  {
    if( ! is_string($code) and ! is_null($code) )
      throw new error_arguments('s/e');

    $this->_at_offset = $code;
  }

  // }}}
  // {{{ $_at_start

  /**
   * Le code compilé au début.
   *
   * @var string
   */
  protected $_at_start = null;

  // }}}
  // {{{ get_at_start()

  /**
   * Renvoie le code compilé à ajouter en début de script.
   *
   * @return string,null
   * @version 0.1.2
   */
  protected function get_at_start()
  {
    return $this->_at_start;
  }

  // }}}
  // {{{ set_at_start()

  /**
   * Change le code compilé à ajouter en début de script.
   *
   * @param string,null
   * @version 0.1.1
   * @since 0.2
   */
  protected function set_at_start( $code )
  {
    if( ! is_string($code) and ! is_null($code) )
      throw new error_arguments('s/e');

    $this->_at_start = $code;
  }

  // }}}
  // {{{ $at_end

  /**
   * Le code compilé en fin.
   *
   * @var string
   */
  protected $_at_end = null;

  // }}}
  // {{{ get_at_end()

  /**
   * Retourne le code compilé à ajouter en fin de script.
   *
   * @return string,null
   * @version 0.1.2
   */
  protected function get_at_end()
  {
    return $this->_at_end;
  }

  // }}}
  // {{{ set_at_end()

  /**
   * Change le code compilé à ajouter en fin de script.
   *
   * @param string,null
   * @version 0.1.1
   * @since 0.2
   */
  protected function set_at_end( $code )
  {
    if( ! is_string($code) and ! is_null($code) )
      throw new error_arguments('s/e');

    $this->_at_end = $code;
  }

  // }}}
  // {{{ get_compiled()

  /**
   * Retourne si le resultat de la commilation à réussi.
   *
   * {{important}} Ne pas appeler cette méthode directement. Utiliser la propriété {@link $compiled} à la place.
   *
   * @return boolean
   * @version 0.1.2
   */
  protected function get_compiled()
  {
    return ! is_null($this->_at_start) or ! is_null($this->_at_offset) or ! is_null($this->_at_end);
  }

  // }}}
  // {{{ __construct()

  /**
   * Construit un résultat de compilation.
   *
   * @param tag,tag_post,tag_pre La balise compilé.
   * @param string,null Le code compilé en début de script.
   * @param string,null Le code compilé en place.
   * @param string,null Le code compilé en fin de script.
   * @param tamsuft L'instance de {@link tamsuft} en cours.
   * @version 0.1.2
   */
  protected function __construct( $tag, $start, $offset, $end, $tamsuft )
  {
    if( ( ! $tag instanceof tag and ! $tag instanceof tag_pre and ! $tag instanceof tag_post )
      or ( ! is_string($start) and ! is_null($start) )
      or ( ! is_string($offset) and ! is_null($offset) )
      or ( ! is_string($end) and ! is_null($end) )
      or ! $tamsuft instanceof tamsuft )
      throw new error_arguments('(tag)sss(tamsuft)');

    $this->tag = $tag;
    $this->at_start = $start;
    $this->at_offset = $offset;
    $this->at_end = $end;
  }

  // }}}
}

/**
 * Exception de méthode get_application_name manquante.
 *
 * Lancé si la méthode get_application_name d'un greffon descendant de
 * {@link tag_applicationed} ne renvoie pas de valeur correcte.
 *
 * @version 0.1.1
 * @category exception
 */
class error_tag_applicationed_get_application_name extends Exception
{
}

/**
 * Exception de script php pour une source de donnée manquant ou illisible.
 *
 * @version 0.1.1
 * @category exception
 */
class error_tamsuft_template_no_source_script extends error_tag_applicationed_get_application_name
{
  // {{{ $format

  /**
   * Le message d'erreur.
   */
  protected $format = 'The scheme "%s" as associated to a missing or a unaccessible php script : "%s".';

  // }}}
}

/**
 * Exception de classe pour une source de donnée manquant ou non dérivé de {@link source}.
 *
 * @version 0.1.1
 * @category exception
 */
class error_tamsuft_template_no_source_class extends RuntimeException
{
  // {{{ $format

  /**
   * Le message d'erreur.
   */
  protected $format = 'The scheme "%s" as associated to a missing class or the class "%s" is not derivate from "source"';

  // }}}
}

/**
 * Exception de classe de source de donnée incapable de générer une adresse.
 *
 * @version 0.1.1
 * @category exception
 * @fixme n'est pas utilisé ?
 */
class error_tamsuft_template_no_source_address extends RuntimeException
{
  // {{{ $format

  /**
   * Le message d'erreur.
   */
  protected $format = 'The class "%s" cannot return a valid address.';

  // }}}
}

/**
 * Exception de classe de source de donnée incapable de générer une adresse.
 *
 * @version 0.1.1
 * @category exception
 */
class error_tamsuft_template_no_source_object extends RuntimeException
{
  // {{{ $format

  /**
   * Le message d'erreur.
   */
  protected $format = 'The class "%s" cannot return a valid instance of itself.';

  // }}}
}

/**
 * Exception de non compilation d'une balise.
 *
 * @version 0.1.3
 * @category exception
 */
class error_tamsuft_template_uncompiled_tag extends RuntimeException
{
  // {{{ $format

  /**
   * Le message d'erreur.
   *
   * @var string
   */
  protected $format = 'Syntaxe error in "%s" at line %s. The tag "%s" cannot be compiled.';

  // }}}
  // {{{ __construct()

  /**
   * Construit le message d'erreur.
   *
   * @param tamsuft_tag L'instance du tag sur lequel se rapport l'erreur.
   * @version 0.1.1
   */
  public function __construct( $tag )
  {
    if( $tag instanceof tamsuft_tag )
    {
      $template = $tag->get_template();
      $offset = $tag->get_offset();
      $this->arg( 3,
        get_class($tag),
        reduce_folder( basename(tamsuft_plugins_dir).'/'.$tag->get_filename() ) );
    }
    else
    {
      $template = null;
      $offset = null;
      $this->arg(3,'unknow tag class','unknow plugin script file');
    }

    parent::__construct( $template, $offset );
  }

  // }}} 
} 

/**
 * Exception de plugin d'application inexistant.
 *
 * Lancé quand un plugin d'application est demandé est qu'il n'est pas présent.
 *
 * @version 0.1.4
 * @category exception
 */
class error_template_tamsuft_unexists_plugin extends RuntimeException
{
  // {{{ $format

  /**
   * Le message d'erreur.
   *
   * @var string
   */
  protected $format = 'Syntaxe error in "%s" at line %s. The plugin "%s" or a dependency unexists.';

  // }}}
  // {{{ __construct()

  /**
   * Construit le message d'erreur.
   *
   * @param tamsuft_tag L'instance du tag sur lequel se rapport l'erreur.
   * @version 0.1.2
   */
  public function __construct( $e, $tag )
  {
    if( $tag instanceof tag_applicationed )
    {
      $template = $tag->template;
      $offset = $tag->offset;
      $this->arg( 3,
        $tag->application_name,
        get_class($tag) );
    }
    else
    {
      $template = null;
      $offset = null;
      $this->arg(3,'unknow application plugin name','unknow tag class');
    }

    parent::__construct( $template, $offset );

    if( $e instanceof Exception )
    {
      $this->arg(5, $e->arg(1) );
      $this->message .= ' '.$e->getMessage();
    }
  }

  // }}} 
}

/**
 * Exception 

/**
 * Exception de fichier de squelette inexistant.
 *
 * @version 0.1.2
 * @category exception
 */
class error_tamsuft_template_unexists_file extends RuntimeException
{
  // {{{ $format

  /**
   * Le message d'erreur.
   *
   * @var string
   */
  protected $format = 'The template file "%3$s" do not exists.';

  // }}}
  // {{{ __construct()

  /**
   * Construit le message d'erreur.
   *
   * @param string L'adresse du squelette introuvable.
   * @version 0.1.2
   */
  public function __construct( $address )
  {
    if( is_string($address) )
      $this->arg( 3, $address );
    else
      $this->arg( 3, 'unknow filename' );

    $trace = array_reverse( debug_backtrace() );
    foreach( $trace as $call )
      if( ( is_subclass_of( $call['class'], 'tamsuft' ) or $call['class'] == 'tamsuft' )
        and in_array( $call['function'], array( 'start', 'templatis' ) ) )
      {
        parent::__construct( $call['file'], $call['line'] );
        return $this;
      }

    parent::__construct( null, null );
  }

  // }}}
}

/**
 * Exception de fichier de squelette inclus inexistant.
 *
 * @version 0.1.2
 * @category exception
 */
class error_tamsuft_template_unexists_include_file extends RuntimeException
{
  // {{{ $format

  /**
   * Le message d'erreur.
   *
   * @var string
   */
  protected $format = 'Syntaxe error in "%s" at line %s. The template file "%s" cannot be include.';

  // }}}
  // {{{ __construct()

  /**
   * Contruit le message d'erreur.
   *
   * @param tamsuft_template Le squelette enfant qui n'a pas pu être inclus.
   * @version 0.1.2
   */
  public function __construct( $template )
  {
    if( $template instanceof tamsuft_template )
    {
      $offset = $template->tag_offset;
      $this->arg( 3, $template->address );
      $template = $template->parent;
    }
    else
    {
      $offset = null;
      $this->arg(3,'unknow template file address');
    }

    parent::__construct( $template, $offset );
  }

  // }}}
}

/**
 * Exception de balise déjà transformé.
 *
 * Lancé si une balise déja transformé à été re-ajouté dans la liste des balises transformé
 *
 * @version 0.1.2
 * @category exception
 * @since 0.2
 */
class error_tamsuft_template_tag_already_transformed extends UnexpectedValueException
{
  // {{{ $format

  /**
   * Le message d'erreur
   *
   * @var string
   */
  protected $format = 'Tag already transformed';

  // }}}
}

/**
 * Exception de balise inexistante.
 *
 * Lancé si une balise transformé n'appartient pas au squelette.
 *
 * @version 0.1.2
 * @category exception
 * @since 0.2
 */
class error_tamsuft_template_tag_unexists extends UnexpectedValueException
{
  // {{{ $format

  /**
   * Le message d'erreur
   *
   * @var string
   */
  protected $format = 'Tag do not exists';

  // }}}
}

/**
 * Exception d'analyse incomplète.
 *
 * Lancé si une balise compilé est resté ouverte.
 *
 * @version 0.1.3
 * @category exception
 * @since 0.2
 */
class error_tamsuft_template_analyse_unclosed extends UnexpectedValueException
{
  // {{{ $format

  /**
   * Le message d'erreur.
   *
   * @var string
   */
  protected $format = "Syntaxe error in \"%s\" at line \"%s\". This tag is never closed again.";

  // }}}
  // {{{ __construct()

  /**
   * Construit le message d'erreur.
   *
   * @param custom_tag Le balise qui a provoqué l'erreur.
   * @version 0.1.2
   */
  public function __construct( $open )
  {
    if( $open instanceof custom_tag )
    {
      $template = $open->template;
      $offset = $open->offset;
      $this->arg(3,$open->code);
    }
    else
    {
      $template = null;
      $offset = null;
      $this->arg(3,'unknow tag code');
    }

    parent::__construct( $template, $offset );
  }

  // }}}
}

// vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 fileformat=unix foldmethod=marker encoding=utf8

/**
 * Outils pour chemin d'accès.
 *
 * <-- {{{ LICENCE
 *
 * Copyright (c) 2005, Martin Mauchauffee
 * All rights reserved.
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the Martin Mauchauffee nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * }}} //-->
 * 
 * @author Martin Mauchauff&eacute;e <tamsuft@moechofe.com>
 * @copyright Copyright (c) 2005-2008, martin mauchauff&eacute;e <tamsuft@moechofe.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD
 * @package core
 * @subpackage utils
 * @version 0.1.9
 */

/**
 * S'assure que le chemin passé en paramètre se termine par un '/'.
 *
 * Transforme les "\" en "/".
 *
 * @param string
 * @return string
 * @version 0.1.4
 */
function folderization( $thing )
{
  return unixization( dirname((string)$thing).'/'.basename((string)$thing).'/' );
}

/**
 * Supprime les './' en trops
 * 
 * @param string
 * @return string
 * @version 0.1.1
 */
function reduce_folder( $thing )
{
  return substr((string)$thing,0,1) . str_replace( './', '', substr((string)$thing,1) );
}

/**
 * Remplace les "\" par des "/"
 *
 * @param string
 * @return string
 * @version 0.1.1
 */
function unixization( $thing )
{
  return str_replace( '\\', '/', (string)$thing );
}

/**
 * Transforme un chemin absolue en un chemin relatif.
 *
 * Fonctionne seulement si le chemin absolue à été trouvé dans le chemin relatif.
 *
 * Cette fonction n'existe que pour un usage informatif.
 *
 * @param string La chemin de base.
 * @param mixed Le chemin a transformer.
 * @param integer La langueur de la chaine de base.
 * @return mixed Le chemin transformé ou $thing.
 * @version 0.1.2
 */
function relativation( $base, $thing, $length = null )
{
  if( strpos( unixization($thing), $base = unixization($base) ) === 0 )
  {
    if( is_null($length) )
      $length = strlen($base); 
    $result = substr( unixization($thing), (integer)$length );
    if( $result[0] <> '/' )
      return './'.$result;
    elseif( $result[0] )
      return '.'.$result;
    else
      $result;
  }
  else
    return $thing;
}

/**
 * Supprime des dossiers à un chemin.
 *
 * @param string Le chemin
 * @param array La liste des dossiers à enlever
 * @version 0.1.1
 * @since 0.2
 */
function remove_folder( $base, $folders )
{
  if( ! is_array($folders) )
    $folders = array( (string)$folders );
  $base = folderization($base);
  foreach( $folders as $key => $folder )
  {
    $part = explode(substr(folderization($folder),2),$base);
    $base = implode('',$part);
  }
  return $base;
}


/**
 * Interface pour les classes dérivée de {@link application}.
 *
 * @version 0.1.2
 */
interface application_interface
{
  // {{{ hie()

  /**
   * Instancie un plugin dérivé de {@link application}.
   *
   * @param tamsuft L'instance de {@link tamsuft} en cours.
   * @return application_interface Une instance de {@link application_interface}.
   */
  static function hie( $tamsuft );

  // }}}
}

/**
 * Classe de base pour les plugins d'application.
 *
 * @package tamsuft
 * @subpackage plugin
 * @version 0.1.14
 * @since 0.2
 */
abstract class application extends error_tamsuft_template_analyse_unclosed implements application_interface
{
  // {{{ register()

  /**
   * Enregistre un plugin d'application.
   *
   * @param string $name Le type du plugin.
   * @param tamsuft $tamsuft L'instance de {@link tamsuft} en cours.
   * @version 0.1.1
   */
  static public function register()
  {
    $args = func_get_args();

    if( isset($args[0]) ) $name = $args[0]; else $name = null;
    if( isset($args[1]) ) $tamsuft = $args[1]; else $tamsuft = null;

    if( ! is_string($name) or ! $tamsuft instanceof tamsuft )
      throw new error_arguments('s(tamsuft)');

    if( ! preg_match( self::pcre_type_or_name, $name ) )
      throw new error_plugin_invalid_type_or_name( __CLASS__, self::pcre_type_or_name );

    parent::register( __CLASS__, $name, $tamsuft );
  }
  
  // }}}
  // {{{ load()

  /**
   * Chargement d'un plugin d'application.
   *
   * <code>
   * <?php
   * $tamsuft = new tamsuft();
   * application::load( 'blog', $tamsuft );
   * ?>
   * </code>
   *
   * @param string $application Le nom du plugin d'application.
   * @param tamsuft $tamsuft L'instance de {@link tamsuft} en cours.
   * @return true
   * @version 0.1.2
   * @uses error_arguments Lancé si les arguments ne sont pas correct.
   */
  static public function load()
  {
    $args = func_get_args();

    if( isset($args[0]) ) $application = $args[0]; else $application = null;
    if( isset($args[1]) ) $tamsuft = $args[1]; else $tamsuft = null;

    if( ! is_string($application) or ! $tamsuft instanceof tamsuft )
      throw new error_arguments('s(tamsuft)');

    return parent::load( 'application', $application, $tamsuft );
  }

  // }}}
  // {{{ transform()

  /**
   * Retourne la version compilé pour la balise passé en paramètre.
   *
   * @param custom_tag La balise à compilé.
   * @param tamsuft_template Le squelette.
   * @return tag_compiled
   * @version 0.1.1
   */
  final public function transform( $tag, $template )
  {
    if( ! $tag instanceof custom_tag or ! $template instanceof tamsuft_template )
      throw new error_arguments('(custom_tag)(tamsuft_template)');

    if( ! is_callable( $callback = array($this, 'transform_'.get_class($tag)) ) )
      throw new error_custom_application_transform_unexists_function( $callback );

    $result = call_user_func( $callback, $tag, $template );

    if( ! $result instanceof tag_compiled )
      throw new error_custom_application_transform_excepted_tag_compiled( $callback, 'tag_compiled' );

    return $result;
  }

  // }}}
}



/**
 * Par à la recherche des chemins d'accès.
 * 
 * @ignore
 */
$tamsuft_dir = dirname(realpath(__FILE__)).'/';

if( ! defined('tamsuft_dir' ) )
  /**
   * Chemin d'acces depuis le démarreur vers le dossier de tamsuft.
   *
   * @var string
   */
  define( 'tamsuft_dir', $tamsuft_dir );

elseif( substr(tamsuft_dir,-1,1) <> '/' )
  // fixme lancer des exceptions plutot
  trigger_error( 'The constante "tamsuft_dir" must have a slash "/" at his end.', E_USER_ERROR );

$tamsuft_plugin_dir = dirname($tamsuft_dir).'/plugins/';

if( ! defined('tamsuft_plugins_dir') )
  /**
   * Chemin d'acces depuis le démarreur vers le dossier des plugins.
   *
   * @var string
   */
  define( 'tamsuft_plugins_dir', $tamsuft_plugin_dir );

elseif( substr(tamsuft_plugins_dir,-1,1) <> '/' )
  // fixme lancer des exceptions plutot
  trigger_error( 'The constante "tamsuft_plugins_dir" must have a slash "/" at his end.', E_USER_ERROR );

// todo vire ça
if( ! defined( 'PHP_EXT' ) )
  /**
   * Extension des scrits PHP
   *
   * @var string
   */
  define( 'PHP_EXT', '.php' );

unset($tamsuft_index);
unset($tamsuft_file);
unset($tamsuft_dir);

set_include_path( get_include_path().PATH_SEPARATOR.tamsuft_dir.PATH_SEPARATOR.tamsuft_plugins_dir );

/**
 * Seul contrainte au lancement de tamsuft.
 *
 * @ignore
 */
if( ! is_file( tamsuft_dir.'core.tamsuft'.PHP_EXT )
  and ! is_readable( tamsuft_dir.'core.tamsuft'.PHP_EXT ) and false )
    // fixme lancer des exceptions personalise
    throw new Exception( 'Configuration error' );

/**
 * Inclusion de la classe de base de tamsuft.
 */
if( extension_loaded('xend') )
  require_once xend('core.tamsuft.php');
else
  null;

if( false )
require_once 'core.tamsuft.php2';

if( false ) :
elseif( false ) :
else :
endif;

while( false ) :
endwhile;

foreach( array() as $a ) :
endforeach;

for( $i=0; $i<1; $i++ ) :
endfor;

// vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 fileformat=unix foldmethod=marker encoding=utf8

/**
 * Plugins de démarrage.
 */

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

__halt_compiler();

bla bla connerie haha hihi

?>
