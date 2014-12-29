<?php


namespace AngryChimps\ApiBundle\Services;


use AC\NormBundle\Services\NormService;
use AngryChimps\MailerBundle\Messages\BasicMessage;
use AngryChimps\MailerBundle\Services\MailerService;
use AngryChimps\NormBundle\realms\Norm\es\services\NormEsService;
use Armetiz\FacebookBundle\FacebookSessionPersistence;
use Norm\riak\Ad;
use Norm\riak\Calendar;
use Norm\riak\Company;
use Norm\riak\CompanyAds;
use Norm\riak\CompanyServices;
use Norm\riak\Location;
use Norm\riak\Member;
use Norm\riak\ProviderAd;
use Norm\riak\ProviderAdImmutable;
use Norm\es\ProviderAdListing;
use Norm\riak\Service;
use Norm\riak\ServiceCollection;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use AngryChimps\NormBundle\realms\Norm\mysql\services\NormMysqlService;
use AngryChimps\NormBundle\realms\Norm\riak\services\NormRiakService;

class ProviderAdService {
    protected $validator;

    /** @var  NormRiakService */
    protected $riak;

    /** @var  NormMysqlService */
    protected $mysql;

    /** @var NormEsService */
    protected $es;

    /** @var  CompanyService */
    protected $companyService;

    /** @var  LocationService */
    protected $locationService;

    /** @var  CalendarService */
    protected $calendarService;

    /** @var  ServiceService */
    protected $serviceService;

    public function __construct(ValidatorInterface $validator, NormRiakService $riak,
                                NormMysqlService $mysql, NormEsService $es,
                                CompanyService $companyService, LocationService $locationService,
                                CalendarService $calendarService, ServiceService $serviceService) {
        $this->validator = $validator;
        $this->riak = $riak;
        $this->mysql = $mysql;
        $this->es = $es;
        $this->companyService = $companyService;
        $this->locationService = $locationService;
        $this->calendarService = $calendarService;
        $this->serviceService = $serviceService;
    }

    public function create($adTitle, $adDescription, Company $company,
                           Location $location, Calendar $calendar, $category, array &$errors) {
        $ad = new ProviderAd();
        $ad->title = $adTitle;
        $ad->description = $adDescription;
        $ad->companyId = $company->id;
        $ad->locationId = $location->id;
        $ad->calendarId = $calendar->id;
        $ad->categoryId = $category;
        $ad->status = ProviderAd::UN_PUBISHED_STATUS;

        $errors = $this->validator->validate($ad);

        if(count($errors) > 0) {
            return false;
        }

        $this->riak->create($ad);

        return $ad;
    }

    public function publish(ProviderAd $ad) {
        $company = $this->riak->getCompany($ad->companyId);
        $location = $this->riak->getLocation($ad->locationId);
        $calendar = $this->riak->getCalendar($ad->calendarId);
        $services = $this->riak->getServiceCollection($ad->serviceIds);

        //Create ProviderAdImmutable object
        $im = new ProviderAdImmutable();
        $im->providerAd = $ad;
        $im->location = $location;
        $im->company = $company;
        $im->calendar = $calendar;
        $im->services = $services;
        $this->riak->create($im);

        //Create ProviderAdListing object
        $listing = new ProviderAdListing();
        $listing->providerAdImmutableId = $im->id;
        $listing->providerAdId = $im->providerAd->id;
        $listing->title = $ad->title;
        $listing->companyName = $company->name;
        if(!empty($ad->photos)) {
            $listing->photo = $ad->photos[0];
        }
        $listing->address = $location->address;
        $listing->rating = $company->ratingAvg;
        $listing->availabilities = $calendar->availabilities;
        $this->es->publish($listing);

        //Set the ad status
        $ad->status = ProviderAd::PUBLISHED_STATUS;
        $ad->currentImmutableId = $im->id;
        $this->riak->update($ad);

        //Update CompanyAds data
        $companyAds = $this->riak->getCompanyAds($ad->companyId);
        $companyAds->publishedAdIds[] = $ad->id;
        foreach($companyAds->unpublishedAdIds as $key => $id) {
            if($id == $ad->id) {
                unset($companyAds->unpublishedAdIds[$key]);
            }
        }
        $this->riak->update($companyAds);
    }

    public function getProviderAd($id) {
        return $this->riak->getProviderAd($id);
    }

    public function markProviderAdDeleted(ProviderAd $providerAd) {
        $providerAd->status = ProviderAd::DELETED_STATUS;
        $this->riak->update($providerAd);
    }
}