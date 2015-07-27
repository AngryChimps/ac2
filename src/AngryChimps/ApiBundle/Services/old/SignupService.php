<?php


namespace AngryChimps\ApiBundle\services;


use AngryChimps\GeoBundle\Services\TimeService;
use AngryChimps\MediaBundle\Services\MediaService;
use AngryChimps\NormBundle\services\NormService;
use AngryChimps\GeoBundle\Classes\Address;
use AngryChimps\GeoBundle\Services\GeolocationService;
use Armetiz\FacebookBundle\FacebookSessionPersistence;
use Norm\Availability;
use Norm\Company;
use Norm\CompanyAds;
use Norm\Location;
use Norm\Member;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

    /** @var  NormService */
    protected $norm;

    /** @var MediaService */
    protected $mediaService;

    /** @var TimeService */
    protected $timeService;
    public function __construct(MemberService $memberService, CompanyService $companyService,
                                LocationService $locationService, ProviderAdService $providerAdService,
                                CalendarService $calendarService, ServiceService $serviceService,
                                AuthService $authService, ValidatorInterface $validator,
                                SessionService $sessionService, NormService $norm,
                                MediaService $mediaService, TimeService $timeService)
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
        $this->norm = $norm;
        $this->mediaService = $mediaService;
        $this->timeService = $timeService;
    }

    public function registerProviderAd($adTitle, $adDescription, array $availabilities,
                                       array $services, $categoryId, array &$errors) {

        $member = $this->memberService->createEmpty();
        $company = $this->companyService->createEmpty($member);
        $location = $this->locationService->createEmpty($company);
        $calendar = $this->calendarService->createNew($location, 'My First Calendar');

        foreach($availabilities as $availability) {
            $this->calendarService->addAvailability($calendar, $availability);
        }

        foreach($services as $service) {
            $errors = $this->validator->validate($service);
            if(count($errors) > 0) {
                return false;
            }
            $this->norm->create($service);

            $company->serviceIds[] = $service->id;
        }

        foreach($company->locationIds as $locationId) {
            $company->locationIds[] = $locationId;
        }
        $this->norm->update($company);

        $companyServices = $this->norm->getCompanyServices($company->id);
        $companyServices->services->offsetSet($service->id, $service);
        $this->norm->update($companyServices);

        $errors = array();
        $ad = $this->providerAdService->create($adTitle, $adDescription, $company, $location, $calendar, $categoryId, [$service->id], $errors);

        //If it fails validation; destroy what we've created
        if($ad === false) {
            //We should destroy everything here, but it's not worth it
            return false;
        }

        $companyAds = new CompanyAds();
        $companyAds->companyId = $company->id;
        $companyAds->unpublishedAdIds[] = $ad->id;
        $this->norm->create($companyAds);

        //Set the user as authenticated in session
        $this->sessionService->setSessionUser($member);

        return array(
            'member' => array('id' => $member->id),
            'company' => array('id' => $company->id),
            'location' => array('id' => $ad->id),
            'provider_ad' => array('id' => $ad->id),
        );
    }

    public function registerProviderCompany(Member $member, Company $company, Location $location,
                                            $companyName, $memberName, $email, $password,
                                            \DateTime $dob, $street1, $street2, $zip, Address $address,
                                            $phone, $mobilePhone, array &$errors) {
        $mysqlMember = $this->mysql->getMember($member->mysqlId);
        $mysqlMember->name = $memberName;
        $mysqlMember->email = $email;
        $this->mysql->update($mysqlMember);

        $company->name = $companyName;

        $errors = $this->validator->validate($company);
        if(count($errors) > 0) {
            return false;
        }
        $this->norm->update($company);

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
        $this->norm->update($member);

        $location->companyId = $company->id;
        $location->name = 'My First Location';
        $location->address = new \Norm\norm\Address();
        $location->address->street1 = $street1;
        $location->address->street2 = $street2;
        $location->address->zip = $zip;
        $location->address->phone = $phone;
        $location->address->city = $address->city;
        $location->address->state = $address->state;
        $location->address->lat = $address->lat;
        $location->address->lon = $address->lon;

        $errors = $this->validator->validate($location);
        if(count($errors) > 0) {
            return false;
        }
        $this->norm->update($location);

        $company->locationIds = array($location->id);
        $this->norm->update($company);

        $companyAds = $this->norm->getCompanyAds($company->id);
        $companyAds->publishedAdIds[] = $companyAds->unpublishedAdIds[0];
        $companyAds->unpublishedAdIds = array();

        //Now that we know the location, we can localize the start/end availability times
        $calendar = $this->norm->getCalendar($location->calendarIds[0]);
        $avail = new Availability();
        $avail->start = $this->timeService->getTime($calendar->availabilities[0]->start, $zip);
        $avail->end = $this->timeService->getTime($calendar->availabilities[0]->end, $zip);
        $calendar->availabilities = [$avail];
        $this->norm->update($calendar);

        $providerAd = $this->providerAdService->getProviderAd($companyAds->publishedAdIds[0]);
        $ad = $this->providerAdService->publish($providerAd);

        return array('provider_ad' => array('id' => $ad->id));
    }

    public function uploadPhoto(Member $member, UploadedFile $photo) {
        $filename = $this->mediaService->persist('company_images_fs', $photo);

        $companyPhotos = $this->norm->getCompanyPhotos($member->managedCompanyIds[0]);
        $companyPhotos->photos[] = $filename;
        $this->norm->update($companyPhotos);

        $companyAds = $this->norm->getCompanyAds($member->managedCompanyIds[0]);
        if(!empty($companyAds->publishedAdIds)) {
            $providerAd = $this->norm->getProviderAd($companyAds->publishedAdIds[0]);
            $providerAd->photos[0] = 'ci/' . $filename;
            $this->norm->update($providerAd);

            $this->providerAdService->publish($providerAd);
        }
        else {
            $providerAd = $this->norm->getProviderAd($companyAds->unpublishedAdIds[0]);
            $providerAd->photos[0] = 'ci/' . $filename;
            $this->norm->update($providerAd);
        }
    }
 }