<?php

/**
 * The CreateUser task creates a new user in the auth database and sends them
 * an email activation token to allow them to activate their account.
 *
 * Available config options are:
 *
 * --email (required)
 *
 *   The user's email address
 *
 * --password (optional)
 *
 *   If no password is specified, will allocate a random password
 *
 * --name (optional)
 *
 *   The user's name - will use email address if not provided
 *
 *  --roles (optional)
 *
 *   A comma separated list of roles to assign
 *
 * @package    Doctrine_Auth
 * @category   Administration
 * @author     Andrew Coulton
 * @copyright  (c) 2012 Andrew Coulton
 * @license    http://kohanaframework.org/license
 */
class Minion_Task_Auth_CreateUser extends AndrewC_Minion_Task_Auth_CreateUser
{
}