<?php

/**
 * Driver-based password hashing allows hashes to be upgraded over time without
 * locking out existing users.
 *
 * Heavily influenced by [https://github.com/shadowhand/bonafide](Shadowhand's Bonafide Auth module).
 *
 * @package    Doctrine_Auth
 * @category   Hashing
 * @author     Andrew Coulton
 * @copyright  (c) 2012 Andrew Coulton
 * @license    http://kohanaframework.org/license
 */
class Auth_Hash
{

	/**
	 * Instance cache for config data
	 * @var array
	 */
	protected $_config = array();

	/**
	 * The driver name to apply
	 * @var string
	 */
	protected $_driver_name = NULL;

	/**
	 * The hash part (without driver name) of a parsed hash)
	 * @var string
	 */
	protected $_hash_value = NULL;

	/**
	 * Load Kohana config
	 */
	protected function __construct()
	{
		$this->_config = Kohana::$config->load('auth.hash');
	}

	/**
	 * Create a new Auth_Hash object
	 * @return Auth_Hash
	 */
	public static function factory()
	{
		return new Auth_Hash();
	}

	/**
	 * Hash a new value - always uses the latest available driver configuration
	 *
	 * @param string $value
	 * @return string
	 */
	public function hash($value)
	{
		$hash = $this->driver()->hash($value);
		return $this->_driver_name.'|'.$hash;
	}

	/**
	 * Check if a given value matches a hash (using the same driver that was
	 * used originally)
	 *
	 * @param string $value
	 * @param string $hash
	 * @return boolean
	 */
	public function check($value, $hash)
	{
		$this->parse_hash($hash);
		return $this->driver()
				->check($value, $this->_hash_value);
	}

	/**
	 * Check if a particular hash is using the best-available driver settings
	 *
	 * @param string $hash
	 * @return boolean
	 */
	public function is_best($hash = NULL)
	{
		if ($hash !== NULL)
		{
			$this->parse_hash($hash);
		}
		return $this->_driver_name === $this->_config['latest'];
	}

	/**
	 * Parse an existing hash to identify the driver and hash values
	 * @param string $hash
	 */
	protected function parse_hash($hash)
	{
		if (strpos($hash, '|') == FALSE)
		{
			$hash = '|'.$hash;
		}

		list ($this->_driver_name, $this->_hash_value) = explode('|', $hash, 2);

		// Apply the default driver if there is no driver prefix
		if ( ! $this->_driver_name)
		{
			$this->_driver_name = $this->_config['default'];
		}

	}

	/**
	 * Get a driver instance
	 * 
	 * @return Auth_Hash_Driver
	 */
	protected function driver()
	{
		if ($this->_driver_name === NULL)
		{
			$this->_driver_name = $this->_config['latest'];
		}

		$config = Arr::get($this->_config['drivers'], $this->_driver_name, array());
		$class = 'Auth_Hash_'.$this->_driver_name;

		return new $class($config);
	}

}