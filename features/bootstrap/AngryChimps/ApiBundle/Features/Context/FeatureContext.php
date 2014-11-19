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
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context, SnippetAcceptingContext, KernelAwareContext
{
    // Setup the container using the KernelDictionary trait
    // ex:  $this->getContainer()->get('angry_chimps_api.auth')->registerFbUser()
    use KernelDictionary;

    protected $myKernel;

    /** @var \Guzzle\Service\Client  */
    protected $guzzle;

    /** @var Response  */
    protected $response;

    protected $requestPayloadArray;

    public function setKernel(KernelInterface $kernel)
    {
        $this->myKernel = $kernel;
        $this->guzzle = $this->getContainer()->get('guzzle.client');
//        $this->guzzle = $this->getContainer()->get('angry_chimps_api.guzzle');
        $this->guzzle->setBaseUrl($this->getContainer()->getParameter('angry_chimps_api.base_url'));
    }

    /**
     * Returns HttpKernel service container.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->myKernel->getContainer();
    }

    protected function getContentArray() {
        return $this->response->json();
    }

    protected function getPayloadArray() {
        return $this->response->json()['payload'];
    }

    protected function getResponseFieldValue($field) {
        $parts = explode('.', $field);

        $arr = $this->getContentArray();
        for($i=0; $i < count($parts) - 1; $i++) {
            $arr = $arr[$parts[$i]];
        }

        return $arr[$parts[count($parts) - 1]];
    }

    protected function ensureResponseHasField($fieldName) {
        $parts = explode('.', $fieldName);

        $arr = $this->getContentArray();
        for($i=0; $i < count($parts) - 1; $i++) {
            $arr = $arr[$parts[$i]];
        }

        if(!array_key_exists($parts[count($parts) - 1], $arr)) {
            throw new \Exception('Response object does not have field: ' . $fieldName);
        }
    }

    protected function ensureReponseHasFieldType($field, $type) {
        $value = $this->getResponseFieldValue($field);

        switch($type) {
            case 'int':
                if(!is_numeric($value) || $value != intval($value)) {
                    throw new \Exception('The value for the ' . $field . ' field is not of type ' . $type);
                }
                break;
            case 'float':
                if(!is_numeric($value) || $value != floatval($value)) {
                    throw new \Exception('The value for the ' . $field . ' field is not of type ' . $type);
                }
                break;
            case 'string':
                if(is_numeric($value)) {
                    throw new \Exception('The value for the ' . $field . ' field is not of type ' . $type);
                }
                break;
        }
    }

    protected function ensureResponseHasFieldValueStringLength($field, $length) {
        $value = $this->getResponseFieldValue($field);
        if(strlen($value) != $length) {
            throw new \Exception('The response field ' . $field . ' does not have a length of ' . $length);
        }
    }

    protected function postData($url) {
        $request = $this->guzzle->post($url, array( 'content-type' => 'application/json' ));
        $request->setBody(json_encode(array('payload' => $this->requestPayloadArray)));
        $this->response = $request->send();
    }

    /**
     * @Given I have a valid new user object
     */
    public function iHaveAValidNewUserObject()
    {
        $member = array();
        $member['name'] = 'Joe Smith';
        $member['email'] = 'trash@seangallavan.com';
        $member['password'] = 'abcdabcd';
        $member['dob'] = '1950-01-01';

        $this->requestPayloadArray = $member;
    }

    /**
     * @When I register a new user
     */
    public function iRegisterANewUser()
    {
        $this->postData('member');
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
        $this->ensureReponseHasFieldType($arg1, $arg2);
    }

    /**
     * @Then The string length of the :arg1 field is :arg2
     */
    public function theStringLengthOfTheFieldIs($arg1, $arg2)
    {
        $this->ensureResponseHasFieldValueStringLength($arg1, $arg2);
    }
}

