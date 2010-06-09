<?php

require_once 'element.php';

/**
 * Classe d'un element de type classe.
 */
class ClassElement extends MethodedElement
{
	// {{{ $methods

	protected function get_methods()
	{
		$methods = $this->_methods;

		if( $this->parent instanceof DstyleDoc_Element )
			foreach( $this->parent->heritable_methods as $method )
					// todo: in_array() do not work correctly. see bug #38356 at http://bugs.php.net/bug.php?id=39356
				//				if( ! in_array($method, $methods) )
				foreach( $methods as $value )
					if( $method === $value )
						self::add_uniq_name_methods( $methods, $method );

		return $methods;
	}

	// }}}
	// {{{ add_uniq_name_methods()

	static protected function add_uniq_name_methods( &$methods, $method )
	{
		foreach( $methods as $value )
			if( $value->name == $method->name )
				return null;
		$methods[] = $method;
	}

	// }}}
	// {{{ $heritable_methods

	/**
	 * Retourne la liste des méthodes héritables de la classe.
	 * Retourne la liste des méthodes de la classe et des méthodes héritées qui ont un accès publiques ou protétéges.
	 * Returns:
	 *		array(DstyleDoc_Element_Method) = La liste des méthodes héritables de la classe.
	 */
	protected function get_heritable_methods()
	{
		$methods = array();

		foreach( $this->_methods as $method )
			if( $method->protected or $method->public )
				$methods[] = $method;

		if( $this->parent )
			foreach( $this->parent->heritable_methods as $method )
				if( ! in_array($method, $methods) )
					$methods[] = $method;

		return $methods;
	}

	// }}}
	// {{{ $abstract

	protected $_abstract = false;

	protected function set_abstract( $abstract )
	{
		$this->_abstract = $abstract;
	}

	protected function get_abstract()
	{
		return $this->_abstract;
	}

	// }}}
	// {{{ $final

	protected $_final = false;

	protected function set_final( $final )
	{
		$this->_final = $final;
	}

	protected function get_final()
	{
		return $this->_final;
	}

	// }}}
	// {{{ $parent

	protected $_parent = null;

	protected function set_parent( $parent )
	{
		if( $parent )
			$this->_parent = (string)$parent;
	}

	protected function get_parent()
	{
		if( $found = $this->converter->class_exists($this->_parent) )
			return $found;
		else
			return $this->_parent;
	}

	// }}}
	// {{{ $implements

	protected $_implements = array();

	protected function set_implement( $implement )
	{
		$this->_implements[] = (string)$implement;
	}

	protected function get_implements()
	{
		return $this->_implements;
	}

	// }}}
	// {{{ $childs

	protected function get_childs()
	{
		return $this->_childs;
	}

	// }}}
	// {{{ $members

	/**
	 * Contient la listes des membres déclaré par la classe.
	 * Type:
	 *		array(DstyleDoc_Element_Member) = Tableau a clefs numériques contentant des instances de DstyleDoc_Element_Member.
	 */
	protected $_members = array();

	/**
	 * Ajoute un membre a la classe ou selectionne un membre existant.
	 * Si le membre à été ajouté, il sera sélectionné. Le membre sera alors renvoyé par la propriété $membre.
	 * Params:
	 *		string = Le nom du membre a ajouter ou a sélectionner.
	 *		DstyleDoc_Element_Member = L'instance du membre a ajouter ou a laélectionner.
	 */
	protected function set_member( $name )
	{
		$found = false;
		if( ! empty($name) and count($this->_members) )
		{
			reset($this->_members);
			while( true)
			{
				$member = current($this->_members);
				if( $found = ($member->name == $name or $member === $name) or false === next($this->_members) )
					break;
			}
		}

		if( ! $found )
		{
			if( $name instanceof DstyleDoc_Element_Member )
				$this->_members[] = $name;
			else
				$this->_members[] = new DstyleDoc_Element_Member( $this->converter, $name );
			end($this->_members);
		}
	}

	/**
	 * Retourne le dernier membre ajouté.
	 * Returns:
	 *		DstyleDoc_Element_Member = Le dernier membre ajouté.
	 */
	protected function get_member()
	{
		if( ! count($this->_members) )
		{
			$this->_members[] = new DstyleDoc_Element_Member( $this->converter, null );
			return end($this->_members);
		}
		else
			return current($this->_members);
	}

	/**
	 * Retourne la liste des membres de la classe et des membres hérités.
	 * Returns:
	 *		array(DstyleDoc_Element_Member) = La liste des membres de la classe et des membres hérités.
	 */
	protected function get_members()
	{
		$membres = $this->_members;

		if( $this->parent instanceof DstyleDoc_Element )
			foreach( $this->parent->heritable_members as $membre )
				if( ! in_array($membre, $membres) )
					$membres[] = $membre;

		return $membres;
	}

	/**
	 * Retourne la liste des membres héritables de la classe.
	 * Retourne la liste des membres de la classe et des membres hérité pqui ont un accès publique ou protétég.
	 * Returns:
	 *		array(DstyleDoc_Element_Member) = La liste des membres héritables de la classe.
	 */
	protected function get_heritable_members()
	{
		$membres = array();

		foreach( $this->_members as $membre )
			if( $membre->protected or $membre->public )
				$membres[] = $membre;

		if( $this->parent )
			foreach( $this->parent->heritable_members as $membre )
				if( ! in_array($membre, $membres) )
					$membres[] = $membre;

		return $membres;
	}

	// }}}
	// {{{ $id

	protected function get_id()
	{
		return $this->converter->convert_id( array($this->file->file, $this->name), $this );
	}

	// }}}
	// {{{ $display

	protected function get_display()
	{
		return $this->converter->convert_display( $this->name, $this );
	}

	// }}}
	// {{{ $convert

	protected function get_convert()
	{
		$this->analyse();
		return $this->converter->convert_class( $this );
	}

	// }}}
	// {{{ $package

	protected function get_packages()
	{
		if( ! $this->_packages and $this->parent instanceof DstyleDoc_Element )
			return $this->parent->packages;
		else
			return parent::get_packages();
	}

	// }}}
}

