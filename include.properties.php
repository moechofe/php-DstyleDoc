<?php
namespace dstyledoc
{

/**
 * Classe de prise en charge de surcharges des membres.
 * Permet d'utiliser facilement des getter, setter, issetter et unsetter.
 * Cette classe doit être étendu.
 */
abstract class Properties
{
	/**
	 * Permet d'utiliser des getter.
	 * __get() est appelé automatiquement par PHP lors de l'accès en lecture d'un membre inexistant.
	 * __get() vérifiera au préalable que la méthode "get_"+<nom_du_membre>() existe et quelle est appelable. Dans le cas contraire, l'exception BadPropertyException sera lancé.
	 * Params:
	 *	 string $property = Le nom du membre.
	 *	 Si égale à "__class" alors retourne le nom de la classe dérivé de DstyleDoc_Properties.
	 * Returns:
	 *	 mixed = Retournera la valeur retournée par la méthode "get_"+<nom_du_membre>().
	 *	 get_class() = Le nom de la classe dérivé de DstyleDoc_Properties.
	 * Throws:
	 *	 BadPropertyException = Lancé si la méthode "get_"+<nom_du_membre>() n'est pas disponible.
	 */
	public function __get( $property )
	{
		if( $property === '__class' )
			return get_class( $this );

		elseif( ! method_exists($this,'get_'.(string)$property) or ! is_callable( array($this,'get_'.(string)$property) ) )
			throw new \BadPropertyException($this, (string)$property);

		return call_user_func( array($this,'get_'.(string)$property) );
	}

	/**
	 * Permet d'utiliser des setter.
	 * __set() est appelé automatiquement par PHP lors de l'accès en écriture d'un membre inexistant.
	 * __set() vérifiera au préalable que la méthode "set_"+<nom_du_membre>() existe et quelle est appelable. Dans le cas contraire, l'exception BadPropertyException sera lancé.
	 * Params:
	 *	 string $property = Le nom du membre.
	 * Returns:
	 *	 mixed = Retournera la valeur retournée par la méthode "set_"+<nom_du_membre>().
	 * Throws:
	 *	 BadPropertyException = Lancé si la méthode "set_"+<nom_du_membre>() n'est pas disponible.
	 */
	public function __set( $property, $value )
	{
		if( ! method_exists($this,'set_'.(string)$property) or ! is_callable( array($this,'set_'.(string)$property) ) )
			throw new \BadPropertyException($this, (string)$property);

		call_user_func( array($this,'set_'.(string)$property), $value );
	}

	/**
	 * Permet d'utiliser des issetter.
	 * __isset() est appelé automatiquement par PHP lors de l'accès d'existance d'un membre inexistant.
	 * __isset() vérifiera au préalable que la méthode "isset_"+<nom_du_membre>() existe et quelle est appelable. Dans le cas contraire, l'exception BadPropertyException sera lancé.
	 * Params:
	 *	 string $property = Le nom du membre.
	 * Returns:
	 *	 mixed = Retournera la valeur retournée par la méthode "isset_"+<nom_du_membre>().
	 * Throws:
	 *	 BadPropertyException = Lancé si la méthode "isset_"+<nom_du_membre>() n'est pas disponible.
	 */
	public function __isset( $property )
	{
		if( ! method_exists($this,'isset_'.(string)$property) or ! is_callable( array($this,'isset_'.(string)$property) ) )
			throw new \BadPropertyException($this, (string)$property);

		return call_user_func( array($this,'isset_'.(string)$property) );
	}

	/**
	 * Permet d'utiliser des unsetter.
	 * __unset() est appelé automatiquement par PHP lors de l'accès d'effacement d'un membre inexistant.
	 * __unset() vérifiera au préalable que la méthode "unset_"+<nom_du_membre>() existe et quelle est appelable. Dans le cas contraire, l'exception BadPropertyException sera lancé.
	 * Params:
	 *	 string $property = Le nom du membre.
	 * Returns:
	 *	 mixed = Retournera la valeur retournée par la méthode "isset_"+<nom_du_membre>().
	 * Throws:
	 *	 BadPropertyException = Lancé si la méthode "unset_"+<nom_du_membre>() n'est pas disponible.
	 */
	public function __unset( $property )
	{
		if( ! method_exists($this,'unset_'.(string)$property) or ! is_callable( array($this,'unset_'.(string)$property) ) )
			throw new \BadPropertyException($this, (string)$property);

		call_user_func( array($this,'unset_'.(string)$property) );
	}
}

}
namespace
{

if( ! class_exists('BadPropertyException') )
{
class BadPropertyException extends LogicException
{
	public function __construct( $class, $member )
	{
		parent::__construct( sprintf('Access denied for %s::$%s.', get_class($class), $member) );
		$trace = $this->getTrace();
		if( isset($trace[0]) and isset($trace[0]['line']) and isset($trace[0]['file']) )
		{
			$this->line = $trace[0]['line'];
			$this->file = $trace[0]['file'];
		}
	}
}
}

}
