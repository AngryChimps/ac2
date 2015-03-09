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

    /** @var CompanyService  */
    protected $companyService;

    public function __construct(ValidatorInterface $validator, GeolocationService $geo, NormRiakService $riak,
                                NormMysqlService $mysql, CompanyService $companyService) {
        $this->validator = $validator;
        $this->geo = $geo;
        $this->riak = $riak;
        $this->mysql = $mysql;
        $this->companyService = $companyService;
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


    public function createLocation($name, $street1, $street2, $zip, $phone, $company, $isMobile, &$errors) {
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
        $location->address->lon = $address->lon;

        $location->companyId = $company->id;
        $location->isMobile = $isMobile;
        $location->status = Location::ENABLED_STATUS;

        $errors = $this->validator->validate($location);
        if(count($errors) > 0) {
            return false;
        }

        $this->riak->create($location);

        return $location;
    }

    public function updateLocation(Location $location, $name, $street1, $street2, $zip, $phone, $isMobile, &$errors) {
        $address = $this->geo->lookupAddress($street1, $street2, $zip);

        $location->name = $name;
        $location->isMobile = $isMobile;
        $location->address = new Address();
        $location->address->street1 = $street1;
        $location->address->street2 = $street2;
        $location->address->zip = $zip;
        $location->address->phone = $phone;

        $location->address->city = $address->city;
        $location->address->state = $address->state;
        $location->address->lat = $address->lat;
        $location->address->lon = $address->lon;

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

        $company = $this->companyService->getByPk($location->companyId);

        //remove from list of locations
        $index = 0;
        for($i=0; count($company->locationIds) < $i; $i++) {
            if($company->locationIds[$i] == $location->id) {
                $index = $i;
            }
        }

        $locationIds1 =  array_slice($company->locationIds, 0, count($company->locationIds));
        $locationIds2 = array_slice($company->locationIds, count($company->locationIds),
            count($company->locationIds) - $index);
        $company->locationIds = array_merge($locationIds1, $locationIds2);

        //Add to list of deleted services
        $company->locationDeletedIds[] = $location->id;
        $this->riak->update($company);
    }
}