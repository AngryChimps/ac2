<?php

namespace AngryChimps\ApiBundle\Features\Context;

use AngryChimps\ApiBundle\Services\GuzzleService;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Norm\riak\Company;
use Norm\riak\Location;
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
        $this->assertEquals($value, $arg2,
            'The value of the ' . $arg1 . ' should be ' . $arg2 . ' but is actually' . $value);
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

        $this->assertNotEquals(strlen($value), 0, 'The ' . $arg1 . ' field has a string length of zero');
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

    /**
     * @When I delete the authenticated user
     */
    public function iDeleteTheAuthenticatedUser()
    {
        $this->deleteData('member/' . $this->authenticatedUserId);
    }

    /**
     * @Then The authenticated user's :arg1 field is :arg2
     */
    public function theAuthenticatedUserSFieldIs($arg1, $arg2)
    {
        $this->testUser->invalidate();
        $this->testUser = Member::getByPk($this->authenticatedUserId);

        $this->assertEquals($this->testUser->$arg1, $arg2,
            'The authenticated users ' . $arg1 . ' field is not ' . $arg2);
    }

    /**
     * @When I get the company data for myself
     */
    public function iGetTheCompanyDataForMyself()
    {
        $this->getData('company/' . $this->testUser->managedCompanyIds[0]);
    }


    /**
     * @Given The authenticated user has a company
     */
    public function theAuthenticatedUserHasACompany()
    {
        $company = new Company();
        $company->name = 'ABC Company';
        $company->description = "a cool company";
        $company->address = '234 Main Street, Burlington, VT 91023';
        $company->plan = Company::BASIC_PLAN;
        $company->status = Company::ENABLED_STATUS;
        $company->administerMemberIds = [$this->authenticatedUserId];
        $company->save();

        $this->testCompany = $company;

        $this->addObject($company);

        $this->testUser->managedCompanyIds = array($company->id);
        $this->testUser->save();
    }

    /**
     * @Given Another user has a company
     */
    public function anotherUserHasACompany()
    {
        $this->rand = rand(1, 99999999999);

        $member = new Member();
        $member->email = 'trash' . $this->rand . '@seangallavan.com';
        $member->name = 'Trashy ' . $this->rand;
        $member->dob = new \DateTime('1950-01-01');
        $member->password = $this->getAuthService()->hashPassword('abcdabcd');
        $member->status = Member::ACTIVE_STATUS;
        $member->role = Member::USER_ROLE;
        $member->save();

        $company = new Company();
        $company->administerMemberIds = array($member->id);
        $company->name = 'Acme Company';
        $company->plan = Company::BASIC_PLAN;
        $company->status = Company::ENABLED_STATUS;
        $company->save();

        $this->testCompany = $company;
    }

    /**
     * @When I get the company data for the company
     */
    public function iGetTheCompanyDataForTheCompany()
    {
        $this->getData('company/' . $this->testCompany->id);
    }

    /**
     * @When I get the company data for a fake company
     */
    public function iGetTheCompanyDataForAFakeCompany()
    {
        $this->getData('company/a');
    }

    /**
     * @Given I change the test companys :arg1 field to :arg2
     */
    public function iChangeTheTestCompanysFieldTo($arg1, $arg2)
    {
        $this->testCompany->{$arg1} = $arg2;
    }

    /**
     * @When I save changes to the test company
     */
    public function iSaveChangesToTheTestCompany()
    {
        $this->testCompany->save();
    }

    /**
     * @Given I have a valid new company array
     */
    public function iHaveAValidNewCompanyArray()
    {
        $arr = array();
        $arr['name'] = 'Friend Banananas, Inc.';
        $arr['plan'] = Company::BASIC_PLAN;
        $arr['status'] = Company::ENABLED_STATUS;
        $arr['administerMemberIds'] = array($this->authenticatedUserId);
        $this->requestArray = array('payload' => $arr);
    }

    /**
     * @When I create a test company
     */
    public function iCreateATestCompany()
    {
        $this->postData('company');
    }

    /**
     * @When I delete the test company
     */
    public function iDeleteTheTestCompany()
    {
        $this->deleteData('company/' . $this->testCompany->id);
    }

    /**
     * @When I delete a non-existent company
     */
    public function iDeleteANonExistentCompany()
    {
        $this->deleteData('company/a');
    }

    /**
     * @Given I have a test company
     */
    public function iHaveATestCompany()
    {
        $company = new Company();
        $company->name = 'ABC Fabrics';
        $company->administerMemberIds = array($this->authenticatedUserId);
        $company->status = Company::ENABLED_STATUS;
        $company->plan = Company::BASIC_PLAN;
        $company->save();

        $this->addObject($company);

        $this->testCompany = $company;
    }

    /**
     * @Given I change the test companys :arg1 property to an empty array
     */
    public function iChangeTheTestCompanysPropertyToAnEmptyArray($arg1)
    {
        $this->testCompany->$arg1 = array();
    }

    /**
     * @Given I have a valid new location array
     */
    public function iHaveAValidNewLocationArray()
    {
        $arr = array();
        $arr['name'] = 'Main Street Location';
        $arr['street1'] = '440 Castro Street';
        $arr['zip'] = 94114;
        $arr['status'] = Location::ENABLED_STATUS;
        $arr['company_id'] = $this->testCompany->id;
        $arr['phone'] = '555-555-5555';
        $this->requestArray = array('payload' => $arr);
    }

    /**
     * @When I create a test location for my test company
     */
    public function iCreateATestLocationForMyTestCompany()
    {
        $this->postData('location');
    }

    /**
     * @Then If I reload the authenticated user
     */
    public function ifIReloadTheAuthenticatedUser()
    {
        $user = Member::getByPk($this->authenticatedUserId);
        $user->invalidate();

        $this->testUser =  Member::getByPk($this->authenticatedUserId);
    }

    /**
     * @Then The value of the :arg1 field of the authenticated user is :arg2
     */
    public function theValueOfTheFieldOfTheAuthenticatedUserIs($arg1, $arg2)
    {
        $this->assertEquals($this->testUser->$arg1, $arg2,
            'The value of the ' . $arg1 . ' field of the authenticated user is not ' . $arg2);
    }

}

