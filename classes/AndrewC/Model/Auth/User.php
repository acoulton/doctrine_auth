<?php
use Shadowhand\Email;
/**
 * Model_Base_Auth_User
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @property string $email
 * @property string $password
 * @property string $full_name
 * @property string $position
 * @property integer $logins
 * @property integer $last_login
 * @property Doctrine_Collection $Roles
 * @property Doctrine_Collection $Auth_User_Token
 *
 * @package    teamdetails
 * @subpackage Models
 * @author     Andrew Coulton <andrew@edbookfest.co.uk>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class AndrewC_Model_Auth_User extends KoDoctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('auth__user');
        $this->hasColumn('email', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('password', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('full_name', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('position', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('logins', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('last_login', 'integer', null, array(
             'type' => 'integer',
             ));


        $this->index('unique_email', array(
             'fields' =>
             array(
              'email' =>
              array(
              ),
             ),
             'type' => 'unique',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('Model_Auth_Role as Roles', array(
             'refClass' => 'Model_Auth_Roleuser',
             'local' => 'user_id',
             'foreign' => 'role_id'));

        $this->hasMany('Model_Auth_User_Token as Tokens', array(
             'local' => 'id',
             'foreign' => 'user_id'));
        $this->hasMutator('password', 'set_password');
    }

	/**
	 * Custom field validation
	 *
	 * @param array $data
	 * @return Validate
	 */
	public function get_validation($data)
	{
		$validation = parent::get_validation($data);

		$validation->rule('email', 'email');
		$validation->rule('email', 'not_empty');
		$validation->rule('full_name', 'not_empty');

		return $validation;
	}


        	/**
	 * Validate callback wrapper for checking password match
	 * @param Validate $array
	 * @param string   $field
	 * @return void
	 */
	public static function _check_password_matches(Validate $array, $field)
	{
		$auth = Auth::instance();

		if ($array['password'] !== $array[$field])
		{
			// Re-use the error messge from the 'matches' rule in Validate
			$array->error($field, 'matches', array('param1' => 'password'));
		}
	}

	/**
	 * Check if user has a particular role
	 * @param mixed $role 	Role to test for, can be Model_Role object, string role name of integer role id
	 * @return bool			Whether or not the user has the requested role
	 */
	public function has_role($role)
	{
		// Check what sort of argument we have been passed
		if ($role instanceof Model_Auth_Role)
		{
			$key = 'id';
			$val = $role->id;
		}
		elseif (is_string($role))
		{
			$key = 'name';
			$val = $role;
		}
		else
		{
			$key = 'id';
			$val = (int) $role;
		}

                foreach ($this->Roles as $user_role) {
                    /* @var $user_role Model_Role */
			if ($user_role->{$key} === $val)
			{
				return TRUE;
			}
		}

		return FALSE;
	}


	/**
	 * Setter for the password field that hashes the value before storing in
	 * the model.
	 *
	 * @param string $value
	 * @param boolean $load
	 * @return Model_Auth_User
	 */
	public function set_password($value, $load = true)
	{
		$value = Auth_Hash::factory()->hash($value);
		return $this->_set('password', $value, $load);
	}

	/**
	 * Verifies that the password matches the hashed value and - if the hash
	 * is out of date - sets the hashed password to the latest mechanism.
	 *
	 * @param string $password
	 * @return boolean
	 */
	public function password_matches($password)
	{
		$auth_hash = Auth_Hash::factory();
		if ($auth_hash->check($password, $this->password))
		{
			if ( ! $auth_hash->is_best())
			{
				$this->password = $password;
			}
			return TRUE;
		}
		return FALSE;
	}


        public function validate()
        {
            // Validate that the user email is unique
            $query = Doctrine_Query::create()
                        ->from('Model_Auth_User')
                        ->where('email = ?',$this->email);
            if ($this->id)
            {
                $query->andWhere('id <> ?',$this->id);
            }

            if ($query->execute()->count())
            {
                $this->getErrorStack()
                        ->add('email', 'This email account is already registered on this site');
            }

            parent::validate();
        }

        public static function activate($token)
        {
            $token = Model_Auth_User_Token::fetchTokenFromString($token);
            if ( ! $token
                 OR (($token->type != 'activate') AND ($token->type !='reset')))
            {
                return false;
            }

            $user = $token->User;

            $token->delete();

            $login = Model_Auth_Role::factory('login','User can login');

            // Add a login role if they don't have one
            if ( ! $user->has_role($login->id))
            {
                $user->Roles[] = $login;
                $user->save();
            }

            // Force them to be logged in, and don't mark as forced
            Auth::instance()->force_login($user,false);
            return $user;
        }

        public static function send_token($type, $user, $mail_template = null)
        {
            if ( ! ($user instanceof Model_Auth_User))
            {
                $user = Doctrine_Query::create()
                            ->from('Model_Auth_User')
                            ->where('email = ?',$user)
                            ->fetchOne();
            }

            if ( ! $user)
            {
                return false;
            }

            $token = new Model_Auth_User_Token();
            $config = Kohana::$config->load('auth.activation');

            // Set token data
            $token->User = $user;
            $token->expires = time() + $config['token_life'];
            $token->type = $type;
            $token->save();

            $url = Route::url('auth',
                    array('action'=>$type,
                          'token'=>$token->token),
                    'https');

            /*
             * Send activation email
             */
            $mailer = Email::mailer();
            /* @var $mailer Swift_Mailer */
            if ($mail_template == null)
            {
                $mail_template = $type;
            }
            $richMessage = View::factory('templates/emailBase')
                            ->set('bodyText', View::factory("auth/emails/$mail_template")
                                              ->set('user',$user)
                                              ->set('token_uri', $url)
                                              ->render());

            $textMessage = preg_replace('/[ \t]+/', ' ', strip_tags($richMessage));

            $message = Swift_Message::newInstance(
                                $config['email_subject'],
                                $textMessage);
            $message->addPart($richMessage,'text/html');

            $message->setFrom($config['email_sender_email'],
                              $config['email_sender_name']);
            $message->setTo($user->email);

            if ( ! $mailer->send($message)) {
                Kohana::$log->add(Log::ERROR, "Error sending token for user $user->email");
            }

            return $user;
        }

        /**
         * Creates a user account for someone, adding any relevant roles and
         * sending them an activation message.
         * @param string $email
         * @param string $full_name
         * @return Model_Auth_User
         */
        public static function create_user($email, $full_name, $roles = array(), $mail_template = 'new_account')
        {
            $user = new Model_Auth_User();
            $user->email = $email;
            $user->full_name = $full_name;

            // Set a random password, they're going to be sent a reset url
            $user->password = UUID::v4();
            foreach ($roles as $role)
            {
                $role = Model_Auth_Role::factory($role);
                $user->Roles[] = $role;
            }
            $user->save();

            // Now send the activation
            Model_Auth_User::send_token('reset', $user, $mail_template);

            return $user;
        }

    /**
     * Returns the (local) URL a user should be sent to once they login. By default
     * comes from the auth.userhome config setting
     * @return string
     */
    public function getAccountHomepage()
    {
        return Kohana::$config->load('auth.user_homepage');
    }

}
