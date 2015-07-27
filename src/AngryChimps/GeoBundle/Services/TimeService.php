<?php


namespace AngryChimps\GeoBundle\services;

use AngryChimps\NormBundle\services\NormService;

class TimeService {
    protected $geolocationService;
    protected $norm;

    public function __construct(GeolocationService $geolocationService, NormService $norm) {
        $this->geolocationService = $geolocationService;
        $this->norm = $norm;
    }

    public function getTime(\DateTime $time, $zipcode) {
        $zip = $this->norm->getZipcode($zipcode);

        if($zip === null) {
            $zip = $this->geolocationService->lookupZipcode($zipcode);
        }

        $time->setTimezone(new \DateTimeZone($zip->timezoneId));
        return $time;
    }
}