<?php
defined('SYSPATH') or die('No direct script access.');
/* @var $user Model_Auth_User */
/* @var $token_uri string */?>
<h2>Your new account</h2>
<p>Dear <?=HTML::chars($user->full_name)?>,</p>
<p>A user account has been created for you on our site. To continue, you now
   need to complete the process by clicking on <?=HTML::anchor($token_uri,
                    "this link to " . HTML::chars($token_uri));?> .</p>