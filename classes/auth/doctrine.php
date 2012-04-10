<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 *  Auth Doctrine Driver
 *
 * @package    Doctrine Auth
 * @author     Andrew Coulton
 */
class Auth_Doctrine extends Auth {

    /**
     * Checks if a session is active.
     *
     * @param   string   role name
     * @param   array    collection of role names
     * @return  boolean
     */
    public function logged_in($role = NULL) {
        $status = FALSE;

        // Get the user from the session
        $user = $this->get_user();
        if (!is_object($user)) {
            // Attempt auto login
            if ($this->auto_login()) {
                // Success, get the user back out of the session
                $user = $this->get_user();
            }
        }

        if (is_object($user) AND $user instanceof Model_Auth_User AND $user->id) {
            // Everything is okay so far
            $status = TRUE;

            if (!empty($role)) {
                // If role is an array
                if (is_array($role)) {
                    // Check each role
                    foreach ($role as $role_iteration) {
                        // If the user doesn't have the role
                        if (!$user->has_role($role_iteration)) {
                            // Set the status false and get outta here
                            $status = FALSE;
                            break;
                        }
                    }
                }
                else {
                    // Check that the user has the given role
                    $status = $user->has_role($role);
                }
            }
        }

        return $status;
    }

    /**
     * Logs a user in.
     *
     * @param   string   username
     * @param   string   password
     * @param   boolean  enable auto-login
     * @return  boolean
     */
    public function _login($user, $password, $remember) {
        // Make sure we have a user object
        $user = $this->_get_object($user);

        // If the passwords match, perform a login
        if ($user AND $user->has_role('login') AND $user->password_matches($password)) {
            if ($remember === TRUE) {
                // Create a new autologin token
                $token = new Model_Auth_User_Token();

                // Set token data
                $token->user = $user->id;
                $token->expires = time() + $this->_config['lifetime'];
                $token->type = 'autologin';

                $token->create();

                // Set the autologin Cookie
                Cookie::set('authautologin', $token->token, $this->_config['lifetime']);
            }

            // Finish the login
            $this->complete_login($user);

            return TRUE;
        }

        // Login failed
        return FALSE;
    }

    /**
     * Forces a user to be logged in, without specifying a password.
     *
     * @param   mixed    username
     * @return  boolean
     */
    public function force_login($user, $mark = true) {
        // Make sure we have a user object
        $user = $this->_get_object($user);
        // Mark the session as forced, to prevent users from changing account information
        if ($mark)
        {
            $_SESSION['auth_forced'] = TRUE;
        }

        // Run the standard completion
        $this->complete_login($user);
    }

    /**
     * Logs a user in, based on the authautologin Cookie.
     *
     * @return  boolean
     */
    public function auto_login() {
        $tokenString = Cookie::get('authautologin');
        if ($tokenString) {
            // Load the token and user
            $token = Model_Auth_User_Token::fetchTokenFromString($tokenString);

            if ($token) {
                if ($token->user_agent === sha1(Request::$user_agent)) {
                    // Save the token to create a new unique token
                    $token->save();

                    // Set the new token
                    Cookie::set('authautologin', $token->token, $token->expires - time());

                    // Complete the login with the found data
                    $this->complete_login($token->user);

                    // Automatic login was successful
                    return TRUE;
                }

                // Token is invalid
                $token->delete();
            }
        }

        return FALSE;
    }

    /**
     * Log a user out and remove any auto-login Cookies.
     *
     * @param   boolean  completely destroy the session
     * @param	boolean  remove all tokens for user
     * @return  boolean
     */
    public function logout($destroy = FALSE, $logout_all = FALSE) {
        $tokenString = Cookie::get('authautologin');
        $token = Model_Auth_User_Token::fetchTokenFromString($tokenString);

        // Delete the autologin Cookie to prevent re-login
        Cookie::delete('authautologin');

        // Clear the autologin token from the database
        if ($token && $logout_all) {
            $query = Doctrine_Query::create()
                        ->delete('Model_Auth_User_Token')
                        ->where('user_id = ?',$token->user_id)
                        ->execute();
        } elseif ($token) {
            $token->delete();
        }

        return parent::logout($destroy);
    }

    /**
     * Complete the login for a user by incrementing the logins and setting
     * session data: user_id, username, roles
     *
     * @param   object   user model object
     * @return  void
     */
    protected function complete_login($user) {
        // Update the number of logins
        $user->logins += 1;

        // Set the last login date
        $user->last_login = time();

        // Save the user
        $user->save();

        return parent::complete_login($user);
    }

    /**
     * Convert a unique identifier string to a user object
     *
     * @param mixed $user
     * @return Model_User
     */
    protected function _get_object($user) {
        static $current;
        //make sure the user is loaded only once.
        if (!is_object($current) AND is_string($user)) {
            // Load the user
            $current = Doctrine_Query::create()
                        ->from('Model_Auth_User u')
                        ->leftJoin('u.Roles')
                        ->where('email = ?',$user)
                        ->fetchOne();
        }
        //@todo: why did the $user->loaded come out?
        if ($user instanceof Model_Auth_User) {
            $current = $user;
        }

        return $current;
    }

	/**
	 * Override the legacy method - this needs to be handled by the hash
	 * provider class to separate the check and hash methods and allow
	 * upgrading of hashing from version to version.
	 */
	public function hash_password($password, $salt = FALSE)
	{
		throw new BadMethodCallException("Legacy hash_password method called in Auth_Doctrine driver");
	}

	/**
	 * Override the legacy method - this needs to be handled by the hash
	 * provider class to separate the check and hash methods and allow
	 * upgrading of hashing from version to version.
	 */
	public function password($username)
	{
		throw new BadMethodCallException("Unexpected call to password method");
	}

	/**
	 * Override the legacy method - this needs to be handled by the hash
	 * provider class to separate the check and hash methods and allow
	 * upgrading of hashing from version to version.
	 */
	public function check_password($password)
	{
		throw new BadMethodCallException("Unexpected call to check_password method");
	}

}