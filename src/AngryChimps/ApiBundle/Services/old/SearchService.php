<?php


namespace AngryChimps\ApiBundle\Services;

use AngryChimps\NormBundle\services\NormService;
use Elastica\Query\FunctionScore;
use Norm\Availability;
use Location\Coordinate;
use Location\Distance\Haversine;

class SearchService {
    /** @var NormService */
    protected $norm;

    /** @var CalendarService */
    protected $calendarService;

    public function __construct(NormService $norm, CalendarService $calendarService) {
        $this->norm = $norm;
        $this->calendarService = $calendarService;
    }

    public function getSampleProviderAdListing($limit = 10, $offset = 0)
    {
        $query =    '{
                       "query": { "match_all": {} }
                     }';
        $results = $this->norm->search('Norm\\ProviderAdListing', $query, $limit, $offset);

        //Extract the data to return
        $arr = [];
        $arr['count'] = $results->getTotalHits();
        $arr['results'] = [];
        foreach($results->getResults() as $result) {
            $arr['results'][] = array_merge($result->getSource(), array('distance' => 0.5));
        }

        return $arr;
    }

    public function search($text, $categories, $lat, $lon, $radiusMiles, $consumerTravels,
                           $startingAt, $endingAt,
                           $sort, $limit, $offset) {

        //Create the function score query
        $functionScore = new \Elastica\Query\FunctionScore();

        //Configure full text search
        if($text === null) {
            $topQuery = new \Elastica\Query\MatchAll();
        }
        else {
            $topQuery = new \Elastica\Query\Term(array('_all' => $text));
        }

        //Create a top level boolean filter but only use it if supplied a must
        $useTopBoolFilter = false;
        $topBoolFilter = new \Elastica\Filter\Bool();

        //Categories search
        if($categories !== null) {
            $terms = new \Elastica\Filter\Terms('category_id', $categories);
            $topBoolFilter->addMust($terms);
            $useTopBoolFilter = true;
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
            $useTopBoolFilter = true;
        }

        if($consumerTravels !== null) {
            $term = new \Elastica\Filter\Term(['is_mobile' => !($consumerTravels)]);
            $topBoolFilter->addMust($term);
            $useTopBoolFilter = true;
        }

        $functionScore->setQuery($topQuery);
        if($useTopBoolFilter) {
            $functionScore->setFilter($topBoolFilter);
        }
        $functionScore->addDecayFunction(FunctionScore::DECAY_LINEAR, 'location', $lat . ',' . $lon,
            "2mi");

        $query = new \Elastica\Query($functionScore);
        $query->setFrom($offset);
        $query->setSize($limit);
//        $query->setFields(['provider_ad_immutable_id', 'provider_ad_id', 'title', 'photo', 'rating', 'ratingCount',
//                           'discountedPrice', 'discountPercentage', 'location']);

        //Sort the query results
//        if($sort === null || $sort === 'relevance' || $sort === 'distance') {
//            $query->addSort(
//                ['_geo_distance' => [
//                    'location' => [$lat, $long],
//                    'order' => 'asc',
//                    'unit' => 'mi']
//                ]
//            );
//        }

        //Get the results
        $results = $this->norm->search('Norm\\ProviderAdListing', $query, $limit, $offset);

        //Extract the data to return
        $arr = [];
        $arr['count'] = $results->getTotalHits();
        $arr['results'] = [];
        foreach($results->getResults() as $result) {
            $sourceArray = $result->getSource();

            //Calculate the distance
            $geopoint = explode(',', $sourceArray['location']);
            $distance = $this->calculateDistance($lat, $lon, $geopoint[0], $geopoint[1]);

            if($radiusMiles !== null) {
                if($distance > $radiusMiles) {
                    $arr['count']--;
                    continue;
                }
            }
            $data = array_merge($sourceArray,
                [
                    'distance' => $distance,
                    'lat' => $geopoint[0],
                    'long' => $geopoint[1],
                ]
            );

            //Unset data we don't want
            unset($data['location']);
            unset($data['company_name']);
            unset($data['category_name']);
            unset($data['category_id']);
            unset($data['city']);
            unset($data['state']);
            unset($data['description']);
            unset($data['service_names']);
            unset($data['service_descriptions']);
            unset($data['start_times']);

            $arr['results'][] = $data;
        }

        return $arr;
    }

    public function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $coordinate1 = new Coordinate($lat1, $lon1);
        $coordinate2 = new Coordinate($lat2, $lon2);

        $meters = $coordinate1->getDistance($coordinate2, new Haversine());
        return $meters * 0.000621371;
    }
} 