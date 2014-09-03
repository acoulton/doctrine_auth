<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Base class for a password hashing driver - used to provide gradually incremental
 * security.
 *
 * Heavily influenced by [https://github.com/shadowhand/bonafide](Shadowhand's Bonafide Auth module).
 *
 * @package    Doctrine_Auth
 * @category   Hashing
 * @author     Andrew Coulton
 * @copyright  (c) 2012 Andrew Coulton
 * @license    http://kohanaframework.org/license
 */
abstract class Auth_Hash_Driver
{

	/**
	 * Local config storage
	 * @var array
	 */
	protected $_config = NULL;

	/**
	 * Store the config locally
	 * @param array $config
	 */
	public function __construct($config)
	{
		$this->_config = $config;
	}

	/**
	 * Check that a given plain-text value matches a hash
	 *
	 * @param string $value   Plain-text value
	 * @param string $hash    Previous hash value
	 * @return boolean
	 */
	abstract public function check($value, $hash);

	/**
	 * Hash a value
	 *
	 * @param string $value
	 * @return string The hash
	 */
	abstract public function hash($value);
}