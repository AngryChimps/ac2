<?php


namespace AngryChimps\ApiBundle\Services;


use AC\NormBundle\Services\InfoService;
use AngryChimps\GeoBundle\Services\GeolocationService;
use Norm\Company;
use Norm\Location;
use Norm\Address;
use Norm\Member;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use AngryChimps\NormBundle\services\NormService;

class LocationService extends AbstractRestService {
    /** @var \Symfony\Component\Validator\Validator\ValidatorInterface */
    protected $validator;

    /** @varAngryChimps\GeoBundle\services\GeolocationService */
    protected $geo;

    /** @var  NormService */
    protected $norm;

    /** @var CompanyService  */
    protected $companyService;

    public function __construct(ValidatorInterface $validator, GeolocationService $geo, NormService $norm,
                                CompanyService $companyService, InfoService $infoService) {
        $this->validator = $validator;
        $this->geo = $geo;
        $this->norm = $norm;
        $this->companyService = $companyService;

        parent::__construct($norm, $infoService, $validator);
    }

    /**
     * @param Location $location
     * @param Member $authenticatedMember
     * @return bool
     */
    public function isOwner($location, Member $authenticatedMember) {
        return $this->companyService->isOwner($this->norm->getCompany($location->getCompanyId()), $authenticatedMember);
    }

    public function post($endpoint, $data, $additionalData = [])
    {
        //Add geo data
        $this->addGeoData($data);

        /** @var Location $location*/
        $location = parent::post($endpoint, $data, $additionalData);

        return $location;
    }

    public function patch($endpoint, $data)
    {
        //Add geo data
        $this->addGeoData($data);

        /** @var Location $location*/
        $location = parent::patch($endpoint, $data);

        return $location;
    }

    public function getStaff($locationId, $count = null) {
        return $this->norm->getStaffByLocation($locationId, $count);
    }

    public function getStaffCount($locationId) {
        return $this->norm->getStaffCountByLocation($locationId);
    }

    public function getReviews($locationId, $count = null) {
        return $this->norm->getReviewsByLocation($locationId, $count);
    }

    public function getReviewCount($locationId) {
        return $this->norm->getReviewCountByLocation($locationId);
    }

    protected function addGeoData(array &$data) {
        $geoAddr = $this->geo->lookupAddress($data['address']['street1'],
            $data['address']['zip']);
        $data['address']['city'] = $geoAddr->city;
        $data['address']['state'] = $geoAddr->state;
        $data['address']['lat'] = $geoAddr->lat;
        $data['address']['lon'] = $geoAddr->lon;
    }
}