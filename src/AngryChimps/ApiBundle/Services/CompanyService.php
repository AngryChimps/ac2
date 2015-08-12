<?php


namespace AngryChimps\ApiBundle\Services;

use AC\NormBundle\services\InfoService;
use AngryChimps\GeoBundle\services\GeolocationService;
use Norm\Company;
use Norm\Member;
use Norm\MemberCompany;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use AngryChimps\NormBundle\services\NormService;

class CompanyService extends AbstractRestService {

    protected $geolocationService;

    public function __construct(ValidatorInterface $validator, NormService $norm, InfoService $infoService,
                                GeolocationService $geolocationService) {
        parent::__construct($norm, $infoService, $validator);

        $this->geolocationService = $geolocationService;
    }

    /**
     * @param Company $company
     * @param Member $authenticatedMember
     * @return bool
     */
    public function isOwner($company, Member $authenticatedMember) {
        $role = $this->getRole($authenticatedMember->getId(), $company->getId());
        return ($role && $role != MemberCompany::CONSUMER_ROLE);
    }

    public function post($endpoint, $data, $additionalData = [])
    {
        $geoAddr = $this->geolocationService->lookupAddress($data['billing_address']['street1'],
                   $data['billing_address']['zip']);
        $data['billing_address']['location'] = $geoAddr->lat . ', ' . $geoAddr->lon;

        /** @var Company $company */
        $company = parent::post($endpoint, $data, $additionalData);

        //Create the MemberCompany object
        $memberCompany = new MemberCompany();
        $memberCompany->setMemberId($additionalData['created_by']);
        $memberCompany->setCompanyId($company->getId());
        $memberCompany->setRole(MemberCompany::SUPER_ADMIN_ROLE);
        $this->norm->create($memberCompany);

        return $company;
    }

    public function patch($obj, $data, array $additionalData = [])
    {
        $geoAddr = $this->geolocationService->lookupAddress($data['billing_address']['street1'],
            $data['billing_address']['zip']);
        $data['billing_address']['location'] = $geoAddr->lat . ', ' . $geoAddr->lon;

        $originalCompanyName = $obj->getName();

        if(parent::patch($obj, $data, $additionalData) !== false) {
            if($obj->getName() !== $originalCompanyName) {
                $locations = $this->getLocations($obj->getId());
                foreach($locations as $location) {
                    $location->setCompanyName($obj->getName());
                    $this->norm->update($location);
                }
            }
        }
    }

    protected function getLocations($companyId) {
        return $this->norm->getLocationsByCompany($companyId);
    }

    public function getRole($memberId, $companyId) {
        $obj = $this->norm->getMemberCompany([$memberId, $companyId]);
        return $obj->getRole();
    }
}