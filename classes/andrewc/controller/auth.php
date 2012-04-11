<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Controller for user authentication handling
 *
 * @package    KoDoctrine
 * @category   Administration
 * @author     Andrew Coulton
 * @copyright  (c) 2010 Andrew Coulton
 * @license    http://kohanaphp.com/license
 */
class AndrewC_Controller_Auth extends Controller_Base_Public {

	/**
	 * Redirects the current request to the homepage for the logged in (or a
	 * specific) user.
	 *
	 * @param Model_Auth_User $user
	 */
	protected function _redirect_home($user = NULL)
	{
		if ($user === NULL)
		{
			$user = Auth::instance()
					->get_user();
		}

		$this->request->redirect($user->getAccountHomepage());
	}

	/**
	 * Attempts to sign the user in and redirects to their homepage, or reports
	 * failure.
	 *
	 * @return boolean	FALSE if signin failed, NULL if it passed
	 */
	protected function _do_signin(&$values, &$errors)
	{
        // If user already signed-in then send to their homepage
        if (Auth::instance()->logged_in() != 0)
			return $this->_redirect_home();

		// Try to sign in
		if ($this->request->method() == Request::POST)
		{
            if (Auth::instance()
                    ->login(Arr::get($values, 'email'),
                            Arr::get($values, 'password'),
                            Arr::get($values, 'remember')))
			{
				return $this->_redirect_home(NULL);
			}
			else
			{
				return FALSE;
			}
		}

		// If not submitted
		return NULL;
	}

    /**
	 * Signs in to the application and redirects to the user's homepage
	 */
	public function action_signin() {

		$this->template->body = View::factory('auth/signin')
                                ->bind('error_message',$error_message);

		if ($this->_do_signin($this->request->post()) === FALSE)
		{
			// Login failed
			$error_message = Kohana::message('auth','signin.failed');
		}

    }

	/**
	 * Signs out of the application and redirects to the default page
	 */
    public function action_signout() {
        #Sign out the user
        Auth::instance()->logout();

        #redirect to the user account and then the signin page if logout worked as expected
        $this->request->redirect(Route::get('default')->uri());
    }

	/**
	 * Attempts to register a user and sends an activation token
	 * @param array $values  POST data
	 * @param array $errors  A set of field errors
	 * @return boolean|null  TRUE if registered OK, FALSE if not registered, NULL if the form has not been submitted
	 */
	protected function _do_register(&$values = array(), &$errors = array())
	{

        if ($this->request->method() == Request::POST)
        {
			// They must provide some kind of password
			if ($values['password'] == NULL)
			{
				$errors['password'] = 'You must enter a password to register';
				return FALSE;
			}

            $user = new Model_Auth_User();
            $user->fromArray(Arr::extract($values, array('email','password','full_name')));

            if ( ! $user->isValid())
            {
                foreach ($user->errorStack()->toArray() as $field=>$errors)
                {
                    $errors[$field] = implode('\r\n',$errors);
                }
            }

            if ( ! Valid::equals($values['password'], $values['password_confirm']))
            {
                $errors['password_confirm'] = Kohana::message('auth','account.no_password_match');
            }

            if ( ! $errors)
            {
                $user->save();
                Model_Auth_User::send_token('activate', $user);
                return TRUE;
            }
			else
			{
				return FALSE;
			}
        }

		return NULL;
	}

	/**
	 * Registers for an account and sends an activation token
	 */
    public function action_register()
    {
		$values = Arr::extract($this->request->post(), array('email','password','password_confirm','full_name'));

		if ($this->_do_register($values, $errors))
			return $this->request->redirect(Route::get('auth')->uri(array('action'=>'activate')));

        $this->template->body = View::factory('auth/register')
                                ->set('errors',$errors)
                                ->set('values',$values);
    }

	/**
	 * Activates a user's account and redirects them to their homepage
	 *
	 * @return boolean|null
	 */
	protected function _do_activate()
	{
		$token = $this->request->param('token');

		if ($token)
		{
			if ($user = Model_Auth_User::activate($token))
			{
				return $this->_redirect_home($user);
			}

			return FALSE;
		}

		return NULL;
	}

	/**
	 * Activates a user's account and redirects them to their homepage
	 */
    public function action_activate()
    {
		$this->_do_activate();
        $this->template->body = View::factory('auth/activate');
    }


	/**
	 * Resets a user's password and sends them a token that allows them to
	 * login and change their password.
	 */
    public function action_reset()
    {
        $token = $this->request->param('token');
        $this->template->body = View::factory('auth/reset')
                                ->bind('error_message',$error_message);

        // If a token was provided
        if ($token)
        {
            if (Model_Auth_User::activate($token))
            {
                // Go to their account edit page
                $this->request->redirect(Route::get('auth')->uri(array('action'=>'account')));
            }
            else
            {
                $error_message = Kohana::message('auth','activation.token_not_valid');
            }
        }

        // If the form was submitted, resend the token
        if ($_POST)
        {
            if (Model_Auth_User::send_token('reset',Arr::get($_POST,'email')))
            {
                $this->flashMessage('formdone', Kohana::message('auth','activation.token_sent'));
            }
            else
            {
                $error_message = Kohana::message('auth','activation.account_not_found');
            }
        }
    }
	
	
	/**
	 * Allows the user to edit their account details
	 * 
	 * @param array $values
	 * @param array $errors
	 * @return boolean|null 
	 */
	protected function _do_account(&$values, &$errors)
	{
		// Verify that the user is logged in
        if ( ! Auth::instance()->logged_in()) 
		{
			Kohana::$log->add(Log::ERROR,
				"Attempted to access account edit without logging in");
            return $this->request->redirect(Route::get('auth')->uri(array('action'=>'signout')));
        }			
		
		// Load the current user
		$user = Auth::instance()->get_user();
		/* @var $user Model_Auth_User */
		
		if ($this->request->method() == Request::POST)
        {
			// Set the full name if it's present
			if (isset($values['full_name']))
			{
				$user->full_name = $values['full_name'];
			}
			
			// Set password if they have set a value
            if ($values['password'])
			{
				$user->password = $password;
			}

			// Validate the user object
            if ( ! $user->isValid())
            {
                foreach ($user->errorStack()->toArray() as $field=>$errors)
                {
                    $errors[$field] = implode('\r\n',$errors);
                }
            }

			// Validate the password confirmation
            if ( ! Valid::equals($values['password'], $values['password_confirm']))
            {
                $errors['password_confirm'] = Kohana::message('auth','account.no_password_match');
            }

            if ( ! $errors)
            {
                $user->save();
				return TRUE;
            }
        }
		
		// Populate fields that aren't in POST data
		$values['email'] = $user->email;
		$values['full_name'] = $user->full_name;
		
		if ($errors)
		{
			return FALSE;
		}
		
		return NULL;		
	}

	/**
	 * Allows the user to edit their account details
	 */
    public function action_account()
    {			
		$values = Arr::extract($this->request->post(), array('email','full_name','password','password_confirm'));
		
		if ($this->_do_account($values, $errors))
		{
			    $this->flashMessage('formdone', Kohana::message('auth','account.updated'));
                return $this->_redirect_home();
		}
		
        $this->template->body = View::factory('auth/account')
                                ->bind('errors',$errors)
                                ->bind('values',$values);
    }

	/**
	 * CLI method to create a user on installation
	 * @throws BadMethodCallException
	 */
    public function action_create_user()
    {
        if ( ! Kohana::$is_cli)
        {
            throw new BadMethodCallException("create_user is only permitted from CLI");
        }
        $this->auto_render = false;

        $cli_values = CLI::options('email','name','roles', 'password');
        $roles = explode(',', Arr::get($cli_values, 'roles','admin,login'));

        $user = Model_Auth_User::create_user(Arr::get($cli_values, 'email', null),
                Arr::get($cli_values, 'name', null), $roles);

        if ($password = Arr::get($cli_values, 'password'))
        {
            $user->password = $password;
            $user->save();
        }

    }
}