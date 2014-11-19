<?php


namespace AngryChimps\ApiBundle\Services;

use Norm\riak\Member;
use Norm\riak\Service;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ServiceService {
    /** @var \Symfony\Component\Validator\Validator\ValidatorInterface */
    protected $validator;

    public function __construct(ValidatorInterface $validator) {
        $this->validator = $validator;
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

        $service->save();

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

        $service->save();

        return $service;
    }

} 