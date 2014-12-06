<?php


namespace AngryChimps\ApiBundle\Services;


use AngryChimps\GeoBundle\Classes\Address;
use AngryChimps\GeoBundle\Services\GeolocationService;
use AngryChimps\MailerBundle\Messages\BasicMessage;
use AngryChimps\MailerBundle\Services\MailerService;
use Armetiz\FacebookBundle\FacebookSessionPersistence;
use Norm\riak\Availability;
use Norm\riak\Company;
use Norm\riak\CompanyAds;
use Norm\riak\CompanyServices;
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

    /** @var  ProviderAdService */
    protected $adService;

    /** @var  CalendarService */
    protected $calendarService;

    /** @var  ServiceService */
    protected $serviceService;

    /** @var AuthService */
    protected $authService;

    /** @var GeolocationService */
    protected $geolocationService;

    /** @var SessionService */
    protected $sessionService;

    /** @var \Symfony\Component\Validator\Validator\ValidatorInterface */
    protected $validator;

    public function __construct(MemberService $memberService, CompanyService $companyService,
                                LocationService $locationService, ProviderAdService $adService,
                                CalendarService $calendarService, ServiceService $serviceService,
                                AuthService $authService, ValidatorInterface $validator,
                                SessionService $sessionService)
    {
        $this->memberService = $memberService;
        $this->companyService = $companyService;
        $this->locationService = $locationService;
        $this->adService = $adService;
        $this->calendarService = $calendarService;
        $this->serviceService = $serviceService;
        $this->authService = $authService;
        $this->validator = $validator;
        $this->sessionService = $sessionService;
    }

    public function registerProviderAd($adTitle, $adDescription, \DateTime $start, \DateTime $end,
                                       $serviceName, $discountedPrice, $originalPrice,
                                       $minsForService, $minsNotice, $categoryId, array &$errors) {

        $member = $this->memberService->createEmpty();
        $company = $this->companyService->createEmpty($member);
        $location = $this->locationService->createEmpty($company);
        $calendar = $this->calendarService->createNew($location, 'My First Calendar');

        $member->managedCompanyIds[] = $company->id;
        $member->save();

        $company->administerMemberIds[] = $member->id;
        $company->save();

        $availability = new Availability();
        $availability->start = $start;
        $availability->end = $end;
        //No need to check for overlaps since it's a new calendar
        $this->calendarService->addAvailability($calendar, $availability);

        $service = new Service();
        $service->name = $serviceName;
        $service->discountedPrice = $discountedPrice;
        $service->originalPrice = $originalPrice;
        $service->minsForService = $minsForService;
        $service->minsNotice = $minsNotice;

        $errors = $this->validator->validate($service);
        if(count($errors) > 0) {
            return false;
        }

        $companyServices = CompanyServices::getByPk($company->id);
        $companyServices->services->offsetSet($service->id, $service);
//        $companyServices->services[$service->id] = $service;
        $companyServices->save();

        $errors = array();
        $ad = $this->adService->create($adTitle, $adDescription, $company, $location, $calendar, $categoryId, $errors);

        //If it fails validation; destroy what we've created
        if($ad === false) {
            //We should destroy everything here, but it's not worth it
            return false;
        }

        $companyAds = new CompanyAds();
        $companyAds->companyId = $company->id;
        $companyAds->unpublishedAdIds[] = $ad->id;
        $companyAds->save();

        //Set the user as authenticated in session
        $this->sessionService->setSessionUser($member);

        return array(
            'member' => $member->getPrivateArray(),
        );
    }

    public function registerProviderCompany(Member $member, Company $company,
                                            $companyName, $memberName, $email, $password,
                                            \DateTime $dob, $street1, $street2, $zip, Address $address,
                                            $phone, $mobilePhone, array &$errors) {
        $company->name = $companyName;

        $errors = $this->validator->validate($company);
        if(count($errors) > 0) {
            return false;
        }
        $company->save();

        $member->name = $memberName;
        $member->email = $email;
        $member->password = $password;
        $member->dob = $dob;
        $member->mobile = $mobilePhone;

        $errors = $this->validator->validate($member);
        if(count($errors) > 0) {
            return false;
        }
        $member->password = $this->authService->hashPassword($password);
        $member->save();

        $location = new Location();
        $location->companyId = $company->id;
        $location->name = 'My First Location';
        $location->street1 = $street1;
        $location->street2 = $street2;
        $location->zip = $zip;
        $location->phone = $phone;
        $location->city = $address->city;
        $location->state = $address->state;
        $location->lat = $address->lat;
        $location->long = $address->long;

        $errors = $this->validator->validate($location);
        if(count($errors) > 0) {
            return false;
        }
        $location->save();

        $company->locationIds = array($location->id);
        $company->save();

        $companyAds = CompanyAds::getByPk($company->id);
        $companyAds->publishedAdIds[] = $companyAds->unpublishedAdIds[0];
        $companyAds->unpublishedAdIds = array();

        return array('ad' => array('id' => $companyAds->publishedAdIds[0]));
    }
 }