<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Driver for native Kohana 3.2 password hashing - uses hash_hmac
 *
 * Heavily influenced by [https://github.com/shadowhand/bonafide](Shadowhand's Bonafide Auth module).
 *
 * @package    Doctrine_Auth
 * @category   Hashing
 * @author     Andrew Coulton
 * @copyright  (c) 2012 Andrew Coulton
 * @license    http://kohanaframework.org/license
 */
class Auth_Hash_KO32 extends Auth_Hash_Driver
{

	public function check($value, $hash)
	{
		$check_hash = Auth::instance()->hash($value);
		return $hash === $check_hash;
	}

	public function hash($value)
	{
		return Auth::instance()->hash($value);
	}

}