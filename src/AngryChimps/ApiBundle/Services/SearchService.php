<?php


namespace AngryChimps\ApiBundle\Services;

use AngryChimps\NormBundle\realms\Norm\es\services\NormEsService;
use Norm\riak\Availability;

class SearchService {
    /** @var NormEsService */
    protected $es;

    /** @var CalendarService */
    protected $calendarService;

    public function __construct(NormEsService $es, CalendarService $calendarService) {
        $this->es = $es;
        $this->calendarService = $calendarService;
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

    public function search($text, $categories, $lat, $long, $radiusMiles, $consumerTravels,
                           $startingAt, $endingAt,
                           $sort, $limit, $offset) {
        //Create the query builder
        $qb = new \Elastica\QueryBuilder();

        //Create the function score query
        $functionScore = new \Elastica\Query\FunctionScore();

        //Configure full text search
        if($text === null) {
            $topQuery = $qb->query()->match_all();
        }
        else {
            $topQuery = new \Elastica\Query\Term(array('_all' => $text));
        }

        //Create a top level boolean filter
        $topBoolFilter = new \Elastica\Filter\Bool();

        //Categories search
        if($categories !== null) {
            $terms = new \Elastica\Filter\Terms('category_id', $categories);
            $topBoolFilter->addMust($terms);
        }

        //Availability Search
        if($startingAt !== null && $endingAt !== null) {
            $startingAt = $startingAt->add(new \DateInterval('PT30M'));
            $range = new \Elastica\Filter\Range('start_times',
                [
                    'gte' => $startingAt->format('c'),
                    'lt' => $endingAt->format('c')
                ]);
            $topBoolFilter->addMust($range);
        }

        //Create the query from the query builder
        $query = new \Elastica\Query();
        $query->setFrom($offset);
        $query->setSize($limit);
        $query->setQuery(
            $qb->query()->filtered(
                $topQuery,
                $topBoolFilter
            )
        );

        //Sort the query results
        if($sort === null || $sort === 'relevance' || $sort === 'distance') {
            $query->addSort(
                ['_geo_distance' => [
                    'location' => [$lat, $long],
                    'order' => 'asc',
                    'unit' => 'mi']
                ]
            );
        }

        //Get the results
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