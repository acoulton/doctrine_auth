<?php
defined('SYSPATH') or die('No direct script access.');
/* @var $user Model_Auth_User */
/* @var $token_uri string */?>
<h2>Thank you for registering</h2>
<p>Dear <?=HTML::chars($user->full_name)?>,</p>
<p>Thank you for registering for an account on the Edinburgh International Book
    Festival HR site. To continue, you now need to activate your account by
    clicking on <?=HTML::anchor($token_uri,
                    "this link to " . HTML::chars($token_uri) . ".");?>.</p>