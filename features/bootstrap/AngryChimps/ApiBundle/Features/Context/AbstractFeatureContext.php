<?php


namespace AngryChimps\ApiBundle\Features\Context;

use Behat\Symfony2Extension\Context\KernelDictionary;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use AngryChimps\ApiBundle\Services\MemberService;
use AngryChimps\NormBundle\services\NormService;
use GuzzleHttp\Client;
use Guzzle\Http\Message\Response;

class AbstractFeatureContext
{
    // Setup the container using the KernelDictionary trait
    // ex:  $this->getContainer()->get('angry_chimps_api.auth')->registerFbUser()
    use KernelDictionary;

    /** @var  KernelInterface */
    protected $myKernel;

    /** @var \GuzzleHttp\Client  */
    protected $guzzle;

    /** @var Response  */
    protected $response;

    private $baseUrl;
    private $sessionHeaderName;
    private $objects = array();
    protected $requestArray;
    protected $authenticatedUserId;
    protected $sessionId;


    /** @var  NormService */
    protected $norm;

    /** @var  MemberService */
    protected $memberService;

    /** @var array  */
    private $variables = [];

    public function setKernel(KernelInterface $kernel)
    {
        $this->myKernel = $kernel;
        $this->guzzle = new Client();
        $this->sessionHeaderName = $this->getContainer()->getParameter('angry_chimps_api.session_header_name');
        $this->baseUrl = $this->getContainer()->getParameter('angry_chimps_api.base_url');
        $this->norm = $this->getContainer()->get('ac_norm.norm');
        $this->memberService = $this->getContainer()->get('angry_chimps_api.member');
    }

    protected function addObject($obj) {
        $this->objects[] = $obj;
    }

    protected function cleanUpObjects() {
        foreach($this->objects as $obj) {
            if(is_object($obj)) {
//                $obj->delete();
            }
        }
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

    protected function setVariable($name, $value) {
        $this->variables[$name] = $value;
    }

    protected function getVariable($name) {
        return $this->variables[$name];
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

    protected function getSampleRequestArray($endpoint, $method) {
        $json = file_get_contents(__DIR__ . '/../../../../../../src/AngryChimps/ApiSampleBundle/apis/' . $endpoint
            . '/' . $method . '/input.json');
        return json_decode($json, true);
    }

    protected function getResponseFieldValue($field) {
        $parts = explode('.', $field);

        $arr = $this->getContentArray();
        for($i=0; $i < count($parts) - 1; $i++) {
            $arr = $arr[$parts[$i]];
        }

        return $arr[$parts[count($parts) - 1]];
    }

    protected function getRequestFieldValue($field) {
        $parts = explode('.', $field);

        $arr = $this->requestArray;
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
            case 'array':
                if(!is_array($value)) {
                    throw new \Exception('The value for the ' . $field . ' field is not of type ' . $type);
                }
        }
    }

    protected function ensureResponseHasFieldValueStringLength($field, $length) {
        $value = $this->getResponseFieldValue($field);
        if(strlen($value) != $length) {
            throw new \Exception('The response field ' . $field . ' does not have a length of ' . $length);
        }
    }

    protected function ensureResponseFieldHasValue($field, $value) {
        if($value != $this->getResponseFieldValue($field)) {
            throw new \Exception('The response field ' . $field . ' does not have a value of ' . $value);
        }
    }

    public function assertTrue($arg1, $msg) {
        if(!$arg1) {
            echo 'Request: ' . print_r($this->requestArray, true);

            try {
                echo 'Response: ' . print_r($this->response->json(), true);
            } catch (\Exception $ex) {
                echo 'Response is not valid JSON';
            }

            throw new \Exception($msg);
        }
    }

    public function assertEquals($arg1, $arg2, $msg) {
        $this->assertTrue($arg1 == $arg2, $msg);
    }
    public function assertNotEquals($arg1, $arg2, $msg) {
        $this->assertTrue($arg1 != $arg2, $msg);
    }

    public function assertNotEmpty($arg1, $msg) {
        $this->assertTrue(!empty($arg1), $msg);
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
//dump((string) $this->response->getBody());
        }
        catch(\Exception $ex) {
            //Ignore this exception, we'll test the return status separately
        }
    }

    protected function patchData($url) {
        try {
            if($this->authenticatedUserId !== null){
                $url = $this->baseUrl . '/' . $url . '?userId=' . $this->authenticatedUserId;
            }
            else {
                $url = $this->baseUrl . '/' . $url;
            }

            $request = $this->guzzle->createRequest('PATCH', $url, [
                'headers' => [
                    $this->sessionHeaderName => $this->sessionId,
                    'content-type' => 'application/json'
                ],
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
                'json' => $this->requestArray,
                'exceptions' => false,
            ]);

            $this->response = $this->guzzle->send($request);
        }
        catch(\Exception $ex) {
            //Ignore this exception, we'll test the return status separately
        }
    }

}