<?php


namespace AngryChimps\ApiBundle\Services;


use AngryChimps\GeoBundle\Services\GeolocationService;
use Norm\riak\Location;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LocationService {
    /** @var \Symfony\Component\Validator\Validator\ValidatorInterface */
    protected $validator;

    /** @varAngryChimps\GeoBundle\Services\GeolocationService */
    protected $geo;

    public function __construct(ValidatorInterface $validator, GeolocationService $geo) {
        $this->validator = $validator;
        $this->geo = $geo;
    }


    public function createLocation($name, $street1, $street2, $zip, $phone, $company, $user, &$errors) {
        $address = $this->geo->lookupAddress($street1, $street2, $zip);

        $location = new Location();
        $location->name = $name;
        $location->street1 = $street1;
        $location->street2 = $street2;
        $location->zip = $zip;
        $location->phone = $phone;

        $location->city = $address->city;
        $location->state = $address->state;
        $location->lat = $address->lat;
        $location->long = $address->long;

        $location->companyId = $company->id;
        $location->status = Location::ENABLED_STATUS;

//        $errors = $this->validator->validate($location);
//        if(count($errors) > 0) {
//            return false;
//        }

        $location->save();

        return $location;
    }

    public function updateLocation($location, $company, $name, $street1, $street2, $zip, $phone, &$errors) {
        $address = $this->geo->lookupAddress($street1, $street2, $zip);

        $location->name = $name;
        $location->street1 = $street1;
        $location->street2 = $street2;
        $location->zip = $zip;
        $location->phone = $phone;

        $location->city = $address->city;
        $location->state = $address->state;
        $location->lat = $address->lat;
        $location->long = $address->long;

        $location->companyId = $company->id;

//        $errors = $this->validator->validate($location);
//        if(count($errors) > 0) {
//            return false;
//        }

        $location->save();

        return $location;
    }

}