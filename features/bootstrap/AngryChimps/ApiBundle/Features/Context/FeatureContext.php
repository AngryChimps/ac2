<?php

namespace AngryChimps\ApiBundle\Features\Context;

use AngryChimps\ApiBundle\Services\GuzzleService;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Norm\riak\Address;
use Norm\riak\Availability;
use Norm\riak\Calendar;
use Norm\riak\Company;
use Norm\riak\Location;
use Norm\riak\Member;
use Guzzle\Http\Message\Response;
use Norm\riak\Service;
use Norm\riak\Session;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\KernelInterface;
use Guzzle\Http\Exception\ClientException;
use Behat\Behat\Tester\Exception\PendingException;

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
            $member = $this->riak->getMember($this->getResponseFieldValue('payload.member.id'));
            $this->addObject($member);
            $this->testUser = $member;
            $this->authenticatedUserId = $member->id;
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
            $this->iGetANewSessionToken();
            $this->iHaveAValidSignupAdArray();
            $this->iRegisterAProviderAd();
            $this->iHaveAValidSignupCompanyArray();
            $this->iRegisterAProviderAdCompany();

            //Get a new session so we are no longer authenticated
            $this->iGetANewSessionToken();
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
        $arr['email'] = $this->testUser->email;
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

        //Reset the authenticated user id if there is one
        $this->authenticatedUserId = null;

        //Add to objects so it gets cleaned up
        $this->addObject($this->riak->getSession($this->sessionId));
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
        $this->requestArray = array('payload' => array('id' => $this->testUser->id,
                                                        'name' => $this->testUser->name,
                                                        'email' => $this->testUser->email,
                                                        'photo' => $this->testUser->photo));
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
        $this->riak->invalidate($this->testUser);
        $this->testUser = $this->riak->getMember($this->authenticatedUserId);

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
        $company->plan = Company::BASIC_PLAN;
        $company->status = Company::ENABLED_STATUS;
        $company->administerMemberIds = [$this->authenticatedUserId];
        $this->riak->create($company);

        $this->testCompany = $company;

        $this->addObject($company);

        $this->testUser->managedCompanyIds = array($company->id);
        $this->riak->update($this->testUser);
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
        $this->riak->create($member);

        $company = new Company();
        $company->administerMemberIds = array($member->id);
        $company->name = 'Acme Company';
        $company->plan = Company::BASIC_PLAN;
        $company->status = Company::ENABLED_STATUS;
        $this->riak->create($company);

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
        $this->riak->update($this->testCompany);
    }

    /**
     * @When I put changes to the test company
     */
    public function iPutChangesToTheTestCompany()
    {
        $arr = array();
        $arr['name'] = $this->testCompany->name;
        $this->requestArray = array('payload' => $arr);

        $this->putData('company/' . $this->testCompany->id);
    }

    /**
     * @Given I have a valid new company array
     */
    public function iHaveAValidNewCompanyArray()
    {
        $arr = array();
        $arr['name'] = 'Fried Banananas, Inc.';
        $this->requestArray = array('payload' => $arr);
    }

    /**
     * @When I create a test company
     */
    public function iCreateATestCompany()
    {
        $this->postData('company');

        if($this->response->getStatusCode() == 200) {
            $id = $this->getResponseFieldValue('payload.company.id');
            $this->testCompany = $this->riak->getCompany($id);
            $this->addObject($this->testCompany);

            $this->testUser->managedCompanyIds = array($id);
            $this->riak->update($this->testUser);
        }
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
        $this->riak->create($company);

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
        $arr['company_id'] = $this->testCompany->id;
        $arr['phone'] = '(415) 555-5555';
        $arr['is_mobile'] = false;
        $this->requestArray = array('payload' => $arr);
    }

    /**
     * @When I create a test location for my test company
     */
    public function iCreateATestLocationForMyTestCompany()
    {
        $this->postData('location');

        if($this->response->getStatusCode() === 200) {
            $this->testLocation = $this->riak->getLocation($this->getResponseFieldValue('payload.location.id'));
        }
    }

    /**
     * @Then If I reload the authenticated user
     */
    public function ifIReloadTheAuthenticatedUser()
    {
        $user = $this->riak->getMember($this->authenticatedUserId);
        $this->riak->invalidate($user);

        $this->testUser =  $this->riak->getMember($this->authenticatedUserId);
    }

    /**
     * @Then The value of the :arg1 field of the authenticated user is :arg2
     */
    public function theValueOfTheFieldOfTheAuthenticatedUserIs($arg1, $arg2)
    {
        $this->assertEquals($this->testUser->$arg1, $arg2,
            'The value of the ' . $arg1 . ' field of the authenticated user is not '
            . $arg2 . ' it is actually ' . $this->testUser->$arg1);
    }

    /**
     * @Then If I reload the test company
     */
    public function ifIReloadTheTestCompany()
    {
        $id = $this->testCompany->id;
        $this->riak->invalidate($this->testCompany);
        $this->testCompany = $this->riak->getCompany($id);
    }

    /**
     * @Then The value of the :arg1 field of the test company is :arg2
     */
    public function theValueOfTheFieldOfTheTestCompanyIs($arg1, $arg2)
    {
        $id = $this->testCompany->id;
        $this->riak->invalidate($this->testCompany);
        $this->testCompany = $this->riak->getCompany($id);

        $this->assertEquals($this->testCompany->$arg1, $arg2,
            'The value of the ' . $arg1 . ' field of the test company is not ' . $arg2);
    }

    /**
     * @Given I change the test locations :arg1 property to :arg2
     */
    public function iChangeTheTestLocationsPropertyTo($arg1, $arg2)
    {
        $this->testLocation->{$arg1} = $arg2;
    }

    /**
     * @When I put changes to the test location
     */
    public function iPutChangesToTheTestLocation()
    {
        $arr = array();
        $arr['name'] = $this->testLocation->name;
        $arr['street1'] = $this->testLocation->address->street1;
        $arr['street2'] = $this->testLocation->address->street2;
        $arr['zip'] = $this->testLocation->address->zip;
        $arr['companyId'] = $this->testLocation->companyId;
        $arr['phone'] = $this->testLocation->address->phone;
        $arr['is_mobile'] = $this->testLocation->isMobile;

        $this->requestArray = array('payload' => $arr);
        $this->putData('location/' . $this->testLocation->id);
    }

    /**
     * @When I put changes to the test location with the wrong id
     */
    public function iPutChangesToTheTestLocationWithTheWrongId()
    {
        $arr = array();
        $arr['name'] = $this->testLocation->name;
        $arr['street1'] = $this->testLocation->address->street1;
        $arr['street2'] = $this->testLocation->address->street2;
        $arr['zip'] = $this->testLocation->address->zip;
        $arr['companyId'] = $this->testCompany->id;
        $arr['phone'] = $this->testLocation->address->phone;
        $arr['is_mobile'] = $this->testLocation->isMobile;

        $this->requestArray = array('payload' => $arr);
        $this->putData('location/a');
    }
    /**
     * @Then If I reload the test location
     */
    public function ifIReloadTheTestLocation()
    {
        $id = $this->testLocation->id;
        $this->riak->invalidate($this->testLocation);
        $this->testLocation = $this->riak->getLocation($id);
    }

    /**
     * @Then The value of the :arg1 field of the test location is :arg2
     */
    public function theValueOfTheFieldOfTheTestLocationIs($arg1, $arg2)
    {
        $this->assertEquals($this->testLocation->$arg1, $arg2,
            'The value of the ' . $arg1 . ' field of the test location is not ' . $arg2);
    }

    /**
     * @Given The test company has a test location
     */
    public function theTestCompanyHasATestLocation()
    {
        $this->testLocation = new Location();
        $this->testLocation->companyId = $this->testCompany->id;
        $this->testLocation->address = new Address();
        $this->testLocation->address->street1 = '230 Dolores Street';
        $this->testLocation->address->street2 = 'APT 212';
        $this->testLocation->address->city = 'San Francisco';
        $this->testLocation->address->state = 'CA';
        $this->testLocation->address->zip = 94110;
        $this->testLocation->address->phone = '(415) 555-1213';
        $this->testLocation->address->lat = 37.762822;
        $this->testLocation->address->long = -122.437239;
        $this->testLocation->phone = '(415) 555-1212';
        $this->testLocation->isMobile = false;
        $this->riak->create($this->testLocation);
    }

    /**
     * @When I delete the test location
     */
    public function iDeleteTheTestLocation()
    {
        $this->deleteData('location/' . $this->testLocation->id);
    }

    /**
     * @When I delete a non-existent location
     */
    public function iDeleteANonExistentLocation()
    {
        $this->deleteData('location/c');
    }

    /**
     * @When I get the location data for the test location
     */
    public function iGetTheLocationDataForTheTestLocation()
    {
        $this->getData('location/' . $this->testLocation->id);
    }

    /**
     * @When I get the location data for a fake location
     */
    public function iGetTheLocationDataForAFakeLocation()
    {
        $this->getData('location/d');
    }

    /**
     * @Given The test location has a test service
     */
    public function theTestLocationHasATestService()
    {
        $service = new Service();
        $service->companyId = $this->testCompany->id;
        $service->name = 'Long hair cut';
        $service->discountedPrice = 55.99;
        $service->originalPrice = 70;
        $service->minsForService = 30;
        $service->minsNotice = 60;
        $this->riak->create($service);

        $this->testService = $service;
    }

    /**
     * @When I get a list of categories from the server
     */
    public function iGetAListOfCategoriesFromTheServer()
    {
        $this->getData('categories');
    }

    /**
     * @Then The :arg1 array is not empty
     */
    public function theArrayIsNotEmpty($arg1)
    {
        $arr = $this->getResponseFieldValue($arg1);
        $this->assertNotEmpty($arr, 'The ' . $arg1 . ' array is empty and should not be');
    }

    /**
     * @When I get the service data for the test service
     */
    public function iGetTheServiceDataForTheTestService()
    {
        $this->getData('service/' . $this->testService->id);
    }

    /**
     * @Given I have a valid signup ad array
     */
    public function iHaveAValidSignupAdArray()
    {
        $arr = array();
        $arr['ad_title'] = 'My Nifty Ad Title';
        $arr['ad_description'] = 'And a description';

        $arr['availabilities'] = [];
        $avail = ['start'=> 'today 09:00:00-08:00', 'end'=> 'today 17:00:00-08:00'];
        $arr['availabilities'][] = $avail;
        $avail = ['start'=> 'tomorrow 10:00:00-08:00', 'end'=> 'tomorrow 14:00:00-08:00'];
        $arr['availabilities'][] = $avail;

        $arr['services'] = [];
        $svc = [];
        $svc['service_name'] = 'Long Haircut';
        $svc['discounted_price'] = 70.00;
        $svc['original_price'] = 129.00;
        $svc['mins_for_service'] = 60;
        $svc['mins_notice'] = 180;
        $svc['category_id'] = 101;
        $arr['services'][] = $svc;

        $this->requestArray = array('payload'=>$arr);
    }

    /**
     * @When I register a provider ad
     */
    public function iRegisterAProviderAd()
    {
        $this->postData('signup/registerProviderAd');

        if($this->response->getStatusCode() === 200) {
            $this->authenticatedUserId = $this->getResponseFieldValue('payload.member.id');
            $this->testUser = $this->riak->getMember($this->authenticatedUserId);
            $this->testCompany = $this->riak->getCompany($this->testUser->managedCompanyIds[0]);
            $this->addObject($this->testUser);
            $this->addObject($this->testCompany);
        }
    }

    /**
     * @Given I have a valid signup company array
     */
    public function iHaveAValidSignupCompanyArray()
    {
        $this->rand = rand(1,99999999999);

        $arr = array();
        $arr['member_id'] = $this->authenticatedUserId;
        $arr['company_name'] = 'Acme Auto Repair';
        $arr['member_name'] = 'James Williams';
        $arr['email'] = 'testy' . $this->rand . '@seangallavan.com';
        $arr['password'] = 'abcdabcd';
        $arr['dob'] = '1950-01-03';
        $arr['street1'] = '440 Castro Street';
        $arr['street2'] = '';
        $arr['zip'] = 94114;
        $arr['phone'] = '4155551212';
        $arr['mobile_phone'] = '4155551213';

        $this->requestArray = array('payload'=>$arr);
    }

    /**
     * @Given I register a provider ad company
     */
    public function iRegisterAProviderAdCompany()
    {
        $this->postData('signup/registerProviderCompany');

        if($this->response->getStatusCode() === 200) {
            $this->riak->invalidate($this->testUser);
            $this->testUser = $this->riak->getMember($this->authenticatedUserId);
            $this->riak->invalidate($this->testCompany);
            $this->testCompany = $this->riak->getCompany($this->testUser->managedCompanyIds[0]);
            $this->testLocation = $this->riak->getLocation($this->testCompany->locationIds[0]);
            $this->addObject($this->testUser);
            $this->addObject($this->testCompany);
            $this->addObject($this->testLocation);
        }
    }

    /**
     * @Given I have a valid service array
     */
    public function iHaveAValidServiceArray()
    {
        $arr = [];
        $arr['name'] = 'Tire rotation';
        $arr['company_id'] = $this->testCompany->id;
        $arr['discounted_price'] = 24.99;
        $arr['original_price'] = 49.99;
        $arr['mins_for_service'] = 30;
        $arr['mins_notice'] = 15;

        $this->requestArray = array('payload' => $arr);
    }

    /**
     * @When I post the service data array
     */
    public function iPostTheServiceDataArray()
    {
        $this->postData('service');

        if($this->response->getStatusCode() === 200) {
            $this->testService = $this->riak->getService($this->getResponseFieldValue("payload.service.id"));
        }
    }

    /**
     * @Given I change the test service's :arg1 field to :arg2
     */
    public function iChangeTheTestServiceSFieldTo($arg1, $arg2)
    {
        $this->testService->$arg1 = $arg2;
    }

    /**
     * @When I put changes to the service
     */
    public function iPutChangesToTheService()
    {
        $arr = [];
        $arr['id'] = $this->testService->id;
        $arr['name'] = $this->testService->name;
        $arr['company_id'] = $this->testCompany->id;
        $arr['discounted_price'] = $this->testService->discountedPrice;
        $arr['original_price'] = $this->testService->originalPrice;
        $arr['mins_for_service'] = $this->testService->minsForService;
        $arr['mins_notice'] = $this->testService->minsNotice;

        $this->requestArray = array('payload' => $arr);

        $this->putData('service/' . $this->testService->id);
    }

    /**
     * @Then The value of the :arg1 field of the test service is :arg2
     */
    public function theValueOfTheFieldOfTheTestServiceIs($arg1, $arg2)
    {
        $id = $this->testService->id;
        $this->riak->invalidate($this->testService);

        $this->testService = $this->riak->getService($id);

        $this->assertEquals($this->testService->$arg1, $arg2, "The test service's $arg1 field is supposed to be $arg2 but actually is " . $this->testService->$arg1);
    }

    /**
     * @When I delete the test service
     */
    public function iDeleteTheTestService()
    {
        $this->deleteData('service/' . $this->testService->id);
    }

    /**
     * @Given The test location has a test calendar
     */
    public function theTestLocationHasATestCalendar()
    {
        $calendar = new Calendar();
        $calendar->name = "Joe's Calendar";
        $calendar->locationId = $this->testLocation->id;
        $calendar->companyId = $this->testCompany->id;
        $this->riak->create($calendar);

        $this->testCalendar = $calendar;
    }

    /**
     * @Given The test calendar has a test availability
     */
    public function theTestCalendarHasATestAvailability()
    {
        $availability = new Availability();
        $availability->start = $this->getDate('tomorrow', 9, 0);
        $availability->end = $this->getDate('tomorrow', 12, 0);

        $this->testCalendar->availabilities[] = $availability;
        $this->riak->update($this->testCalendar);
    }

    /**
     * @Given I have a valid non-conflicting availability array
     */
    public function iHaveAValidNonConflictingAvailabilityArray()
    {
        $arr = [];
        $arr['calendar_id'] = $this->testCalendar->id;
        $arr['start'] = $this->getDate('tomorrow', 13, 0)->format('Y-m-d H:i:s');
        $arr['end'] = $this->getDate('tomorrow', 14, 0)->format('Y-m-d H:i:s');

        $this->requestArray = array('payload' => $arr);
    }

    /**
     * @When I post the availability array
     */
    public function iPostTheAvailabilityArray()
    {
        $this->postData('availability');
    }

    /**
     * @Then I reload the test calendar
     */
    public function iReloadTheTestCalendar()
    {
        $id = $this->testCalendar->id;
        $this->riak->invalidate($this->testCalendar);

        $this->testCalendar = $this->riak->getCalendar($id);
    }

    /**
     * @Then The test calendar has :arg1 availabilities
     */
    public function theTestCalendarHasAvailabilities($arg1)
    {
        $this->assertEquals(count($this->testCalendar->availabilities), $arg1,
            'There were ' . count($this->testCalendar->availabilities) . ' availabilities when there should have  been ' . $arg1);
    }

    /**
     * @Given I have a valid availability array starting :arg1 at :arg2 until :arg3 at :arg4
     */
    public function iHaveAValidAvailabilityArrayStartingAtUntilAt($arg1, $arg2, $arg3, $arg4)
    {
        list($startHour, $startMinute) = explode(':', $arg2);
        list($endHour, $endMinute) = explode(':', $arg4);

        $arr = [];
        $arr['calendar_id'] = $this->testCalendar->id;
        $arr['start'] = $this->getDate($arg1, $startHour, $startMinute)->format('Y-m-d H:i:s');
        $arr['end'] = $this->getDate($arg3, $endHour, $endMinute)->format('Y-m-d H:i:s');

        $this->requestArray = array('payload' => $arr);
    }

    /**
     * @Then The calendar's first availability starts :arg1 at :arg2 and ends :arg3 at :arg4
     */
    public function theCalendarSFirstAvailabilityStartsAtAndEndsAt($arg1, $arg2, $arg3, $arg4)
    {
        list($startHour, $startMinute) = explode(':', $arg2);
        list($endHour, $endMinute) = explode(':', $arg4);

        $this->assertEquals($this->testCalendar->availabilities[0]->start, $this->getDate($arg1, $startHour, $startMinute),
            "The first availability start time is incorrect: " . $this->testCalendar->availabilities[0]->start->format('Y-m-d H:i:s'));
        $this->assertEquals($this->testCalendar->availabilities[0]->end, $this->getDate($arg3, $endHour, $endMinute),
            "The first availability end time is incorrect: " . $this->testCalendar->availabilities[0]->end->format('Y-m-d H:i:s'));

    }

    /**
     * @Given The test calendar has an availability starting :arg1 at :arg2 until :arg3 at :arg4
     */
    public function theTestCalendarHasAnAvailabilityStartingAtUntilAt($arg1, $arg2, $arg3, $arg4)
    {
        list($startHour, $startMinute) = explode(':', $arg2);
        list($endHour, $endMinute) = explode(':', $arg4);

        $availability = new Availability();
        $availability->start = $this->getDate($arg1, $startHour, $startMinute);
        $availability->end = $this->getDate($arg3, $endHour, $endMinute);

        $this->testCalendar->availabilities[] = $availability;
        $this->riak->update($this->testCalendar);
    }

    /**
     * @When I delete the availability
     */
    public function iDeleteTheAvailability()
    {
        $this->deleteData('availability');
    }


}

