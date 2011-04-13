<?php
    if ( ! isset($values))
    {
        $values = array();
    }
    if ( ! isset($errors))
    {
        $errors = array();
    }
?>
<?= Form::open(Route::get('auth')->uri(array('action' => 'account'))) ?>
<fieldset>
    <legend>Edit your account details</legend>
    <?php if ($errors):?>
    <div class="formerror">
        <p>There were problems with your submission. Please correct the errors below and try again.</p>
    </div>
    <?php endif;?>

    <label class="modelblock <?=($message = Arr::get($errors,'full_name')) ? 'error':''?>">
        <span class="caption">Name</span>
        <?=Form::input('full_name', Arr::get($values,'full_name'))?>
        <?php if ($message):?>
        <span class='errortext'><?=HTML::chars($message)?></span>
        <?php endif;?>
    </label>
    <p>If you do not wish to change your password, leave these fields blank.</p>
    <label class="modelblock <?=($message = Arr::get($errors,'password')) ? 'error':''?>">
        <span class="caption">New Password</span>
        <?=Form::password('password', Arr::get($values,'password'))?>
        <?php if ($message):?>
        <span class='errortext'><?=HTML::chars($message)?></span>
        <?php endif;?>
    </label>
    <label class="modelblock <?=($message = Arr::get($errors,'password_confirm')) ? 'error':''?>">
        <span class="caption">Retype New Password</span>
        <?=Form::password('password_confirm', Arr::get($values,'password_confirm'))?>
        <?php if ($message):?>
        <span class='errortext'><?=HTML::chars($message)?></span>
        <?php endif;?>
    </label>

    <?=Form::submit('change', 'Change my details >')?>
</fieldset>
<?=Form::close()?>