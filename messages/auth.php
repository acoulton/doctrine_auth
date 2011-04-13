<?php
defined('SYSPATH') or die('No direct script access.');
return array(
    'signin' => array(
        'failed' => 'We could not log you in with the details you provided.
            Please try again or reset your account below.'),
    'account' => array(
        'no_password_match' => 'The passwords entered did not match',
        'updated' => 'We have updated your details'),
    'activation' => array(
        'token_not_valid' => 'The activation link used was not valid,
            or may have expired. Enter your email address below to
            receive a new link.',
        'token_sent' => 'We have sent a new activation link
                    to your email account. Please check for an email from
                    jobs@edbookfest.co.uk.',
        'account_not_found' => 'The email address you entered did not match
                    a user account on our system. Please double check and try again,
                    or sign up for a new account'),
    );