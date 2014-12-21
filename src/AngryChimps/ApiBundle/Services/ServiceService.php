<?php


namespace AngryChimps\ApiBundle\Services;

use Norm\riak\Member;
use Norm\riak\Service;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use AngryChimps\NormBundle\realms\Norm\mysql\services\NormMysqlService;
use AngryChimps\NormBundle\realms\Norm\riak\services\NormRiakService;

class ServiceService {
    /** @var \Symfony\Component\Validator\Validator\ValidatorInterface */
    protected $validator;

    /** @var  NormRiakService */
    protected $riak;

    /** @var  NormMysqlService */
    protected $mysql;

    public function __construct(ValidatorInterface $validator, NormRiakService $riak, NormMysqlService $mysql) {
        $this->validator = $validator;
        $this->riak = $riak;
        $this->mysql = $mysql;
    }

    public function createService($name, $companyId, $discountedPrice,
                                  $originalPrice, $minsForService, $minsNotice, $category,
                                  array $errors)
    {
        $service = new Service();
        $service->name = $name;
        $service->companyId = $companyId;
        $service->discountedPrice = $discountedPrice;
        $service->originalPrice = $originalPrice;
        $service->minsForService = $minsForService;
        $service->minsNotice = $minsNotice;
        $service->category = $category;

        $errors = $this->validator->validate($service);
        if(count($errors) > 0) {
            return false;
        }

        $this->riak->create($service);

        return $service;
    }

    public function updateService(Service $service, $name, $discountedPrice,
                                  $originalPrice, $minsForService, $minsNotice, $category,
                                  array $errors)
    {
        $service->name = $name;
        $service->discountedPrice = $discountedPrice;
        $service->originalPrice = $originalPrice;
        $service->minsForService = $minsForService;
        $service->minsNotice = $minsNotice;
        $service->category = $category;

        $errors = $this->validator->validate($service);
        if(count($errors) > 0) {
            return false;
        }

        $this->riak->update($service);

        return $service;
    }

    public function getService($id) {
        return $this->riak->getService($id);
    }

    public function markServiceDeleted(Service $service) {
        $service->status = Service::DISABLED_STATUS;
        $this->riak->update($service);
    }
} 