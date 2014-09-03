<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Driver for legacy Kohana 3.0 password hashing mechanism for legacy compatability
 *
 * Heavily influenced by [https://github.com/shadowhand/bonafide](Shadowhand's Bonafide Auth module).
 *
 * @package    Doctrine_Auth
 * @category   Hashing
 * @author     Andrew Coulton
 * @copyright  (c) 2012 Andrew Coulton
 * @license    http://kohanaframework.org/license
 */
class Auth_Hash_KO30 extends Auth_Hash_Driver
{

	/**
	 * Loads configuration options
	 *
	 * @return  void
	 */
	public function __construct($config)
	{
		$config['salt_pattern'] = preg_split('/,\s*/', $config['salt_pattern']);
		parent::__construct($config);
	}

	public function check($value, $hash)
	{
		if (empty($value))
			return FALSE;

		// Get the salt from the stored password
		$salt = $this->find_salt($hash);

		// Create a hashed password using the salt from the stored password
		$check_hash = $this->hash_password($value, $salt);

		return $check_hash === $hash;
	}

	public function hash($value)
	{
		return $this->hash_password($value);
	}

	/**
	 * Creates a hashed password from a plaintext password, inserting salt
	 * based on the configured salt pattern.
	 *
	 * @param   string  plaintext password
	 * @return  string  hashed password string
	 */
	protected function hash_password($password, $salt = FALSE)
	{
		if ($salt === FALSE)
		{
			// Create a salt seed, same length as the number of offsets in the pattern
			$salt = substr($this->_hash(uniqid(NULL, TRUE)), 0, count($this->_config['salt_pattern']));
		}

		// Password hash that the salt will be inserted into
		$hash = $this->_hash($salt.$password);

		// Change salt to an array
		$salt = str_split($salt, 1);

		// Returned password
		$password = '';

		// Used to calculate the length of splits
		$last_offset = 0;

		foreach ($this->_config['salt_pattern'] as $offset)
		{
			// Split a new part of the hash off
			$part = substr($hash, 0, $offset - $last_offset);

			// Cut the current part out of the hash
			$hash = substr($hash, $offset - $last_offset);

			// Add the part to the password, appending the salt character
			$password .= $part.array_shift($salt);

			// Set the last offset to the current offset
			$last_offset = $offset;
		}

		// Return the password, with the remaining hash appended
		return $password.$hash;
	}

	/**
	 * Perform a hash, using the configured method.
	 *
	 * @param   string  string to hash
	 * @return  string
	 */
	public function _hash($str)
	{
		return hash($this->_config['hash_method'], $str);
	}

	/**
	 * Finds the salt from a password, based on the configured salt pattern.
	 *
	 * @param   string  hashed password
	 * @return  string
	 */
	public function find_salt($hash)
	{
		$salt = '';

		foreach ($this->_config['salt_pattern'] as $i => $offset)
		{
			// Find salt characters, take a good long look...
			$salt .= substr($hash, $offset + $i, 1);
		}

		return $salt;
	}

}