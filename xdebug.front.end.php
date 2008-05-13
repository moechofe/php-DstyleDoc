<?php

function d( $var = null )
{
  if( func_num_args() == 0 )
    return xdebug_front_end::add( new xdebug_front_null, xdebug_call_file(), xdebug_call_line() );
  else
    return xdebug_front_end::add( &$var, xdebug_call_file(), xdebug_call_line() );
}

class xdebug_front_null
{
}

class xdebug_front_end
{
  static private $vars = array();

  static public function add( $var, $file, $line )
  {
    self::$vars[] = $v = new xdebug_front_end_var( &$var, $file, $line );
    return $v;
  }

  private function __construct() {}

  private function __clone() {}

  static public function css()
  {
    static $sent = false;

    if( ! $sent )
      echo '<!-- "]]> --></a><br clear="both"/>',"\r\n",
<<<HTML
<style type="text/css">
div.xdebug_front_end { background: black !important; color: #6f6 !important; padding: 1px !important; }
html div.xdebug_front_end { text-align: left !important; }
div.xdebug_front_end * { font: 10px/12px lucida console, monospace !important; margin: 0px !important; padding: 0px !important; border: none !important; width: auto !important; }
div.xdebug_front_end a { color: white !important; }
div.xdebug_front_end b { color: #f66 !important; font-weight: normal !important; }
div.xdebug_front_end a b { text-decoration: underline !important; } 
div.xdebug_front_end i { color: #ccc !important; font-style: normal !important; }
div.xdebug_front_end a i { text-decoration: underline !important; } 
div.xdebug_front_end u { text-decoration: none !important; color: gold !important; }
div.xdebug_front_end h1 { background: #666 !important; color: yellow !important; }
div.xdebug_front_end h2 { background: #333 !important; }
div.xdebug_front_end h2 a { display: block !important; text-decoration: none !important; color: orange !important; }
div.xdebug_front_end h3 { background: #222 !important; color: lime !important; }
div.xdebug_front_end h4 { float: left !important; font-size: 20px !important; line-height: 24px !important; }
div.xdebug_front_end li { display: block !important; float: left !important; margin-bottom: 12px !important; margin-right: 7px !important; }
div.xdebug_front_end small { color: gray !important; }
div.xdebug_front_end pre { float: left !important; }
div.xdebug_front_end div.stack { clear: left !important; border-left: solid 7px black !important; }
div.xdebug_front_end div.stack div.params { border-left: solid 7px black !important; }
div.xdebug_front_end div.stack a { color: #f66 !important; }
div.xdebug_front_end li.mark { background: #113 !important; }
div.xdebug_front_end li.mark h2 { background: #447 !important; }
div.xdebug_front_end li.call { background: #131 !important; }
div.xdebug_front_end li.call h2 { background: #242 !important; }
div.xdebug_front_end li.call h4 { background: #242 !important; padding: 0px 4px 0px 4px !important; }
div.xdebug_front_end div.string { border-left: solid 7px black !important; }
div.xdebug_front_end li div.extend div.extend { background: #222 !important; }
div.xdebug_front_end li div.extend div.extend div.extend div.extend { background: black !important; }
div.xdebug_front_end li div.extend div.extend div.extend div.extend div.extend div.extend { background: #222 !important; }
div.xdebug_front_end li.mark div.extend div.extend { background: #224 !important; }
div.xdebug_front_end li.mark div.extend div.extend div.extend div.extend { background: #113 !important; }
div.xdebug_front_end li.mark div.extend div.extend div.extend div.extend div.extend div.extend { background: #224 !important; }
div.xdebug_front_end li.call div.extend div.extend { background: #242 !important; }
div.xdebug_front_end li.call div.extend div.extend div.extend div.extend { background: #131 !important; }
div.xdebug_front_end li.call div.extend div.extend div.extend div.extend div.extend div.extend { background: #242 !important; }
div.xdebug_front_end div.config { float: right !important; background: white !important; }
div.xdebug_front_end div.config a { color: black !important; text-decoration: none !important; }
div.xdebug_front_end div.config a:hover { background: #c00 !important; color: white !important; text-decoration: none !important; }
</style>
<script type="text/javascript">
function xdebug_swap(d1)
{
  item = document.getElementById(d1);
  if( item )
  {
    if( item.style.display=='none' )
      item.style.display='';
    else
      item.style.display='none';
  }
}
</script>

HTML;
    $sent = true;
  }

  static public function dump( $var )
  {
    static $id = 0, $order = 0;
    $id++;

    $old_depth = ini_get('xdebug.var_display_max_depth');
    $old_children = ini_get('xdebug.var_display_max_children');
    $old_data = ini_get('xdebug.var_display_max_data');
    $old_string = ini_get('highlight.string');
    $old_comment = ini_get('highlight.comment');
    $old_keyword = ini_get('highlight.keyword');
    $old_bg = ini_get('highlight.bg');
    $old_default = ini_get('highlight.default');
    $old_html = ini_get('highlight.html');

    ini_set('highlight.string','yellow');
    ini_set('highlight.comment','tan');
    ini_set('highlight.keyword','deepskyblue');
    ini_set('highlight.default','white');
    ini_set('highlight.html','xdebug_front_end_html');

  if( ! $var->var instanceof xdebug_front_null ) :

    if( ! extension_loaded('xdebug') ) return var_dump( $var->var );

    if( (integer)$var->depth )
      ini_set('xdebug.var_display_max_depth', max(1,(integer)$var->depth));

    if( (integer)$var->children )
      ini_set('xdebug.var_display_max_children', max(1,(integer)$var->children));

    if( (integer)$var->length )
      ini_set('xdebug.var_display_max_data', max(1,(integer)$var->length));

    ob_start();
    var_dump( $var->var );
    $dump = substr(ob_get_clean(),strlen('<pre>'),-strlen('</pre>'));

    for( $i=0; $i< abs((integer)$var->pass); $i++ )
      $dump =
        preg_replace_callback(
          '/((?:  )*  )(<b>(?:object|array)<\/b>(?:\\(<i>(?:.*?)<\/i>\\)\\[<i>(?:\\d+)<\/i>\\])?)(\\r?\\n?)(\\r?\\n?(?:(?<!  )\\1.*\\r?\\n?)*)/',
          'xdebug_front_end::dump_object',
        $dump );

    $dump =
    preg_replace_callback(
      '/(<i>(?:private|protected|public)<\/i> \'(?:.*?)\' <font color=\'#\\w+\'>=&gt;<\/font> )(?:<small>string<\/small> )?(<font color=\'#\\w+\'>)\'([\\r\\n]*.*)\'(...)?(<\/font> <i>\\(length=(\\w+)\\)<\/i>)/',
      'xdebug_front_end::dump_string',
      $dump );

    $dump = "<pre id=\"xdebug_{$id}\">{$dump}</pre>";
  else :
    $dump = '';
  endif;

  if( is_array($var->stack) ) :

    $dump .= '<div class="stack" id="xdebug_stack_'.$id.'">';

    foreach( $var->stack as $i => $stack )
    {
      if( $stack['file'] == __FILE__ )
        continue;

      $file = self::path( @$stack['file'] );
      $line = @$stack['line'];

      $call = '';
      if( $call = @$stack['class'] )
        $call .= '::';
      $call .= @$stack['function'].'()';

      if( ! empty($stack['params']) )
      {
        ob_start();
        var_dump($stack['params']);
        $params =  "<div class=\"params\" id=\"xdebug_stack_{$id}_{$i}\" style=\"display:none\">".nl2br(substr(ob_get_clean(),19,-6)).'<br></div>';
        $call = "<a href=\"javascript:void(0)\" onclick=\"xdebug_swap('xdebug_stack_{$id}_{$i}')\">".$call.'</a>';
      }
      else
        $params = null;

      if( $line > 0 )
        $dump .= "<div>$file <u>@ $line</u> <b>$call</b>$params</div>";
      else
        $dump .= "<div>$file <b>$call</b>$params</div>";
    }

    $dump .= '</div><div style="clear:both"></div>';

  endif;

    ini_set('xdebug.var_display_max_depth', $old_depth);
    ini_set('xdebug.var_display_max_children', $old_children);
    ini_set('xdebug.var_display_max_data', $old_data);

    $file = self::path( $var->file );
    $line = $var->line;

    $class = $before = '';
    
    if( (string)$var->label )
      $label = (string)$var->label;
    else
    {
      $class = 'mark';
      $label = "$file <u>@ $line</u>";
    }

    if( (string)$var->call )
    {
      $class = 'call';
      $label = (string)$var->call;
      if( (string)$dump )
        $before = '<h4 id="xdebug_call_'.$id.'">'.++$order.'</h4>';
    }
    elseif( (string)$var->ordered )
    {
      $class = 'call';
      if( (string)$dump )
        $before = '<h4 id="xdebug_call_'.$id.'">'.++$order.'</h4>';
    }

    if( $var->exit )
      $auto = "<li>exit: {$file} <u>@ {$line}</u></li>";
    else
      $auto = '';

    echo <<<HTML
<li class="{$class}" title="{$file} @ {$line}">$before<h2><a href="javascript:void(0)" onclick="xdebug_swap('xdebug_stack_{$id}');xdebug_swap('xdebug_call_{$id}');xdebug_swap('xdebug_{$id}');return false;">{$label}</a></h2>
{$dump}
</li>
{$auto}

HTML;

    if( false !== $index =  array_search( $var, self::$vars, true ) )
      unset(self::$vars[$index]);

    if( $var->exit )
    {
      echo <<<HTML
</ul><div style="clear:both"></div></div>

HTML;
      exit;
    }
  }

  static private function dump_object( $m )
  {
    static $id = 0;
    $id++;
    $m[4] = substr($m[4],0,-1);
    return <<<HTML
{$m[1]}<a href="javascript:void(0)" onclick="xdebug_swap('xdebug_object_{$id}');return false;">{$m[2]}</a><div class="extend" style="display:none" id="xdebug_object_{$id}">{$m[4]}</div>{$m[3]}
HTML;
  }

  static private function dump_string( $m )
  {
    static $id = 0;
    if( substr_count($m[3],'&#10;') == 0 )
      return $m[0];
    $id++;
    if( substr($m[3],0,strlen('&lt;?php')) == '&lt;?php' )
    {
      $class = 'string highlight';
      $m[3] = substr(strtr(highlight_string(strtr(html_entity_decode($m[3]),array('&apos;'=>"'")),true),array('<code>'=>'','</code>'=>'',"<span style=\"color: xdebug_front_end_html\">\n"=>'<span>')),0,-1);
    }
    else
      $class = 'string';
    return <<<HTML
{$m[1]}{$m[2]}<a href="#" onclick="xdebug_swap('xdebug_string_{$id}');return false;"><b>string</b></a><div class="{$class}" style="display:none" id="xdebug_string_{$id}">{$m[3]}{$m[4]}</div>{$m[5]}
HTML;
  }

  static public function dump_all()
  {
    if( ! self::$vars )
      return;

    self::css();

    echo '<!-- "]]> --></a><br clear="both"/>',"\r\n",
<<<HTML
<div class="xdebug_front_end">
<div class="config"><a href="javascript:void(0);">L</a></div>
<ul>
HTML;

    $scalar = false;
    foreach( self::$vars as $var )
      if( ( ( is_string($var->var) and strlen($var->var)<=40 )
        or ((is_scalar($var->var) or is_null($var->var)) and ! is_string($var->var) ) or $var->var instanceof xdebug_front_null ) and is_null($var->stack) )
      {
        $scalar = true;
        self::dump( &$var );
      }

    if( $scalar )
      echo '</ul><ul style="clear:both">';

    foreach( self::$vars as $var )
      self::dump( &$var );

    echo <<<HTML
</ul><div style="clear:both"></div></div>

HTML;
  }

  static public function path( $path )
  {
    if( false !== strpos($path,$_SERVER['DOCUMENT_ROOT'] ) )
      return substr( $path, strlen($_SERVER['DOCUMENT_ROOT'])+1 );
    else
      return $path;
  }

}

class xdebug_front_end_var
{
  public $var = null;
  public $file = null;
  public $line = null;
  public $label = null;
  public $depth = null;
  public $pass = 3;
  public $children = null;
  public $length = null;
  public $call = null;
  public $exit = false;
  public $stack = null;
  public $ordered = false;

  public function __construct( $var, $file, $line )
  {
    $this->var = &$var;
    $this->file = $file;
    $this->line = $line;
  }

  public function label( $label )
  {
    $this->label = (string)$label;
    return $this;
  }

  public function now()
  {
    xdebug_front_end::css();

    echo '<!-- "]]> --></a><br clear="both"/>',"\r\n",
<<<HTML
<div class="xdebug_front_end">
<div class="config"><a href="javascript:void(0);">L</a></div>
<ul>
HTML;

    xdebug_front_end::dump( $this );

    echo <<<HTML
</ul><div style="clear:both"></div></div>

HTML;
    unset( $this );
  }

  public function __get( $property )
  {
    if( $property[0] == 'd' and (integer)substr($property,1)>0 )
      $this->depth = (integer)substr($property,1);
    elseif( $property[0] == 'p' and (integer)substr($property,1)>0 )
      $this->pass = (integer)substr($property,1);
    elseif( $property == 'x' )
      $this->exit = true;
    elseif( $property == 'c' and extension_loaded('xdebug') )
    {
      $calls = array_reverse(xdebug_get_function_stack());
      if( @$calls[1]['class'] )
        $this->call = $calls[1]['class'].'::';
      if( @$calls[1]['function'] )
        $this->call .= $calls[1]['function'].'()';
    }
    elseif( $property == 'o' )
      $this->ordered = true;    
    elseif( $property == 's' and extension_loaded('xdebug') )
      $this->stack = xdebug_get_function_stack();
    elseif( $property == 'n' )
    {
      $this->now();
      return null;
    }
    elseif( $property[0] == 'c' and (integer)substr($property,1)>0 )
      $this->children = (integer)substr($property,1);
    elseif( $property[0] == 'l' and (integer)substr($property,1)>0 )
      $this->length = (integer)substr($property,1);
    else
      $this->label = $property;

    return $this;
  }

  public function __call( $property, $arg )
  {
    if( $property == 'label' and is_string($arg[0]) )
      $this->label = $arg[0];
    elseif( $property == 'x' and $arg[0] )
      $this->exit = true;

    return $this;
  }

}

register_shutdown_function( array('xdebug_front_end','dump_all') );

?>
