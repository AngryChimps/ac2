<?php


namespace AngryChimps\GeoBundle\Services;

use AngryChimps\NormBundle\realms\Norm\riak\services\NormRiakService;

class TimeService {
    protected $geolocationService;
    protected $riak;

    public function __construct(GeolocationService $geolocationService, NormRiakService $riak) {
        $this->geolocationService = $geolocationService;
        $this->riak = $riak;
    }

    public function getTime(\DateTime $time, $zipcode) {
        $zip = $this->riak->getZipcode($zipcode);

        if($zip === null) {
            $zip = $this->geolocationService->lookupZipcode($zipcode);
        }

        $time->setTimezone(new \DateTimeZone($zip->timezoneId));
        return $time;
    }
}