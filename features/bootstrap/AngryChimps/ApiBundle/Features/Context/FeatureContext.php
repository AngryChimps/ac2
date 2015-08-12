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
        $this->requestArray['payload']['email'] = 'email' . rand(1, 1000000000) . '@seangallavan.com';
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
     * @When I send a :arg1 message to the :arg2 api
     */
    public function iSendAMessageToTheApi($arg1, $arg2)
    {
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

    /**
     * @When I create a new company
     */
    public function iCreateANewCompany()
    {
        $this->requestArray = $this->getSampleRequestArray('company', 'post');
        $this->postData('company');
        $this->setVariable('company.id', $this->getResponseFieldValue('payload.company.id'));
    }

    /**
     * @When I get the :arg1 data for id :arg2
     */
    public function iGetTheDataForId($arg1, $arg2)
    {
        $this->getData($arg1 . '/' . $this->getVariable($arg2));
    }

    /**
     * @When I create a new location
     */
    public function iCreateANewLocation()
    {
        $this->requestArray = $this->getSampleRequestArray('location', 'post');
        $this->requestArray['payload']['company_id'] = $this->getVariable('company.id');
        $this->postData('location');
        $this->setVariable('location.id', $this->getResponseFieldValue('payload.location.id'));
    }

    /**
     * @When I create a new staff member
     */
    public function iCreateANewStaffMember()
    {
        $this->requestArray = $this->getSampleRequestArray('staff', 'post');
        $this->requestArray['payload']['company_id'] = $this->getVariable('company.id');
        $this->requestArray['payload']['location_ids'] = [$this->getVariable('location.id')];
        $this->postData('staff');
        $this->setVariable('staff.id', $this->getResponseFieldValue('payload.staff.id'));
    }

    /**
     * @When I get the :arg1 data with GET param :arg2 and id :arg3
     */
    public function iGetTheDataWithGetParamAndId($arg1, $arg2, $arg3)
    {
        $this->getData($arg1, [$arg2 => $this->getVariable($arg3)]);
    }

    /**
     * @Then The response field :arg1 has a count of :arg2
     */
    public function theResponseFieldHasACountOf($arg1, $arg2)
    {
        $this->ensureResponseFieldHasCount($arg1, $arg2);
    }

    /**
     * @When I create a new review
     */
    public function iCreateANewReview()
    {
        $this->requestArray = $this->getSampleRequestArray('review', 'post');
        $this->requestArray['payload']['location_id'] = $this->getVariable('location.id');
        $this->requestArray['payload']['staff_ids'] = [$this->getVariable('staff.id')];
        $this->postData('review');
        $this->setVariable('review.id', $this->getResponseFieldValue('payload.review.id'));
    }

    /**
     * @Given I wait :arg1 seconds
     */
    public function iWaitSeconds($arg1)
    {
        sleep($arg1);
    }

    /**
     * @When I get the :arg1 data for id :arg2 with GET param string :arg3
     */
    public function iGetTheDataForIdWithGetParamString($arg1, $arg2, $arg3)
    {
        $this->getData($arg1 . '/' . $this->getVariable($arg2), $arg3);
    }

    /**
     * @Then The response fields are shown in the documentation for the :arg1 entity :arg2 method
     */
    public function theResponseFieldsAreShownInTheDocumentationForTheEntityMethod($arg1, $arg2)
    {
        $documentedResponse = $this->getSampleResponseArray($arg1, $arg2);

        foreach($this->getPayloadArray() as $entityName => $entityData) {
            if(!isset($documentedResponse['payload'][$entityName])) {
                throw new \Exception('Documentation does not have a payload object named ' . $entityName);
            }

            foreach($entityData as $fieldName => $val) {
                if(gettype($documentedResponse['payload'][$entityName][$fieldName]) !== gettype($val)) {
                    throw new \Exception('The payload object ' . $entityName . ' field ' . $fieldName . "doesn't match the documented type");
                }
                if(gettype($val) === 'array') {
                    $isSubclass = false;
                    foreach($val as $value) {
                        if(is_array($value)) {
                            $isSubclass = true;
                        }
                    }
                    if($isSubclass) {
                        $this->ensureDocumentedArrayMatchesReturnedArray($val, $documentedResponse['payload'][$entityName][$fieldName]);
                    }
                    else {
                        if(gettype($val[0]) !== gettype($documentedResponse['payload'][$entityName][$fieldName][0])) {
                            throw new \Exception('The payload object ' . $entityName . ' field ' . $fieldName . "'s values types don't match the documented types");
                        }
                    }
                }
            }

        }
    }

    protected function ensureDocumentedArrayMatchesReturnedArray(array $returned, array $documented) {
       foreach($returned as $key => $val) {
           if(!isset($documented[$key])) {
               throw new \Exception('The documented array is missing field: ' . $key);
           }
           if(gettype($val) !== gettype($documented[$key])) {
               throw new \Exception('The documented array has a different type for field: ' . $key);
           }
           if(is_array($val)) {
               foreach($val as $value) {
                   if(is_array($value)) {
                       $this->ensureDocumentedArrayMatchesReturnedArray($val, $documented[$key]);
                   }
               }
           }
       }
    }

    /**
     * @Then No undocumented fields are returned in the response for the :arg1 entity :arg2 method
     */
    public function noUndocumentedFieldsAreReturnedInTheResponseForTheEntityMethod($arg1, $arg2)
    {
        $documentedResponse = $this->getSampleResponseArray($arg1, $arg2);
        $payloadArray = $this->getPayloadArray();

        foreach($documentedResponse['payload'] as $entityName => $entityData) {
            if(!isset($payloadArray[$entityName])) {
                throw new \Exception('Response is missing payload object: ' . $entityName);
            }

            foreach($entityData as $fieldName => $val) {
                if(gettype($payloadArray[$entityName][$fieldName]) !== gettype($val)) {
                    throw new \Exception('The payload object ' . $entityName . ' field ' . $fieldName . "doesn't match the documented type");
                }
                if(gettype($val) === 'array') {
                    $isSubclass = false;
                    foreach($val as $value) {
                        if(is_array($value)) {
                            $isSubclass = true;
                        }
                    }
                    if($isSubclass) {
                        $this->ensureReturnedArrayMatchesDocumentedArray($payloadArray[$entityName][$fieldName], $val);
                    }
                    else {
                        if(gettype($val[0]) !== gettype($payloadArray[$entityName][$fieldName][0])) {
                            throw new \Exception('The payload object ' . $entityName . ' field ' . $fieldName . "'s values types don't match the documented types");
                        }
                    }
                }
            }

        }
    }

    protected function ensureReturnedArrayMatchesDocumentedArray(array $returned, array $documented) {
        foreach($documented as $key => $val) {
            if(!isset($returned[$key])) {
                throw new \Exception('The returned array is missing field: ' . $key);
            }
            if(gettype($val) !== gettype($returned[$key])) {
                throw new \Exception('The returned array has a different type for field: ' . $key);
            }
            if(is_array($val)) {
                foreach($val as $value) {
                    if(is_array($value)) {
                        $this->ensureReturnedArrayMatchesDocumentedArray($val, $documented[$key]);
                    }
                }
            }
        }
    }

    /**
     * @When I search for locations
     */
    public function iSearchForLocations()
    {
        $this->requestArray = $this->getSampleRequestArray('search', 'post');
        $this->postData('search');
    }
}