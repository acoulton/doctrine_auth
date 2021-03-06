<?php defined('SYSPATH') OR die('No direct access allowed.');

return array
(
	'driver' => 'Doctrine',
	'hash_method' => 'sha1',
	'salt_pattern' => '1, 3, 5, 9, 14, 15, 20, 21, 28, 30',
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

);
