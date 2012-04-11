<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

//
// Require 3rd-party libraries here:
//
   require_once 'PHPUnit/Autoload.php';
   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class Auth_FeatureContext extends BehatContext
{

    /**
     * @var string The last token found by the aUniqueTokenShouldBeCreated test
     */
    public $last_token = NULL;

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param   array   $parameters     context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
    }

    /**
     * @Given /^(only|) the following users exist:$/
     */
    public function onlyTheFollowingUsersExist($only, TableNode $table)
    {
        // Delete existing users if required
        if ($only)
        {
            Doctrine_Query::create()
                ->delete('Model_Auth_User')
                ->execute();
        }

        // Create users
        $users = $table->getHash();
        $collection = new Doctrine_Collection('Model_Auth_User');
        foreach ($users as $user_data)
        {
            $user_model = $collection->get(NULL);

            // Remove roles data for separate processing
            if (isset($user_data['roles']))
            {
                $roles = $user_data['roles'];
                unset($user_data['roles']);
            }

            $user_model->fromArray($user_data);

            // Process roles
            $roles = explode(',',$roles);
            foreach ($roles as $role)
            {
                $role_model = Model_Auth_Role::factory($role);
                $user_model->Roles[] = $role_model;
            }

        }

        $collection->save();
    }

    /**
     * @Given /^a unique (reset|activation) token should be created for user "([^"]*)"$/
     */
    public function aUniqueTokenShouldBeCreatedForUser($type, $user_email)
    {
        if ($type === 'activation')
        {
            $type = 'activate';
        }

        $user = Model_Auth_User::factory_by_email($user_email);

        foreach ($user->Tokens as $token)
        {
            if ($token->type === $type)
            {
                // Store for verification or use in other contexts
                $this->last_token = $token->token;
                return TRUE;
            }
        }

        // No match
        throw new Exception("No token of type '$type' was found for '$user_email'");

    }

    /**
     * @Given /^the following tokens exist:$/
     */
    public function theFollowingTokensExist(TableNode $table)
    {
        $tokens = new Doctrine_Collection('Model_Auth_User_Token');

        foreach ($table->getHash() as $token_data)
        {
            $token = $tokens->get(NULL);
            $token->fromArray($token_data);
        }

        $tokens->save();
    }

    /**
     * @Given /^I should be logged in as "([^"]*)"$/
     */
    public function iShouldBeLoggedInAs($user)
    {
        throw new PendingException();
    }

    /**
     * @Given /^the user "([^"]*)" should exist with password "([^"]*)" and roles "([^"]*)"$/
     */
    public function theUserShouldExistWithPasswordAndRoles($user_email, $password, $roles)
    {
        $user = Model_Auth_User::factory_by_email($user_email);
        assertInstanceOf('Model_Auth_User', $user);

        assertEquals($password, Auth::instance()->hash($password), 'Assert hashed passwords match');

        $this->theUserShouldHaveTheRoles($user, $roles);
    }

    /**
     * @Given /^the token "([^"]*)" should no longer be valid$/
     */
    public function theTokenShouldNoLongerBeValid($token)
    {
        $token = Model_Auth_User_Token::fetchTokenFromString($token);

        assertFalse($token);
    }

    /**
     * @Given /^the user "([^"]*)" should have the "([^"]*)" roles?$/
     */
    public function theUserShouldHaveTheRole($user, $roles)
    {
        if ( ! $user instanceof Model_Auth_User)
        {
            $user = Model_Auth_User::factory_by_email($user);
        }

        $roles = explode(',',$roles);

        foreach ($roles as $role)
        {
            assertTrue($user->has_role($role), "Verifying user has role '$role'");
        }
    }

}
