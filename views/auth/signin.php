<?= Form::open(Route::get('auth')->uri(array('action' => 'signin'))) ?>
<fieldset>
    <legend>Login</legend>
    <?php if (isset($error_message)): ?>
        <div class="formerror"><p><?= $error_message ?></p></div>
    <?php endif; ?>
    <label class="modelblock">
        <span class="caption">Email</span>
        <?= Form::input('email') ?>
    </label>
    <label class="modelblock">
        <span class="caption">Password</span>
        <?= Form::password('password') ?>
    </label>
    <label class="modelblock" style="width:50%; float: left;">
        <span class="caption"></span>
        <?= Form::checkbox('remember', true, true, array('class' => 'check')) ?>
        <span class="checkcaption">Remember Me</span>
    </label>
    <?= Form::submit('login', 'Log In >') ?>
</fieldset>
<?= Form::close() ?>
<?= View::factory('auth/reset');?>