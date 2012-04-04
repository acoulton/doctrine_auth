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
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param   array   $parameters     context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
    }

    /**
     * @Given /^only the following users exist:$/
     */
    public function onlyTheFollowingUsersExist(TableNode $table)
    {
        throw new PendingException();
    }

    /**
     * @Given /^a unique reset token should be created for user "([^"]*)"$/
     */
    public function aUniqueResetTokenShouldBeCreatedForUser($argument1)
    {
        throw new PendingException();
    }

    /**
     * @Given /^the following tokens exist:$/
     */
    public function theFollowingTokensExist(TableNode $table)
    {
        throw new PendingException();
    }

    /**
     * @Given /^I should be logged in as "([^"]*)"$/
     */
    public function iShouldBeLoggedInAs($argument1)
    {
        throw new PendingException();
    }

    /**
     * @Given /^the user "([^"]*)" should exist with password "([^"]*)" and roles "([^"]*)"$/
     */
    public function theUserShouldExistWithPasswordAndRoles($argument1, $argument2, $argument3)
    {
        throw new PendingException();
    }

    /**
     * @Given /^the token "([^"]*)" should be marked as activated$/
     */
    public function theTokenShouldBeMarkedAsActivated($argument1)
    {
        throw new PendingException();
    }

    /**
     * @Given /^a unique activation token should be created for user "([^"]*)"$/
     */
    public function aUniqueActivationTokenShouldBeCreatedForUser($argument1)
    {
        throw new PendingException();
    }

    /**
     * @Given /^the user "([^"]*)" should have the "([^"]*)" role$/
     */
    public function theUserShouldHaveTheRole($argument1, $argument2)
    {
        throw new PendingException();
    }

}
