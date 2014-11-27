<?php


namespace AngryChimps\ApiBundle\Features\Context;

use AngryChimps\ApiBundle\Services\GuzzleService;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Guzzle\Common\Event;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Norm\riak\Member;
use Guzzle\Http\Message\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\KernelInterface;

class AbstractFeatureContext {
    // Setup the container using the KernelDictionary trait
    // ex:  $this->getContainer()->get('angry_chimps_api.auth')->registerFbUser()
    use KernelDictionary;

    protected $myKernel;

    /** @var \GuzzleHttp\Client  */
    protected $guzzle;

    /** @var  array */
    protected $requestArray;

    /** @var Response  */
    protected $response;

    protected $authToken;
    protected $sessionId;
    protected $authenticatedUserId;
    protected $rand;

    /** @var  \Norm\riak\Member */
    protected $testUser;

    private $baseUrl;
    private $sessionHeaderName;

    private $objects = array();

    protected function addObject($obj) {
        $this->objects[] = $obj;
    }

    protected function cleanUpObjects() {
        foreach($this->objects as $obj) {
            if(is_object($obj)) {
                $obj->delete();
                $obj->invalidate();
            }
        }
    }

    public function setKernel(KernelInterface $kernel)
    {
        $this->myKernel = $kernel;
        $this->guzzle = new Client();
        $this->sessionHeaderName = $this->getContainer()->getParameter('angry_chimps_api.session_header_name');
        $this->baseUrl = $this->getContainer()->getParameter('angry_chimps_api.base_url');
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

    protected function getRequestArray() {
        return $this->requestArray;
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

    protected function setRequestFieldValue($field, $value) {
        $parts = explode('.', $field);

        switch(count($parts)) {
            case 0:
                throw new \Exception('getRequestFieldValue: invalid value for field');
            case 1:
                $this->requestArray[$parts[0]] = $value;
                break;
            case 2:
                $this->requestArray[$parts[0]][$parts[1]] = $value;
                break;
            case 3:
                $this->requestArray[$parts[0]][$parts[1]][$parts[2]] = $value;
                break;
            case 4:
                $this->requestArray[$parts[0]][$parts[1]][$parts[2]][$parts[3]] = $value;
                break;
            case 5:
                $this->requestArray[$parts[0]][$parts[1]][$parts[2]][$parts[3]][$parts[4]] = $value;
                break;
            case 6:
                $this->requestArray[$parts[0]][$parts[1]][$parts[2]][$parts[3]][$parts[4]][$parts[5]] = $value;
                break;
            default:
                throw new \Exception('getRequestFieldValue: too many levels in field value');
        }
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

    protected function ensureResponseDoesNotHaveField($fieldName) {
        $parts = explode('.', $fieldName);

        $arr = $this->getContentArray();
        for($i=0; $i < count($parts) - 1; $i++) {
            $arr = $arr[$parts[$i]];
        }

        if(array_key_exists($parts[count($parts) - 1], $arr)) {
            throw new \Exception('Response object has the field: ' . $fieldName);
        }
    }

    protected function ensureResponseHasFieldType($field, $type) {
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

    protected function getData($url) {
        try {
            if($this->authenticatedUserId !== null){
                $url = $this->baseUrl . '/' . $url . '?userId=' . $this->authenticatedUserId;
            }
            else {
                $url = $this->baseUrl . '/' . $url;
            }

            $request = $this->guzzle->createRequest('GET', $url, [
                'headers' => [
                    $this->sessionHeaderName => $this->sessionId,
                    'content-type' => 'application/json',
                ],
                'exceptions' => false,
            ]);

            $this->response = $this->guzzle->send($request);
        }
        catch(\Exception $ex) {
            //Ignore this exception, we'll test the return status separately
        }
    }

    protected function postData($url) {
        try {
            if($this->authenticatedUserId !== null){
                $url = $this->baseUrl . '/' . $url . '?userId=' . $this->authenticatedUserId;
            }
            else {
                $url = $this->baseUrl . '/' . $url;
            }

            $request = $this->guzzle->createRequest('POST', $url, [
                'headers' => [$this->sessionHeaderName => $this->sessionId,
                    'content-type' => 'application/json'],
                'json' => $this->requestArray,
                'exceptions' => false,
            ]);

            $this->response = $this->guzzle->send($request);
        }
        catch(\Exception $ex) {
            //Ignore this exception, we'll test the return status separately
        }
    }

    protected function putData($url) {
        try {
            if($this->authenticatedUserId !== null){
                $url = $this->baseUrl . '/' . $url . '?userId=' . $this->authenticatedUserId;
            }
            else {
                $url = $this->baseUrl . '/' . $url;
            }

            $request = $this->guzzle->createRequest('PUT', $url, [
                'headers' => [$this->sessionHeaderName => $this->sessionId,
                    'content-type' => 'application/json'],
                'json' => $this->requestArray,
                'exceptions' => false,
            ]);

            $this->response = $this->guzzle->send($request);
        }
        catch(\Exception $ex) {
            //Ignore this exception, we'll test the return status separately
        }
    }

    protected function deleteData($url) {
        try {
            if($this->authenticatedUserId !== null){
                $url = $this->baseUrl . '/' . $url . '?userId=' . $this->authenticatedUserId;
            }
            else {
                $url = $this->baseUrl . '/' . $url;
            }

            $request = $this->guzzle->createRequest('DELETE', $url, [
                'headers' => [$this->sessionHeaderName => $this->sessionId,
                    'content-type' => 'application/json'],
                'exceptions' => false,
            ]);

            $this->response = $this->guzzle->send($request);
        }
        catch(\Exception $ex) {
            //Ignore this exception, we'll test the return status separately
        }
    }

    /**
     * @return \AngryChimps\ApiBundle\Services\AuthService
     */
    protected function getAuthService() {
        return $this->getContainer()->get('angry_chimps_api.auth');
    }

    public function displayError(Event $e) {
        print_r($this->response->getBody());
    }

} 