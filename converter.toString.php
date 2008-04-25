<?php

abstract class DstyleDoc_Converter_HTML extends DstyleDoc_Converter
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
    return implode('<br />',$description);
  }

  // }}}
}

/**
 * Convertisseur qui affiche du HTML.
 */
class DstyleDoc_Converter_toString extends DstyleDoc_Converter_HTML
{
  // {{{ forall()

  protected function forall( $var, $eval )
  {
    $result = '';
    foreach( $var as $key => $value )
      $result .= eval('return "'.$eval.'";');
    return $result;
  }

  // }}}
  // {{{ either()

  protected function either( $if = false, $then = null, $else = null )
  {
    if( $if )
    {
      if( is_string($then) )
        return eval('return stripslashes(\''.addslashes($then).'\');');
    }
    else
      if( is_string($else) )
        return eval('return stripslashes(\''.addslashes($else).'\');');
  }

  // }}}
  // {{{ id()

  protected function convert_id( $id )
  {
    return $id;
  }

  // }}}
  // {{{ convert_all()

  public function convert_all()
  {
    echo <<<HTML
<style>
dl dt { margin-top: 0px; font-weight: bold; }
dl dd { margin-left: 20px; }
</style>
HTML;

    $this->all_functions();
/*

    foreach( $this->classes as $class )
    {
      echo <<<HTML
<hr /><h1 id="{$this->id($class)}">class: {$class->name}</h1>
<dl>
{$this->element_filed($class)}
{$this->either($class->parent,'<dt>extend</dt><dd>'.$this->link($class->parent).'</dd>')}
{$this->either($class->implements,'<dt>implement</dt><dd>'.$this->forall($class->implements,'<li>{$this->link($value)}</li>').'</dd>')}
</dl>
HTML;
    }

    foreach( $this->interfaces as $interface )
    {
      echo <<<HTML
<hr /><h1 id="{$this->id($interface)}">interface: {$interface->name}</h1>
<dl>
{$this->element_filed($interface)}
</dl>
HTML;
    }

    foreach( $this->functions as $function )
    {
      echo <<<HTML
<hr /><h1 id="{$this->id($function)}">function: {$function->name}</h1>
<dl>
{$this->element_filed($function)}
</dl>
HTML;*/
  }

  protected function all_functions()
  {
    echo <<<HTML
<h1>Indexes des fonctions</h1>
<ul>
HTML;
    foreach( $this->fonctions
  }

  // }}}
  // {{{ element_filed()

  protected function element_filed( DstyleDoc_Element $element )
  {
    return <<<HTML
<dt>file</dt><dd>{$element->file}</dd>
<dt>line</dt><dd>{$element->line}</dd>
{$this->either($element->title,'<dt>title</dt><dd>'.$element->title.'</dd>')}
{$this->either($element->description,'<dt>description</dt><dd>'.$element->description.'</dd>')}
{$this->either($element->version,'<dt>version</dt><dd>'.$element->version.'</dd>')}
{$this->either($element->historys,'<dt>history</dt><dd>'.$this->forall($element->historys,'<li><b>{$value->version}: </b>{$value->description}</li>').'</dd>')}
HTML;
  }

  // }}}
}

// }}}

?>
