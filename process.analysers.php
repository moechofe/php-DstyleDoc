<?php

/**
 * Interface de la base des analysers
 */
interface DstyleDoc_Analyseable
{
	// {{{ analyse()

	/**
	 * PHP deprecie le passage par reference a des fonctions appelÃ©s dynamiquement. Pour cette raison : $instance et $priority doivent Ãªtre des objects.
	 */
	static function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd );

	// }}}
	// {{{ apply()

	function apply( DstyleDoc_Custom_Element $element );

	// }}}
}

interface DstyleDoc_Analyser_Descriptable
{
	// {{{ descriptable()

	function descriptable( DstyleDoc_Element $element, $description );

	// }}}
}

/**
 * Classe abstraite de la base des analysers.
 */
abstract class DstyleDoc_Analyser extends DstyleDoc_Properties implements DstyleDoc_Analyseable
{
	// {{{ remove_stars()

	static public function remove_stars( $source )
	{
		// ^\s*(?:\/*\**|\**)\s*(.*?)\s*(?:\**\/|\**)$
		if( preg_match( '/^\\s*(?:\\/*\\**|\\**)\\s*(.*?)\\s*(?:\\**\\/|\\**)$/', $source, $matches ) )
			return $matches[1];
		else
			return $source;
	}

	// }}}
	// {{{ finalize()

	static public function finalize( DstyleDoc_Custom_Element $element )
	{
	}

	// }}}
}

/**
 * Classe d'analyse de la description.
 */
class DstyleDoc_Analyser_Description extends DstyleDoc_Analyser
{
	// {{{ priority

	const priority = 100;

	// }}}
	// {{{ analyse()

	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		if( $source and $current instanceof DstyleDoc_Analyser_Descriptable )
		{
			$instance->value = new self( $source );
			$priority->value = self::priority;
			$instance->descriptable = $current;
			return true;
		}
		elseif( $source )
		{
			$instance->value = new self( $source );
			$priority->value = self::priority;
			return true;
		}
		else
			return false;
	}

	// }}}
	// {{{ apply()

	/**
	 * Ajoute une nouvelle ligne de description a l'Ã©lÃ©ment.
	 */
	public function apply( DstyleDoc_Custom_Element $element )
	{
		if( $this->descriptable )
		{
			$this->descriptable->descriptable( $element, $this->description );
			return $this->descriptable;
		}
		else
		{
			$element->description = new DstyleDoc_Descritable( $this->description, $element );
			return $this;
		}
	}

	// }}}
	// {{{ $description

	protected $_description = '';

	protected function set_description( $description )
	{
		$this->_description = (string)$description;
	}

	protected function get_description()
	{
		return $this->_description;
	}

	// }}}
	// {{{ __construct()

	protected function __construct( $description )
	{
		$this->description = $description;
	}

	// }}}
	// {{{ $descriptable

	protected $_descriptable = null;

	protected function set_descriptable( DstyleDoc_Analyser_Descriptable $descriptable )
	{
		$this->_descriptable = $descriptable;
	}

	protected function get_descriptable()
	{
		return $this->_descriptable;
	}

	// }}}
}

/**
 * Classe d'analyse d'un sÃ©parateur de paragraphe la description
 */
class DstyleDoc_Analyser_Description_Paragraphe extends DstyleDoc_Analyser
{
	// {{{ priority

	const priority = 1000;

	// }}}
	// {{{ analyse()

	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		if( $current instanceof DstyleDoc_Analyser_Description and $source === '' )
		{
			$instance->value = new self();
			$priority->value = self::priority;
			return true;
		}
		else
			return false;
	}

	// }}}
	// {{{ apply()

	/**
	 * Ajoute un nouveau paragraphe à la description à l'élément.
	 * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
	 */
	public function apply( DstyleDoc_Custom_Element $element )
	{
		if( count($element->descriptions) > 1 )
		{
			list($last) = array_reverse($element->descriptions);
			if( $last !== '' )
				$element->description = '';
		}
		return $this;
	}

	// }}}
	// {{{ finalize()

	static public function finalize( DstyleDoc_Custom_Element $element )
	{
		if( count($element->descriptions) > 1 )
		{
			list($last) = array_reverse($element->descriptions);
			if( $last === '' )
			{
				$new = $element->descriptions;
				array_pop($new);
				$element->descriptions = $new;
			}
		}
	}

	// }}}
}

/**
 * Classe d'analyse d'une balise de version.
 */
class DstyleDoc_Analyser_Version extends DstyleDoc_Analyser
{
	// {{{ $version

	protected $_version = '';

	protected function set_version( $version )
	{
		$this->_version = $version;
	}

	protected function get_version()
	{
		return $this->_version;
	}

	// }}}
	// {{{ priority

	const priority = 10;

	// }}}
	// {{{ analyse()

	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		// ^version\s*:\\s*(.+)$
		if( $dsd->dstyledoc and $dsd->version and preg_match( '/^version\\s*:\\s*(.+)$/i', $source, $matches ) )
		{
			$instance->value = new self( $matches[1] );
			$priority->value = self::priority;
			return true;
		}

		// ^@version\s*(.+)$
		elseif( $dsd->javadoc_link and $dsd->javadoc_version and preg_match( '/^@version\s*(.+)$/i', $source, $matches ) )
		{
			$instance->value = new self( $matches[1] );
			$priority->value = self::priority;
			return true;
		}

		else
			return false;
	}

	// }}}
	// {{{ __construct()

	protected function __construct( $version )
	{
		$this->version = $version;
	}

	// }}}
	// {{{ apply()

	/**
	 * Ajoute un nouveau paragraphe à la description à l'élément.
	 * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
	 */
	public function apply( DstyleDoc_Custom_Element $element )
	{
		$element->version = $this->version;
		return $this;
	}

	// }}}
}

/**
 * Classe d'analyse d'une balise d'historique.
 */
class DstyleDoc_Analyser_History extends DstyleDoc_Analyser
{
	// {{{ priority

	const priority = 10;

	// }}}
	// {{{ analyse()

	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		// ^history\s*:(?:\s*(?:v|version:?\s*)?(\d.*?)\s*[:=]?(?:\s+(.*)))?$
		if( $dsd->dstyledoc and $dsd->history and preg_match( '/^history\\s*:(?:\\s*(?:v|version:?\\s*)?(\\d.*?)\\s*[:=]?(?:\\s+(.*)))?$/i', $source, $matches ) )
		{
			$instance->value = new self();
			$priority->value = self::priority;
			if( ! empty($matches[1]) )
			{
				$instance->value = new DstyleDoc_Analyser_Element_History_List( $matches[1], $matches[2] );
				$priority->value = DstyleDoc_Analyser_Element_History_List::priority;
			}
			return true;
		}
		else
			return false;
	}

	// }}}
	// {{{ apply()

	/**
	 * Ajoute un nouveau paragraphe à la description à l'élément.
	 * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
	 */
	public function apply( DstyleDoc_Custom_Element $element )
	{
		return $this;
	}

	// }}}
}

/**
 * Classe d'analyse d'un Ã©lÃ©ment de liste d'historique.
 */
class DstyleDoc_Analyser_Element_History_List extends DstyleDoc_Analyser implements DstyleDoc_Analyser_Descriptable
{
	// {{{ $version

	protected $_version = '';

	protected function set_version( $version )
	{
		$this->_version = $version;
	}

	protected function get_version()
	{
		return $this->_version;
	}

	// }}}
	// {{{ $description

	protected $_description = '';

	protected function set_description( $description )
	{
		$this->_description = (string)$description;
	}

	protected function get_description()
	{
		return $this->_description;
	}

	// }}}
	// {{{ descriptable()

	public function descriptable( DstyleDoc_Element $element, $description )
	{
		$element->history->description = $description;
	}

	// }}}
	// {{{ priority

	const priority = 20;

	// }}}
	// {{{ analyse()

	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		// ^(?:v|version:?\s*)?(\d.*?)\s*[:=]\s*(?:\s+(.*))$
		if( $dsd->dstyledoc and $dsd->history and ($current instanceof DstyleDoc_Analyser_History or $current instanceof DstyleDoc_Analyser_Element_History_List)
			and preg_match( '/^(?:v|version:?\\s*)?(\\d.*?)\\s*[:=]\\s*(?:\\s+(.*))$/', $source, $matches ) )
		{
			$instance->value = new self( $matches[1], $matches[2] );
			$priority->value = self::priority;
			return true;
		}

		// ^(?:[-+*]\s+)(?:v|version:?\s*)?(\d.*?)\s*[:=]?\s*(?:\s+(.*))$
		elseif( $dsd->dstyledoc and $dsd->history and ($current instanceof DstyleDoc_Analyser_History or $current instanceof DstyleDoc_Analyser_Element_History_List)
			and preg_match( '/^(?:[-+*]\\s+)(?:v|version:?\\s*)?(\\d.*?)\\s*[:=]?\\s*(?:\\s+(.*))$/i', $source, $matches ) )
		{
			$instance->value = new self( $matches[1], $matches[2] );
			$priority->value = self::priority;
			return true;
		}

		// ^(?:@history\s+)(?:v|version:?\s*)?(\d.*?)\s*[:=]?\s*(?:\s+(.*))$
		elseif( $dsd->javadoc and $dsd->javadoc_history and preg_match( '/^(?:@history\\s+)(?:v|version:?\\s*)?(\\d.*?)\\s*[:=]?\\s*(?:\\s+(.*))$/i', $source, $matches ) )
		{
			$instance->value = new self( $matches[1], $matches[2] );
			$priority->value = self::priority;
			return true;
		}

		else
			return false;
	}

	// }}}
	// {{{ apply()

	/**
	 * Ajoute un nouveau paragraphe à la description à l'élément.
	 * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
	 */
	public function apply( DstyleDoc_Custom_Element $element )
	{
		$element->history = $this->version;
		$element->history->description = $this->description;
		return $this;
	}

	// }}}
	// {{{ __construct()

	public function __construct( $version, $description )
	{
		$this->version = $version;
		$this->description = $description;
	}

	// }}}
}

/**
 * Classe d'analyse d'une balise de paramÃ¨tre.
 */
class DstyleDoc_Analyser_Param extends DstyleDoc_Analyser
{
	// {{{ priority

	const priority = 10;

	// }}}
	// {{{ analyse()

	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		// ^params?\s*:\s*(?:\s(?:[(]?([^\s+]+)[)]?\s+)?(?:(\$.+?|\.{3})(?:\s*[:=]?\s+)?)(.*))?$
		if( $dsd->dstyledoc and $dsd->params and preg_match( '/^params?\\s*:\\s*(?:\\s(?:[(]?([^\\s+]+)[)]?\\s+)?(?:(\\$.+?|\\.{3})(?:\\s*[:=]?\\s+)?)(.*))?$/i', $source, $matches ) )
		{
			$instance->value = new self();
			$priority->value = self::priority;
			if( isset($matches[3]) )
			{
				$instance->value = new DstyleDoc_Analyser_Element_Param_List( $matches[1], $matches[2], $matches[3] );
				$priority->value = DstyleDoc_Analyser_Element_Param_List::priority;
			}
			return true;
		}
		else
			return false;
	}

	// }}}
	// {{{ apply()

	public function apply( DstyleDoc_Custom_Element $element )
	{
		return $this;
	}

	// }}}
}

/**
 * Classe d'analyse d'un Ã©lÃ©ment de liste de paramÃ¨tre.
 */
class DstyleDoc_Analyser_Element_Param_List extends DstyleDoc_Analyser implements DstyleDoc_Analyser_Descriptable
{
	// {{{ $types

	protected $_types = '';

	protected function set_types( $types )
	{
		$this->_types = (array)$types;
	}

	protected function set_type( $type )
	{
		$this->_types[] = (string)$type;
	}

	protected function get_types()
	{
		return $this->_types;
	}

	// }}}
	// {{{ $var

	protected $_var = '';

	protected function set_var( $var )
	{
		if( $var{0}=='$' ) $var = substr($var,1);
		$this->_var = $var;
	}

	protected function get_var()
	{
		return $this->_var;
	}

	// }}}
	// {{{ $description

	protected $_description = '';

	protected function set_description( $description )
	{
		$this->_description = (string)$description;
	}

	protected function get_description()
	{
		return $this->_description;
	}

	// }}}
	// {{{ descriptable()

	public function descriptable( DstyleDoc_Element $element, $description )
	{
		if( $element instanceof DstyleDoc_Element_Function )
			$element->param->description = $description;
	}

	// }}}
	// {{{ priority

	const priority = 15;

	// }}}
	// {{{ analyse()

	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		// ^(?:[(]?([-_,\| \pL\d]+)[)]?\s+)?(\$[-_\pL\d]+|\.{3})\s*[:=]\s*(.*)$
		if( $dsd->dstyledoc and $dsd->params and ($current instanceof DstyleDoc_Analyser_Param or $current instanceof DstyleDoc_Analyser_Element_Param_List)
			and preg_match( '/^(?:[(]?([-_,\\| \\pL\d]+)[)]?\\s+)?(\\$[-_\\pL\d]+|\\.{3})\\s*[:=]\\s*(.*)$/', $source, $matches ) )
		{
			$instance->value = new self( $matches[1], $matches[2], $matches[3] );
			$priority->value = self::priority;
			return true;
		}

		// ^(?:[-+*]\s+)(?:[(]?([-_,\| \pL\d]+)[)]?\s+)?(\$[-_\pL\d]+|\.{3})\s*[:=]?\s*(.*)$
		elseif( $dsd->dstyledoc and $dsd->params and ($current instanceof DstyleDoc_Analyser_Param or $current instanceof DstyleDoc_Analyser_Element_Param_List)
			and preg_match( '/^(?:[-+*]\\s+)(?:[(]?([-_,\\| \\pL\d]+)[)]?\\s+)?(\\$[-_\\pL\d]+|\\.{3})\\s*[:=]?\\s*(.*)$/i', $source, $matches ) )
		{
			$instance->value = new self( $matches[1], $matches[2], $matches[3] );
			$priority->value = self::priority;
			return true;
		}

		// ^(?:@params?\s+)(?:[(]?([-_,\| \pL\d]+)[)]?\s+)?(\$[-_\pL\d]+|\.{3})\s*[:=]?\s*(.*)$
		elseif( $dsd->javadoc and $dsd->javadoc_params and preg_match( '/^(?:@params?\\s+)(?:[(]?([-_,\\| \\pL\d]+)[)]?\\s+)?(\\$[-_\\pL\d]+|\\.{3})\\s*[:=]?\\s*(.*)$/i', $source, $matches ) )
		{
			$instance->value = new self( $matches[1], $matches[2], $matches[3] );
			$priority->value = self::priority;
			return true;
		}

		else
			return false;
	}

	// }}}
	// {{{ apply()

	public function apply( DstyleDoc_Custom_Element $element )
	{
		if( $element instanceof DstyleDoc_Element_Function )
		{
			$element->param = $this->var;

			if( $this->var and ! $element->param->var )
				$element->param->var = $this->var;

			foreach( $this->types as $type )
				// j'ai bien gallÃ©rÃ© pour trouver Ã§a, j'espÃ¨re que c'est la bonne solution.
				// dans les cas ou :
				// - de la doc pour "Syntax:" dÃ©clare des types pour les paramÃ¨tres
				// - et, de la doc pour "Params:" ne dÃ©clare pas les types pour les mÃªme paramÃ¨tres.
				if( $type )
					$element->param->type = $type;

			$element->param->description = $this->description;
		}
		return $this;
	}

	// }}}
	// {{{ __construct()

	public function __construct( $types, $var, $description )
	{
		foreach( preg_split('/[,|]/', $types) as $type )
			$this->type = trim($type);
		$this->var = $var;
		$this->description = $description;
	}

	// }}}
}

/**
 * Classe d'analyse d'un Ã©lÃ©ment de liste de paramÃ¨tre.
 */
class DstyleDoc_Analyser_Element_Param_Sub_List extends DstyleDoc_Analyser_Element_Param_List
{
	// {{{ priority

	/**
	 * Prioritaire sur DstyleDoc_Analyser_Element_Param_List
	 */
	const priority = 14;

	// }}}
	// {{{ $ref

	protected $_ref = null;

	protected function set_ref( DstyleDoc_Analyser_Element_Param_List $ref )
	{
		if( $ref instanceof self )
			$this->_ref = $ref->ref;
		else
			$this->_ref = $ref;
	}

	protected function get_ref()
	{
		return $this->_ref;
	}

	// }}}
	// {{{ get_ref_var()

	static protected function get_ref_var( DstyleDoc_Analyser_Element_Param_List $ref )
	{
		if( $ref instanceof self )
			return $ref->ref->var;
		else
			return $ref->var;
	}

	// }}}
	// {{{ analyse()

	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		// ^(?:[(]?([-_,\| \pL\d]+)[)]?\s+)?"([\PC]+|\.{3})"\s*[:=]\s*(.*)$
		if( $dsd->dstyledoc and $dsd->params_sub and
			( ($current instanceof DstyleDoc_Analyser_Element_Param_List and in_array($current->types[0],array('array','object')) )
				or ($current instanceof self) )
			and preg_match( '/^(?:[(]?([-_,| \pL\d]+)[)]?\s+)?"([\\PC]+|\.{3})"\s*[:=]\s*(.*)$/', $source, $matches ) )
		{
			$instance->value = new self( $matches[1], '$'.self::get_ref_var($current).'[ '.$matches[2].' ]', $matches[3] );
			$instance->ref = $current;
			$priority->value = self::priority;
			return true;
		}

		// ^(?:[-+*]\s+)(?:[(]?([-_,\| \pL\d]+)[)]?\s+)?"([\PC]+|\.{3})"\s*[:=]?\s*(.*)$
		elseif( $dsd->dstyledoc and $dsd->params_sub and
			( ($current instanceof DstyleDoc_Analyser_Param or $current instanceof DstyleDoc_Analyser_Element_Param_List)
				or ($current instanceof self) )
			and preg_match( '/^(?:[-+*]\s+)(?:[(]?([-_,| \pL\d]+)[)]?\s+)?"([\\PC]+|\.{3})"\s*[:=]?\s*(.*)$/', $source, $matches ) )
		{
			$instance->value = new self( $matches[1], $matches[2], $matches[3] );
			$instance->ref = $current;
			$priority->value = self::priority;
			return true;
		}

		else
			return false;
	}

	// }}}
	// {{{ apply()

	public function apply( DstyleDoc_Custom_Element $element )
	{
		if( $element instanceof DstyleDoc_Element_Function )
		{
			$element->param_sub = $this->var;

			if( $this->var and ! $element->param_sub->var )
				$element->param_sub->var = $this->var;

			foreach( $this->types as $type )
	// j'ai bien gallÃ©rÃ© pour trouver Ã§a, j'espÃ¨re que c'est la bonne solution.
	// dans les cas ou :
	// - de la doc pour "Syntax:" dÃ©clare des types pour les paramÃ¨tres
	// - et, de la doc pour "Params:" ne dÃ©clare pas les types pour les mÃªme paramÃ¨tres.
	if( $type )
					$element->param_sub->type = $type;

			$element->param_sub->description = $this->description;
		}
		return $this;
	}

	// }}}
}

/**
 * Classe d'analyse d'une balise de retour.
 */
class DstyleDoc_Analyser_Return extends DstyleDoc_Analyser
{
	// {{{ priority

	const priority = 10;

	// }}}
	// {{{ analyse()

	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		// ^returns?\s*:\s*(:([-_\pL\d]+(?:\(\))?)\s*[:=]?\s*(.*))?$
		if( $dsd->dstyledoc and $dsd->returns and preg_match( '/^returns?\\s*:\\s*(?:([-_\\pL\d]+(?:\(\))?)\\s*[:=]?\\s*(.*))?$/i', $source, $matches ) )
		{
			$instance->value = new self();
			$priority->value = self::priority;
			if( isset($matches[2]) )
			{
				$instance->value = new DstyleDoc_Analyser_Element_Return_List( $matches[1], $matches[2] );
				$priority->value = DstyleDoc_Analyser_Element_Return_List::priority;
			}
			return true;
		}
		else
			return false;
	}

	// }}}
	// {{{ apply()

	/**
	 * Ajoute un nouveau paragraphe à la description à l'élément.
	 * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
	 */
	public function apply( DstyleDoc_Custom_Element $element )
	{
		return $this;
	}

	// }}}
}

/**
 * Classe d'analyse d'un Ã©lÃ©ment de liste de retour.
 */
class DstyleDoc_Analyser_Element_Return_List extends DstyleDoc_Analyser implements DstyleDoc_Analyser_Descriptable
{
	// {{{ $type

	protected $_type = '';

	protected function set_type( $type )
	{
		$this->_type = (string)$type;
	}

	protected function get_type()
	{
		return $this->_type;
	}

	// }}}
	// {{{ $description

	protected $_description = '';

	protected function set_description( $description )
	{
		$this->_description = (string)$description;
	}

	protected function get_description()
	{
		return $this->_description;
	}

	// }}}
	// {{{ descriptable()

	public function descriptable( DstyleDoc_Element $element, $description )
	{
		if( $element instanceof DstyleDoc_Element_Function )
			$element->return->description = $description;
	}

	// }}}
	// {{{ priority

	const priority = 15;

	// }}}
	// {{{ analyse()

	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		// ^((?:[-_\pL\d]+\s*,\s*)*[-_\pL\d]+(?:\(\))?)\s*[:=]\s*(.*)$
		if( $dsd->dstyledoc and $dsd->returns and ($current instanceof DstyleDoc_Analyser_Return or $current instanceof DstyleDoc_Analyser_Element_Return_List)
			and preg_match( '/^((?:[-_\pL\d]+\s*,\s*)*[-_\pL\d]+(?:\(\))?)\s*[:=]\s*(.*)$/', $source, $matches ) )
			{
				$instance->value = new self( $matches[1], $matches[2] );
				$priority->value = self::priority;
				return true;
			}

		// ^(?:[-+*]\s+)((?:[-_\pL\d]+(?:\(\))?\s*,\s*)*[-_\pL\d]+(?:\(\))?)\s*[:=]?\s*(.*)$
		elseif( $dsd->dstyledoc and $dsd->returns and ($current instanceof DstyleDoc_Analyser_Return or $current instanceof DstyleDoc_Analyser_Element_Return_List)
			and preg_match( '/^(?:[-+*]\s+)((?:[-_\pL\d]+(?:\(\))?\s*,\s*)*[-_\pL\d]+(?:\(\))?)\s*[:=]?\s*(.*)$/', $source, $matches ) )
			{
				$instance->value = new self( $matches[1], $matches[2] );
				$priority->value = self::priority;
				return true;
			}

		// ^(?:@returns?\s+)((?:[-_\pL\d]+(?:\(\))?\s*,\s*)*[-_\pL\d]+(?:\(\))?)\s*[:=]?\s*(.*)$
		elseif( $dsd->javadoc and $dsd->javadoc_returns and preg_match( '/^(?:@returns?\\s+)((?:[-_\pL\d]+(?:\(\))?\s*,\s*)*[-_\\pL\d]+(?:\(\))?)\\s*[:=]?\\s*(.*)$/i', $source, $matches ) )
			{
				$instance->value = new self( $matches[1], $matches[2] );
				$priority->value = self::priority;
				return true;
			}

		else
			return false;
	}

	// }}}
	// {{{ apply()

	/**
	 * Ajoute un nouveau paragraphe Ã  la description de l'Ã©lÃ©ment.
	 * S'assure que le prÃ©cÃ©dent ajout n'Ã©taient pas dÃ©jÃ  un nouveau paragraphe.
	 * Todo:
	 *	 - ne devrait pas faire de trim() ici.
	 */
	public function apply( DstyleDoc_Custom_Element $element )
	{
		if( $element instanceof DstyleDoc_Element_Function )
		{
			foreach( explode(',',$this->type) as $type )
			{
				$element->return = trim($type);
				if( $this->description )
				{
					unset($element->return->description);
					$element->return->description = $this->description;
					$element->return->from = $element;
				}
			}
		}
		return $this;
	}

	// }}}
	// {{{ __construct()

	public function __construct( $type, $description )
	{
		$this->type = $type;
		$this->description = $description;
	}

	// }}}
}

/**
 * Classe d'analyse d'une balise d'exception.
 */
class DstyleDoc_Analyser_Throw extends DstyleDoc_Analyser
{
	// {{{ priority

	const priority = 10;

	// }}}
	// {{{ analyse()

	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		// ^throws?\s*:\s*(?:\s(?:([\pL\pN]+)\s*)(?:[:=]\s+)?(.*))?$
		if( $dsd->dstyledoc and $dsd->throws and preg_match( '/^throws?\\s*:\\s*(?:\\s(?:([\\pL\\pN]+)\\s*)(?:[:=]\\s+)?(.*))?$/i', $source, $matches ) )
		{
			$instance->value = new self();
			$priority->value = self::priority;
			if( isset($matches[2]) )
			{
				$instance->value = new DstyleDoc_Analyser_Element_Throw_List( $matches[1], $matches[2] );
				$priority->value = DstyleDoc_Analyser_Element_Throw_List::priority;
			}
			return true;
		}
		else
			return false;
	}

	// }}}
	// {{{ apply()

	/**
	 * Ajoute un nouveau paragraphe à la description à l'élément.
	 * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
	 */
	public function apply( DstyleDoc_Custom_Element $element )
	{
		return $this;
	}

	// }}}
}

/**
 * Classe d'analyse d'un Ã©lement de liste d'exception.
 */
class DstyleDoc_Analyser_Element_Throw_List extends DstyleDoc_Analyser implements DstyleDoc_Analyser_Descriptable
{
	// {{{ $exception

	protected $_exception = '';

	protected function set_exception( $exception )
	{
		$this->_exception = (string)$exception;
	}

	protected function get_exception()
	{
		return $this->_exception;
	}

	// }}}
	// {{{ $description

	protected $_description = '';

	protected function set_description( $description )
	{
		$this->_description = (string)$description;
	}

	protected function get_description()
	{
		return $this->_description;
	}

	// }}}
	// {{{ descriptable()

	public function descriptable( DstyleDoc_Element $element, $description )
	{
		if( $element instanceof DstyleDoc_Element_Function )
			$element->exception->description = $description;
	}

	// }}}
	// {{{ priority

	const priority = 15;

	// }}}
	// {{{ analyse()

	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		// ^\s*(?:[-+*]\s+)?([-_\pL\pN]+)\s*[:=]\s*(.*)$
		if( $dsd->dstyledoc and $dsd->throws and ($current instanceof DstyleDoc_Analyser_Throw or $current instanceof DstyleDoc_Analyser_Element_Throw_List)
			and preg_match( '/\\s*(?:[-+*]\\s+)?([-_\\pL\\pN]+)\\s*[:=]\\s*(.*)$/', $source, $matches ) )
			{
				$instance->value = new self( $matches[1], $matches[2] );
				$priority->value = self::priority;
				return true;
			}

		// ^\s*(?:[-+*]\s)([-_\pL\pN]+)\s*[:=]?\s*(.*)$
		elseif( $dsd->dstyledoc and $dsd->throws and ($current instanceof DstyleDoc_Analyser_Throw or $current instanceof DstyleDoc_Analyser_Element_Throw_List)
			and preg_match( '/\\s*(?:[-+*]\\s+)([-_\\pL\\pN]+)\\s*[:=]?\\s*(.*)$/i', $source, $matches ) )
			{
				$instance->value = new self( $matches[1], $matches[2] );
				$priority->value = self::priority;
				return true;
			}

		// ^\s*(?:@throws?\s+|@exceptions?\s+)([-_\pL\pN]+)\s*[:=]?\s*(.*)$
		elseif( $dsd->javadoc and $dsd->javadoc_exception and preg_match( '/\\s*(?:@throws?\\s+|@exceptions?\\s+)([-_\\pL\\pN]+)\\s*[:=]?\\s*(.*)$/i', $source, $matches ) )
			{
				$instance->value = new self( $matches[1], $matches[2] );
				$priority->value = self::priority;
				return true;
			}

		else
			return false;
	}

	// }}}
	// {{{ apply()

	/**
	 * Ajoute une exception à l'élément.
	 */
	public function apply( DstyleDoc_Custom_Element $element )
	{
		if( $element instanceof DstyleDoc_Element_Function )
		{
			$element->exception = $this->exception;
			$element->exception->description = $this->description;
		}
		return $this;
	}

	// }}}
	// {{{ __construct()

	public function __construct( $exception, $description )
	{
		$this->exception = $exception;
		$this->description = $description;
	}

	// }}}
}

/**
 * Classe d'analyse d'une balise de syntaxe.
 * todo: ajouter la positibilitÃ© de mettre la syntaxe apres ^syntax\s*:
 */
class DstyleDoc_Analyser_Syntax extends DstyleDoc_Analyser
{
	// {{{ priority

	const priority = 10;

	// }}}
	// {{{ analyse()

	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		// ^syntax\s*:\s*$
		if( $dsd->dstyledoc and $dsd->syntax and preg_match( '/^syntax\\s*:\\s*$/i', $source, $matches ) )
		{
			$instance->value = new self();
			$priority->value = self::priority;
			return true;
		}
		else
			return false;
	}

	// }}}
	// {{{ apply()

	/**
	 * Ajoute un nouveau paragraphe à la description à l'élément.
	 * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
	 */
	public function apply( DstyleDoc_Custom_Element $element )
	{
		return $this;
	}

	// }}}
}

/**
 * Classe d'analyse d'un Ã©lement de liste de syntaxe.
 */
class DstyleDoc_Analyser_Element_Syntax_List extends DstyleDoc_Analyser implements DstyleDoc_Analyser_Descriptable
{
	// {{{ $returns

	protected $_returns = array();

	protected function set_returns( $returns )
	{
		if( empty($returns) ) return;
		$optional = false;
		foreach( explode(',', $returns) as $type )
			$this->_returns[] = trim($type);
	}

	protected function get_returns()
	{
		return $this->_returns;
	}

	// }}}
	// {{{ $params

	protected $_params = array();

	protected function set_params( $params )
	{
		$optional = false;
		foreach( explode(',', $params) as $var )
		{
			// \s*(\[?)\s*(?:([-_\pL\d]+)\s)?\s*(\$[-_\pL\d]+|\.{3})\s*\]?
			if( preg_match('/\\s*(\\[?)\\s*(?:([-_\\pL\d]+)\\s)?\\s*(\\$[-_\\pL\d]+|\\.{3})\\s*\\]?/', $var, $matches) )
			{
				if( ! empty($matches[1]) )
					$optional = true;
				$this->_params[] = array(
					'types' => $matches[2]?$matches[2]:false,
					'var' => $matches[3],
					'optional' => ($optional)?true:(($matches[3]==='...')?true:false) );
			}
		}
	}

	protected function get_params()
	{
		return $this->_params;
	}

	// }}}
	// {{{ $description

	protected $_description = '';

	protected function set_description( $description )
	{
		if( empty($description) ) return;
		$this->_description = (string)$description;
	}

	protected function get_description()
	{
		return $this->_description;
	}

	// }}}
	// {{{ descriptable()

	public function descriptable( DstyleDoc_Element $element, $description )
	{
		if( $element instanceof DstyleDoc_Element_Function )
			$element->syntax->description = $description;
	}

	// }}}
	// {{{ priority

	const priority = 15;

	// }}}
	// {{{ analyse()

	/**
	 * Analyse la ligne de documentation a la recherche d'un probable Ã©lÃ©ment de liste de syntax.
	 * Params:
	 *		DstyleDoc_Analyser $current = L'instance de la ligne prÃ©cÃ©dante.
	 *		string $source = La ligne de documentation a tester.
	 *		DstyleDoc_Analyser_Syntax,null $instance = L'instance a retourner en cas de succÃ©s.
	 *		integer $priority->value = La prioritÃ© de l'Ã©lÃ©ment trouvÃ© en cas de succÃ©s.
	 * Returns:
	 *		boolean = Renvoie true en case de succÃ©s, sinon false.
	 */
	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		// ^(?:[-+*]\s+)?((?:[-_\pL\d]+\s*,\s*)*[-_\pL\d]+)?\s*\(\s*((?:\[?\s*(?:[-_\pL\d]+\s+)(?:\$[-_\pL\d]+|\.{3})\s*\]?\s*,\s*)*\[?\s*(?:[-_\pL\d]+\s+)(?:\$[-_\pL\d]+|\.{3})[\s\]]*\s*)?\)\s*[:=]\s*(.*)$
		if( $dsd->dstyledoc and $dsd->syntax and ($current instanceof DstyleDoc_Analyser_Syntax or $current instanceof DstyleDoc_Analyser_Element_Syntax_List)
			and preg_match( '/^(?:[-+*]\s+)?((?:[-_\pL\d]+\s*,\s*)*[-_\pL\d]+)?\s*\(\s*((?:\[?\s*(?:[-_\pL\d]+\s+)(?:\$[-_\pL\d]+|\.{3})\s*\]?\s*,\s*)*\[?\s*(?:[-_\pL\d]+\s+)(?:\$[-_\pL\d]+|\.{3})[\s\]]*\s*)?\)\s*[:=]\s*(.*)$/', $source, $matches ) )
			{
				$instance->value = new self( $matches[1], $matches[2], $matches[3] );
				$priority->value = self::priority;
				return true;
			}

		// ^(?:[-+*]\s+)((?:[-_\pL\d]+\s*,\s*)*[-_\pL\d]+)?\s*\(\s*((?:(?:[-_\pL\d]+\s+)(?:\$[-_\pL\d]+|\.{3})\s*,\s*)*(?:[-_\pL\d]+\s+)(?:\$[-_\pL\d]+|\.{3}))?\s*\)\s*[:=]?\s*(.*)$
		elseif( $dsd->dstyledoc and $dsd->syntax and ($current instanceof DstyleDoc_Analyser_Syntax or $current instanceof DstyleDoc_Analyser_Element_Syntax_List)
			and preg_match( '/^(?:[-+*]\s+)((?:[-_\pL\d]+\s*,\s*)*[-_\pL\d]+)?\s*\(\s*((?:(?:[-_\pL\d]+\s+)(?:\$[-_\pL\d]+|\.{3})\s*,\s*)*(?:[-_\pL\d]+\s+)(?:\$[-_\pL\d]+|\.{3}))?\s*\)\s*[:=]?\s*(.*)$/', $source, $matches ) )
			{
				$instance->value = new self( $matches[1], $matches[2], $matches[3] );
				$priority->value = self::priority;
				return true;
			}

		// ^(?:@syntax\s+)((?:[-_\pL\d]+\s*,\s*)*[-_\pL\d]+)?\s*\(\s*((?:(?:[-_\pL\d]+\s+)(?:\$[-_\pL\d]+|\.{3})\s*,\s*)*(?:[-_\pL\d]+\s+)(?:\$[-_\pL\d]+|\.{3}))?\s*\)\s*[:=]?\s*(.*)$
		elseif( $dsd->javadoc and $dsd->javadoc_syntax and preg_match( '/^(?:@syntax\s+)((?:[-_\pL\d]+\s*,\s*)*[-_\pL\d]+)?\s*\(\s*((?:(?:[-_\pL\d]+\s+)(?:\$[-_\pL\d]+|\.{3})\s*,\s*)*(?:[-_\pL\d]+\s+)(?:\$[-_\pL\d]+|\.{3}))?\s*\)\s*[:=]?\s*(.*)$/i', $source, $matches ) )
			{
				$instance->value = new self( $matches[1], $matches[2], $matches[3] );
				$priority->value = self::priority;
				return true;
			}

		else
			return false;
	}

	// }}}
	// {{{ apply()

	public function apply( DstyleDoc_Custom_Element $element )
	{
		if( $element instanceof DstyleDoc_Element_Function )
		{
			$element->syntax = true;
			$element->syntax->description = $this->description;
			$element->syntax->function = $element;
			foreach( $this->params as $param )
			{
				$element->param = $param['var'];
				$element->param->type = $param['types'];
				$element->param->optional = $param['optional'];
				if( $param['var'] === '...' )
					$element->param->optional = true;

				$element->syntax->param = $param['var'];
				$element->syntax->param->type = $param['types'];
				$element->syntax->param->optional = $param['optional'];
				if( $param['var'] === '...' )
					$element->syntax->param->optional = true;
			}
			foreach( $this->returns as $return )
			{
				$element->return = $return;
				$element->syntax->return = $return;
			}
		}
		return $this;
	}

	// }}}
	// {{{ __construct()

	public function __construct( $returns, $params, $description )
	{
		$this->returns = $returns;
		$this->params = $params;
		$this->description = $description;
	}

	// }}}
}

/**
 * Classe d'analyse d'une balise de type.
 */
class DstyleDoc_Analyser_Type extends DstyleDoc_Analyser
{
	// {{{ priority

	const priority = 10;

	// }}}
	// {{{ analyse()

	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		// ^(?:types?|vars?)\s*:\s*(:([-_\pL\d]+)\s*[:=]?\s*(.*))?$
		if( $dsd->dstyledoc and $dsd->type and preg_match( '/^(?:types?|vars?)\\s*:\\s*(?:([-_\\pL\d]+)\\s*[:=]?\\s*(.*))?$/i', $source, $matches ) )
		{
			$instance->value = new self();
			$priority->value = self::priority;
			if( isset($matches[2]) )
			{
				$instance->value = new DstyleDoc_Analyser_Element_Type_List( $matches[1], $matches[2] );
				$priority->value = DstyleDoc_Analyser_Element_Type_List::priority;
			}
			return true;
		}
		else
			return false;
	}

	// }}}
	// {{{ apply()

	/**
	 * Ajoute un nouveau paragraphe à la description à l'élément.
	 * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
	 */
	public function apply( DstyleDoc_Custom_Element $element )
	{
		return $this;
	}

	// }}}
}

/**
 * Classe d'analyse d'un Ã©lement de type.
 */
class DstyleDoc_Analyser_Element_Type_List extends DstyleDoc_Analyser implements DstyleDoc_Analyser_Descriptable
{
	// {{{ $type

	protected $_type = '';

	protected function set_type( $type )
	{
		$this->_type = (string)$type;
	}

	protected function get_type()
	{
		return $this->_type;
	}

	// }}}
	// {{{ $description

	protected $_description = '';

	protected function set_description( $description )
	{
		$this->_description = (string)$description;
	}

	protected function get_description()
	{
		return $this->_description;
	}

	// }}}
	// {{{ descriptable()

	public function descriptable( DstyleDoc_Element $element, $description )
	{
		if( $element instanceof DstyleDoc_Element_Function )
			$element->return->description = $description;
	}

	// }}}
	// {{{ priority

	const priority = 15;

	// }}}
	// {{{ analyse()

	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		// ^(?:[-+*]\s+)?([-_\pL\d]+)\s*[:=]\s*(.*)$
		if( $dsd->dstyledoc and $dsd->type and ($current instanceof DstyleDoc_Analyser_Type or $current instanceof DstyleDoc_Analyser_Element_Type_List)
			and preg_match( '/^(?:[-+*]\\s+)?([-_\\pL\d]+)\\s*[:=]\\s*(.*)$/', $source, $matches ) )
			{
				$instance->value = new self( $matches[1], $matches[2] );
				$priority->value = self::priority;
				return true;
			}

		// ^(?:[-+*]\s+)([-_\pL\d]+)\s*[:=]?\s*(.*)$
		elseif( $dsd->dstyledoc and $dsd->type and ($current instanceof DstyleDoc_Analyser_Type or $current instanceof DstyleDoc_Analyser_Element_Type_List)
			and preg_match( '/^(?:[-+*]\\s+)([-_\\pL\d]+)\\s*[:=]?\\s*(.*)$/i', $source, $matches ) )
			{
				$instance->value = new self( $matches[1], $matches[2] );
				$priority->value = self::priority;
				return true;
			}

		// ^(?:@(?:vars?|types?)\s+)([-_\pL\d]+)\s*[:=]?\s*(.*)$
		elseif( $dsd->javadoc and $dsd->javadoc_var and preg_match( '/^(?:@types?\\s+|@vars?\\s+)([-_\\pL\d]+)\\s*[:=]?\\s*(.*)$/i', $source, $matches ) )
			{
				$instance->value = new self( $matches[1], $matches[2] );
				$priority->value = self::priority;
				return true;
			}

		else
			return false;
	}

	// }}}
	// {{{ apply()

	/**
	 * Ajoute un nouveau paragraphe Ã  la description Ã  l'Ã©lÃ©ment.
	 * S'assure que le prÃ©cÃ©dent ajout n'Ã©taient pas dÃ©jÃ  un nouveau paragraphe.
	 */
	public function apply( DstyleDoc_Custom_Element $element )
	{
		if( $element instanceof DstyleDoc_Element_Member )
		{
			$element->type = $this->type;
			$element->type->description = $this->description;
		}
		return $this;
	}

	// }}}
	// {{{ __construct()

	public function __construct( $type, $description )
	{
		$this->type = $type;
		$this->description = $description;
	}

	// }}}
}

/**
 * Classe d'analyse d'une balise de version minimale.
 */
class DstyleDoc_Analyser_Since extends DstyleDoc_Analyser_Version
{
	// {{{ analyse()

	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		// ^since\s*:\\s*(.+)$
		if( $dsd->dstyledoc and $dsd->since and preg_match( '/^since\\s*:\\s*(.+)$/i', $source, $matches ) )
		{
			$instance->value = new self( $matches[1] );
			$priority->value = self::priority;
			return true;
		}

		// ^@since\s*(.+)$
		elseif( $dsd->javadoc and $dsd->javadoc_since and preg_match( '/^@since\s*(.+)$/i', $source, $matches ) )
		{
			$instance->value = new self( $matches[1] );
			$priority->value = self::priority;
			return true;
		}

		else
			return false;
	}

	// }}}
	// {{{ apply()

	/**
	 * Ajoute un nouveau paragraphe à la description à l'élément.
	 * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
	 */
	public function apply( DstyleDoc_Custom_Element $element )
	{
		$element->since = $this->version;
		return $this;
	}

	// }}}
}

class DstyleDoc_Analyser_PHPCode extends DstyleDoc_Analyser
{
	// {{{ priority

	const priority = 10;

	// }}}
	// {{{ $code

	protected $_code = '';

	protected function set_code( $code )
	{
		$this->_code = (string)$code;
	}

	protected function get_code()
	{
		return $this->_code;
	}

	// }}}
	// {{{ analyse()

	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		/* ^(.*)\?>$ */
		if( $current instanceof DstyleDoc_Analyser_PHPOpenTag and preg_match( '/^(.*)\?>$/', $source, $match ) )
		{
			$instance->value = new DstyleDoc_Analyser_PHPCode( $match[1] );
			$priority->value = self::priority;
			return true;
		}
		elseif( $current instanceof DstyleDoc_Analyser_PHPOpenTag )
		{
			$instance->value = new DstyleDoc_Analyser_PHPOpenTag( $source );
			$priority->value = DstyleDoc_Analyser_PHPOpenTag::priority;
			return true;
		}

		else
			return false;
	}

	// }}}
	// {{{ apply()

	public function apply( DstyleDoc_Custom_Element $element )
	{
		$tmp = $element->descriptions;
		$last = end($tmp);

		if( $last instanceof DstyleDoc_Descritable_PHP_Code )
			$last->append = "\n".$this->code;
		else
			$element->description = new DstyleDoc_Descritable_PHP_Code( $this->code, $element );

		return $this;
	}

	// }}}
	// {{{ __construct()

	public function __construct( $code )
	{
		$this->code = $code;
	}

	// }}}
}

/**
 * Classe d'analyse d'une balise de dÃ©but de code PHP.
 */
class DstyleDoc_Analyser_PHPOpenTag extends DstyleDoc_Analyser
{
	// {{{ priority

	const priority = 25;

	// }}}
	// {{{ $code

	protected $_code = '';

	protected function set_code( $code )
	{
		$this->_code = (string)$code;
	}

	protected function get_code()
	{
		return $this->_code;
	}

	// }}}
	// {{{ analyse()

	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		/* ^<\?php(.*)(?<!\?>)$ */
		if( preg_match( '/^<\?php(.*)(?<!\?>)$/i', $source, $match ) )
		{
			$instance->value = new DstyleDoc_Analyser_PHPOpenTag( $match[1] );
			$priority->value = self::priority;
			return true;
		}

		/* ^<\?php(.*)(?>\?>)$ */
		elseif( preg_match( '/^<\?php(.*)(?>\?>)$/i', $source, $match ) )
		{
			$instance->value = new DstyleDoc_Analyser_PHPCode( $match[1] );
			$priority->value = DstyleDoc_Analyser_PHPCode::priority;
			return true;
		}

		else
			return false;
	}

	// }}}
	// {{{ apply()

	public function apply( DstyleDoc_Custom_Element $element )
	{
		$tmp = $element->descriptions;
		$last = end($tmp);

		if( $last instanceof DstyleDoc_Descritable_PHP_Code )
			$last->append = "\n".$this->code;
		else
			$element->description = new DstyleDoc_Descritable_PHP_Code( $this->code, $element );

		return $this;
	}

	// }}}
	// {{{ __construct()

	public function __construct( $code )
	{
		$this->code = $code;
	}

	// }}}
}

/**
 * Classe d'analyse d'une balise d'historique.
 */
class DstyleDoc_Analyser_Package extends DstyleDoc_Analyser
{
	// {{{ priority

	const priority = 10;

	// }}}
	// {{{ analyse()

	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		// ^packages?\s*:(?:\s*(.+))?$
		if( $dsd->dstyledoc and $dsd->history and preg_match( '/^packages?\s*:(?:\s*(.+))?$/i', $source, $matches ) )
		{
			$instance->value = new self();
			$priority->value = self::priority;
			if( ! empty($matches[1]) )
			{
				$instance->value = new DstyleDoc_Analyser_Element_Package_List( $matches[1] );
				$priority->value = DstyleDoc_Analyser_Element_Package_List::priority;
			}
			return true;
		}
		else
			return false;
	}

	// }}}
	// {{{ apply()

	/**
	 * Ajoute un nouveau paragraphe Ã  la description Ã  l'Ã©lÃ©ment.
	 * S'assure que le prÃ©cÃ©dent ajout n'Ã©taient pas dÃ©jÃ  un nouveau paragraphe.
	 */
	public function apply( DstyleDoc_Custom_Element $element )
	{
		return $this;
	}

	// }}}
}

/**
 * Classe d'analyse d'un Ã©lÃ©ment de liste d'historique.
 */
class DstyleDoc_Analyser_Element_Package_List extends DstyleDoc_Analyser
{
	// {{{ $package, $packages

	protected $_packages = array();

	protected function set_package( $packages )
	{
		// [.,:/]+
		foreach( preg_split( '%[.,:/]+%', (string)$packages ) as $package )
		{
			if( $package )
	$this->_packages[] = trim($package);
		}
	}

	protected function get_packages()
	{
		return $this->_packages;
	}

	// }}}
	// {{{ priority

	const priority = 20;

	// }}}
	// {{{ analyse()

	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		//	^(?:[-+*]\s+)?((?:[-_\pL\d]+\s*[.,:\/]+\s*)*[-_\pL\d]+)$
		if( $dsd->dstyledoc and $dsd->package and ($current instanceof DstyleDoc_Analyser_Package or $current instanceof DstyleDoc_Analyser_Element_Package_List)
			and preg_match( '%^(?:[-+*]\s+)?((?:[-_\pL\d]+\s*[.,:/]+\s*)*[-_\pL\d]+)$%', $source, $matches ) )
		{
			$instance->value = new self( $matches[1] );
			$priority->value = self::priority;
			return true;
		}

		// ^@(?:sub)?package\s+((?:[-_\pL\d]+\s*[.,:\/]+\s*)*[-_\pL\d]+)$
		elseif( $dsd->javadoc and $dsd->javadoc_package and preg_match( '%^@(?:sub)?package\s+((?:[-_\pL\d]+\s*[.,:/]+\s*)*[-_\pL\d]+)$%i', $source, $matches ) )
		{
			$instance->value = new self( $matches[1] );
			$priority->value = self::priority;
			return true;
		}

		else
			return false;
	}

	// }}}
	// {{{ apply()

	/**
	 * Ajoute la liste des packages Ã  l'Ã©lement.
	 */
	public function apply( DstyleDoc_Custom_Element $element )
	{
		$element->package = $this->packages;
		return $this;
	}

	// }}}
	// {{{ __construct()

	public function __construct( $package )
	{
		$this->package = $package;
	}

	// }}}
}

/**
 * Classe d'analyse d'une balise de todo.
 */
class DstyleDoc_Analyser_Todo extends DstyleDoc_Analyser
{
	// {{{ priority

	const priority = 10;

	// }}}
	// {{{ analyse()

	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		// ^todos?\s*:\s*(.*)?$
		if( $dsd->dstyledoc and $dsd->todo and preg_match( '/^todos?\s*:\s*(.*)?$/i', $source, $matches ) )
		{
			$instance->value = new self();
			$priority->value = self::priority;
			if( isset($matches[1]) )
			{
				if( trim($matches[1]) )
					$instance->value = new DstyleDoc_Analyser_Element_Todo_List( $matches[1] );
				else
					$instance->value = new self;
				$priority->value = DstyleDoc_Analyser_Element_Todo_List::priority;
			}
			return true;
		}
		else
			return false;
	}

	// }}}
	// {{{ apply()

	/**
	 * Ajoute un nouveau paragraphe Ã  la description Ã  l'Ã©lÃ©ment.
	 * S'assure que le prÃ©cÃ©dent ajout n'Ã©taient pas dÃ©jÃ  un nouveau paragraphe.
	 */
	public function apply( DstyleDoc_Custom_Element $element )
	{
		return $this;
	}

	// }}}
}

/**
 * Classe d'analyse d'un Ã©lement de liste des todos.
 */
class DstyleDoc_Analyser_Element_Todo_List extends DstyleDoc_Analyser implements DstyleDoc_Analyser_Descriptable
{
	// {{{ $description

	protected $_description = '';

	protected function set_description( $description )
	{
		$this->_description = (string)$description;
	}

	protected function get_description()
	{
		return $this->_description;
	}

	// }}}
	// {{{ descriptable()

	public function descriptable( DstyleDoc_Element $element, $description )
	{
		if( $element instanceof DstyleDoc_Element_Function )
			$element->todo->description = $description;
	}

	// }}}
	// {{{ priority

	const priority = 15;

	// }}}
	// {{{ analyse()

	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		// ^\s*(?:[-+*]\s+)(.*)$
		if( $dsd->dstyledoc and $dsd->todo and ($current instanceof DstyleDoc_Analyser_Todo or $current instanceof DstyleDoc_Analyser_Element_Todo_List)
			and preg_match( '/^\s*(?:[-+*]\s+)(.*)$/', $source, $matches ) )
			{
				$instance->value = new self( $matches[1] );
				$priority->value = self::priority;
				return true;
			}

		// ^\s*(?:@todos?\s)[:=]?\s*(.*)$
		elseif( $dsd->javadoc and $dsd->javadoc_todo and preg_match( '/^\s*(?:@todos?\s)[:=]?\s*(.*)$/i', $source, $matches ) )
			{
				$instance->value = new self( $matches[1] );
				$priority->value = self::priority;
				return true;
			}

		else
			return false;
	}

	// }}}
	// {{{ apply()

	/**
	 * Ajoute une todo Ã  l'Ã©lÃ©ment.
	 */
	public function apply( DstyleDoc_Custom_Element $element )
	{
		if( $this->description and $element instanceof DstyleDoc_Element_Titled )
				$element->todo->description = $this->description;
		return $this;
	}

	// }}}
	// {{{ __construct()

	public function __construct( $description )
	{
		$this->description = $description;
	}

	// }}}
}

/**
 * Classe d'analyse d'une balise licence.
 * Todo: permettre la javadoc
 * Todo: permettre les urls de licence
 */
class DstyleDoc_Analyser_Licence extends DstyleDoc_Analyser implements DstyleDoc_Analyser_Descriptable
{
	// {{{ $licence

	protected $_licence = '';

	protected function set_licence( $licence )
	{
		$this->_licence = (string)$licence;
	}

	protected function get_licence()
	{
		return $this->_licence;
	}

	// }}}
	// {{{ descriptable()

	public function descriptable( DstyleDoc_Element $element, $licence )
	{
		if( $element instanceof DstyleDoc_Element_File )
			$element->licence = $licence;
	}

	// }}}
	// {{{ priority

	const priority = 10;

	// }}}
	// {{{ analyse()

	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		// ^licence\s*:\s*$
		if( $dsd->dstyledoc and $dsd->licence and preg_match( '/^licence\s*:\s*$/i', $source, $matches ) )
		{
			$instance->value = new self();
			$priority->value = self::priority;
			return true;
		}
		else
			return false;
	}

	// }}}
	// {{{ apply()

	/**
	 * Ajoute un nouveau paragraphe Ã  la description Ã  l'Ã©lÃ©ment.
	 * S'assure que le prÃ©cÃ©dent ajout n'Ã©taient pas dÃ©jÃ  un nouveau paragraphe.
	 */
	public function apply( DstyleDoc_Custom_Element $element )
	{
		return $this;
	}

	// }}}
}

/**
 * Classe d'analyse d'une balise de membre.
 */
class DstyleDoc_Analyser_Member extends DstyleDoc_Analyser_Param
{
	// {{{ analyse()

	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		// ^members?\s*:\s*(?:\s(?:[(]?([^\s+]+)[)]?\s+)?(?:(\$.+?|\.{3})(?:\s*[:=]?\s+)?)(.*))?$
		if( $dsd->dstyledoc and $dsd->params and preg_match( '/^members?\s*:\s*(?:\s(?:[(]?([^\s+]+)[)]?\s+)?(?:(\$.+?|\.{3})(?:\s*[:=]?\s+)?)(.*))?$/i', $source, $matches ) )
		{
			$instance->value = new self();
			$priority->value = self::priority;
			if( isset($matches[3]) )
			{
				$instance->value = new DstyleDoc_Analyser_Element_Member_List( $matches[1], $matches[2], $matches[3] );
				$priority->value = DstyleDoc_Analyser_Element_Member_List::priority;
			}
			return true;
		}
		else
			return false;
	}

	// }}}
}

/**
 * Classe d'analyse d'un Ã©lÃ©ment de liste de paramÃ¨tre.
 */
class DstyleDoc_Analyser_Element_Member_List extends DstyleDoc_Analyser_Element_Param_List
{
	// {{{ priority

	/**
	 * Prioritaire sur DstyleDoc_Analyser_Element_Param_List
	 */
	const priority = 14;

	// }}}
	// {{{ analyse()

	static public function analyse( $current, $source, $instance, $priority, DstyleDoc $dsd )
	{
		// ^(?:[(]?([-_,\| \pL\d]+)[)]?\s+)?(\$[-_\pL\d]+|\.{3})\s*[:=]\s*(.*)$
		if( $dsd->dstyledoc and $dsd->params and ($current instanceof DstyleDoc_Analyser_Member or $current instanceof DstyleDoc_Analyser_Element_Member_List)
			and preg_match( '/^(?:[(]?([-_,\\| \\pL\d]+)[)]?\\s+)?(\\$[-_\\pL\d]+|\\.{3})\\s*[:=]\\s*(.*)$/', $source, $matches ) )
		{
			$instance->value = new self( $matches[1], $matches[2], $matches[3] );
			$priority->value = self::priority;
			return true;
		}

		// ^(?:[-+*]\s+)(?:[(]?([-_,\| \pL\d]+)[)]?\s+)?(\$[-_\pL\d]+|\.{3})\s*[:=]?\s*(.*)$
		elseif( $dsd->dstyledoc and $dsd->params and ($current instanceof DstyleDoc_Analyser_Member or $current instanceof DstyleDoc_Analyser_Element_Member_List)
			and preg_match( '/^(?:[-+*]\\s+)(?:[(]?([-_,\\| \\pL\d]+)[)]?\\s+)?(\\$[-_\\pL\d]+|\\.{3})\\s*[:=]?\\s*(.*)$/i', $source, $matches ) )
		{
			$instance->value = new self( $matches[1], $matches[2], $matches[3] );
			$priority->value = self::priority;
			return true;
		}

		// ^(?:@params?\s+)(?:[(]?([-_,\| \pL\d]+)[)]?\s+)?(\$[-_\pL\d]+|\.{3})\s*[:=]?\s*(.*)$
		elseif( $dsd->javadoc and $dsd->javadoc_params and preg_match( '/^(?:@members?\\s+)(?:[(]?([-_,\\| \\pL\d]+)[)]?\\s+)?(\\$[-_\\pL\d]+|\\.{3})\\s*[:=]?\\s*(.*)$/i', $source, $matches ) )
		{
			$instance->value = new self( $matches[1], $matches[2], $matches[3] );
			$priority->value = self::priority;
			return true;
		}

		else
			return false;
	}

	// }}}
	// {{{ apply()

	/**
	 * Ajoute un nouveau paragraphe Ã  la description Ã  l'Ã©lÃ©ment.
	 */
	public function apply( DstyleDoc_Custom_Element $element )
	{
		if( $element instanceof DstyleDoc_Element_Class )
		{
			$element->member = $this->var;

			foreach( $this->types as $type )
				if( $type )
					$element->member->type = $type;

			$element->member->description = $this->description;
		}
		return $this;
	}

	// }}}
}

