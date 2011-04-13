<?php
defined('SYSPATH') or die('No direct script access.');
?>
<div class="formdone">
<p>Thank you for registering for an account. You will soon receive an email
    containing instructions on how to complete the registration process.
    If you do not receive the email within the next few minutes you can
    have it re-sent by entering your details below.</p></div>
<?=View::factory('auth/reset')
    ->set('title','Didn\'t receive the email?')
    ->set('message','If you haven\'t received the activation email within a few
            minutes, enter your email address below to have it resent.');?>