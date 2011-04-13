<?php
defined('SYSPATH') or die('No direct script access.');
/* @var $user Model_Auth_User */
/* @var $token_uri string */?>
<h2>Reset your account</h2>
<p>Dear <?=HTML::chars($user->full_name)?>,</p>
<p>You have received this email because you opted to reset your account on the
   Edinburgh International Book Festival HR site. To continue, you now need to
   complete the process by clicking on <?=HTML::anchor($token_uri,
                    "this link to " . HTML::chars($token_uri) . ".");?>.</p>