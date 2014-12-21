<?php


namespace AngryChimps\ApiBundle\Services;


use Norm\es\ProviderAdListingCollection;

class SearchService {
    public function getSampleProviderAdListing($limit = 10, $offset = 0)
    {
        $counter = 0;
        $listings = ProviderAdListingCollection::getAll($counter, $limit, $offset);
        return array('count' => $counter, 'ProviderAdListings' => $listings->getArray());
    }
} 