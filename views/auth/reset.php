<?php
defined('SYSPATH') or die('No direct script access.');
if ( ! isset($title)):
    $title = 'Problems logging in?';
endif;
if ( ! isset($message)):
    $message = 'If you have forgotten your password, not received an activation email,
       or are having trouble logging in, enter your email address to reset your account.';
endif;
?>
<?= Form::open(Route::get('auth')->uri(array('action' => 'reset'))) ?>
<fieldset>
    <legend><?=$title?></legend>
    <p><?=$message?></p>
    <?php if (isset($error_message)):?>
        <div class="formerror"><p><?=HTML::chars($error_message)?></p></div>
    <?php endif;?>
    <label class="modelblock">
        <span class="caption">Email</span>
        <?=Form::input('email')?>
    </label>
    <?=Form::submit('reset', 'Reset account >')?>
</fieldset>
<?=Form::close()?>