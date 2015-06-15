<?php


namespace AngryChimps\ApiBundle\services;


use AngryChimps\GeoBundle\Services\GeolocationService;
use Norm\Company;
use Norm\Location;
use Norm\Address;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use AngryChimps\NormBundle\services\NormService;

class LocationService {
    /** @var \Symfony\Component\Validator\Validator\ValidatorInterface */
    protected $validator;

    /** @varAngryChimps\GeoBundle\services\GeolocationService */
    protected $geo;

    /** @var  NormService */
    protected $norm;

    /** @var CompanyService  */
    protected $companyService;

    public function __construct(ValidatorInterface $validator, GeolocationService $geo, NormService $norm,
                                CompanyService $companyService) {
        $this->validator = $validator;
        $this->geo = $geo;
        $this->norm = $norm;
        $this->companyService = $companyService;
    }

    public function createEmpty(Company $company) {
        $location = new Location();
        $location->setCompanyId($company->getId());
        $location->setStatus(Location::ENABLED_STATUS);
        $this->norm->create($location);

        $company->addToLocationIds($location->getId());
        $this->norm->update($company);

        return $location;
    }


    public function createLocation($name, $street1, $street2, $zip, $phone, $company, $isMobile, &$errors) {
        $address = $this->geo->lookupAddress($street1, $street2, $zip);

        $location = new Location();
        $location->setName($name);

        $address = new Address();
//        $address->set
        $address->street1 = $street1;
        $address->street2 = $street2;
        $address->zip = $zip;
        $address->phone = $phone;

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

        $this->norm->create($location);

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

        $this->norm->update($location);

        return $location;
    }

    public function getByPk($id) {
        return $this->norm->getLocation($id);
    }

    public function markLocationDeleted(Location $location) {
        $location->status = Location::DISABLED_STATUS;
        $this->norm->update($location);

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
        $this->norm->update($company);
    }
}