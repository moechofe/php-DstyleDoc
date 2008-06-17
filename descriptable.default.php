<?php

class DstyleDoc_Descritable_Default extends DstyleDoc_Properties
{
  // {{{ $line

  protected $_line = '';

  protected function set_line( $line ) 
  {
    $this->_line = $line;
  }

  protected function get_line()
  {
    return $this->_line;
  }

  // }}}
  // {{{ __construct()

  public function __construct( $line )
  {
    $this->line = $line;
  }

  // }}}
}

?>
