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
            $arr['company_name'] = $obj->companyName;
            $arr['title'] = $obj->title;
            $arr['photo'] = $obj->photo;
            $arr['address']['street1'] = $obj->address->street1;
            $arr['address']['city'] = $obj->address->city;
            $arr['address']['state'] = $obj->address->state;
            $arr['address']['zip'] = $obj->address->zip;
            $arr['address']['lat'] = $obj->address->lat;
            $arr['address']['long'] = $obj->address->long;

            /** @var \Norm\riak\Availability $availability */
            foreach($obj->availabilities as $availability) {
                $arr2 = [];
                $arr2['start'] = $availability->start->format('c');
                $arr2['end'] = $availability->end->format('c');
                $arr['availabilities'][] = $arr2;
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