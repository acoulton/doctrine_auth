<?php

/**
 * Model_Base_Auth_Roleuser
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @property integer $user_id
 * @property integer $role_id
 *
 * @package    StaffAdmin
 * @subpackage Models
 * @author     Andrew Coulton <andrew@edbookfest.co.uk>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class AndrewC_Model_Auth_Roleuser extends KoDoctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('auth__roleuser');
        $this->hasColumn('user_id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             ));
        $this->hasColumn('role_id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             ));
    }

    public function setUp()
    {
        parent::setUp();

    }
}