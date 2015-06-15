<?php


namespace AC\NormBundle\services;


use AC\NormBundle\cached\NormBaseService;
use Norm\Address;

class NormService extends NormBaseService {
//    public function publish($obj) {
//        $arr = [];
//        $now = new \DateTime();
//        if($obj instanceof ProviderAdListing) {
//            $arr['provider_ad_immutable_id'] = $obj->providerAdImmutableId;
//            $arr['provider_ad_id'] = $obj->providerAdId;
//            $arr['title'] = $obj->title;
//            $arr['photo'] = $obj->photo;
//            $arr['rating'] = $obj->rating;
//            $arr['rating_count'] = $obj->ratingCount;
//            $arr['discounted_price'] = $obj->discountedPrice;
//            $arr['discount_percentage'] = $obj->discountPercentage;
//            $arr['location'] = $obj->location;
//            $arr['company_name'] = $obj->companyName;
//            $arr['category_name'] = $obj->categoryName;
//            $arr['category_id'] = $obj->categoryId;
//            $arr['city'] = $obj->city;
//            $arr['state'] = $obj->state;
//            $arr['description'] = $obj->description;
//            $arr['service_names'] = $obj->serviceNames;
//            $arr['service_descriptions'] = $obj->serviceDescriptions;
//            foreach($obj->startTimes as $time) {
//                $arr['start_times'][] = $time->format('c');
//            }
//        }
//        elseif($obj instanceof Comment) {
//            $arr['id'] = $obj->id;
//            $arr['rating'] = $obj->rating;
//            $arr['comment'] = $obj->comment;
//            $arr['company_id'] = $obj->companyId;
//            $arr['member_id'] = $obj->memberId;
//            $arr['created_at'] = $now->format('c');
//        }
//        else {
//            throw new \Exception('Unable to publish unknown type: ' . get_class($obj));
//        }
//
//        $this->publishObject($obj, $arr);
//    }
////
////    /**
////     * @param null|int $totalCount A variable passed by reference to return the total count
////     * @param int $limit The maximum number of items to return
////     * @param int $offset The offset to begin the results returned
////     * @return ProviderAdListingCollection
////     *
////     */
////    public function getProviderAdListingCollectionAll(&$totalCount = null, $limit = 10, $offset = 0)
////    {
////        $query =    '{
////                       "query": { "match_all": {} }
////                     }';
////
////        /** @var \Elastica\ResultSet $resultSet */
////        $resultSet = $this->search('ProviderAdListing', $query, $limit, $offset);
////
////        if($totalCount !== null) {
////            $totalCount = $resultSet->getTotalHits();
////        }
////
////        $coll = new ProviderAdListingCollection();
////        foreach($resultSet->getResults() as $result) {
////            $obj = new ProviderAdListing();
////            $obj->adId = $resultSet['adId'];
////            $obj->companyName = $resultSet['companyName'];
////            $obj->title = $resultSet['title'];
////            $obj->photo = $resultSet['photo'];
////            $obj->address = new Address();
////            $obj->loadByArray($result->getSource());
////            $obj->save();
////            $coll[$obj->getIdentifier()] = $obj;
////        }
////
////        return $coll;
////    }
}