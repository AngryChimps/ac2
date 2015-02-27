<?php


namespace AngryChimps\NormBundle\realms\Norm\es\services;

use AC\NormBundle\cached\realms\es\services\NormEsBaseService;
use AC\NormBundle\Services\RealmInfoService;
use Norm\es\ProviderAdListing;
use Norm\es\ProviderAdListingCollection;
use Elastica\Client;
use Norm\riak\Address;

class NormEsService extends NormEsBaseService {
    public function publish($obj) {
        //Store only exactly what we wish to have returned to the client so we don't have to parse as much
        $arr = [];
        if($obj instanceof ProviderAdListing) {
            $arr['provider_ad_immutable_id'] = $obj->providerAdImmutableId;
            $arr['provider_ad_id'] = $obj->providerAdId;
            $arr['title'] = $obj->title;
            $arr['photo'] = $obj->photo;
            $arr['rating'] = $obj->rating;
            $arr['ratingCount'] = $obj->ratingCount;
            $arr['discountedPrice'] = $obj->discountedPrice;
            $arr['discountPercentage'] = $obj->discountPercentage;
            $arr['location'] = $obj->location;
            $arr['company_name'] = $obj->companyName;
            $arr['category_name'] = $obj->categoryName;
            $arr['category_id'] = $obj->categoryId;
            $arr['city'] = $obj->city;
            $arr['state'] = $obj->state;
            $arr['description'] = $obj->description;
            $arr['service_names'] = $obj->serviceNames;
            $arr['service_descriptions'] = $obj->serviceDescriptions;
            foreach($obj->startTimes as $time) {
                $arr['start_times'][] = $time->format('c');
            }
        }
        else {
            throw new \Exception('Unable to publish unknown type: ' . get_class($obj));
        }

        $this->publishObject($obj, $arr);
    }
//
//    /**
//     * @param null|int $totalCount A variable passed by reference to return the total count
//     * @param int $limit The maximum number of items to return
//     * @param int $offset The offset to begin the results returned
//     * @return ProviderAdListingCollection
//     *
//     */
//    public function getProviderAdListingCollectionAll(&$totalCount = null, $limit = 10, $offset = 0)
//    {
//        $query =    '{
//                       "query": { "match_all": {} }
//                     }';
//
//        /** @var \Elastica\ResultSet $resultSet */
//        $resultSet = $this->search('ProviderAdListing', $query, $limit, $offset);
//
//        if($totalCount !== null) {
//            $totalCount = $resultSet->getTotalHits();
//        }
//
//        $coll = new ProviderAdListingCollection();
//        foreach($resultSet->getResults() as $result) {
//            $obj = new ProviderAdListing();
//            $obj->adId = $resultSet['adId'];
//            $obj->companyName = $resultSet['companyName'];
//            $obj->title = $resultSet['title'];
//            $obj->photo = $resultSet['photo'];
//            $obj->address = new Address();
//            $obj->loadByArray($result->getSource());
//            $obj->save();
//            $coll[$obj->getIdentifier()] = $obj;
//        }
//
//        return $coll;
//    }
}