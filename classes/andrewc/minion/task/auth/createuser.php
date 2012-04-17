<?php

/**
 * The CreateUser task creates a new user in the auth database and sends them
 * an email activation token to allow them to activate their account.
 *
 * Available config options are:
 *
 * --email (required)
 *
 *   The user's email address
 *
 * --password (optional)
 *
 *   If no password is specified, will allocate a random password
 *
 * --name (optional)
 *
 *   The user's name - will use email address if not provided
 *
 *  --roles (optional)
 *
 *   A comma separated list of roles to assign
 *
 * @package    Doctrine_Auth
 * @category   Administration
 * @author     Andrew Coulton
 * @copyright  (c) 2012 Andrew Coulton
 * @license    http://kohanaframework.org/license
 */
class AndrewC_Minion_Task_Auth_CreateUser extends Minion_Task
{

	/**
	 * A set of config options that this task accepts
	 * @var array
	 */
	protected $_config = array(
		'email',
		'password',
		'name',
		'roles',
	);

	/**
	 * Creates the user
	 * @param array $config
	 */
	public function execute(array $config)
	{
		// Get options
		$config = Arr::extract($config, array('email','password','roles','name'));
		$roles = explode(',', $config['roles']);

		// Set defaults
		if ( ! $config['name'])
		{
			$config['name'] = $config['email'];
		}

		// Send the activation link
		$user = Model_Auth_User::create_user($config['email'],
				$config['name'],
				$roles);

		// If a password was set, set their password
		if ($config['password'])
        {
            $user->password = $password;
            $user->save();
        }

		Minion_CLI::write('Account successfully created for '.$config['email']);
	}
}