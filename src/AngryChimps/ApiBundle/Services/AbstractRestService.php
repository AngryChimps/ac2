<?php


namespace AngryChimps\ApiBundle\Services;


use AC\NormBundle\core\Utils;
use AC\NormBundle\services\InfoService;
use AngryChimps\NormBundle\services\NormService;
use Norm\Member;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractRestService
{
    /** @var  NormService */
    protected $norm;

    /** @var  InfoService */
    protected $infoService;

    /** @var ValidatorInterface */
    protected $validator;

    abstract public function isOwner($obj, Member $authenticatedMember);

    public function __construct(NormService $norm, InfoService $infoService, ValidatorInterface $validator) {
        $this->norm = $norm;
        $this->infoService = $infoService;
        $this->validator = $validator;
    }

    public function get($endpoint, $id) {
        $func = 'get' . ucfirst($endpoint);
        $obj = $this->norm->$func($id);
        $class = get_class($obj);

        if(method_exists($obj, 'getStatus') && $obj->getStatus() == $class::DELETED_STATUS) {
            return null;
        }

        return $obj;
    }

    public function post($endpoint, $data, $additionalData = []) {
        $class = $this->infoService->getClassName($endpoint);
        $obj = new $class();

        foreach($data as $field => $value) {
            $this->setField($obj, $field, $value);
        }

        $errors = $this->validator->validate($obj);

        if(count($errors) > 0) {
            return false;
        }

        foreach($additionalData as $field => $value) {
            $this->setField($obj, $field, $value);
        }

        $this->norm->create($obj);

        return $obj;
    }

    public function patch($obj, $data) {
        foreach($data as $field => $value) {
            $this->setField($obj, $field, $value);
        }

        $errors = $this->validator->validate($obj);

        if(count($errors) > 0) {
            return false;
        }

        $this->norm->update($obj);

        return $obj;
    }

    public function delete($obj) {
        $class = get_class($obj);

        $obj->setStatus($class::DELETED_STATUS);
        $this->norm->update($obj);

        return $obj;
    }

    protected function setField($obj, $fieldName, $value) {
        $func = 'set' . Utils::field2property($fieldName);
        $obj->$func($value);
    }

    public function getApiPublicArray($obj) {
        return $this->norm->getApiPublicArray($obj);
    }

    public function getApiPrivateArray($obj) {
        return $this->norm->getApiPrivateArray($obj);
    }
}