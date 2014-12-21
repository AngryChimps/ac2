<?php


namespace AngryChimps\ApiBundle\Services;


use AngryChimps\NormBundle\realms\Norm\mysql\services\NormMysqlService;
use AngryChimps\NormBundle\realms\Norm\riak\services\NormRiakService;
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
use Norm\riak\ProviderAd;
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
    protected $providerAdService;

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

    /** @var  NormRiakService */
    protected $riak;

    /** @var  NormMysqlService */
    protected $mysql;

    public function __construct(MemberService $memberService, CompanyService $companyService,
                                LocationService $locationService, ProviderAdService $providerAdService,
                                CalendarService $calendarService, ServiceService $serviceService,
                                AuthService $authService, ValidatorInterface $validator,
                                SessionService $sessionService, NormRiakService $riak, NormMysqlService $mysql)
    {
        $this->memberService = $memberService;
        $this->companyService = $companyService;
        $this->locationService = $locationService;
        $this->providerAdService = $providerAdService;
        $this->calendarService = $calendarService;
        $this->serviceService = $serviceService;
        $this->authService = $authService;
        $this->validator = $validator;
        $this->sessionService = $sessionService;
        $this->riak = $riak;
        $this->mysql = $mysql;
    }

    public function registerProviderAd($adTitle, $adDescription, \DateTime $start, \DateTime $end,
                                       $serviceName, $discountedPrice, $originalPrice,
                                       $minsForService, $minsNotice, $categoryId, array &$errors) {

        $member = $this->memberService->createEmpty();
        $company = $this->companyService->createEmpty($member);
        $location = $this->locationService->createEmpty($company);
        $calendar = $this->calendarService->createNew($location, 'My First Calendar');

        $member->managedCompanyIds[] = $company->id;
        $this->riak->update($member);

        $company->administerMemberIds[] = $member->id;
        $this->riak->update($company);

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

        $this->riak->create($service);

        $companyServices = $this->riak->getCompanyServices($company->id);
        $companyServices->services->offsetSet($service->id, $service);
        $this->riak->update($companyServices);

        $errors = array();
        $ad = $this->providerAdService->create($adTitle, $adDescription, $company, $location, $calendar, $categoryId, $errors);

        //If it fails validation; destroy what we've created
        if($ad === false) {
            //We should destroy everything here, but it's not worth it
            return false;
        }

        $companyAds = new CompanyAds();
        $companyAds->companyId = $company->id;
        $companyAds->unpublishedAdIds[] = $ad->id;
        $this->riak->create($companyAds);

        //Set the user as authenticated in session
        $this->sessionService->setSessionUser($member);

        return array(
            'member' => array('id' => $member->id),
        );
    }

    public function registerProviderCompany(Member $member, Company $company, Location $location,
                                            $companyName, $memberName, $email, $password,
                                            \DateTime $dob, $street1, $street2, $zip, Address $address,
                                            $phone, $mobilePhone, array &$errors) {
        $company->name = $companyName;

        $errors = $this->validator->validate($company);
        if(count($errors) > 0) {
            return false;
        }
        $this->riak->update($company);

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
        $this->riak->update($member);

        $location->companyId = $company->id;
        $location->name = 'My First Location';
        $location->address = new \Norm\riak\Address();
        $location->address->street1 = $street1;
        $location->address->street2 = $street2;
        $location->address->zip = $zip;
        $location->address->phone = $phone;
        $location->address->city = $address->city;
        $location->address->state = $address->state;
        $location->address->lat = $address->lat;
        $location->address->long = $address->long;

        $errors = $this->validator->validate($location);
        if(count($errors) > 0) {
            return false;
        }
        $this->riak->update($location);

        $company->locationIds = array($location->id);
        $this->riak->update($company);

        $companyAds = $this->riak->getCompanyAds($company->id);
        $companyAds->publishedAdIds[] = $companyAds->unpublishedAdIds[0];
        $companyAds->unpublishedAdIds = array();

        $ad = $this->providerAdService->getProviderAd($companyAds->publishedAdIds[0]);
        $this->providerAdService->markProviderAdDeleted($ad);

        return array('providerAd' => array('id' => $companyAds->publishedAdIds[0]));
    }


 }