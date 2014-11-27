<?php

namespace AngryChimps\ApiBundle\Features\Context;

use AngryChimps\ApiBundle\Services\GuzzleService;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Norm\riak\Member;
use Guzzle\Http\Message\Response;
use Norm\riak\Session;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\KernelInterface;
use Guzzle\Http\Exception\ClientException;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends AbstractFeatureContext implements Context, SnippetAcceptingContext, KernelAwareContext
{

    /**
     * @Given I have a valid new user array
     */
    public function iHaveAValidNewUserArray()
    {
        $this->rand = rand(1,99999999999);

        $member = array();
        $member['name'] = "Joe " . $this->rand;
        $member['email'] = 'trash' . $this->rand .'@seangallavan.com';
        $member['password'] = 'abcdabcd';
        $member['dob'] = '1950-01-01';

        $this->requestArray = array('payload' => $member);
    }

    /**
     * @When I register a new user
     */
    public function iRegisterANewUser()
    {
        $this->postData('auth/register');

        //Lookup member and add it to the objects array to be deleted after we're done
        if(isset($this->getContentArray()['payload']['member'])) {
            $this->addObject(Member::getByPk($this->getResponseFieldValue('payload.member.id')));
        }
    }

    /**
     * @Then I get a status code :arg1
     */
    public function iGetAStatusCode($arg1)
    {
        if($this->response->getStatusCode() != $arg1) {
            throw new \Exception("Status code should have been $arg1 but actually was " . $this->response->getStatusCode());
        }
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
     * @Given I change the :arg1 field's value of the request object to :arg2
     */
    public function iChangeTheFieldSValueOfTheRequestObjectTo($arg1, $arg2)
    {
        $this->setRequestFieldValue($arg1, $arg2);
    }

    /**
     * @Then The value of the :arg1 field is :arg2
     */
    public function theValueOfTheFieldIs($arg1, $arg2)
    {
        $value = $this->getResponseFieldValue($arg1);
        if($value !== $arg2) {
            throw new \Exception('The value of the ' . $arg1 . ' should be ' . $arg2 . ' but is actually' . $value);
        }
    }

    /**
     * @Given I have a test user
     */
    public function iHaveATestUser()
    {
        try {
            $this->rand = rand(1, 99999999999);

            $member = new Member();
            $member->email = 'trash' . $this->rand . '@seangallavan.com';
            $member->name = 'Trashy ' . $this->rand;
            $member->dob = new \DateTime('1950-01-01');
            $member->password = $this->getAuthService()->hashPassword('abcdabcd');
            $member->status = Member::ACTIVE_STATUS;
            $member->role = Member::USER_ROLE;
            $member->save();

            //Save the user_id for future use
            $this->testUser = $member;

            //Add it to the objects array so it gets cleaned up
            $this->addObject($member);
        }
        catch(\Exception $ex) {
            echo 'message: ' . $ex->getMessage() . "\n";
            echo 'file: ' . $ex->getFile() . "\n";
            echo 'line: ' . $ex->getLine() . "\n";
            echo 'stack: ' . $ex->getTraceAsString() . "\n";

            throw($ex);
        }
    }

    /**
     * @Given I have a valid form login array
     */
    public function iHaveAValidFormLoginArray()
    {
        $arr = array();
        $arr['email'] = 'trash' . $this->rand . '@seangallavan.com';
        $arr['password'] = 'abcdabcd';
        $this->requestArray = array('payload' => $arr);
    }

    /**
     * @When I log in
     */
    public function iLogIn()
    {
        $this->postData("auth/login");

        if($this->response->getStatusCode() == 200) {
            $this->authenticatedUserId = $this->getResponseFieldValue('payload.member.id');
        }
    }

    /**
     * @Then Finally, I clean up my objects
     */
    public function finallyICleanUpMyObjects()
    {
        $this->cleanUpObjects();
    }

    /**
     * @When I get a new session token
     */
    public function iGetANewSessionToken()
    {
        $this->getData('session');

        //Set sessionId for future calls
        $this->sessionId = $this->getResponseFieldValue('payload.session_id');

        //Add to objects so it gets cleaned up
        $this->addObject(Session::getByPk($this->sessionId));
    }

    /**
     * @When I get the member data for myself
     */
    public function iGetTheMemberDataForMyself()
    {
        $this->getData('member/' . $this->testUser->id);
    }

    /**
     * @Then The response does not contain a field named :arg1
     */
    public function theResponseDoesNotContainAFieldNamed($arg1)
    {
        $this->ensureResponseDoesNotHaveField($arg1);
    }

    /**
     * @Then The string length of the :arg1 field greater than zero
     */
    public function theStringLengthOfTheFieldGreaterThanZero($arg1)
    {
        $value = $this->getResponseFieldValue($arg1);

        if(strlen($value) === 0) {
            throw new \Exception('The ' . $arg1 . ' field has a string length of zero');
        }
    }

    /**
     * @Given I have an authenticated user
     */
    public function iHaveAnAuthenticatedUser()
    {
        $this->iHaveATestUser();
        $this->iGetANewSessionToken();
        $this->iHaveAValidFormLoginArray();
        $this->iLogIn();
    }

    /**
     * @When I get the member data for an invalid user
     */
    public function iGetTheMemberDataForAnInvalidUser()
    {
        $this->getData('member/a');
    }

    /**
     * @Given I change the authenticated users :arg1 field to :arg2
     */
    public function iChangeTheAuthenticatedUsersFieldTo($arg1, $arg2)
    {
        $this->testUser->$arg1 = $arg2;
    }

    /**
     * @When I save changes to the authenticated user
     */
    public function iSaveChangesToTheAuthenticatedUser()
    {
        $this->requestArray = array('payload' => $this->testUser->getPrivateArray());
        $this->putData('member/' . $this->testUser->id);
    }
}

