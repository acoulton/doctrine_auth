<?php defined('SYSPATH') OR die('No direct access allowed.');

return array
(
	'driver' => 'Doctrine',
	'lifetime' => 1209600,
	'session_key' => 'auth_user',
        'register_role' => 'registerUser',
        'activation' => array(
            'token_life' => Date::DAY,
            'email_subject' => 'Email verification',
            'email_sender_email' => null,
            'email_sender_name' => null,
        ),
    'user_homepage' => '/',

	// Configuration of various password hashing drivers
	'hash' => array(
		'drivers' => array (
			'KO30' => array (
				'driver' => 'Auth_Hash_KO30',
				'hash_method' => 'sha1',
				'salt_pattern' => '1, 3, 5, 9, 14, 15, 20, 21, 28, 30',
			),
			'KO32' => array(
				'driver' => 'Auth_Hash_KO32',
				'hash_method'  => 'sha256',
				// This must be set at the application level
				'hash_key'     => NULL,
			)
		),
		// The latest available - passwords will be rehashed with this on login
		'latest' => 'KO32',
		// The default - if no prefix has been applied to the hash
		'default' => 'KO30',
	)

);
