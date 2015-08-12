<?php
/**
 * Created by PhpStorm.
 * User: Sean
 * Date: 8/12/2015
 * Time: 11:39 AM
 */

namespace AngryChimps\ApiBundle\Services;


use AngryChimps\NormBundle\services\NormService;
use Elastica\Query\FunctionScore;
use Elastica\Result;
use Elastica\ResultSet;
use Location\Coordinate;
use Location\Distance\Haversine;
use Norm\Location;

class SearchService
{
    /** @var NormService */
    protected $norm;

    public function __construct(NormService $norm) {
        $this->norm = $norm;
    }

    public function search($lat, $lon, $mobileLocation,
                           $animal, $emergencies, $walkIn, $limit, $offset) {
        //Create the function score query
        $functionScore = new \Elastica\Query\FunctionScore();
        $topQuery = new \Elastica\Query\MatchAll();

        //Create a top level boolean filter but only use it if supplied a must
        $topBoolFilter = new \Elastica\Filter\Bool();

        //Status
        $term = new \Elastica\Filter\Term(['status' => Location::ACTIVE_STATUS]);
        $topBoolFilter->addMust($term);

        //Mobile Location
        if($mobileLocation !== null) {
            $term = new \Elastica\Filter\Term(['is_mobile' => $mobileLocation]);
            $topBoolFilter->addMust($term);
        }

        //Animal search
        if($animal !== null) {
            $term = new \Elastica\Filter\Term(['animals' => $animal]);
            $topBoolFilter->addMust($term);
        }

        //Emergency search
        if($emergencies !== null) {
            $term = new \Elastica\Filter\Term(['emergencies' => $emergencies]);
            $topBoolFilter->addMust($term);
        }

        //Walk-in search
        if($walkIn !== null) {
            $term = new \Elastica\Filter\Term(['walk_ins' => $walkIn]);
            $topBoolFilter->addMust($term);
        }

        $functionScore->setQuery($topQuery);
        $functionScore->setFilter($topBoolFilter);
        $functionScore->addDecayFunction(FunctionScore::DECAY_LINEAR, 'location', $lat . ',' . $lon,
            "2mi");

        $query = new \Elastica\Query($functionScore);

        /** @var ResultSet $results */
        $results = $this->norm->search('location', $query, $limit, $offset, 'es_ds');

        $arr = [];
        $arr['count'] = $results->count();
        $arr['results'] = [];
        foreach($results->getResults() as $result) {
            $sourceArray = $result->getSource();
            $resultArray = [];
            $resultArray['location_id'] = $sourceArray['id'];
            $resultArray['company_name'] = $sourceArray['company_name'];
            $resultArray['photo'] = $sourceArray['photos'][0];
            $resultArray['rating'] = $sourceArray['rating_avg'];
            $resultArray['rating_count'] = $sourceArray['rating_count'];
            $resultArray['location']['lat'] = $sourceArray['address']['location']['lat'];
            $resultArray['location']['lon'] = $sourceArray['address']['location']['lon'];

            //Calculate distance
            $resultArray['distance_miles'] = $this->calculateDistance($lat, $lon, $sourceArray['address']['location']['lat'],
                $sourceArray['address']['location']['lon']);

            $arr['results'][] = $resultArray;
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