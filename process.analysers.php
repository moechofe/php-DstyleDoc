<?php

/**
 * Interface de la base des analysers
 */
interface DstyleDoc_Analyseable
{
  // {{{ analyse()

  static function analyse( $current, $source, &$instance, &$priority );

  // }}}
  // {{{ apply()

  function apply( DstyleDoc_Element $element );

  // }}}
}

interface DstyleDoc_Analyser_Descriptable
{
  // {{{ descriptable()

  function descriptable( DstyleDoc_Element $element, $description );

  // }}}
}

/**
 * Classe abstraite de la base des analysers.
 */
abstract class DstyleDoc_Analyser extends DstyleDoc_Properties implements DstyleDoc_Analyseable
{
  // {{{ remove_stars()

  static public function remove_stars( $source )
  {
    // ^\s*(?:\/*\**|\**)\s*(.*?)\s*(?:\**\/|\**)$
    if( preg_match( '/^\\s*(?:\\/*\\**|\\**)\\s*(.*?)\\s*(?:\\**\\/|\\**)$/', $source, $matches ) )
      return $matches[1];
    else
      return $source;
  }

  // }}}
  // {{{ finalize()

  static public function finalize( DstyleDoc_Element $element )
  {
  }

  // }}}
}

/**
 * Classe d'analyse de la description.
 */
class DstyleDoc_Analyser_Description extends DstyleDoc_Analyser
{
  // {{{ priority

  const priority = 100;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    if( $source and $current instanceof DstyleDoc_Analyser_Descriptable )
    {
      $instance = new self( $source );
      $priority = self::priority;
      $instance->descriptable = $current;
      return true;
    }
    elseif( $source )
    {
      $instance = new self( $source );
      $priority = self::priority;
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute une nouvelle ligne de description à l'élément.
   */
  public function apply( DstyleDoc_Element $element )
  {
    if( $this->descriptable )
    {
      $this->descriptable->descriptable( $element, $this->description );
      return $this->descriptable;
    }
    else
    {
      $element->description = $this->description;
      return $this;
    }
  }

  // }}}
  // {{{ $description

  protected $_description = '';

  protected function set_description( $description )
  {
    $this->_description = (string)$description;
  }

  protected function get_description()
  {
    return $this->_description;
  }

  // }}}
  // {{{ __construct()

  protected function __construct( $description )
  {
    $this->description = $description;
  }

  // }}}
  // {{{ $descriptable

  protected $_descriptable = null;

  protected function set_descriptable( DstyleDoc_Analyser_Descriptable $descriptable )
  {
    $this->_descriptable = $descriptable;
  }

  protected function get_descriptable()
  {
    return $this->_descriptable;
  }

  // }}}
}

/**
 * Classe d'analyse d'un séparateur de paragraphe la description
 */
class DstyleDoc_Analyser_Description_Paragraphe extends DstyleDoc_Analyser
{
  // {{{ priority

  const priority = 1000;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    if( $current instanceof DstyleDoc_Analyser_Description and $source === '' )
    {
      $instance = new self();
      $priority = self::priority;
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute un nouveau paragraphe à la description à l'élément.
   * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
   */
  public function apply( DstyleDoc_Element $element )
  {
    if( count($element->descriptions) > 1 )
    {
      list($last) = array_reverse($element->descriptions);
      if( $last !== '' )
        $element->description = '';
    }
    return $this;
  }

  // }}}
  // {{{ finalize()

  static public function finalize( DstyleDoc_Element $element )
  {
    if( count($element->descriptions) > 1 )
    {
      list($last) = array_reverse($element->descriptions);
      if( $last === '' )
      {
        $new = $element->descriptions;
        array_pop($new);
        $element->descriptions = $new;
      }
    }
  }

  // }}}
}

/**
 * Classe d'analyse d'une balise de version.
 */
class DstyleDoc_Analyser_Version extends DstyleDoc_Analyser
{
  // {{{ $version

  protected $_version = '';

  protected function set_version( $version ) 
  {
    $this->_version = $version;
  }

  protected function get_version()
  {
    return $this->_version;
  }

  // }}}
  // {{{ priority

  const priority = 10;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    // ^version:\s*(.+)$
    if( preg_match( '/^version:\\s*(.+)$/i', $source, $matches ) )
    {
      $instance = new self( $matches[1] );
      $priority = self::priority;
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ __construct()

  protected function __construct( $version )
  {
    $this->version = $version;
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute un nouveau paragraphe à la description à l'élément.
   * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
   */
  public function apply( DstyleDoc_Element $element )
  {
    $element->version = $this->version;
    return $this;
  }

  // }}}
}

/**
 * Classe d'analyse d'une balise d'historique.
 */
class DstyleDoc_Analyser_History extends DstyleDoc_Analyser
{
  // {{{ priority

  const priority = 10;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    // ^history:(?:\s*(?:v|version:?\s*)?(\d.*?)\s*[:=]?(?:\s+(.*)))?$
    if( preg_match( '/^history:(?:\\s*(?:v|version:?\\s*)?(\\d.*?)\\s*[:=]?(?:\\s+(.*)))?$/i', $source, $matches ) )
    {
      $instance = new self();
      $priority = self::priority;
      if( ! empty($matches[1]) )
      {
        $instance = new DstyleDoc_Analyser_Element_History_List( $matches[1], $matches[2] );
        $property = DstyleDoc_Analyser_Element_History_List::priority;
      }
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute un nouveau paragraphe à la description à l'élément.
   * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
   */
  public function apply( DstyleDoc_Element $element )
  {
    return $this;
  }

  // }}}
}

/**
 * Classe d'analyse d'un élément de liste d'historique.
 */
class DstyleDoc_Analyser_Element_History_List extends DstyleDoc_Analyser implements DstyleDoc_Analyser_Descriptable
{
  // {{{ $version

  protected $_version = '';

  protected function set_version( $version ) 
  {
    $this->_version = $version;
  }

  protected function get_version()
  {
    return $this->_version;
  }

  // }}}
  // {{{ $description

  protected $_description = '';

  protected function set_description( $description )
  {
    $this->_description = (string)$description;
  }

  protected function get_description()
  {
    return $this->_description;
  }

  // }}}
  // {{{ descriptable()

  public function descriptable( DstyleDoc_Element $element, $description )
  {
    $element->history->description = $description;
  }

  // }}}
  // {{{ priority

  const priority = 20;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    // ^(?:[-+*]\s*)?(?:v|version:?\s*)?(\d.*?)\s*[:=]?(?:\s+(.*))$
    if( ($current instanceof DstyleDoc_Analyser_History or $current instanceof DstyleDoc_Analyser_Element_History_List)
      and preg_match( '/^(?:[-+*]\\s*)?(?:v|version:?\\s*)?(\\d.*?)\\s*[:=]?(?:\\s+(.*))$/i', $source, $matches ) )
    {
      $instance = new self( $matches[1], $matches[2] );
      $priority = self::priority;
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute un nouveau paragraphe à la description à l'élément.
   * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
   */
  public function apply( DstyleDoc_Element $element )
  {
    $element->history = $this->version;
    $element->history->description = $this->description;
    return $this;
  }

  // }}}
  // {{{ __construct()

  public function __construct( $version, $description )
  {
    $this->version = $version;
    $this->description = $description;
  }

  // }}}
}

/**
 * Classe d'analyse d'une balise de paramètre.
 */
class DstyleDoc_Analyser_Param extends DstyleDoc_Analyser
{
  // {{{ priority

  const priority = 10;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    // ^params?:\s*(?:\s(?:([^\s+]+)\s+)?(?:(\$.+?|\.{3})(?:\s*[:=]?\s+)?)(.*))?$
    if( preg_match( '/^params?:\\s*(?:\\s(?:([^\\s+]+)\\s+)?(?:(\\$.+?|\\.{3})(?:\\s*[:=]?\\s+)?)(.*))?$/i', $source, $matches ) )
    {
      $instance = new self();
      $priority = self::priority;
      if( isset($matches[3]) )
      {
        $instance = new DstyleDoc_Analyser_Element_Param_List( $matches[1], $matches[2], $matches[3] );
        $property = DstyleDoc_Analyser_Element_Param_List::priority;
      }
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ apply()

  public function apply( DstyleDoc_Element $element )
  {
    return $this;
  }

  // }}}
}

/**
 * Classe d'analyse d'un élément de liste de paramètre.
 */
class DstyleDoc_Analyser_Element_Param_List extends DstyleDoc_Analyser implements DstyleDoc_Analyser_Descriptable
{
  // {{{ $types

  protected $_types = '';

  protected function set_types( $types ) 
  {
    $this->_types = (array)$types;
  }

  protected function get_types()
  {
    return $this->_types;
  }

  // }}}
  // {{{ $var

  protected $_var = '';

  protected function set_var( $var ) 
  {
    $this->_var = $var;
  }

  protected function get_var()
  {
    return $this->_var;
  }

  // }}}
  // {{{ $description

  protected $_description = '';

  protected function set_description( $description )
  {
    $this->_description = (string)$description;
  }

  protected function get_description()
  {
    return $this->_description;
  }

  // }}}
  // {{{ descriptable()

  public function descriptable( DstyleDoc_Element $element, $description )
  {
    if( $element instanceof DstyleDoc_Element_Function )
      $element->param->description = $description;
  }

  // }}}
  // {{{ priority

  const priority = 15;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    // ^\s*(?:[-+*]\s+)?(?:([^\s]+)\s+)?(?:(\$.+?|\.{3})(?:\s*[:=]?\s+)?)(.*)$
    if( ($current instanceof DstyleDoc_Analyser_Param or $current instanceof DstyleDoc_Analyser_Element_Param_List)
      and preg_match( '/^\\s*(?:[-+*]\\s+)?(?:([^\\s]+)\\s+)?(?:(\\$.+?|\\.{3})(?:\\s*[:=]?\\s+)?)(.*)$/i', $source, $matches ) )
    {
      $instance = new self( $matches[1], $matches[2], $matches[3] );
      $priority = self::priority;
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ apply()

  public function apply( DstyleDoc_Element $element )
  {
    if( $element instanceof DstyleDoc_Element_Function )
    {
      $element->param = $this->var;

      // It's impossible to call a getter twice with the magic method __get()
      //$param = $element->param;
      $param = $element->__get('param');

      if( $this->var )
        $param->var = $this->var;

      foreach( $this->types as $type )
        $param->type = $type;

      $param->description = $this->description;
    }
    return $this;
  }

  // }}}
  // {{{ __construct()

  public function __construct( $types, $var, $description )
  {
    $this->types = preg_split('/[,|]/', $types);
    $this->var = $var;
    $this->description = $description;
  }

  // }}}
}

/**
 * Classe d'analyse d'une balise de retour.
 */
class DstyleDoc_Analyser_Return extends DstyleDoc_Analyser
{
  // {{{ priority

  const priority = 10;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    // ^returns?:\s*(?:\s(?:([^\s+]+)\s+)?(.*))?$
    if( preg_match( '/^returns?:\\s*(?:\\s(?:([^\\s+]+)\\s+)?(.*))?$/i', $source, $matches ) )
    {
      $instance = new self();
      $priority = self::priority;
      if( isset($matches[2]) )
      {
        $instance = new DstyleDoc_Analyser_Element_Return_List( $matches[1], $matches[2] );
        $property = DstyleDoc_Analyser_Element_Return_List::priority;
      }
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute un nouveau paragraphe à la description à l'élément.
   * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
   */
  public function apply( DstyleDoc_Element $element )
  {
    return $this;
  }

  // }}}
}

/**
 * Classe d'analyse d'un élément de liste de retour.
 */
class DstyleDoc_Analyser_Element_Return_List extends DstyleDoc_Analyser implements DstyleDoc_Analyser_Descriptable
{
  // {{{ $types

  protected $_types = '';

  protected function set_types( $types ) 
  {
    $this->_types = (array)$types;
  }

  protected function get_types()
  {
    return $this->_types;
  }

  // }}}
  // {{{ $description

  protected $_description = '';

  protected function set_description( $description )
  {
    $this->_description = (string)$description;
  }

  protected function get_description()
  {
    return $this->_description;
  }

  // }}}
  // {{{ descriptable()

  public function descriptable( DstyleDoc_Element $element, $description )
  {
    if( $element instanceof DstyleDoc_Element_Function )
      $element->return->description = $description;
  }

  // }}}
  // {{{ priority

  const priority = 15;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    // ^\s*(?:-\s+)?(?:(?:([^\s+]+)\s+)?(.*))?$
    if( ($current instanceof DstyleDoc_Analyser_Return or $current instanceof DstyleDoc_Analyser_Element_Return_List)
      and preg_match( '/^\\s*(?:-\\s+)?(?:(?:([^\\s+]+)\\s+)?(.*))?$/i', $source, $matches ) )
    {
      if( ! trim($matches[2]) )
        return false;
      $instance = new self( $matches[1], $matches[2] );
      $priority = self::priority;
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute un nouveau paragraphe à la description à l'élément.
   * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
   */
  public function apply( DstyleDoc_Element $element )
  {
    if( $element instanceof DstyleDoc_Element_Function )
    {
      foreach( $this->types as $type )
        $element->return->type = $type;

      $element->return->description = $this->description;
    }
    return $this;
  }

  // }}}
  // {{{ __construct()

  public function __construct( $types, $description )
  {
    $this->types = preg_split('/[,|]/', $types);
    $this->description = $description;
  }

  // }}}
}

/**
 * Classe d'analyse d'une balise de version.
 */
class DstyleDoc_Analyser_Package extends DstyleDoc_Analyser
{
  // {{{ $packages

  protected $_packages = '';

  protected function set_packages( $packages ) 
  {
    $this->_packages = (array)$packages;
  }

  protected function get_packages()
  {
    return $this->_packages;
  }

  // }}}
  // {{{ priority

  const priority = 10;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    // ^package:\s*(.+)$
    if( preg_match( '/^package:\\s*(.+)$/i', $source, $matches ) )
    {
      $instance = new self( $matches[1] );
      $priority = self::priority;
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ __construct()

  protected function __construct( $package )
  {
    $this->packages = preg_split( '/[.,;:> ]+/', $package );
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute un nouveau paragraphe à la description à l'élément.
   * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
   */
  public function apply( DstyleDoc_Element $element )
  {
    $element->packages = $this->packages;
    return $this;
  }

  // }}}
}

/**
 * Classe d'analyse d'une balise d'exception.
 */
class DstyleDoc_Analyser_Throw extends DstyleDoc_Analyser
{
  // {{{ priority

  const priority = 10;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    // ^throws?:\s*(?:\s(?:([\pL\pN]+)\s*)(?:[:=]\s+)?(.*))?$
    if( preg_match( '/^throws?:\\s*(?:\\s(?:([\\pL\\pN]+)\\s*)(?:[:=]\\s+)?(.*))?$/i', $source, $matches ) )
    {
      $instance = new self();
      $priority = self::priority;
      if( isset($matches[2]) )
      {
        $instance = new DstyleDoc_Analyser_Element_Throw_List( $matches[1], $matches[2] );
        $property = DstyleDoc_Analyser_Element_Throw_List::priority;
      }
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute un nouveau paragraphe à la description à l'élément.
   * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
   */
  public function apply( DstyleDoc_Element $element )
  {
    return $this;
  }

  // }}}
}

/**
 * Classe d'analyse d'un élément de liste d'exception.
 */
class DstyleDoc_Analyser_Element_Throw_List extends DstyleDoc_Analyser implements DstyleDoc_Analyser_Descriptable
{
  // {{{ $exception

  protected $_exception = '';

  protected function set_exception( $exception ) 
  {
    $this->_exception = (string)$exception;
  }

  protected function get_exception()
  {
    return $this->_exception;
  }

  // }}}
  // {{{ $description

  protected $_description = '';

  protected function set_description( $description )
  {
    $this->_description = (string)$description;
  }

  protected function get_description()
  {
    return $this->_description;
  }

  // }}}
  // {{{ descriptable()

  public function descriptable( DstyleDoc_Element $element, $description )
  {
    if( $element instanceof DstyleDoc_Element_Function )
      $element->exception->description = $description;
  }

  // }}}
  // {{{ priority

  const priority = 15;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    // \s*(?:[-+*]\s+)?(?:([\pL\pN]+)\s*)(?:[:=]\s+)?(.*)$
    if( ($current instanceof DstyleDoc_Analyser_Throw or $current instanceof DstyleDoc_Analyser_Element_Throw_List)
      and preg_match( '/\\s*(?:[-+*]\\s+)?(?:([\\pL\\pN]+)\\s*)(?:[:=]\\s+)?(.*)$/i', $source, $matches ) )
    {
      if( ! trim($matches[1]) )
        return false;
      $instance = new self( $matches[1], $matches[2] );
      $priority = self::priority;
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute une exception à l'élément.
   */
  public function apply( DstyleDoc_Element $element )
  {
    if( $element instanceof DstyleDoc_Element_Function )
    {
      $element->exception = $this->exception;
      $element->exception->description = $this->description;
    }
    return $this;
  }

  // }}}
  // {{{ __construct()

  public function __construct( $exception, $description )
  {
    $this->exception = $exception;
    $this->description = $description;
  }

  // }}}
}

/**
 * Classe d'analyse d'une balise de syntaxe.
 */
class DstyleDoc_Analyser_Syntax extends DstyleDoc_Analyser
{
  // {{{ priority

  const priority = 10;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    // ^syntax:\s*$
    if( preg_match( '/^syntax:\\s*$/i', $source, $matches ) )
    {
      $instance = new self();
      $priority = self::priority;
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute un nouveau paragraphe à la description à l'élément.
   * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
   */
  public function apply( DstyleDoc_Element $element )
  {
    return $this;
  }

  // }}}
}

/**
 * Classe d'analyse d'un élément de liste de syntaxe.
 * Todo:
 *    utiliser les -_\pLpN
 */
class DstyleDoc_Analyser_Element_Syntax_List extends DstyleDoc_Analyser implements DstyleDoc_Analyser_Descriptable
{
  // {{{ $syntax

  protected $_syntax = array();

  protected function set_syntax( $syntax ) 
  {
    $optional = false;
    foreach( explode(',', $syntax) as $var )
    {
      // \s*(\[?)\s*(?:([-_\pLpN]+)\s)?\s*(\$[-_\pLpN]+|\.{3})\s*\]?
      if( preg_match('/\\s*(\\[?)\\s*(?:([-_\\pLpN]+)\\s)?\\s*(\\$[-_\\pLpN]+|\\.{3})\\s*\\]?/', $var, $matches) )
      {
        if( ! empty($matches[1]) )
          $optional = true;
        $this->_syntax[] = array(
          'types' => $matches[2],
          'var' => $matches[3],
          'optional' => $optional );
      }
    }
  }

  protected function get_syntax()
  {
    return $this->_syntax;
  }

  // }}}
  // {{{ $description

  protected $_description = '';

  protected function set_description( $description )
  {
    $this->_description = (string)$description;
  }

  protected function get_description()
  {
    return $this->_description;
  }

  // }}}
  // {{{ descriptable()

  public function descriptable( DstyleDoc_Element $element, $description )
  {
    if( $element instanceof DstyleDoc_Element_Function )
      $element->syntax->description = $description;
  }

  // }}}
  // {{{ priority

  const priority = 15;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    // (?:[-+*]\s*)?((?:\s*,?\s*\[?\s*(?:[-_\pLpN]+\s+)?(?:\$[-_\pLpN]+|\.{3}))*\]?)\s*[:=]?\s*(.*)$
    if( ($current instanceof DstyleDoc_Analyser_Syntax or $current instanceof DstyleDoc_Analyser_Element_Syntax_List)
      and preg_match( '/(?:[-+*]\\s*)?((?:\\s*,?\\s*\\[?\\s*(?:[-_\\pLpN]+\\s+)?(?:\\$[-_\\pLpN]+|\\.{3}))*\\]?)\\s*[:=]?\\s*(.*)$/', $source, $matches ) )
    {
      if( ! trim($matches[1]) )
        return false;
      $instance = new self( $matches[1], $matches[2] );
      $priority = self::priority;
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute une exception à l'élément.
   */
  public function apply( DstyleDoc_Element $element )
  {
    if( $element instanceof DstyleDoc_Element_Function )
    {
      foreach( $this->syntax as $syntax )
      {
        $element->param = $syntax['var'];
        // It's because It's impossible to call a getter twice with the magic methode __get()
        //$param = $element->param;
        $param = $element->__get('param');
        $param->type = $syntax['types'];
      }
      $element->syntax = $this->syntax;
      $element->syntax->description = $this->description;
    }
    return $this;
  }

  // }}}
  // {{{ __construct()

  public function __construct( $syntax, $description )
  {
    $this->syntax = $syntax;
    $this->description = $description;
  }

  // }}}
}

?>
