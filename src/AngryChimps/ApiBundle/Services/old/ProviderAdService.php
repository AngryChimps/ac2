<?php


namespace AngryChimps\ApiBundle\services;


use AngryChimps\MailerBundle\Messages\BasicMessage;
use AngryChimps\MailerBundle\Services\MailerService;
use AngryChimps\NormBundle\services\NormService;
use Armetiz\FacebookBundle\FacebookSessionPersistence;
use Norm\norm\Ad;
use Norm\norm\Calendar;
use Norm\norm\Company;
use Norm\norm\CompanyAds;
use Norm\norm\CompanyServices;
use Norm\norm\Location;
use Norm\norm\Member;
use Norm\norm\ProviderAd;
use Norm\norm\ProviderAdImmutable;
use Norm\es\ProviderAdListing;
use Norm\norm\Service;
use Norm\norm\ServiceCollection;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use AngryChimps\NormBundle\realms\Norm\mysql\services\NormMysqlService;
use AngryChimps\NormBundle\realms\Norm\norm\services\NormRiakService;

class ProviderAdService {
    protected $validator;

    /** @var  NormService */
    protected $norm;

    /** @var  CompanyService */
    protected $companyService;

    /** @var  LocationService */
    protected $locationService;

    /** @var  CalendarService */
    protected $calendarService;

    /** @var  ServiceService */
    protected $serviceService;

    /** @var CategoriesService */
    protected $categoriesService;

    public function __construct(ValidatorInterface $validator, NormService $norm,
                                CompanyService $companyService, LocationService $locationService,
                                CalendarService $calendarService, ServiceService $serviceService,
                                CategoriesService $categoriesService) {
        $this->validator = $validator;
        $this->norm = $norm;
        $this->companyService = $companyService;
        $this->locationService = $locationService;
        $this->calendarService = $calendarService;
        $this->serviceService = $serviceService;
        $this->categoriesService = $categoriesService;
    }

    public function create($adTitle, $adDescription, Company $company,
                           Location $location, Calendar $calendar, $category, $serviceIds, array &$errors) {
        $ad = new ProviderAd();
        $ad->title = $adTitle;
        $ad->description = $adDescription;
        $ad->companyId = $company->id;
        $ad->locationId = $location->id;
        $ad->calendarId = $calendar->id;
        $ad->categoryId = $category;
        $ad->serviceIds = $serviceIds;
        $ad->status = ProviderAd::UN_PUBISHED_STATUS;

        $errors = $this->validator->validate($ad);

        if(count($errors) > 0) {
            return false;
        }

        $this->norm->create($ad);

        return $ad;
    }

    public function publish(ProviderAd $ad) {
        $company = $this->norm->getCompany($ad->companyId);
        $location = $this->norm->getLocation($ad->locationId);
        $calendar = $this->norm->getCalendar($ad->calendarId);
        $services = $this->norm->getServiceCollection($ad->serviceIds);

        if(empty($services)) {
            throw new \Exception('asdfasdf');
        }

        //Create ProviderAdImmutable object
        $im = new ProviderAdImmutable();
        $im->providerAd = $ad;
        $im->location = $location;
        $im->company = $company;
        $im->calendar = $calendar;
        $im->services = $services;
        $this->norm->create($im);

        //Create ProviderAdListing object
        $listing = new ProviderAdListing();
        $listing->providerAdImmutableId = $im->id;
        $listing->providerAdId = $ad->id;
        $listing->title = $ad->title;
        $listing->city = $location->address->city;
        $listing->state = $location->address->state;
        $listing->location = $location->address->lat . ',' . $location->address->lon;
        $listing->companyName = $company->name;
        $listing->categoryId = $ad->categoryId;
        $listing->categoryName = $this->categoriesService->getCategoryName($ad->categoryId);
        $listing->isMobile = $location->isMobile;
        $listing->description = $ad->description;

        if(!empty($ad->photos)) {
            $listing->photo = $ad->photos[0];
        }
        $listing->rating = $company->ratingAvg;
        $listing->ratingCount = $company->ratingCount;

        $minPrice = 0;
        $discount = 0;
        $minTimeRequired = 0;
        /** @var \Norm\norm\Service $service */
        foreach($services as $service) {
            if($service->discountedPrice < $minPrice || $minPrice == 0) {
                $minPrice = $service->discountedPrice;
                $discount = 100 - (($service->discountedPrice / $service->originalPrice) * 100);
            }
            if($service->minsForService < $minTimeRequired || $minTimeRequired === 0) {
                $minTimeRequired = $service->minsForService + $service->minsNotice;
            }
            $listing->serviceNames[] = $service->name;
            $listing->serviceDescriptions[] = $service->description;
        }

        $listing->startTimes = $this->calendarService->getAvailableStartTimes($calendar->availabilities, $minTimeRequired);
        $listing->discountedPrice = $minPrice;
        $listing->discountPercentage = $discount;
        $this->es->publish($listing);

        //Set the ad status
        $ad->status = ProviderAd::PUBLISHED_STATUS;
        $ad->currentImmutableId = $im->id;
        $this->norm->update($ad);

        //Update CompanyAds data
        $companyAds = $this->norm->getCompanyAds($ad->companyId);
        $companyAds->publishedAdIds[] = $ad->id;
        foreach($companyAds->unpublishedAdIds as $key => $id) {
            if($id == $ad->id) {
                unset($companyAds->unpublishedAdIds[$key]);
            }
        }
        $this->norm->update($companyAds);

        return $ad;
    }

    public function getExpirationDate(ProviderAdImmutable $providerAdImmutable, Service $service) {
        $now = new \DateTime();
        $bookBy = $now->sub(new \DateInterval('PT' . $service->minsNotice . 'M'));
        
    }

    public function getProviderAd($id) {
        return $this->norm->getProviderAd($id);
    }

    public function markProviderAdDeleted(ProviderAd $providerAd) {
        $providerAd->status = ProviderAd::DELETED_STATUS;
        $this->norm->update($providerAd);
    }
}