<?php


namespace AngryChimps\ApiBundle\Services;


use AngryChimps\GeoBundle\Services\GeolocationService;
use Norm\riak\Company;
use Norm\riak\Location;
use Norm\riak\Address;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use AngryChimps\NormBundle\realms\Norm\mysql\services\NormMysqlService;
use AngryChimps\NormBundle\realms\Norm\riak\services\NormRiakService;

class LocationService {
    /** @var \Symfony\Component\Validator\Validator\ValidatorInterface */
    protected $validator;

    /** @varAngryChimps\GeoBundle\Services\GeolocationService */
    protected $geo;

    /** @var  NormRiakService */
    protected $riak;

    /** @var  NormMysqlService */
    protected $mysql;

    public function __construct(ValidatorInterface $validator, GeolocationService $geo, NormRiakService $riak, NormMysqlService $mysql) {
        $this->validator = $validator;
        $this->geo = $geo;
        $this->riak = $riak;
        $this->mysql = $mysql;
    }

    public function createEmpty(Company $company) {
        $location = new Location();
        $location->companyId = $company->id;
        $location->status = Location::ENABLED_STATUS;
        $this->riak->create($location);

        $company->locationIds[] = $location->id;
        $this->riak->update($company);

        return $location;
    }


    public function createLocation($name, $street1, $street2, $zip, $phone, $company, &$errors) {
        $address = $this->geo->lookupAddress($street1, $street2, $zip);

        $location = new Location();
        $location->name = $name;
        $location->address = new Address();
        $location->address->street1 = $street1;
        $location->address->street2 = $street2;
        $location->address->zip = $zip;
        $location->address->phone = $phone;

        $location->address->city = $address->city;
        $location->address->state = $address->state;
        $location->address->lat = $address->lat;
        $location->address->long = $address->long;

        $location->companyId = $company->id;
        $location->status = Location::ENABLED_STATUS;

        $errors = $this->validator->validate($location);
        if(count($errors) > 0) {
            return false;
        }

        $this->riak->create($location);

        return $location;
    }

    public function updateLocation(Location $location, $name, $street1, $street2, $zip, $phone, &$errors) {
        $address = $this->geo->lookupAddress($street1, $street2, $zip);

        $location->name = $name;
        $location->address = new Address();
        $location->address->street1 = $street1;
        $location->address->street2 = $street2;
        $location->address->zip = $zip;
        $location->address->phone = $phone;

        $location->address->city = $address->city;
        $location->address->state = $address->state;
        $location->address->lat = $address->lat;
        $location->address->long = $address->long;

        $errors = $this->validator->validate($location);
        if(count($errors) > 0) {
            return false;
        }

        $this->riak->update($location);

        return $location;
    }

    public function getByPk($id) {
        return $this->riak->getLocation($id);
    }

    public function markLocationDeleted(Location $location) {
        $location->status = Location::DISABLED_STATUS;
        $this->riak->update($location);
    }
}