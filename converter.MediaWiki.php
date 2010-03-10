<?php

class DstyleDoc_Converter_MediaWiki extends DstyleDoc_Converter
{
  // {{{ link()

  protected function link( $value )
  {
    if( $value instanceof DstyleDoc_Element )
      return "<a href=\"#{$value}\">{$value}</a>";
    else
      return (string)$value;
  }

  // }}}
  // {{{ convert_title()

  public function convert_title( $title )
  {
    return $title;
  }

  // }}}
  // {{{ convert_description()

  public function convert_description( $description )
  {
    return implode("\n\n",$description);
  }

  // }}}
  // {{{

  public function convert_all()
  {
    d( $this->classes );
  }

  // }}}
  // {{{ hie()

  static public function hie()
  {
    return new self;
  }

  // }}}
}

?>
