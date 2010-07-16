<?php
namespace dstyledoc\converter;

require_once 'converter.php';

use dstyledoc\Converter;

abstract class ConverterHTML extends Converter
{
  // {{{ convert_title()

  public function convert_title( $title, DstyleDoc_Element $element )
  {
    return $title;
  }

  // }}}
  // {{{ convert_description()

  public function convert_description( $description, CustomElement $element )
  {
    return implode('<br />',$description);
  }

  // }}}
  // {{{ convert_licence()

  public function convert_licence( $licence, DstyleDoc_Custom_Element $element )
  {
    return '<tt>'.implode('<br />',$licence).'</tt>';
  }

  // }}}
	// {{{ convert_id()

  public function convert_id( $id, DstyleDoc_Element $element )
  {
    if( is_array($id) )
      $id = implode('_', $id);

    return $this->html_id( (string)$id );
  }

  // }}}
  // {{{ convert_link()

  public function convert_link( $id, $name, DstyleDoc_Element $element )
  {
    return <<<HTML
<a href="{$id}">{$name}</a>
HTML;
  }

  // }}}
  // {{{ convert_display()

  public function convert_display( $name, CustomElement $element )
  {
    return (string)htmlspecialchars( $name );
  }

  // }}}
  // {{{ convert_text()

  /**
   * Todo: C'est utilisé ça ?
   */
  public function convert_text( $text )
  {
    return htmlspecialchars($text);
  }

  // }}}
  // {{{ convert_php()

  public function convert_php( $code )
  {
    return highlight_string( "<?php\n{$code}\n?>", true );
  }

	// }}}
	// {{{ convert_sql( $code )

	public function convert_sql( $code )
	{
		return "<code>{$code}</code>";
	}

  // }}}
  // {{{ convert_todo()

  public function convert_todo( $todo )
  {
    return implode('<br />',$todo);
  }

  // }}}
  // {{{ html_id()

  /**
   * S'assure que les caratères contenu dans la chaîne sont acceptées dans la valeur d'un attribut ID HTML.
   * Params:
   *    string $string = La chaîne a traiter.
   * Returns:
   *    string La chaîne traiter.
   */
  protected function html_id( $string )
  {
    return (string)preg_replace( '/(?:(?<=^)[^a-z]|[^-_a-z0-9])/', '_', strtolower((string)$string) );
  }

  // }}}
}
