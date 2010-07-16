<?php

/**
 * Classe de base pour une ligne ou un morceau de description.
 */
class DstyleDoc_Descritable extends DstyleDoc_Properties
{
  // {{{ $element

  protected $_element = null;

  protected function set_element( DstyleDoc_Custom_Element $element )
  {
    $this->_element = $element;
  }

  protected function get_element()
  {
    return $this->_element;
  }

  // }}}
  // {{{ $content

  protected $_content = '';

  protected function set_content( $content )
  {
    $this->_content = $content;
  }

  protected function get_content()
  {
    return $this->_content;
  }

  // }}}
  // {{{ $append

  protected function set_append( $content )
  {
    $this->_content .= $content;
  }

  // }}}
  // {{{ __construct()

  public function __construct( $content, DstyleDoc_Custom_Element $element )
  {
    $this->content = $content;
    $this->element = $element;
  }

  // }}}
  // {{{ __toString()

  public function __toString()
  {
//    try
//    {
    if( $this instanceof DstyleDoc_Descritable )
    {
      if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'inline')!==false )
      {
        $c = get_class($this);
        $h = htmlspecialchars($this->content);
        echo <<<HTML
<div style='clear:left;float:left;color:white;background:DarkMagenta;padding:1px 3px'>{$c}</div>
<div style='float:left;background:Wheat;padding:1px 3px'>{$h}</div><dt style="margin-left: 22px">
HTML;
      }
      foreach( get_declared_classes() as $class )
        if( in_array('DstyleDoc_Descritable_Analysable',class_implements($class))
          and $result = call_user_func( array($class,'analyse'), $this->content, $this->element) )
        {
          $next = '';
          foreach( $result->nexts as $tmp )
            $next .= (string)$tmp;
          if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'inline')!==false )
          {
            $c = is_object($result->current)?get_class($result->current).': '.htmlspecialchars($result->current->content):gettype($result->current).': '.htmlspecialchars($result->current);
            $r = '';
            foreach( $result->nexts as $n )
              $r .= (is_object($n)?get_class($n).': '.htmlspecialchars($n->content):gettype($n).': '.htmlspecialchars($n)).". ";
            echo <<<HTML
<div style='float:left;background:MistyRose;padding:1px 3px'>{$c}</div>
<div style='background:Gainsboro;padding:1px 3px'>{$r}</div>
HTML;
          }
          return (string)$result->current . $next;
        }
      if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'inline')!==false )
      {
        echo <<<HTML
<div style='clear:both'></div></dt>
HTML;
      }
//    }
/*    catch( Exception $e )
    {
      d( $e )->d5;
      exit;
    }*/
    }

    return (string)$this->element->converter->convert_text( $this->content );
  }

  // }}}
}

interface DstyleDoc_Descritable_Analysable
{
  static function analyse( $content, DstyleDoc_Custom_Element $element );
}

/**
 * Classe de résultat si un analyse a réussi.
 */
final class DstyleDoc_Descritable_Analysable_Replace extends DstyleDoc_Properties
{
  // {{{ $element

  protected $_element = null;

  protected function set_element( DstyleDoc_Custom_Element $element )
  {
    $this->_element = $element;
  }

  protected function get_element()
  {
    return $this->_element;
  }

  // }}}
  // {{{ $current

  protected $_current = null;

  protected function set_current( $current )
  {
    if( is_string($current) and $current )
      $this->_current = $current;
    elseif( $current instanceof DstyleDoc_Descritable and $current->content )
      $this->_current = $current;
  }

  protected function get_current()
  {
    return $this->_current;
  }

  // }}}
  // {{{ $nexts

  protected $_nexts = array();

  protected function set_next( $next )
  {
    if( is_string($next) and $next )
      $this->_nexts[] = $next;
    elseif( $next instanceof DstyleDoc_Descritable and $next->content )
      $this->_nexts[] = $next;
  }

  protected function get_nexts()
  {
    return $this->_nexts;
  }

  // }}}
  // {{{ __construct()

  public function __construct()
  {
    $args = func_get_args();
    if( $args )
      $this->current = array_shift($args);
    foreach( $args as $arg )
      $this->next = $arg;
  }

  // }}}
}

/* todo: c'est beaucoup le bordel cette partie
 on car on cherche pas les element dans l'ordre dans lequel on les trouve mais dans un ordre de type
 et comme la fonction est recusrive.
 $content est une reference
 */
class DstyleDoc_Descritable_Link implements DstyleDoc_Descritable_Analysable
{
  // {{{ result()

  static private function result( $content, $offset, $link, $length, DstyleDoc_Custom_Element $element )
  {
    $return = new DstyleDoc_Descritable_Analysable_Replace;
    $return->element = $element;
    if( $offset > 1 )
    {
      $return->current = new DstyleDoc_Descritable( substr($content, 0, $offset), $element );
      $return->next = $link;
      $return->next = new DstyleDoc_Descritable( substr($content, $offset+$length), $element );
    }
    else
    {
      $return->current = $link;
      $return->next = new DstyleDoc_Descritable( substr($content, $length), $element );
    }
    return $return;
  }

  // }}}
  // {{{ analyse()

  static public function analyse( $content, DstyleDoc_Custom_Element $element )
  {
    // search for a java doc compatible inline link
    // {@link\s+([-_\pLpN]*(?:::|->)?\$?[-_\pLpN]+(?:\(\))?)\s*(\s[^}]+)?}
    if( preg_match( '/\{@link\s+([-_\pLpN]*(?:::|->)?\$?[-_\pLpN]+(?:\(\))?)\s*(\s[^}]+)?\}/', $content, $match, PREG_OFFSET_CAPTURE ) )
    {
      if( $scheme = @parse_url( $match[2][0], PHP_URL_SCHEME ) )
        return self::result( $content, $match[0][1], $match[2][0], strlen($match[0][0]), $element );
      elseif( $result = self::analyse( $match[1][0], $element ) )
      {
        if( ! empty($match[2][0]) )
          return self::result( $content, $match[0][1], $result->element->link( $match[2][0] ), strlen($match[0][0]), $element );
        else
          return self::result( $content, $match[0][1], $result->element->link, strlen($match[0][0]), $element );
      }
    }

    // search for function or method without the object, class or interface reference
    // faut-il ajouter l'option /S ?
    // (?<!::|->)\b[-_\pLpN]+\(\)\B
    if( preg_match( '/(?<!::|->)\b([-_\pLpN]+)\(\)\B/', $content, $match, PREG_OFFSET_CAPTURE ) )
    {
      if( $found = $element->converter->function_exists( $match[1][0] ) )
        return self::result( $content, $match[0][1], $found->link, strlen($match[0][0]), $found );
      elseif( $found = $element->converter->method_exists( $element, $match[1][0] ) )
        return self::result( $content, $match[0][1], $found->link, strlen($match[0][0]), $found );
    }

    // search for method with object, class or interface reference
    // \b([-_\pLpN]+)(?:::|->)([-_\pLpN]+)\(\)\B
    if( preg_match( '/\b([-_\pLpN]+)(?:::|->)([-_\pLpN]+)\(\)\B/', $content, $match, PREG_OFFSET_CAPTURE ) )
    {
      if( $found = $element->converter->method_exists( $match[1][0], $match[2][0] ) )
        return self::result( $content, $match[0][1], $found->link, strlen($match[0][0]), $found );
    }

    // search for a member
    // (?:([-_\pLpN]+)(?:::|->))?\B\$([-_\pLpN]+)\b
    if( preg_match( '/(?:([-_\pLpN]+)(?:::|->))?\B\$([-_\pLpN]+)\b/', $content, $match, PREG_OFFSET_CAPTURE ) )
    {
      if( $found = $element->converter->member_exists( $match[1][0], $match[2][0] ) )
        return self::result( $content, $match[0][1], $found->link, strlen($match[0][0]), $found );
      elseif( $found = $element->converter->member_exists( $element, $match[2][0] ) )
        return self::result( $content, $match[0][1], $found->link, strlen($match[0][0]), $found );
    }

    // search for a class
    // (?<!\)|::|->|\$)\b([-_\pLpN]+)\b(?!\(|::|->|\$)
    if( preg_match_all( '/(?<!\)|::|->|\$)\b([-_\pLpN]+)\b(?!\(|::|->|\$)/', $content, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER ) )
    {
      foreach( $matches as $match )
      {
        if( $found = $element->converter->class_exists( $match[1][0] ) )
          return self::result( $content, $match[0][1], $found->link, strlen($match[0][0]), $found );
      }
    }

    return false;
  }

  // }}}
}

/**
 * Classe pour une ligne ou un morceau de code de PHP.
 */
class DstyleDoc_Descritable_PHP_Code extends DstyleDoc_Descritable
{
  // {{{ $content

  protected function set_content( $content )
  {
    if( $content )
      $this->_content = $content;
  }

  // }}}
  // {{{ $append

  protected function set_append( $content )
  {
    if( $content )
      $this->_content .= $content;
  }

  // }}}
  // {{{ __toString()

  public function __toString()
  {
    return (string)$this->element->converter->convert_php( $this->content );
  }

  // }}}
}

class DstyleDoc_Descritable_SQL_Code extends DstyleDoc_Descritable implements DstyleDoc_Descritable_Analysable
{
  // {{{ $content

  protected function set_content( $content )
  {
    if( $content )
      $this->_content = $content;
  }

  // }}}
  // {{{ $append

  protected function set_append( $content )
  {
    if( $content )
      $this->_content .= $content;
  }

  // }}}
  // {{{ __toString()

  public function __toString()
  {
    return (string)$this->element->converter->convert_sql( $this->content );
  }

  // }}}
  // {{{ analyse()

  static public function analyse( $content, DstyleDoc_Custom_Element $element )
  {
    // <sql>(.*?)</sql>
    if( preg_match( '%<sql>(.+?)</sql>%si', $content, $match, PREG_OFFSET_CAPTURE ) )
      return self::result( $content, $match[0][1], $match[1][0], strlen($match[0][0]), $element );

    return false;
  }

  // }}}
  // {{{ result()

  static private function result( $content, $offset, $sql_code, $length, DstyleDoc_Custom_Element $element )
  {
    $return = new DstyleDoc_Descritable_Analysable_Replace;
    $return->element = $element;
    if( $offset > 1 )
    {
      $return->current = new DstyleDoc_Descritable( substr($content, 0, $offset), $element );
      $return->next = new self($sql_code, $element);
      $return->next = new DstyleDoc_Descritable( substr($content, $offset+$length), $element );
    }
    else
    {
      $return->current = new self($sql_code, $element);
      $return->next = new DstyleDoc_Descritable( substr($content, $length), $element );
    }
    return $return;
  }

  // }}}
}

