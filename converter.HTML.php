<?php

require_once( 'DstyleDoc.php' );

abstract class DstyleDoc_Converter_HTML extends DstyleDoc_Converter
{
  // {{{ convert_title()

  public function convert_title( $title )
  {
    return $title;
  }

  // }}}
  // {{{ convert_description()

  public function convert_description( $description )
  {
    return implode('<br />',$description);
  }

  // }}}
  // {{{ convert_id()

  public function convert_id( $id )
  {
    if( is_array($id) )
      $id = implode('_', $id);

    return $this->html_id( (string)$id );
  }

  // }}}
  // {{{ convert_link()

  public function convert_link( $id, $name )
  {
    return <<<HTML
<a href="{$id}">{$name}</a>
HTML;
  }

  // }}}
  // {{{ convert_display()

  public function convert_display( $name )
  {
    return (string)htmlspecialchars( $name );
  }

  // }}}
  // {{{ html_id()

  /**
   * S'assure que les carat�res contenu dans la cha�ne sont accept�es dans la valeur d'un attribut ID HTML.
   * Params:
   *    string $string = La cha�ne � traiter.
   * Returns:
   *    string La cha�ne traiter.
   */
  protected function html_id( $string )
  {
    return (string)preg_replace( '/(?:(?<=^)[^a-z]|[^-_a-z0-9])/', '_', strtolower((string)$string) );
  }

  // }}}
}

?>