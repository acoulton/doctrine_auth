<?php

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
 * @property Model_Person $Person
 * @property Doctrine_Collection $Application
 * @property Doctrine_Collection $Auth_UserToken
 *
 * @package    StaffAdmin
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
             'validation' =>
             array(
              'rules' =>
              array(
              'email' =>
              array(
               0 => false,
              ),
              'not_empty' => NULL,
              ),
             ),
             'length' => '255',
             ));
        $this->hasColumn('password', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('full_name', 'string', 255, array(
             'type' => 'string',
             'validation' =>
             array(
              'rules' =>
              array(
              'not_empty' => NULL,
              ),
             ),
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

        $this->hasMany('Model_Application as Application', array(
             'local' => 'id',
             'foreign' => 'tag_user_id'));

        $this->hasMany('Model_Auth_UserToken as Auth_UserToken', array(
             'local' => 'id',
             'foreign' => 'user_id'));
        $this->hasMutator('password', 'set_password');
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
		if ($role instanceof Model_Role)
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


        public function set_password($value, $load = true)
        {
            $value = Auth::instance()->hash_password($value);
            return $this->_set('password', $value, $load);
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
            $token = Model_Auth_UserToken::fetchTokenFromString($token);
            if ( ! $token
                 OR (($token->type != 'activate') AND ($token->type !='reset')))
            {
                return false;
            }

            $user = $token->User;

            $token->delete();

            $login = Model_Auth_Role::getLoginRole();

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

        public static function send_token($type, $user)
        {
            if ( ! ($user instanceof Model_User))
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

            $token = new Model_User_Token();

            // Set token data
            $token->User = $user;
            $token->expires = time() + Kohana::config('auth.activation_token_life');
            $token->type = $type;
            $token->save();

            $url = Route::url('auth',
                    array('action'=>$type,
                          'token'=>$token->token),
                    'https');

            /*
             * Send activation email
             */
            $mailer = Email::connect();
            /* @var $mailer Swift_Mailer */

            $richMessage = View::factory('templates/emailBase')
                            ->set('bodyText', View::factory("auth/emails/$type")
                                              ->set('user',$user)
                                              ->set('token_uri', $url)
                                              ->render());

            $textMessage = preg_replace('/[ \t]+/', ' ', strip_tags($richMessage));

            $message = Swift_Message::newInstance(
                                "Edinburgh International Book Festival - email verification",
                                $textMessage);
            $message->addPart($richMessage,'text/html');

            $message->setFrom('jobs@edbookfest.co.uk','Edinburgh International Book Festival');
            $message->setTo($user->email);

            if ( ! $mailer->send($message)) {
                Kohana::$log->add(Kohana::ERROR, "Error sending token for user $user->email");
            }

            return $user;
        }
}