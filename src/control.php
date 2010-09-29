<?php
namespace dstyledoc;

require_once 'include.properties.php';
require_once 'tokens.all.php';

/**
 * Classe de control de DstyleDoc.
 * La classe Control permet de configurer et de lancer un processus de génération de documentation, en utilisante un syntaxe fluide.
 * ----
 * Control::hie()->source( 'fichier1.php' )->source( 'fichier2.php' );
 * ----
 * Members:
 *  string $source = Fichiers source à analyser.
 *  Accès en écriture : ajoute un fichier source à analyser.
 *  Accès en lecture, isset() et unset() : refusé.
 */
class Control extends Properties
{
	// {{{ version

	const version = 'DstyleDoc v0.2 2k8-2k10 Martin Mauchauffée';

	// }}}
	// {{{ $source_dir

	protected $_source_dir = '';

	protected function set_source_dir( $source_dir )
	{
		$this->_source_dir = (string)$source_dir;
	}

	protected function get_source_dir()
	{
		return $this->_source_dir;
	}

	// }}}
	// {{{ $sources

	/**
	 * Liste des fichiers sources à analyser.
	 * Type: array(string)
	 */
	protected $_sources = array();

	protected function set_source( $files )
	{
		if( file_exists($this->source_dir.(string)$files) and is_file($this->source_dir.(string)$files) and is_readable($this->source_dir.(string)$files) )
			$this->_sources[] = (string)$files;
		elseif( is_array($files) or $files instanceof Iterator )
			foreach( $files as $file )
				$this->source = $file;
		else
			throw new DstyleDoc_Error_Source( $files );
	}

	protected function get_sources()
	{
		return $this->_sources;
	}

	// }}}
	// {{{ analyse_all()

	protected function analyse_all( Converter $converter )
	{
		foreach( $this->sources as $file )
		{
			if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'log')!==false )
				self::log( "<span style=\"color: Crimson\">Parsing file: <strong>$file</strong></span>", true );
			$this->analyse_file( $converter, $file );
		}
	}

	// }}}
	// {{{ analyse_file()

	protected function analyse_file( Converter $converter, $file )
	{
		$line = 1;
		$current = new FakeToken;
		$doc = '';
		foreach( token_get_all(file_get_contents($this->source_dir.$file)) as $token )
		{
			if( is_array($token) )
				list( $token, $source, $line ) = $token;
			else
				list( $call, $token, $source, $line ) = array( 'UnknowToken', null, $token, $line );

			// skip T_WHITESPACE for speed up
			if( $token === T_WHITESPACE )
				continue;

			if( substr(token_name($token),0,2)=='T_' )
				$call = 'Token'.implode('',array_map('ucfirst',explode('_',strtolower(substr(token_name($token),2)))));

			if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'tokens')!==false )
			{
				static $f = 0;
				$ff = (++$f%2)?'BurlyWood':'Goldenrod';
				$s = htmlentities($source); if(!trim($s))$s='&nbsp;'; $c = get_class($current);
	echo <<<HTML
<div style='clear:left;background:black;height:2px;'></div>
<div style='clear:left;float:left;color:white;background:Brown;padding:1px 3px'>{$c}</div>
<div style='float:left;background:Chocolate;color:white;padding:1px 3px'>$line</div>
<div style='float:left;background:Wheat;padding:1px 3px'>$call</div>
<div style='background:{$ff};color:SaddleBrown;padding:1px 3px;'>{$s}</div>
<div style='clear:both'></div>
HTML;
			}

			$save = $current;
			// processing token
			$current = call_user_func( array($call,'hie'), $converter, $current, $source, $file, $line );

			if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'tokens')!==false and ( strpos($_REQUEST['debug'],'current')!==false or strpos($_REQUEST['debug'],get_class($current))!==false ) )
				var_dump( $current );

			if( $current instanceof StopToken )
				break;

			if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'open_tag')!==false )
			{
				$o = $d = '';
				$c = get_class($current);
				if( ! $current instanceof StopToken )
				{
					$o = get_class($current->open_tag);
					if( $current->open_tag instanceof TokenOpenTag )
						$d = strlen($current->open_tag->documentation)." ".substr($current->open_tag->documentation,0,30);
				}
				if(!trim($d))$d='&nbsp;';
				echo <<<HTML
<div style='clear:left;float:left;color:white;background:OliveDrab;padding:1px 3px'>{$c}</div>
<div style='float:left;color:white;background:DarkOliveGreen;padding:1px 3px'>{$o}</div>
<div style='background:YellowGreen;color:white;padding:1px 3px;'>{$d}</div>
<div style='clear:both'></div>
HTML;
			}

			if( ! $current instanceof CustomToken )
				throw new UnexpectedValueException;
		}

		if( $current instanceof TokenOpenTag or $current instanceof TokenHaltCompiler )
			TokenCloseTag::hie( $converter, $current, $source, $file, $line );
	}

	// }}}
	// {{{ source_dir(), source()

	public function source_dir( $source_dir )
	{
		$this->source_dir = $source_dir;
		return $this;
	}

	public function source()
	{
		$args = func_get_args();
		foreach( $args as $arg )
			$this->source = $arg;
		return $this;
	}

	// }}}
	// {{{ convert_with()

	public function convert_with( Converter $converter )
	{
		$converter->control = $this;
		$this->analyse_all( $converter );
		if( isset($_REQUEST['debug']) and strpos($_REQUEST['debug'],'hide')!==false )
			null;
		else
			$converter->convert_all();
		return $this;
	}

	// }}}
	// {{{ $config

	protected $_config = array(

		'container' => 'TokyoTyrantContainer',
		'tokyotyrant_uri' => 'localhost:1978',

		'dstyledoc' => true,
		'version' => true,
		'history' => true,
		'params' => true,
		'params_sub' => true,
		'returns' => true,
		'package' => true,
		'throws' => true,
		'syntax' => true,
		'type' => true,
		'since' => true,
		'todo' => true,
		'member' => true,
		'licence' => true,
		'method' => true,

		'element_link' => true,
		'href_link' => true,

		'javadoc' => true,
		'javadoc_version' => true,
		'javadoc_history' => true,
		'javadoc_params' => true,
		'javadoc_returns' => true,
		'javadoc_package' => true,
		'javadoc_subpackage' => true,
		'javadoc_exception' => true,
		'javadoc_syntax' => true,
		'javadoc_var' => true,
		'javadoc_since' => true,
		'javadoc_todo' => true,
		'javadoc_member' => true,
		'javadoc_method' => true,

		'javadoc_link' => true,

		);

	// }}}
	// {{{ __get()

	public function __get( $property )
	{
		if( substr((string)$property,0,7)==='enable_')
		{
			$this->_config[ substr((string)$property,7) ] = true;
			return $this;
		}
		elseif( substr((string)$property,0,8)==='disable_')
		{
			$this->_config[ substr((string)$property,8) ] = false;
			return $this;
		}
		elseif( isset($this->_config[(string)$property]) )
			return $this->_config[(string)$property];
		elseif( substr((string)$property,0,4)!=='get_' )
			return parent::__get( (string)$property );
		else
			throw new BadPropertyException($this, substr((string)$property,4) );
	}

	// }}}
	// {{{ __call()

	public function __call( $method, $params )
	{
		if( substr($method,0,7)==='config_')
		{
			$this->_config[ substr($method,7) ] = array_shift($params);
			return $this;
		}
	 else
			return parent::__get( $property );
	}

	// }}}
}
