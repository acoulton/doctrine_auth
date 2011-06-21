<?php
defined('SYSPATH') or die('No direct script access.');

Route::set('auth','<action>(/<token>)',
        array('action'=>'signin|signout|register|reset|activate|account|create_user'))
        ->defaults(array(
            'controller' => 'auth',
        ));
