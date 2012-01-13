<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Controller for user authentication handling
 *
 * @package    KoDoctrine
 * @category   Administration
 * @author     Andrew Coulton
 * @copyright  (c) 2011 Ingenerator
 * @license    http://kohanaphp.com/license
 */
class AndrewC_Controller_Auth extends Controller_Base_Public {

	protected function _redirect_home($user = NULL)
	{
		if ( ! $user)
		{
			$user = Auth::instance()->get_user();
		}

		$this->request->redirect($user->getAccountHomepage());
	}

    protected function _action_signin() {
        #If user already signed-in
        if (Auth::instance()->logged_in() != 0) {
            #redirect to the user account
			$this->_redirect_home();
        }

        #If there is a post and $_POST is not empty
        if ($_POST) {
            /* @var $user Model_User */
            $user = Auth::instance()
                    ->login(Arr::get($_POST, 'email'),
                            Arr::get($_POST, 'password'),
                            Arr::get($_POST, 'remember'));
            if ($user) {
                //redirect
				$this->_redirect_home();
            }
        }

		return FALSE;

    }

	public function action_signin() {
		$this->template->body = View::factory('auth/signin')
                      ->bind('error_message',$error_message);

		if ( ! $this->_action_signin())
		{
			$error_message = Kohana::message('auth','signin.failed');
		}
	}

	protected function _action_signout()
	{
        #Sign out the user
        Auth::instance()->logout();

        #redirect to the user account and then the signin page if logout worked as expected
        $this->request->redirect(Route::get('default')->uri());
	}

    public function action_signout() {
		$this->_action_signout();
    }

	protected function _action_register( & $values, & $errors, $confirm = TRUE)
	{
		$values = array();
        $errors = array();

        if ($_POST)
        {
            $values = Arr::extract($_POST, array('email','password','password_confirm','full_name'));

            $user = new Model_User();
            $user->fromArray(Arr::extract($_POST, array('email','password','full_name')));

            if ( ! $user->isValid())
            {
                foreach ($user->errorStack()->toArray() as $field=>$errors)
                {
                    $errors[$field] = implode('\r\n',$errors);
                }
            }

            if ($confirm AND ! Validate::equals($values['password'], $values['password_confirm']))
            {
                $errors['password_confirm'] = Kohana::message('auth','account.no_password_match');
            }

            if ( ! $errors)
            {
                $user->save();
                Model_Auth_User::send_token('activate', $user);
                return TRUE;
            }

			return FALSE;
        }

	}

    public function action_register()
    {
        $this->template->body = View::factory('auth/register')
                                ->bind('errors',$errors)
                                ->bind('values',$values);

		if ($this->_action_register($values, $errors))
		{
			$this->request->redirect(Route::get('auth')->uri(array('action'=>'activate')));
		}
    }

	public function _action_activate()
	{
        $token = $this->request->param('token');

        if ($token)
        {
            if ($user = Model_User::activate($token))
            {
                /* @var $user Model_User */
                $this->_redirect_home($user);
            }
        }

	}

    public function action_activate()
    {
		$this->_action_activate();
		$this->template->body = View::factory('auth/activate');
    }

	public function _action_reset()
	{
        $token = $this->request->param('token');
        // If a token was provided
        if ($token)
        {
            if (Model_User::activate($token))
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
            if (Model_User::send_token('reset',Arr::get($_POST,'email')))
            {
                return TRUE;
            }
            else
            {
				return FALSE;
            }
        }
	}

    public function action_reset()
    {
        $this->template->body = View::factory('auth/reset')
                                ->bind('error_message',$error_message);
		if ($this->_action_reset($error_message))
		{
			$this->flashMessage('formdone', Kohana::message('auth','activation.token_sent'));
		}
		else
		{
			$error_message = Kohana::message('auth','activation.account_not_found');
		}
    }

    public function action_account()
    {
        if ( ! Auth::instance()->logged_in()) {
                Kohana::$log->add(Kohana::ERROR,
                            "Attempted to access account edit without logging in");
                $this->request->redirect(Route::get('auth')->uri(array('action'=>'signout')));
            }

        $user = Auth::instance()->get_user();
        /* @var $user Model_Auth_User */
        $values = array('email'=>$user->email,
                        'full_name'=>$user->full_name);
        $errors = array();
        $this->template->body = View::factory('auth/account')
                                ->bind('errors',$errors)
                                ->bind('values',$values);

        if ($_POST)
        {
            //$user->email = Arr::get($_POST,'email');
            $user->full_name = Arr::get($_POST,'full_name');
            if ($password = Arr::get($_POST,'password'))
            {
                $user->password = $password;
            }

            if ( ! $user->isValid())
            {
                foreach ($user->errorStack()->toArray() as $field=>$errors)
                {
                    $errors[$field] = implode('\r\n',$errors);
                }
            }

            if ( ! Validate::equals(Arr::get($_POST,'password'), Arr::get($_POST,'password_confirm')))
            {
                $errors['password_confirm'] = Kohana::message('auth','account.no_password_match');
            }

            if ( ! $errors)
            {
                $user->save();
                $this->flashMessage('formdone', Kohana::message('auth','account.updated'));
                $this->request->redirect($user->getAccountHomepage());
            }
        }
    }

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