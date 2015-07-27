<?php


namespace AngryChimps\ApiBundle\services;

use Norm\norm\Member;
use Norm\norm\Service;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use AngryChimps\NormBundle\services\NormService;

class ServiceService {
    /** @var \Symfony\Component\Validator\Validator\ValidatorInterface */
    protected $validator;

    /** @var  NormService */
    protected $norm;

    /** @var CompanyService a */
    protected $companyService;

    public function __construct(ValidatorInterface $validator, NormService $riak, CompanyService $companyService) {
        $this->validator = $validator;
        $this->norm = $riak;
        $this->companyService = $companyService;
    }

    public function createService($name, $companyId, $discountedPrice,
                                  $originalPrice, $minsForService, $minsNotice, array $errors)
    {
        $service = new Service();
        $service->name = $name;
        $service->companyId = $companyId;
        $service->discountedPrice = $discountedPrice;
        $service->originalPrice = $originalPrice;
        $service->minsForService = $minsForService;
        $service->minsNotice = $minsNotice;

        $errors = $this->validator->validate($service);
        if(count($errors) > 0) {
            return false;
        }

        $this->norm->create($service);

        //Add to services list in company
        $company = $this->norm->getCompany($companyId);
        $company->serviceIds[] = $service->id;
        $this->norm->update($company);

        return $service;
    }

    public function updateService(Service $service, $name, $discountedPrice,
                                  $originalPrice, $minsForService, $minsNotice,
                                  array $errors)
    {
        $service->name = $name;
        $service->discountedPrice = $discountedPrice;
        $service->originalPrice = $originalPrice;
        $service->minsForService = $minsForService;
        $service->minsNotice = $minsNotice;

        $errors = $this->validator->validate($service);
        if(count($errors) > 0) {
            return false;
        }

        $this->norm->update($service);

        return $service;
    }

    /**
     * @param $id
     * @return Service
     */
    public function getService($id) {
        return $this->norm->getService($id);
    }

    public function markServiceDeleted(Service $service) {
        $service->status = Service::DISABLED_STATUS;
        $this->norm->update($service);

        //remove from list of services
        $company = $this->companyService->getByPk($service->companyId);
        for($i=0; count($company->serviceIds) < $i; $i++) {
            if($company->serviceIds[$i] == $service->id) {
                    $index = $i;
            }
        }

        $serviceIds1 =  array_slice($company->serviceIds, 0, count($company->serviceIds));
        $serviceIds2 = array_slice($company->serviceIds, count($company->serviceIds),
            count($company->serviceIds) - $index);
        $company->serviceIds = array_merge($serviceIds1, $serviceIds2);

        //Add to list of deleted services
        $company->serviceDeletedIds[] = $service->id;
        $this->norm->update($company);

    }
} 