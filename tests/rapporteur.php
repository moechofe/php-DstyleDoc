<?php

require_once( 'simpletest/reporter.php' );

class rapporteur extends simplereporter
{

  var $count = 1;

  function paintheader($test_name)
  {
    echo <<<html
<html>
<head>
<style type="text/css">
  body { background: #ccc; margin: 0px; padding: 0px; font-family: georgia; }
  a { color: white; }
  dl { float: left; width: 50%; margin: 0px; }
  dl.left dt, dl.left dd { margin-right: 1px; }
  dl.right dt, dl.right dd { margin-left: 1px; }
  dt { color: #ccc; padding: 4px 0px; }
  dt strong { color: white; }
  dd { margin-left: 0px; color: white; font-size: 16px; line-height: 115%; padding: 4px 6px; height: 38px; overflow: auto; }
  ul { list-style: none; margin-top: 0px; padding-left: 52px; }
  li { font-size: 0.9em; line-height: 125%; }
  dt.fail, dt.exception, dt.error { border-top: solid 2px #900; background: #c33 url("http://upload.wikimedia.org/wikipedia/commons/thumb/6/6e/dialog-warning.svg/48px-dialog-warning.svg.png") no-repeat  2px 2px; }
  dd.fail, dd.exception, dd.error { background: #d55; }
  dt.pass { border-top: solid 2px #363; background: #3a3 url("http://upload.wikimedia.org/wikipedia/commons/thumb/3/30/application-certificate.svg/48px-application-certificate.svg.png") no-repeat  2px 2px; }
  dd.pass { background: #4c4; }
  dd span.type, dd.exception span.type { background: #aa0; padding: 0px 2px; }
  dd.exception span.file, dd.exception span.line { background: #666; }
  dd.exception span.func { background: #693; }
  dt.dump { padding: 0px; }
  dd.dump { border-top: solid 2px #333; background: #666 url("http://upload.wikimedia.org/wikipedia/commons/thumb/9/9d/applications-development.svg/48px-applications-development.svg.png") no-repeat 2px 2px; font: 11px/11px lucida console,monospace; height: 114px; padding-left: 40px; }
  dt.info { height: 68px; border-top: solid 2px #036; background: #69c url("http://upload.wikimedia.org/wikipedia/commons/thumb/0/03/dialog-information.svg/48px-dialog-information.svg.png") no-repeat  2px 2px; }
  dd.info { background: #369; text-align: center; }
  dt.result { background-image: url("http://upload.wikimedia.org/wikipedia/commons/thumb/8/86/utilities-system-monitor.svg/48px-utilities-system-monitor.svg.png"); }
</style>
<script type="text/javascript">
function doDiv( id )
{
  if( document.getelementbyid ) return document.getelementbyid(id);
  else if( document.all ) return document.all[id];
  else if( document.layers ) return document.layers[id];
  else return null;
}
function getHttpObject()
{
  if( window.XMLHttpRequest )
  {
    xhr = new XMLHttpRequest();
    if( xhr.overrideMimeType )
      xhr.overrideMimeType("text/xml");
    return xhr;
  }
  else if( window.ActiveXObject )
  {
    try
    {
      return new ActiveXObject("Msxml2.XMLHTTP");
    }
    catch(e)
    {
      try
      {
        return new ActiveXObject("Microsoft.XMLHTTP");
      }
      catch(e)
      {
        return null;
      }
    }
  }
  else
    return null;
}
function adaptheight( id )
{
  var item = doDiv(id);
  if( item && item.style )
  {
    var loop = 0;
    var fontsize = 9;
    while( item.scrollHeight > item.clientHeight - 4 )
    {
      if( loop++ > 14 || fontsize < 6 )
      {
        //item.style.overflow = 'hidden';
        break;
      }
      item.style.fontSize = ( fontsize-- ) / 10 + 'em';
    }
  }
}
function showHideGroupTest( id )
{
  if( id )
  {
    var item = doDiv( id.name );
    var xhr = getHttpObject();
    if( xhr )
    {
      xhr.open( 'GET', 'show_hide_group_test.php?origin='+id.name+'&value='+id.value, true);
      xhr.send( null );
    }
/*    if( item.style )
    {
      if( item.style.display == 'none' )
        item.style.display = '';
      else
        item.style.display = 'none';
  }
 */
  }
}
</script>
</head>
<body>
html;
  }

  function paintfooter( $test_name )
  {
    $color = ( $this->getfailcount() + $this->getexceptioncount() > 0 )
      ? 'fail'
      : 'pass';
    $align = ( $this->count%2 == 0 )
      ? 'right'
      : 'left';
    echo <<<html
<dl class="{$color} result {$align}">
<dt class="{$color} result"><ul>
  <li>&nbsp;</li>
  <li>pass&eacute;s : <strong>{$this->getpasscount()}</strong></li>
  <li>&eacute;chou&eacute;s : <strong>{$this->getfailcount()}</strong></li>
  <li>except&eacute;s : <strong>{$this->getexceptioncount()}</strong></li>
</ul></dt>
<dd class="{$color} result">{$this->getpasscount()} succ&egrave;(s)<br/>{$this->gettestcaseprogress()} test(s) effectu&eacute;(s) sur {$this->gettestcasecount()}</dd>
</dl>
</body>
</html>
html;
  }

  function paintstart($test_name, $size)
  {
    parent::paintstart($test_name, $size);
  }

  function paintend($test_name, $size)
  {
    parent::paintend($test_name, $size);
  }

  function painterror($message)
  {
    parent::painterror($message);
    $this->displayresult( 'error',
      $message );
  }

  function paintexception($exception)
  {
    parent::paintexception($exception);
    $this->displayresult( 'exception',
      $exception->getmessage().' at ['.
      $exception->getfile().' line '.
      $exception->getline().']' );
  }

  function paintgroupstart($test_name, $size)
  {
    parent::paintgroupstart($test_name, $size);
    if( substr($test_name,-4,4) <> '.php' and trim($test_name) )
      $this->displayresult( 'info', $test_name, 'input' );
  }

  function paintpass($message)
  {
    parent::paintpass($message);
    if( ! isset($_REQUEST['nopass']) )
      $this->displayresult( 'pass', $message );
  }

  function paintfail($message)
  {
    parent::paintfail($message);
    $this->displayresult( 'fail', $message );
  }

  function paintformattedmessage($message)
  {
    parent::paintformattedmessage($message);
    $this->displayresult( 'dump', $message );
  }

  function displayresult( $mode, $message, $addon = null )
  {
    $origin = md5( join( '', array_pad( array_slice( $this->getTestList(), 0, -2 ), 1, null ) ) );
    foreach( array_merge( $this->getTestList(), array(null) ) as $link )
      if( substr( $link, -4 ) == '.php' )
        break;
    list( $class, $method ) = array_pad( array_slice( $this->gettestlist(), -2 ), 2, null );
    list( $message, $file, $line ) = array_pad( $this->extractmessage( $message ), 3, null );
    $file = basename($file);
    $css = (($this->count++)%2)?'left':'right';
    $message = $this->colormessage( htmlspecialchars_decode(htmlentities(strip_tags($message,'<b><strong><i><em><u><span><div>'))) );

    if( $addon == 'input' )
      $addon = '<input type="checkbox" name="'.$origin.'" onclick="showHideGroupTest(this);" />';

    if( $link )
    {
      $linkS = '<a href="'.$link.'">';
      $linkE = '</a>';
    }
    else
      $linkS = $linkE = null;

    if( $file and $line )
      echo <<<html
<dl class="{$mode} {$css}">
<dt class="{$mode}"><ul>
  <li>fichier : <strong>{$file}</strong></li>
  <li>line : <strong>{$line}</strong></li>
  <li>classe : <strong>{$class}</strong></li>
  <li>methode : <strong>{$method}</strong></li>
</ul></dt>
<dd class="{$mode}" id="dd{$this->count}">
  {$message}
</dd>
<script type="text/javascript">
  adaptheight( 'dd{$this->count}' );
</script>
</dl>
html;
    else
      echo <<<html
<dl class="{$mode} {$css}">
<dt class="{$mode}"></dt>
<dd class="{$mode}">
  {$addon}{$linkS}{$message}{$linkE}
</dd>
</dl>
html;
  }

  function extractmessage( $message )
  {
    if( preg_match( '/(.*) (?:at|in) \\[(.*) line (\\d+)\\]/s', $message, $matches ) )
      return array_slice( $matches, 1 );
    else
      return array( $message );
  }

  function colormessage( $message )
  {
    return preg_replace(
      array(
        '/\\[string: (.*?)\\]/i',
        '/\\[object: of (.*?)\\]/i',
        '/(?:\\[null\\]|a null value)/i',
        '/\\[(?:float|integer): (-?[\\.\\d]+)\\]/i',
        '/should be type \\[(.*?)\\]/',
        '/an instance of (.*?)\\./',
        '/severity \\[(.*?)\\]/',
        '/in file "(.*?)"/',
        '/at line (\d+)/',
        '/passed to "(.*?)"/',
      ),
      array(
        '<span class="type string">string : &laquo;<strong>\\1</strong>&raquo;</span>',
        '<span class="type object">object : &laquo;<strong>\\1</strong>&raquo;</span>',
        '<span class="type null">&laquo;<strong>null</strong>&raquo;</span>',
        '<span class="type integer">integer : &laquo;<strong>\\1</strong>&raquo;</span>',
        'should be type <span class="type object">object : &laquo;<strong>\\1</strong>&raquo;</span>',
        'an instance of <span class="type object">object : &laquo;<strong>\\1</strong>&raquo;</span>',
        '<span class="type error">&laquo;<strong>\\1</strong>&raquo;</span>',
        'in file <span class="type file">&laquo;<strong>\\1</strong>&raquo;</span>',
        'at line <span class="type line">&laquo;<strong>\\1</strong>&raquo;</span>',
        'passed to <span class="type func">&laquo;<strong>\\1</strong>&raquo;</span>',
      ),
      $message);
  }

}

// vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 fileformat=unix foldmethod=marker
?>
