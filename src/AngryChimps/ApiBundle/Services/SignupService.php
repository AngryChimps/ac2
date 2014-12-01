<?php


namespace AngryChimps\ApiBundle\Services;


use AngryChimps\MailerBundle\Messages\BasicMessage;
use AngryChimps\MailerBundle\Services\MailerService;
use Armetiz\FacebookBundle\FacebookSessionPersistence;
use Norm\riak\Availability;
use Norm\riak\Company;
use Norm\riak\Member;
use Norm\riak\Service;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SignupService {
    /** @var  MemberService */
    protected $memberService;

    /** @var  CompanyService */
    protected $companyService;

    /** @var  LocationService */
    protected $locationService;

    /** @var  AdService */
    protected $adService;

    /** @var  CalendarService */
    protected $calendarService;

    /** @var  ServiceService */
    protected $serviceService;

    public function __construct(MemberService $memberService, CompanyService $companyService,
                                LocationService $locationService, AdService $adService,
                                CalendarService $calendarService, ServiceService $serviceService)
    {
        $this->memberService = $memberService;
        $this->companyService = $companyService;
        $this->locationService = $locationService;
        $this->adService = $adService;
        $this->calendarService = $calendarService;
        $this->serviceService = $serviceService;
    }

    public function registerProviderAd($adTitle, $adDescription, $calendarName, \DateTime $start, \DateTime $end,
                                       $serviceName, $serviceDescription, $discountedPrice, $originalPrice,
                                       $minsForService, $minsNotice, $categoryId) {

        $member = $this->memberService->createEmpty();
        $company = $this->companyService->createEmpty($member);
        $location = $this->locationService->createEmpty($company);
        $calendar = $this->calendarService->createNew($location, $calendarName);

        $availability = new Availability();
        $availability->start = $start;
        $availability->end = $end;
        $calendar->availabilities[] = $availability;
        $calendar->save();

        $service = new Service();
        $service->name = $serviceName;
        $service->description = $serviceDescription;
        $service->discountedPrice = $discountedPrice;
        $service->originalPrice = $originalPrice;
        $service->minsForService = $minsForService;
        $service->minsNotice = $minsNotice;
        $service->categoryId = $categoryId;
        $company->getCompanyServicesCollection()[$service->id] = $service;
        $company->save();

        $ad = $this->adService->create($adTitle, $adDescription, $company, $location, $calendar);

        return array(
            'member' => $member,
            'company' => $company,
            'location' => $location,
            'service' => $service,
            'ad' => $ad,
        );
    }

    public function registerProviderCompany(Company $company, $companyName, $memberName,
                                            $email, $password, \DateTime $dob, $street1, $street2, $zip, $phone) {
        $company->name = $companyName;
        $company-
    }
 }