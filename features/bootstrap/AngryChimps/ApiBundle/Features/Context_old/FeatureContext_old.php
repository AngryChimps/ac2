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

/**
 * Defines application features from the specific context.
 */
class FeatureContext_old extends AbstractFeatureContext implements Context, SnippetAcceptingContext, KernelAwareContext
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
            $member = $this->norm->getMember($this->getResponseFieldValue('payload.member.id'));
            $this->addObject($member);
            $this->testUser = $member;
            $this->authenticatedUserId = $member->getId();
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
        $arr['email'] = $this->testUser->getEmail();
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
        $this->addObject($this->norm->getSession($this->sessionId));
    }

    /**
     * @When I get the member data for myself
     */
    public function iGetTheMemberDataForMyself()
    {
        $this->getData('member/' . $this->testUser->getId());
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
        $this->requestArray = array('payload' => array('id' => $this->testUser->getId(),
                                                        'fname' => $this->testUser->getFName(),
                                                        'lname' => $this->testUser->getLname(),
                                                        'email' => $this->testUser->getEmail(),
                                                        'photo' => $this->testUser->getPhoto()));
        $this->putData('member/' . $this->testUser->getId());
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
        $this->norm->invalidate($this->testUser);
        $this->testUser = $this->norm->getMember($this->authenticatedUserId);

        $this->assertEquals($this->testUser->$arg1, $arg2,
            'The authenticated users ' . $arg1 . ' field is not ' . $arg2);
    }

    /**
     * @When I get the company data for myself
     */
    public function iGetTheCompanyDataForMyself()
    {
        $this->getData('company/' . $this->testUser->getManagedCompanyIds()[0]);
    }


    /**
     * @Given The authenticated user has a company
     */
    public function theAuthenticatedUserHasACompany()
    {
        $company = new Company();
        $company->setName('ABC Company');
        $company->setDescription("a cool company");
        $company->setPlan(Company::BASIC_PLAN);
        $company->setStatus(Company::ENABLED_STATUS);
        $this->norm->create($company);

        $this->testCompany = $company;

        $this->addObject($company);
    }

    /**
     * @Given Another user has a company
     */
    public function anotherUserHasACompany()
    {
        $this->rand = rand(1, 99999999999);

        $member = new Member();
        $member->setEmail('trash' . $this->rand . '@seangallavan.com');
        $member->setName('Trashy ' . $this->rand);
        $member->setDob(new \DateTime('1950-01-01'));
        $member->setPassword($this->getAuthService()->hashPassword('abcdabcd'));
        $member->setStatus(Member::ACTIVE_STATUS);
        $member->setRole(Member::USER_ROLE);
        $this->norm->create($member);

        $company = new Company();
        $company->setName('Acme Company');
        $company->setPlan(Company::BASIC_PLAN);
        $company->setStatus(Company::ENABLED_STATUS);
        $this->norm->create($company);

        $this->testCompany = $company;
    }

    /**
     * @When I get the company data for the company
     */
    public function iGetTheCompanyDataForTheCompany()
    {
        $this->getData('company/' . $this->testCompany->getId());
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
        $this->norm->update($this->testCompany);
    }

    /**
     * @When I put changes to the test company
     */
    public function iPutChangesToTheTestCompany()
    {
        $arr = array();
        $arr['name'] = $this->testCompany->getName();
        $this->requestArray = array('payload' => $arr);

        $this->putData('company/' . $this->testCompany->getId());
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
            $this->testCompany = $this->norm->getCompany($id);
            $this->addObject($this->testCompany);
        }
    }

    /**
     * @When I delete the test company
     */
    public function iDeleteTheTestCompany()
    {
        $this->deleteData('company/' . $this->testCompany->getId());
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
        $company->setName('ABC Fabrics');
        $company->setStatus(Company::ENABLED_STATUS);
        $company->setPlan(Company::BASIC_PLAN);
        $this->norm->create($company);

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
        $arr['company_id'] = $this->testCompany->getId();
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
            $this->testLocation = $this->norm->getLocation($this->getResponseFieldValue('payload.location.id'));
        }
    }

    /**
     * @Then If I reload the authenticated user
     */
    public function ifIReloadTheAuthenticatedUser()
    {
        $user = $this->norm->getMember($this->authenticatedUserId);
        $this->norm->invalidate($user);

        $this->testUser =  $this->norm->getMember($this->authenticatedUserId);
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
        $id = $this->testCompany->getId();
        $this->norm->invalidate($this->testCompany);
        $this->testCompany = $this->norm->getCompany($id);
    }

    /**
     * @Then The value of the :arg1 field of the test company is :arg2
     */
    public function theValueOfTheFieldOfTheTestCompanyIs($arg1, $arg2)
    {
        $id = $this->testCompany->getId();
        $this->norm->invalidate($this->testCompany);
        $this->testCompany = $this->norm->getCompany($id);

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
        $arr['name'] = $this->testLocation->getName();
        $arr['street1'] = $this->testLocation->getStreet1();
        $arr['street2'] = $this->testLocation->getStreet2();
        $arr['zip'] = $this->testLocation->getZip();
        $arr['companyId'] = $this->testLocation->getCompanyId();
        $arr['phone'] = $this->testLocation->getPhone();
        $arr['is_mobile'] = $this->testLocation->getIsMobile();

        $this->requestArray = array('payload' => $arr);
        $this->putData('location/' . $this->testLocation->getId());
    }

    /**
     * @When I put changes to the test location with the wrong id
     */
    public function iPutChangesToTheTestLocationWithTheWrongId()
    {
        $arr = array();
        $arr['name'] = $this->testLocation->getName();
        $arr['street1'] = $this->testLocation->getStreet1();
        $arr['street2'] = $this->testLocation->getStreet2();
        $arr['zip'] = $this->testLocation->getZip();
        $arr['companyId'] = $this->testLocation->getCompanyId();
        $arr['phone'] = $this->testLocation->getPhone();
        $arr['is_mobile'] = $this->testLocation->getIsMobile();

        $this->requestArray = array('payload' => $arr);
        $this->putData('location/a');
    }
    /**
     * @Then If I reload the test location
     */
    public function ifIReloadTheTestLocation()
    {
        $id = $this->testLocation->getId();
        $this->norm->invalidate($this->testLocation);
        $this->testLocation = $this->norm->getLocation($id);
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
        $this->testLocation->companyId = $this->testCompany->getId();
        $this->testLocation->setStreet1('230 Dolores Street');
        $this->testLocation->setStreet2('APT 212');
        $this->testLocation->setCity('San Francisco');
        $this->testLocation->setState('CA');
        $this->testLocation->setZip(94110);
        $this->testLocation->setPhone('(415) 555-1213');
        $this->testLocation->setLat(37.762822);
        $this->testLocation->setLon(-122.437239);
        $this->testLocation->setPhone('(415) 555-1212');
        $this->testLocation->setIsMobile(false);
        $this->norm->create($this->testLocation);
    }

    /**
     * @When I delete the test location
     */
    public function iDeleteTheTestLocation()
    {
        $this->deleteData('location/' . $this->testLocation->getId());
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
        $this->getData('location/' . $this->testLocation->getId());
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
        $service->setCompanyId($this->testCompany->getId());
        $service->setName('Long hair cut');
        $service->setDiscountedPrice(55.99);
        $service->setOriginalPrice(70);
        $service->setMinsForService(30);
        $service->setMinsNotice(60);
        $this->norm->create($service);

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
        $this->getData('service/' . $this->testService->getId());
    }

    /**
     * @Given I have a valid signup ad array
     */
    public function iHaveAValidSignupAdArray()
    {
        $arr = array();
        $arr['ad_title'] = 'My Nifty Ad Title';
        $arr['ad_description'] = 'And a description';
        $arr['category_id'] = 201;

        $arr['availabilities'] = [];
        $avail = ['start'=> 'today 09:00:00-08:00', 'end'=> 'today 17:00:00-08:00'];
        $arr['availabilities'][] = $avail;
        $avail = ['start'=> 'tomorrow 10:00:00-08:00', 'end'=> 'tomorrow 14:00:00-08:00'];
        $arr['availabilities'][] = $avail;

        $arr['services'] = [];
        $svc = [];
        $svc['name'] = 'Long Haircut';
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
            $this->testUser = $this->norm->getMember($this->authenticatedUserId);
            $this->testCompany = $this->norm->getCompany($this->getResponseFieldValue('payload.company.id'));
            $this->testLocation = $this->norm->getLocation($this->getResponseFieldValue('payload.location.id'));
            $this->testCalendar = $this->norm->getCalendar($this->getResponseFieldValue('payload.calendar.id'));
            $this->addObject($this->testUser);
            $this->addObject($this->testCompany);
            $this->addObject($this->testLocation);
            $this->addObject($this->testCalendar);
            $this->addObject($this->providerAd);
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
    public function     iRegisterAProviderAdCompany()
    {
        $this->postData('signup/registerProviderCompany');

        if($this->response->getStatusCode() === 200) {
            $this->norm->invalidate($this->testUser);
            $this->testUser = $this->norm->getMember($this->authenticatedUserId);
            $companyId = $this->testCompany->getId();
            $this->norm->invalidate($this->testCompany);
            $this->testCompany = $this->norm->getCompany($companyId);
            $locationId = $this->testLocation->getId();
            $this->testLocation = $this->norm->getLocation($locationId);
            $calendarId = $this->testCalendar->getId();
            $this->calendar = $this->norm->getCalendar($calendarId);
            $this->providerAd = $this->norm->getProviderAd($this->getResponseFieldValue('payload.provider_ad.id'));
            $this->providerAdImmutableId = $this->providerAd->getCurrentImmutableId();
            $this->addObject($this->testUser);
            $this->addObject($this->testCompany);
            $this->addObject($this->testLocation);
            $this->addObject($this->providerAd);
        }
    }

    /**
     * @Given I have a valid service array
     */
    public function iHaveAValidServiceArray()
    {
        $arr = [];
        $arr['name'] = 'Tire rotation';
        $arr['company_id'] = $this->testCompany->getId();
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
            $this->testService = $this->norm->getService($this->getResponseFieldValue("payload.service.id"));
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
        $arr['id'] = $this->testService->getId();
        $arr['name'] = $this->testService->getName();
        $arr['company_id'] = $this->testCompany->getId();
        $arr['discounted_price'] = $this->testService->getDiscountedPrice();
        $arr['original_price'] = $this->testService->getOriginalPrice();
        $arr['mins_for_service'] = $this->testService->getMinsForService();
        $arr['mins_notice'] = $this->testService->getMinsNotice();

        $this->requestArray = array('payload' => $arr);

        $this->putData('service/' . $this->testService->getId());
    }

    /**
     * @Then The value of the :arg1 field of the test service is :arg2
     */
    public function theValueOfTheFieldOfTheTestServiceIs($arg1, $arg2)
    {
        $id = $this->testService->getId();
        $this->norm->invalidate($this->testService);

        $this->testService = $this->norm->getService($id);

        $this->assertEquals($this->testService->$arg1, $arg2, "The test service's $arg1 field is supposed to be $arg2 but actually is " . $this->testService->$arg1);
    }

    /**
     * @When I delete the test service
     */
    public function iDeleteTheTestService()
    {
        $this->deleteData('service/' . $this->testService->getId());
    }

    /**
     * @Given The test location has a test calendar
     */
    public function theTestLocationHasATestCalendar()
    {
        $calendar = new Calendar();
        $calendar->setName("Joe's Calendar");
        $calendar->setLocationId($this->testLocation->getId());
        $calendar->setCompanyId($this->testCompany->getId());
        $this->norm->create($calendar);

        $this->testCalendar = $calendar;
    }

    /**
     * @Given The test calendar has a test availability
     */
    public function theTestCalendarHasATestAvailability()
    {
        $availability = new Availability();
        $availability->setStart($this->getDate('tomorrow', 9, 0));
        $availability->setEnd($this->getDate('tomorrow', 12, 0));

        $this->testCalendar->addToAvailabilities($availability);
        $this->norm->update($this->testCalendar);
    }

    /**
     * @Given I have a valid non-conflicting availability array
     */
    public function iHaveAValidNonConflictingAvailabilityArray()
    {
        $arr = [];
        $arr['calendar_id'] = $this->testCalendar->getId();
        $arr['start'] = $this->getDate('tomorrow', 13, 0)->format('c');
        $arr['end'] = $this->getDate('tomorrow', 14, 0)->format('c');

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
        $id = $this->testCalendar->getId();
        $this->norm->invalidate($this->testCalendar);

        $this->testCalendar = $this->norm->getCalendar($id);
    }

    /**
     * @Then The test calendar has :arg1 availabilities
     */
    public function theTestCalendarHasAvailabilities($arg1)
    {
        $this->assertEquals(count($this->testCalendar->getAvailabilities()), $arg1,
            'There were ' . count($this->testCalendar->getAvailabilities()) . ' availabilities when there should have  been ' . $arg1);
    }

    /**
     * @Given I have a valid availability array starting :arg1 at :arg2 until :arg3 at :arg4
     */
    public function iHaveAValidAvailabilityArrayStartingAtUntilAt($arg1, $arg2, $arg3, $arg4)
    {
        list($startHour, $startMinute) = explode(':', $arg2);
        list($endHour, $endMinute) = explode(':', $arg4);

        $arr = [];
        $arr['calendar_id'] = $this->testCalendar->getId();
        $arr['start'] = $this->getDate($arg1, $startHour, $startMinute)->format('c');
        $arr['end'] = $this->getDate($arg3, $endHour, $endMinute)->format('c');

        $this->requestArray = array('payload' => $arr);
    }

    /**
     * @Then The calendar's first availability starts :arg1 at :arg2 and ends :arg3 at :arg4
     */
    public function theCalendarSFirstAvailabilityStartsAtAndEndsAt($arg1, $arg2, $arg3, $arg4)
    {
        list($startHour, $startMinute) = explode(':', $arg2);
        list($endHour, $endMinute) = explode(':', $arg4);

        $this->assertEquals($this->testCalendar->getAvailabilities()[0]->start, $this->getDate($arg1, $startHour, $startMinute),
            "The first availability start time is incorrect: " . $this->testCalendar->getAvailabilities()[0]->start->format('c'));
        $this->assertEquals($this->testCalendar->getAvailabilities()[0]->end, $this->getDate($arg3, $endHour, $endMinute),
            "The first availability end time is incorrect: " . $this->testCalendar->getAvailabilities()[0]->end->format('c'));

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

        $this->testCalendar->addToAvailabilities($availability);
        $this->norm->update($this->testCalendar);
    }

    /**
     * @When I delete the availability
     */
    public function iDeleteTheAvailability()
    {
        $this->deleteData('availability');
    }

    /**
     * @Given Another user has a calendar
     */
    public function anotherUserHasACalendar()
    {
        $this->iGetANewSessionToken();
    }

    /**
     * @When I get the calendar data for the calendar
     */
    public function iGetTheCalendarDataForTheCalendar()
    {
        $this->getData('calendar/' . $this->calendar->getId());
    }

    /**
     * @Given The authenticated user has a calendar
     */
    public function theAuthenticatedUserHasACalendar()
    {
        //Nothing to do here, the creator is already authenticated
        return;
    }

    /**
     * @When I get the calendar data for myself
     */
    public function iGetTheCalendarDataForMyself()
    {
        $this->getData('calendar/' . $this->testCalendar->getId());
    }

    /**
     * @Given I have a valid new calendar array
     */
    public function iHaveAValidNewCalendarArray()
    {
        $arr = [];
        $arr['location_id'] = $this->testLocation->getId();
        $arr['name'] = 'My Second Calendar';

        $this->requestArray = array('payload' => $arr);
    }

    /**
     * @When I post the calendar data for the calendar
     */
    public function iPostTheCalendarDataForTheCalendar()
    {
        $this->postData('calendar');
    }

    /**
     * @When I delete the test calendar
     */
    public function iDeleteTheTestCalendar()
    {
        $this->deleteData('calendar/' . $this->testCalendar->getId());
    }

    /**
     * @When I put changes to the test calendar's name field to :arg1
     */
    public function iPutChangesToTheTestCalendarSNameFieldTo($arg1)
    {
        $arr['name'] = $arg1;
        $this->requestArray = array("payload" => $arr);
        $this->putData('calendar/' . $this->testCalendar->getId());
    }

    /**
     * @Given I have a valid booking array starting :arg1 at :arg2 until :arg3 at :arg4
     */
    public function iHaveAValidBookingArrayStartingAtUntilAt($arg1, $arg2, $arg3, $arg4)
    {
        list($startHour, $startMinute) = explode(':', $arg2);
        list($endHour, $endMinute) = explode(':', $arg4);

        $arr = [];
        $arr['type'] = 'system';
        $arr['provider_ad_immutable_id'] = $this->providerAdImmutableId;
        $arr['service_id'] = $this->testCompany->getServiceIds()[0];
        $arr['starting_at'] = $this->getDate($arg1, $startHour, $startMinute)->format('c');
        $arr['ending_at'] = $this->getDate($arg3, $endHour, $endMinute)->format('c');
        $arr['stripe_token'] = 'token';

        $this->requestArray = ['payload' => $arr];
    }

    /**
     * @When I post the booking array
     */
    public function iPostTheBookingArray()
    {
        $this->postData('booking');

        if($this->response->getStatusCode() === 200) {
            $this->testBooking = $this->norm->getBooking($this->getResponseFieldValue('payload.booking.id'));
            $this->addObject($this->testBooking);
        }
    }

    /**
     * @Given I have a valid comment array
     */
    public function iHaveAValidCommentArray()
    {
        $arr = [];
        $arr['company_id'] = $this->testCompany->getId();
        $arr['rating'] = 4;
        $arr['comment'] = 'This is a test comment';

        $this->requestArray = ['payload' => $arr];
    }

    /**
     * @When I post the comment array
     */
    public function iPostTheCommentArray()
    {
        $this->postData('comment');
    }

    /**
     * @Then the test company has :arg1 comment
     */
    public function theTestCompanyHasComment($arg1)
    {
        $companyId = $this->testCompany->getId();
        $this->norm->invalidate($this->testCompany);
        $this->testCompany = $this->norm->getCompany($companyId);

        $this->assertEquals($arg1, $this->testCompany->getRatingCount(), 'The test company should have ' . $arg1
            . ' comments but actually has ' . $this->testCompany->getRatingCount() . ' comments');
    }

    /**
     * @When I get the test booking
     */
    public function iGetTheTestBooking()
    {
        $this->getData('booking/' . $this->testBooking->getId());
    }

    /**
     * @When I delete the test booking
     */
    public function iDeleteTheTestBooking()
    {
        $this->requestArray = ['payload'=>null];
        $this->deleteData('booking/' . $this->testBooking->getId());
    }

    /**
     * @When I get the test company's comments
     */
    public function iGetTheTestCompanySComments()
    {
        $this->getData('comment/' . $this->testCompany->getId());
    }

}

