<?php


namespace AngryChimps\ApiBundle\Services;

use AngryChimps\NormBundle\realms\Norm\es\services\NormEsService;

class SearchService {
    /** @var NormEsService */
    protected $es;

    public function __construct(NormEsService $es) {
        $this->es = $es;
    }

    public function getSampleProviderAdListing($limit = 10, $offset = 0)
    {
        $query =    '{
                       "query": { "match_all": {} }
                     }';
        $results = $this->es->search('Norm\\es\\ProviderAdListing', $query, $limit, $offset);

        //Extract the data to return
        $arr = [];
        $arr['count'] = $results->getTotalHits();
        $arr['results'] = [];
        foreach($results->getResults() as $result) {
            $arr['results'][] = $result->getSource();
        }

        return $arr;
    }
} 