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
            $arr['results'][] = array_merge($result->getSource(), array('distance' => 0.5));
        }

        return $arr;
    }

    public function search($text, $categories, $lat, $long, $radiusMiles, $consumerTravels, $startingAt, $endingAt,
                           $sort, $limit, $offset) {

        $qb = new \Elastica\QueryBuilder();

        if($text === null) {
            $topQuery = $qb->query()->match_all();
        }
        else {
            $topQuery = new \Elastica\Query\Term(array('_all' => $text));
        }

        $topBoolFilter = new \Elastica\Filter\Bool();


        if($categories !== null) {
            $terms = new \Elastica\Filter\Terms('category_id', $categories);
            $topBoolFilter->addMust($terms);
        }
        if($lat !== null && $long !== null) {


        }



        if($sort !== null) {

        }

        $query = new \Elastica\Query();
        $query->setFrom($offset);
        $query->setSize($limit);
        $query->setQuery(
            $qb->query()->filtered(
                $topQuery,
                $topBoolFilter
            )
        );



        $results = $this->es->search('Norm\\es\\ProviderAdListing', $query, $limit, $offset);

        //Extract the data to return
        $arr = [];
        $arr['count'] = $results->getTotalHits();
        $arr['results'] = [];
        foreach($results->getResults() as $result) {
            $arr['results'][] = array_merge($result->getSource(), array('distance' => 0.5));
        }

        return $arr;
    }
} 