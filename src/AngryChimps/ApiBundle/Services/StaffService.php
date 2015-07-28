<?php


namespace AngryChimps\ApiBundle\Services;

use AC\NormBundle\services\InfoService;
use AngryChimps\GeoBundle\services\GeolocationService;
use Norm\Company;
use Norm\Member;
use Norm\MemberCompany;
use Norm\Staff;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use AngryChimps\NormBundle\services\NormService;

class StaffService extends AbstractRestService {

    /** @var CompanyService  */
    protected $companyService;

    public function __construct(ValidatorInterface $validator, NormService $norm, InfoService $infoService,
        CompanyService $companyService) {
        parent::__construct($norm, $infoService, $validator);

        $this->companyService = $companyService;
    }

    /**
     * @param Staff $staff
     * @param Member $authenticatedMember
     * @return bool
     */
    public function isOwner($staff, Member $authenticatedMember) {
        $role = $this->companyService->getRole($authenticatedMember->getId(), $staff->getCompanyId());
        return ($role && $role != MemberCompany::CONSUMER_ROLE);
    }
}