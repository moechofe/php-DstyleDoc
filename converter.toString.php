<?php

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
   * S'assure que les caratères contenu dans la chaîne sont acceptées dans la valeur d'un attribut ID HTML.
   * Params:
   *    string $string = La chaîne à traiter.
   * Returns:
   *    string La chaîne traiter.
   */
  protected function html_id( $string )
  {
    return (string)preg_replace( '/(?:(?<=^)[^a-z]|[^-_a-z0-9])/', '_', strtolower((string)$string) );
  }

  // }}}
}

/**
 * Convertisseur qui affiche du HTML.
 */
class DstyleDoc_Converter_toString extends DstyleDoc_Converter_HTML
{
  // {{{ convert_file()

  public function convert_file( DstyleDoc_Element_File $file )
  {
    return (string)$file->file;
  }

  // }}}
  // {{{ convert_class()

  public function convert_class( DstyleDoc_Element_Class $class )
  {
    return <<<HTML
<hr /><h1 id="{$class->id}">class: {$class->display}</h1>
<dl>
{$this->element_filed($class)}
{$this->either($class->parent,'<dt>extend</dt><dd>'.$class->parent->link.'</dd>')}
{$this->either($class->implements,'<dt>implement</dt><dd>'.$this->forall($class->implements,'<li>{$value->link}</li>').'</dd>')}
</dl>
HTML;
  }

  // }}}
  // {{{ convert_interface()

  public function convert_interface( DstyleDoc_Element_Interface $interface )
  {
    return <<<HTML
<hr /><h1 id="{$interface->id}">interface: {$interface->display}</h1>
<dl>
{$this->element_files($interface)}
</dl>
HTML;
  }

  // }}}
  // {{{ convert_function()

  public function convert_function( DstyleDoc_Element_Function $function )
  {
    return <<<HTML
<hr /><h1 id="{$function->id}">function: {$function->display}</h1>
<dl>
{$this->element_filed($function)}
<dt>syntax</dt>{$this->forall($function->syntaxs,'<dd>$value</dd>')}
<dt>params</dt><dd>{$this->forall($function->params,'<li>$value</li>')}</dd>
{$this->either($function->returns,
'<dt>returns</dt><dd>'.$this->forall($function->returns,'<li>$value</li>').'</dd>')}
</dl>
HTML;
  }

  // }}}
  // {{{ convert_method()

  public function convert_method( DstyleDoc_Element_Method $method )
  {
    return <<<HTML
<hr /><h1 id="{$method->id}">method: {$method->display}</h1>
<dl>
{$this->element_filed($method)}
<dt>class</dt><dd>{$method->class->link}</dd>
</dl>
HTML;
  }

  // }}}
  // {{{ convert_syntax()

  public function convert_syntax( DstyleDoc_Element_Syntax $syntax )
  {
    $result = '';
    foreach( $syntax->params as $param )
      $result .= ', '.
        (($param->optional)?'[ ':'').
        (($param->types)?'<i>'.$param->types.'</i> ':'').
        $param->var.
        (($param->optional)?' ]':'');

    $result = substr($result,2);

    return <<<HTML
<li>{$result}<br/>{$syntax->description}</li>
HTML;
  }

  // }}}
  // {{{ convert_param()

  public function convert_param( DstyleDoc_Element_Param $param )
  {
    $types = implode(', ', $param->types);
    return <<<HTML
{$param->var}: {$this->either($param->types,'<i>('.$types.')</i> ')}{$this->either($param->default,'<i>\['.$param->default.'\]</i> ')}{$param->description}
HTML;
  }

  // }}}
  // {{{ convert_return()

  public function convert_return( DstyleDoc_Element_Return $return )
  {
    return <<<HTML
{$return->type}: {$return->description}
HTML;
  }

  // }}}
  // {{{ convert_link()

  public function convert_link( $id, $name )
  {
    return <<<HTML
<a href="#{$id}">{$name}</a>
HTML;
  }

  // }}}

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
  // {{{ convert_all()

  public function convert_all()
  {
    d( $this->functions[0] );

    echo <<<HTML
<style>
dl dt { margin-top: 0px; font-weight: bold; }
dl dd { margin-left: 20px; }
</style>
HTML;

    $this->index_functions();
    $this->index_interfaces();

    $this->all_functions();
    $this->all_interfaces();
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
*/
  }

  // }}}
  // {{{ all_functions()

  protected function all_functions()
  {
    foreach( $this->functions as $function )
      echo $function;
  }

  // }}}
  // {{{ all_interfaces()

  protected function all_interfaces()
  {
    foreach( $this->interfaces as $interface )
    {
      echo <<<HTML
<hr /><h1 id="{$interface->id}">interface: {$interface->name}</h1>
<dl>
{$this->element_filed($interface)}
<dt>methods</dt>
<dd>
  <ul>
    {$this->forall($interface->methods,'<li>$value->link</li>')}
  </ul>
  {$this->forall($interface->methods,'$value')}
</dd>
</dl>
HTML;
    }
  }

  // }}}
  // {{{ index_functions()

  protected function index_functions()
  {
    echo <<<HTML
<h1>Functions index</h1>
<ul>
  {$this->forall($this->functions,'<li>$value->link</li>')}
</ul>
HTML;
  }

  // }}}
  // {{{ index_interfaces()

  protected function index_interfaces()
  {
    echo <<<HTML
<h1>Interfaces index</h1>
<ul>
  {$this->forall($this->interfaces,'<li>$value->link</li>')}
</ul>
HTML;
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
