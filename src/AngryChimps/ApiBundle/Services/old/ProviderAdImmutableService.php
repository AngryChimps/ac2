<?php


namespace AngryChimps\ApiBundle\services;


use AngryChimps\NormBundle\services\NormService;
use Norm\norm\ProviderAdImmutable;

class ProviderAdImmutableService {

    /** @var NormService */
    protected $norm;

    public function __construct(NormService $norm) {
        $this->norm = $norm;
    }

    public function getProviderAdImmutable($providerAdImmutableId) {
        return $this->norm->getProviderAdImmutable($providerAdImmutableId);
    }

    public function getData(ProviderAdImmutable $providerAdImmutable) {
        $arr = [];
        $arr['provider_ad_immutable']['id'] = $providerAdImmutable->id;
        $arr['provider_ad']['id'] = $providerAdImmutable->providerAd->id;
        $arr['company']['id'] = $providerAdImmutable->company->id;
        $arr['company']['name'] = $providerAdImmutable->company->name;
        $arr['title'] = $providerAdImmutable->providerAd->title;
        $arr['description'] = $providerAdImmutable->providerAd->description;
        $arr['address']['phone'] = $providerAdImmutable->location->address->phone;
        $arr['address']['street1'] = $providerAdImmutable->location->address->street1;
        $arr['address']['street2'] = $providerAdImmutable->location->address->street2;
        $arr['address']['city'] = $providerAdImmutable->location->address->city;
        $arr['address']['state'] = $providerAdImmutable->location->address->state;
        $arr['address']['zip'] = $providerAdImmutable->location->address->zip;
        $arr['address']['lat'] = $providerAdImmutable->location->address->lat;
        $arr['address']['lon'] = $providerAdImmutable->location->address->lon;

        foreach($providerAdImmutable->services as $service) {
            $arr2 = [];
            $arr2['name'] = $service->name;
            $arr2['discounted_price'] = $service->discountedPrice;
            $arr2['original_price'] = $service->originalPrice;
            $arr2['mins_for_service'] = $service->minsForService;
            $arr['services'][] = $arr2;
        }

        $arr['photos'] = $providerAdImmutable->providerAd->photos;
        $arr['rating'] = $providerAdImmutable->company->ratingAvg;
        $arr['rating_count'] = $providerAdImmutable->company->ratingCount;

        return $arr;
    }
}