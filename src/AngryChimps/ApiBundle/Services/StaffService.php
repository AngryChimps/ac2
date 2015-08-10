<?php


namespace AngryChimps\ApiBundle\Services;

use AC\NormBundle\services\InfoService;
use AngryChimps\GeoBundle\services\GeolocationService;
use Norm\Company;
use Norm\Location;
use Norm\Member;
use Norm\MemberCompany;
use Norm\Staff;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use AngryChimps\NormBundle\services\NormService;

class StaffService extends AbstractRestService {

    /** @var CompanyService  */
    protected $companyService;

    /** @var MemberService */
    protected $memberService;

    /** @var LocationService */
    protected $locationService;

    public function __construct(ValidatorInterface $validator, NormService $norm, InfoService $infoService,
        CompanyService $companyService, MemberService $memberService, LocationService $locationService) {
        parent::__construct($norm, $infoService, $validator);

        $this->companyService = $companyService;
        $this->memberService = $memberService;
        $this->locationService = $locationService;
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

    public function post($endpoint, $data, $additionalData = [])
    {
        /** @var Member $member */
        $member = $this->norm->getMemberByEmail($data['email']);
        if($member !== null) {
            $additionalData['member_id'] = $member->getId();
        }

        //Remove the role parameter
        $role = $data['role'];
        unset($data['role']);

        /** @var Staff $staff */
        $staff = parent::post($endpoint, $data, $additionalData);

        /** @var Member $member */
        if($member !== null) {
            //Send welcome to company e-mail
        }
        else {
            $member = $this->memberService->post('member', [
                'first' => $staff->getFirst(),
                'last' => $staff->getLast(),
                'title' => $staff->getTitle(),
                'photo' => $staff->getPhoto(),
                'role' => $role,
            ]);

            //Send welcome e-mail if not sent by member service
        }

        foreach($data['location_ids'] as $locationId) {
            /** @var Location $location */
            $location = $this->locationService->get('location', $locationId);
            $location->addToStaffIds($staff->getId());
            $this->norm->update($location);
        }

        return $staff;
    }

    public function getMultipleByLocation($locationId, $count) {
        return $this->norm->getStaffByLocation($locationId, $count);
    }

    public function getMultipleByCompany($companyId, $count) {
        return $this->norm->getStaffByCompany($companyId, $count);
    }
}