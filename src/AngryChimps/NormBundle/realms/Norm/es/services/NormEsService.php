<?php


namespace AngryChimps\NormBundle\realms\Norm\es\services;

use AC\NormBundle\cached\realms\es\services\NormEsBaseService;
use AC\NormBundle\Services\RealmInfoService;
use Norm\es\ProviderAdListing;
use Norm\es\ProviderAdListingCollection;
use Elastica\Client;
use Norm\riak\Address;

class NormEsService extends NormEsBaseService {
    /**
     * @param null|int $totalCount A variable passed by reference to return the total count
     * @param int $limit The maximum number of items to return
     * @param int $offset The offset to begin the results returned
     * @return ProviderAdListingCollection
     *
     */
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