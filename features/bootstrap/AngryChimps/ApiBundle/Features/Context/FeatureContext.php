<?php


namespace AngryChimps\ApiBundle\Features\Context;

use AngryChimps\ApiBundle\Services\GuzzleService;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Norm\Address;
use Norm\Availability;
use Norm\Booking;
use Norm\Calendar;
use Norm\Company;
use Norm\Location;
use Norm\Member;
use Guzzle\Http\Message\Response;
use Norm\Service;
use Norm\Session;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\KernelInterface;
use Guzzle\Http\Exception\ClientException;
use Behat\Behat\Tester\Exception\PendingException;

class FeatureContext extends AbstractFeatureContext implements Context, SnippetAcceptingContext, KernelAwareContext
{
    /**
     * @When I get a new session
     */
    public function iGetANewSession()
    {
        $this->requestArray = $this->getSampleRequestArray('session', 'post');
        $this->postData('session');
        $this->sessionId = $this->getResponseFieldValue('payload.session.id');
        $this->setVariable('session.id', $this->sessionId);
    }

    /**
     * @Then I get a status code :arg1
     */
    public function iGetAStatusCode($arg1)
    {
        $this->assertEquals($this->response->getStatusCode(), $arg1,
            "Status code should have been $arg1 but actually was " . $this->response->getStatusCode());
    }

    /**
     * @Then I get back a valid json object
     */
    public function iGetBackAValidJsonObject()
    {
        //Should throw an exception if the content is not valid json
        $this->response->json();
    }

    /**
     * @Then The response contains a field named :arg1
     */
    public function theResponseContainsAFieldNamed($arg1)
    {
        $this->ensureResponseHasField($arg1);
    }

    /**
     * @Then The value of the :arg1 field returned is of type :arg2
     */
    public function theValueOfTheFieldReturnedIsOfType($arg1, $arg2)
    {
        $this->ensureResponseHasFieldType($arg1, $arg2);
    }

    /**
     * @Then The string length of the :arg1 field is :arg2
     */
    public function theStringLengthOfTheFieldIs($arg1, $arg2)
    {
        $this->ensureResponseHasFieldValueStringLength($arg1, $arg2);
    }

    /**
     * @Then Finally, I clean up my objects
     */
    public function finallyICleanUpMyObjects()
    {
        $this->cleanUpObjects();
    }

    /**
     * @When I create a new member
     */
    public function iCreateANewMember()
    {
        $this->requestArray = $this->getSampleRequestArray('member', 'post');
        $this->postData('member');
        $this->authenticatedUserId = $this->getResponseFieldValue('payload.member.id');
        $this->setVariable('member.id', $this->authenticatedUserId);
    }

    /**
     * @When I get the authenticated member's information
     */
    public function iGetTheAuthenticatedMemberSInformation()
    {
        $this->getData('member/' . $this->authenticatedUserId);
    }

    /**
     * @Then The string length of the :arg1 field is greater than zero
     */
    public function theStringLengthOfTheFieldIsGreaterThanZero($arg1)
    {
        $value = $this->getResponseFieldValue($arg1);

        $this->assertNotEquals(strlen($value), 0, 'The ' . $arg1 . ' field has a string length of zero');
    }

    /**
     * @When I have a sample request array for the :arg1 api, :arg2 method
     */
    public function iHaveASampleRequestArrayForTheApiMethod($arg1, $arg2)
    {
        $this->requestArray = $this->getSampleRequestArray($arg1, $arg2);
    }

    /**
     * @When I change the request array :arg1 field to :arg2
     */
    public function iChangeTheRequestArrayFieldTo($arg1, $arg2)
    {
        $this->setRequestFieldValue($arg1, $arg2);
    }

    /**
     * @When I send a :arg1 message to the :arg2 api with id from the :arg3 variable
     */
    public function iSendAMessageToTheApiWithIdFromTheVariable($arg1, $arg2, $arg3)
    {
        if(strtolower($arg1) !== 'post') {
            $arg2 .= '/' . $this->getVariable($arg3);
        }
        $func = $arg1 . 'Data';
        $this->$func($arg2);
    }

    /**
     * @Then The value of the :arg1 field is :arg2
     */
    public function theValueOfTheFieldIs($arg1, $arg2)
    {
        $this->ensureResponseFieldHasValue($arg1, $arg2);
    }

}