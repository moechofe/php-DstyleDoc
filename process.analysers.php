<?php

/**
 * Interface de la base des analysers
 */
interface DstyleDoc_Analyseable
{
  // {{{ analyse()

  static function analyse( $current, $source, &$instance, &$priority, DstyleDoc $dsd );

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

  static public function analyse( $current, $source, &$instance, &$priority, DstyleDoc $dsd )
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
      $element->description = new DstyleDoc_Descritable( $this->description, $element );
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

  static public function analyse( $current, $source, &$instance, &$priority, DstyleDoc $dsd )
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

  static public function analyse( $current, $source, &$instance, &$priority, DstyleDoc $dsd )
  {
    // ^version\s*:\\s*(.+)$
    if( $dsd->dstyledoc and $dsd->version and preg_match( '/^version\\s*:\\s*(.+)$/i', $source, $matches ) )
    {
      $instance = new self( $matches[1] );
      $priority = self::priority;
      return true;
    }

    // ^@version\s*(.+)$
    elseif( $dsd->javadoc_link and $dsd->javadoc_version and preg_match( '/^@version\s*(.+)$/i', $source, $matches ) )
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

  static public function analyse( $current, $source, &$instance, &$priority, DstyleDoc $dsd )
  {
    // ^history\s*:(?:\s*(?:v|version:?\s*)?(\d.*?)\s*[:=]?(?:\s+(.*)))?$
    if( $dsd->dstyledoc and $dsd->history and preg_match( '/^history\\s*:(?:\\s*(?:v|version:?\\s*)?(\\d.*?)\\s*[:=]?(?:\\s+(.*)))?$/i', $source, $matches ) )
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

  static public function analyse( $current, $source, &$instance, &$priority, DstyleDoc $dsd )
  {
    // ^(?:[-+*]\s+)?(?:v|version:?\s*)?(\d.*?)\s*[:=]\s*(?:\s+(.*))$
    if( $dsd->dstyledoc and $dsd->history and ($current instanceof DstyleDoc_Analyser_History or $current instanceof DstyleDoc_Analyser_Element_History_List)
      and preg_match( '/^(?:[-+*]\\s+)?(?:v|version:?\\s*)?(\\d.*?)\\s*[:=]\\s*(?:\\s+(.*))$/', $source, $matches ) )
    {
      $instance = new self( $matches[1], $matches[2] );
      $priority = self::priority;
      return true;
    }
  
    // ^(?:[-+*]\s+)(?:v|version:?\s*)?(\d.*?)\s*[:=]?\s*(?:\s+(.*))$
    elseif( $dsd->dstyledoc and $dsd->history and ($current instanceof DstyleDoc_Analyser_History or $current instanceof DstyleDoc_Analyser_Element_History_List)
      and preg_match( '/^(?:[-+*]\\s+)(?:v|version:?\\s*)?(\\d.*?)\\s*[:=]?\\s*(?:\\s+(.*))$/i', $source, $matches ) )
    {
      $instance = new self( $matches[1], $matches[2] );
      $priority = self::priority;
      return true;
    }

    // ^(?:@history\s+)(?:v|version:?\s*)?(\d.*?)\s*[:=]?\s*(?:\s+(.*))$
    elseif( $dsd->javadoc and $dsd->javadoc_history and preg_match( '/^(?:@history\\s+)(?:v|version:?\\s*)?(\\d.*?)\\s*[:=]?\\s*(?:\\s+(.*))$/i', $source, $matches ) )
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

  static public function analyse( $current, $source, &$instance, &$priority, DstyleDoc $dsd )
  {
    // ^params?\s*:\s*(?:\s(?:([^\s+]+)\s+)?(?:(\$.+?|\.{3})(?:\s*[:=]?\s+)?)(.*))?$
    if( $dsd->dstyledoc and $dsd->params and preg_match( '/^params?\\s*:\\s*(?:\\s(?:([^\\s+]+)\\s+)?(?:(\\$.+?|\\.{3})(?:\\s*[:=]?\\s+)?)(.*))?$/i', $source, $matches ) )
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

  protected function set_type( $type )
  {
    $this->_types[] = (string)$type;
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

  static public function analyse( $current, $source, &$instance, &$priority, DstyleDoc $dsd )
  {
    // ^(?:[-+*]\s+)?(?:([-_,\| \pLpN]+)\s+)?(\$[-_\pLpN]+|\.{3})\s*[:=]\s*(.*)$
    if( $dsd->dstyledoc and $dsd->params and ($current instanceof DstyleDoc_Analyser_Param or $current instanceof DstyleDoc_Analyser_Element_Param_List)
      and preg_match( '/^(?:[-+*]\\s+)?(?:([-_,\\| \\pLpN]+)\\s+)?(\\$[-_\\pLpN]+|\\.{3})\\s*[:=]\\s*(.*)$/', $source, $matches ) )
    {
      $instance = new self( $matches[1], $matches[2], $matches[3] );
      $priority = self::priority;
      return true;
    }

    // ^(?:[-+*]\s+)(?:([-_,\| \pLpN]+)\s+)?(\$[-_\pLpN]+|\.{3})\s*[:=]?\s*(.*)$
    elseif( $dsd->dstyledoc and $dsd->params and ($current instanceof DstyleDoc_Analyser_Param or $current instanceof DstyleDoc_Analyser_Element_Param_List)
      and preg_match( '/^(?:[-+*]\\s+)(?:([-_,\\| \\pLpN]+)\\s+)?(\\$[-_\\pLpN]+|\\.{3})\\s*[:=]?\\s*(.*)$/i', $source, $matches ) )
    {
      $instance = new self( $matches[1], $matches[2], $matches[3] );
      $priority = self::priority;
      return true;
    }

    // ^(?:@params?\s+)(?:([-_,\| \pLpN]+)\s+)?(\$[-_\pLpN]+|\.{3})\s*[:=]?\s*(.*)$
    elseif( $dsd->javadoc and $dsd->javadoc_params and preg_match( '/^(?:@params?\\s+)(?:([-_,\\| \\pLpN]+)\\s+)?(\\$[-_\\pLpN]+|\\.{3})\\s*[:=]?\\s*(.*)$/i', $source, $matches ) )
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

      if( $this->var )
        $element->param->var = $this->var;

      foreach( $this->types as $type )
        $element->param->type = $type;

      $element->param->description = $this->description;
    }
    return $this;
  }

  // }}}
  // {{{ __construct()

  public function __construct( $types, $var, $description )
  {
    foreach( preg_split('/[,|]/', $types) as $type )
      $this->type = trim($type);
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

  static public function analyse( $current, $source, &$instance, &$priority, DstyleDoc $dsd )
  {
    // ^returns?\s*:\s*(:([-_\pLpN]+)\s*[:=]?\s*(.*))?$
    if( $dsd->dstyledoc and $dsd->returns and preg_match( '/^returns?\\s*:\\s*(?:([-_\\pLpN]+)\\s*[:=]?\\s*(.*))?$/i', $source, $matches ) )
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
  // {{{ $type

  protected $_type = '';

  protected function set_type( $type ) 
  {
    $this->_type = (string)$type;
  }

  protected function get_type()
  {
    return $this->_type;
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

  static public function analyse( $current, $source, &$instance, &$priority, DstyleDoc $dsd )
  {
    // ^(?:[-+*]\s+)?([-_\pLpN]+)\s*[:=]\s*(.*)$
    if( $dsd->dstyledoc and $dsd->returns and ($current instanceof DstyleDoc_Analyser_Return or $current instanceof DstyleDoc_Analyser_Element_Return_List)
      and preg_match( '/^(?:[-+*]\\s+)?([-_\\pLpN]+)\\s*[:=]\\s*(.*)$/', $source, $matches ) )
      {
        $instance = new self( $matches[1], $matches[2] );
        $priority = self::priority;
        return true;
      }

    // ^(?:[-+*]\s+)([-_\pLpN]+)\s*[:=]?\s*(.*)$
    elseif( $dsd->dstyledoc and $dsd->returns and ($current instanceof DstyleDoc_Analyser_Return or $current instanceof DstyleDoc_Analyser_Element_Return_List)
      and preg_match( '/^(?:[-+*]\\s+)([-_\\pLpN]+)\\s*[:=]?\\s*(.*)$/i', $source, $matches ) )
      {
        $instance = new self( $matches[1], $matches[2] );
        $priority = self::priority;
        return true;
      }

    // ^(?:@returns?\s+)([-_\pLpN]+)\s*[:=]?\s*(.*)$
    elseif( $dsd->javadoc and $dsd->javadoc_returns and preg_match( '/^(?:@returns?\\s+)([-_\\pLpN]+)\\s*[:=]?\\s*(.*)$/i', $source, $matches ) )
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
    if( $element instanceof DstyleDoc_Element_Function )
    {
      $element->return = $this->type;
      $element->return->description = $this->description;
    }
    return $this;
  }

  // }}}
  // {{{ __construct()

  public function __construct( $type, $description )
  {
    $this->type = $type;
    $this->description = $description;
  }

  // }}}
}

/**
 * Classe d'analyse d'une balise de paquetage.
 * todo: compatible javadoc package and subpackage.
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

  static public function analyse( $current, $source, &$instance, &$priority, DstyleDoc $dsd )
  {
    // ^package\s*:\s*(.+)$
    if( $dsd->dstyledoc and $dsd->javadoc_package and preg_match( '/^package\\s*:\\s*(.+)$/i', $source, $matches ) )
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

  static public function analyse( $current, $source, &$instance, &$priority, DstyleDoc $dsd )
  {
    // ^throws?\s*:\s*(?:\s(?:([\pL\pN]+)\s*)(?:[:=]\s+)?(.*))?$
    if( $dsd->dstyledoc and $dsd->throws and preg_match( '/^throws?\\s*:\\s*(?:\\s(?:([\\pL\\pN]+)\\s*)(?:[:=]\\s+)?(.*))?$/i', $source, $matches ) )
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

  static public function analyse( $current, $source, &$instance, &$priority, DstyleDoc $dsd )
  {
    // ^\s*(?:[-+*]\s+)?([-_\pL\pN]+)\s*[:=]\s*(.*)$
    if( $dsd->dstyledoc and $dsd->throws and ($current instanceof DstyleDoc_Analyser_Throw or $current instanceof DstyleDoc_Analyser_Element_Throw_List)
      and preg_match( '/\\s*(?:[-+*]\\s+)?([-_\\pL\\pN]+)\\s*[:=]\\s*(.*)$/', $source, $matches ) )
      {
        $instance = new self( $matches[1], $matches[2] );
        $priority = self::priority;
        return true;
      }

    // ^\s*(?:[-+*]\s)([-_\pL\pN]+)\s*[:=]?\s*(.*)$
    elseif( $dsd->dstyledoc and $dsd->throws and ($current instanceof DstyleDoc_Analyser_Throw or $current instanceof DstyleDoc_Analyser_Element_Throw_List)
      and preg_match( '/\\s*(?:[-+*]\\s+)([-_\\pL\\pN]+)\\s*[:=]?\\s*(.*)$/i', $source, $matches ) )
      {
        $instance = new self( $matches[1], $matches[2] );
        $priority = self::priority;
        return true;
      }

    // ^\s*(?:@throws?\s+|@exceptions?\s+)([-_\pL\pN]+)\s*[:=]?\s*(.*)$
    elseif( $dsd->javadoc and $dsd->javadoc_exception and preg_match( '/\\s*(?:@throws?\\s+|@exceptions?\\s+)([-_\\pL\\pN]+)\\s*[:=]?\\s*(.*)$/i', $source, $matches ) )
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

  static public function analyse( $current, $source, &$instance, &$priority, DstyleDoc $dsd )
  {
    // ^syntax\s*:\s*$
    if( $dsd->dstyledoc and $dsd->syntax and preg_match( '/^syntax\\s*:\\s*$/i', $source, $matches ) )
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

  /**
   * Analyse la ligne de documentation à la recherche d'un probable élément de liste de syntax.
   * Params:
   *    DstyleDoc_Analyser $current = L'instance de la ligne précédante.
   *    string $source = La ligne de documentation à tester.
   *    DstyleDoc_Analyser_Syntax,null $instance = L'instance à retourné en cas de succès.
   *    integer $priority = La priorité de l'élément trouvé en cas de succès.
   * Returns:
   *    boolean = Renvoie true en case de succès, sinon false.
   */
  static public function analyse( $current, $source, &$instance, &$priority, DstyleDoc $dsd )
  {
    // ^(?:[-+*]\s+)?((?:\s*,?\s*\[?\s*(?:[-_\pLpN]+\s+)?(?:\$[-_\pLpN]+|\.{3}))*\]?)\s*[:=]\s*(.*)$
    if( $dsd->dstyledoc and $dsd->syntax and ($current instanceof DstyleDoc_Analyser_Syntax or $current instanceof DstyleDoc_Analyser_Element_Syntax_List)
      and preg_match( '/^(?:[-+*]\\s+)?((?:\\s*,?\\s*\\[?\\s*(?:[-_\\pLpN]+\\s+)?(?:\\$[-_\\pLpN]+|\\.{3}))*\\]?)\\s*[:=]\\s*(.*)$/', $source, $matches ) )
      {
        $instance = new self( $matches[1], $matches[2] );
        $priority = self::priority;
        return true;
      }

    // ^(?:[-+*]\s+)((?:\s*,?\s*\[?\s*(?:[-_\pLpN]+\s+)?(?:\$[-_\pLpN]+|\.{3}))*\]?)\s*[:=]?\s*(.*)$
    elseif( $dsd->dstyledoc and $dsd->syntax and ($current instanceof DstyleDoc_Analyser_Syntax or $current instanceof DstyleDoc_Analyser_Element_Syntax_List)
      and preg_match( '/^(?:[-+*]\\s+)((?:\\s*,?\\s*\\[?\\s*(?:[-_\\pLpN]+\\s+)?(?:\\$[-_\\pLpN]+|\\.{3}))*\\]?)\\s*[:=]?\\s*(.*)$/i', $source, $matches ) )
      {
        $instance = new self( $matches[1], $matches[2] );
        $priority = self::priority;
        return true;
      }

    // ^(?:@syntax\s+)((?:\s*,?\s*\[?\s*(?:[-_\pLpN]+\s+)?(?:\$[-_\pLpN]+|\.{3}))*\]?)\s*[:=]?\s*(.*)$
    elseif( $dsd->javadoc and $dsd->javadoc_syntax and preg_match( '/^(?:@syntax\\s+)((?:\\s*,?\\s*\\[?\\s*(?:[-_\\pLpN]+\\s+)?(?:\\$[-_\\pLpN]+|\\.{3}))*\\]?)\\s*[:=]?\\s*(.*)$/i', $source, $matches ) )
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
   * Ajoute une exception à l'élément.
   */
  public function apply( DstyleDoc_Element $element )
  {
    if( $element instanceof DstyleDoc_Element_Function )
    {
      foreach( $this->syntax as $syntax )
      {
        $element->param = $syntax['var'];
        $element->param->type = $syntax['types'];
        $element->param->optional = $syntax['optional'];
      }
      $element->syntax = $this->syntax;
      $element->syntax->description = $this->description;
      $element->syntax->function = $element;
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

/**
 * Classe d'analyse d'une balise de type.
 */
class DstyleDoc_Analyser_Type extends DstyleDoc_Analyser
{
  // {{{ priority

  const priority = 10;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority, DstyleDoc $dsd )
  {
    // ^(?:types?|vars?)\s*:\s*(:([-_\pLpN]+)\s*[:=]?\s*(.*))?$
    if( $dsd->dstyledoc and $dsd->type and preg_match( '/^(?:types?|vars?)\\s*:\\s*(?:([-_\\pLpN]+)\\s*[:=]?\\s*(.*))?$/i', $source, $matches ) )
    {
      $instance = new self();
      $priority = self::priority;
      if( isset($matches[2]) )
      {
        $instance = new DstyleDoc_Analyser_Element_Type_List( $matches[1], $matches[2] );
        $property = DstyleDoc_Analyser_Element_Type_List::priority;
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
 * Classe d'analyse d'un élément de type.
 */
class DstyleDoc_Analyser_Element_Type_List extends DstyleDoc_Analyser implements DstyleDoc_Analyser_Descriptable
{
  // {{{ $type

  protected $_type = '';

  protected function set_type( $type ) 
  {
    $this->_type = (string)$type;
  }

  protected function get_type()
  {
    return $this->_type;
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

  static public function analyse( $current, $source, &$instance, &$priority, DstyleDoc $dsd )
  {
    // ^(?:[-+*]\s+)?([-_\pLpN]+)\s*[:=]\s*(.*)$
    if( $dsd->dstyledoc and $dsd->type and ($current instanceof DstyleDoc_Analyser_Type or $current instanceof DstyleDoc_Analyser_Element_Type_List)
      and preg_match( '/^(?:[-+*]\\s+)?([-_\\pLpN]+)\\s*[:=]\\s*(.*)$/', $source, $matches ) )
      {
        $instance = new self( $matches[1], $matches[2] );
        $priority = self::priority;
        return true;
      }

    // ^(?:[-+*]\s+)([-_\pLpN]+)\s*[:=]?\s*(.*)$
    elseif( $dsd->dstyledoc and $dsd->type and ($current instanceof DstyleDoc_Analyser_Type or $current instanceof DstyleDoc_Analyser_Element_Type_List)
      and preg_match( '/^(?:[-+*]\\s+)([-_\\pLpN]+)\\s*[:=]?\\s*(.*)$/i', $source, $matches ) )
      {
        $instance = new self( $matches[1], $matches[2] );
        $priority = self::priority;
        return true;
      }

    // ^(?:@(?:vars?|types?)\s+)([-_\pLpN]+)\s*[:=]?\s*(.*)$
    elseif( $dsd->javadoc and $dsd->javadoc_var and preg_match( '/^(?:@types?\\s+|@vars?\\s+)([-_\\pLpN]+)\\s*[:=]?\\s*(.*)$/i', $source, $matches ) )
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
    if( $element instanceof DstyleDoc_Element_Member )
    {
      $element->type = $this->type;
      $element->type->description = $this->description;
    }
    return $this;
  }

  // }}}
  // {{{ __construct()

  public function __construct( $type, $description )
  {
    $this->type = $type;
    $this->description = $description;
  }

  // }}}
}

/**
 * Classe d'analyse d'une balise de version minimale.
 */
class DstyleDoc_Analyser_Since extends DstyleDoc_Analyser_Version
{
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority, DstyleDoc $dsd )
  {
    // ^since\s*:\\s*(.+)$
    if( $dsd->dstyledoc and $dsd->since and preg_match( '/^since\\s*:\\s*(.+)$/i', $source, $matches ) )
    {
      $instance = new self( $matches[1] );
      $priority = self::priority;
      return true;
    }

    // ^@since\s*(.+)$
    elseif( $dsd->javadoc and $dsd->javadoc_since and preg_match( '/^@since\s*(.+)$/i', $source, $matches ) )
    {
      $instance = new self( $matches[1] );
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
    $element->since = $this->version;
    return $this;
  }

  // }}}
}

class DstyleDoc_Analyser_PHPCode extends DstyleDoc_Analyser
{
  // {{{ $code

  protected $_code = '';

  protected function set_code( $code ) 
  {
    $this->_code = (string)$code;
  }

  protected function get_code()
  {
    return $this->_code;
  }

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority, DstyleDoc $dsd )
  {
    return false;
  }

  // }}}
  // {{{ apply()

  public function apply( DstyleDoc_Element $element )
  {
    $element->description = new DstyleDoc_Descritable_PHP_Code( $this->code, $element );
    return $this;
  }

  // }}}
}

/**
 * Classe d'analyse d'une balise de début de code PHP.
 */
class DstyleDoc_Analyser_PHPOpenTag extends DstyleDoc_Analyser
{
  // {{{ priority

  const priority = 25;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority, DstyleDoc $dsd )
  {
    /* ^<\?php(.*)(?<!\?>)$ */
    if( preg_match( '/^<\?php(.*)(?<!\?>)$/i', $source, $matches ) )
      {
        $instance = new DstyleDoc_Analyser_PHPCode( $matches[1] );
        $priority = self::priority;
        return true;
      }

    /* ^<\?php(.*)(?>\?>)$ */
    elseif( preg_match( '/^<\?php(.*)(?>\?>)$/i', $source, $matches ) )
      {
        $instance = new DstyleDoc_Analyser_PHPCode( $matches[1] );
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
    return $this;
  }

  // }}}
  // {{{ __construct()

  public function __construct( $code )
  {
    $this->code = $code;
  }

  // }}}
}

?>
