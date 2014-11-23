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
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\KernelInterface;
use Guzzle\Http\Exception\ClientException;

class AbstractFeatureContext {
    // Setup the container using the KernelDictionary trait
    // ex:  $this->getContainer()->get('angry_chimps_api.auth')->registerFbUser()
    use KernelDictionary;

    protected $myKernel;

    /** @var \Guzzle\Service\Client  */
    protected $guzzle;

    /** @var  array */
    protected $requestArray;

    /** @var Response  */
    protected $response;

    protected $authToken;
    protected $phpSessionId;
    protected $userId;
    protected $rand;

    private $objects = array();

    public function __construct() {
        $this->rand = rand(1,99999999999999999999999999);
    }

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
        $this->guzzle = $this->getContainer()->get('guzzle.client');
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

    protected function postData($url) {
        try {
            $request = $this->guzzle->post($url, array('content-type' => 'application/json'),
                null, array('exceptions' => false));
            $request->setBody(json_encode($this->requestArray));


            $this->response = $request->send();
        }
        catch(ClientException $ex) {
            //Ignore this exception, we'll test the return status separately
        }
    }

    /**
     * @return \AngryChimps\ApiBundle\Services\AuthService
     */
    protected function getAuthService() {
        return $this->getContainer()->get('angry_chimps_api.auth');
    }

} 