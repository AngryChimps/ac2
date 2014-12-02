<?php


namespace AngryChimps\ApiBundle\Services;


use AngryChimps\GeoBundle\Services\GeolocationService;
use AngryChimps\MailerBundle\Messages\BasicMessage;
use AngryChimps\MailerBundle\Services\MailerService;
use Armetiz\FacebookBundle\FacebookSessionPersistence;
use Norm\riak\Availability;
use Norm\riak\Company;
use Norm\riak\Location;
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

    /** @var AuthService */
    protected $authService;

    /** @var GeolocationService */
    protected $geolocationService;

    public function __construct(MemberService $memberService, CompanyService $companyService,
                                LocationService $locationService, AdService $adService,
                                CalendarService $calendarService, ServiceService $serviceService,
                                AuthService $authService, GeolocationService $geolocationService)
    {
        $this->memberService = $memberService;
        $this->companyService = $companyService;
        $this->locationService = $locationService;
        $this->adService = $adService;
        $this->calendarService = $calendarService;
        $this->serviceService = $serviceService;
        $this->authService = $authService;
        $this->geolocationService = $geolocationService;
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
        //No need to check for overlaps since it's a new calendar
        $this->calendarService->addAvailability($calendar, $availability);

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

    public function registerProviderCompany(Member $member, Company $company, Location $location,
                                            $companyName, $memberName, $email, $password,
                                            \DateTime $dob, $street1, $street2, $zip, $phone) {
        $company->name = $companyName;
        $company->save();

        $member->name = $memberName;
        $member->email = $email;
        $member->password = $this->authService->hashPassword($password);
        $member->dob = $dob;
        $member->save();

        $address = $this->geolocationService->lookupAddress($street1, $street2, $zip);
        $location->street1 = $street1;
        $location->street2 = $street2;
        $location->zip = $zip;
        $location->phone = $phone;
        $location->city = $address->city;
        $location->state = $address->state;
        $location->lat = $address->lat;
        $location->long = $address->long;
        $location->save();
    }
 }