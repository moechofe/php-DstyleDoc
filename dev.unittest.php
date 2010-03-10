<?php

require_once( 'include.libraries.php' );

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/scorer.php';

register_shutdown_function('unittest_autorun');

function unittest_autorun()
{
	foreach( get_declared_classes() as $class )
		if( is_subclass_of($class,'UnitTestCase') )
		{
			$reflection = new ReflectionClass($class);
			if( $_SERVER['SCRIPT_FILENAME'] == $reflection->getFileName() )
			{
				$loader = new SimpleFileLoader();
				$suite = $loader->createSuiteFromClasses(basename($reflection->getFileName()),array($class));
				$result = $suite->run( new DstyleDocReporter() );
				if( SimpleReporter::inCli() ) exit( $result ? 0 : 1 );
			}
		}
}

class DstyleDocReporter extends SimpleReporter
{
	public function __construct()
	{
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

	public function paintHeader( $test_name )
	{
		echo '<div id="unittest" class="content-index" style="clear:left;"><h3 class="list-header">Test unitaire du fichier <strong><span class="display display-file">',$test_name,'</span></strong></h3><dl>';
	}

	public function paintFooter( $test_name )
	{
		echo <<<HTML
</dl>
</div>
<style type="text/css">
	#unittest .fail { background:IndianRed; }
	#unittest ul { list-style:none; margin:0px; padding:0px; }
	#unittest li { font:11px monospace !important; margin:0px; padding:0px; }
	#unittest dl { margin:0px; padding:0px; }
	#unittest dt { margin:0px; padding:5px 20px 0px 20px; }
	#unittest dd { margin:0px; padding:0px 20px 5px 20px; font:15px sans-serif !important; color:white; }
</style>
<script  type="text/javascript">
$(function(){
	$("#page-content").children(".page-content").first().append( $("#unittest").detach() );
});
</script>
HTML;
	}

	public function paintAll( $message, $css_class )
	{
		list( $class, $method ) = array_pad( array_slice( $this->gettestlist(), -2 ), 2, null );
		list( $message, $file, $line ) = array_pad( $this->extractmessage( $message ), 3, null );
		$file = basename($file);
    $message = $this->colormessage( htmlspecialchars_decode(htmlentities(strip_tags($message,'<b><strong><i><em><u><span><div>'))) );
		echo <<<HTML
<dt class="{$css_class}"><ul>
  <li>fichier : <strong>{$file}</strong></li>
  <li>line : <strong>{$line}</strong></li>
  <li>classe : <strong>{$class}</strong></li>
  <li>methode : <strong>{$method}</strong></li>
</ul></dt>
<dd class="{$css_class}">
  {$message}
</dd>
HTML;
	}

	public function paintFail( $message )
	{
		parent::paintFail($message);
		$this->paintAll($message,'fail');
	}

	public function paintError( $message )
	{
		parent::paintError($message);
		$this->paintAll($message,'fail');
	}

	public function paintException( $message )
	{
		parent::paintException($message);
		$this->paintAll($message,'fail');
	}

	public function paintSkip( $message )
	{
		parent::paintSkip($message);
		$this->paintAll($message,'fail');
	}
}

