<?php

namespace AngryChimps\SampleBundle\services;


use AngryChimps\ApiBundle\Services\CompanyService;
use AngryChimps\ApiBundle\Services\LocationService;
use AngryChimps\ApiBundle\Services\MemberService;
use AngryChimps\ApiBundle\Services\ResponseService;
use AngryChimps\ApiBundle\Services\SessionService;
use AngryChimps\ApiBundle\Services\StaffService;
use AngryChimps\MediaBundle\Services\MediaService;
use Symfony\Component\Yaml\Parser;

class PublishService
{
    protected $sampleDir;

    /** @var MediaService */
    protected $mediaService;

    /** @var MemberService */
    protected $memberService;

    /** @var SessionService */
    protected $sessionService;

    /** @var CompanyService */
    protected $companyService;

    /** @var LocationService */
    protected $locationService;

    /** @var StaffService */
    protected $staffService;

    public function __construct(MediaService $mediaService, MemberService $memberService,
                                SessionService $sessionService, CompanyService $companyService,
                                LocationService $locationService, StaffService $staffService) {
        $this->sampleDir = __DIR__ . '/../samples';
        $this->mediaService = $mediaService;
        $this->memberService = $memberService;
        $this->sessionService = $sessionService;
        $this->companyService = $companyService;
        $this->locationService = $locationService;
        $this->staffService = $staffService;
    }

    public function publish($region, $companyShortName) {
        $mediaIds = $this->uploadMedia($region, $companyShortName);

        $session = $this->createSession();
        $member = $this->createMember($region, $companyShortName);
        $company = $this->createCompany($region, $companyShortName, $member->getId());
        $locationIds = $this->createLocations($region, $companyShortName);
        $staffIds = $this->createStaff($region, $companyShortName, $mediaIds, $locationIds);

        $arr = [
            'session' => $session->getId(),
            'media_ids' => $mediaIds,
            'member' => $member->getId(),
            'company' => $company->getId(),
            'locations' => $locationIds,
            'staff' => $staffIds,
        ];

        return $arr;
    }

    protected function createSession() {
        $input = [
            'device_type' => 3,
            'push_token' => 'abclkjflsdjflkjf',
            'description' => 'Android 2.x...',
        ];

        return $this->sessionService->post('session', $input);
    }

    protected function uploadMedia($region, $companyShortName) {
        $mediaIds = [];
        $mediaShortNames = $this->getMediaShortNames($region, $companyShortName);

        foreach($mediaShortNames as $mediaShortName) {
            $contents = file_get_contents($this->sampleDir . '/' . $region . '/' . $companyShortName . '/image_data/'
                                          . $mediaShortName . '.yml');
            $data = yaml_parse($contents);
            $filename = $this->sampleDir . '/' . $region . '/' . $companyShortName . '/images/'
                . $data['filename'];

            $id = $this->mediaService->post_local($filename, $data['top_x'], $data['top_y'], $data['bottom_x'], $data['bottom_y']);

            $mediaIds[$data['name']] = $id;
        }

        return $mediaIds;
    }

    protected function createMember($region, $companyShortName) {
        $contents = file_get_contents($this->sampleDir . '/' . $region . '/' . $companyShortName . '/company.yml');
        $arr = yaml_parse($contents);

        return $this->memberService->post('member', $arr['member']);
    }

    protected function createCompany($region, $companyShortName, $memberId) {
        $contents = file_get_contents($this->sampleDir . '/' . $region . '/' . $companyShortName . '/company.yml');
        $arr = yaml_parse($contents);
        unset($arr['member']);
        unset($arr['id']);
        unset($arr['region_id']);

        return $this->companyService->post('company', $arr, ['created_by' => $memberId]);
    }

    protected function createLocations($region, $companyShortName) {
        $locations = [];
        foreach($this->getLocationShortNames($region, $companyShortName) as $locationShortName) {
            $contents = file_get_contents($this->sampleDir . '/' . $region . '/' . $companyShortName . '/locations/'
                . $locationShortName . '.yml');
            $arr = yaml_parse($contents);
            unset($arr['id']);

            $location = $this->locationService->post('location', $arr);
            $locations[$locationShortName] = $location->getId();
        }

        return $locations;
    }

    protected function createStaff($region, $companyShortName, $mediaIds, $locationIds) {
        $staff = [];
        foreach($this->getStaffShortNames($region, $companyShortName) as $staffShortName) {
            $contents = file_get_contents($this->sampleDir . '/' . $region . '/' . $companyShortName . '/staff/'
                . $staffShortName . '.yml');
//            $arr = yaml_parse($contents);

            $parser = new Parser();
            $arr = $parser->parse($contents);

            $arr['photo'] = $mediaIds[$arr['photo']];
            $locations = [];
            foreach($arr['location_ids'] as $locationId) {
                $locations[] = $locationIds[$locationId];
            }
            $arr['location_ids'] = $locations;

            $staffMember = $this->staffService->post('staff', $arr);
            $staff[$staffShortName] = $staffMember->getId();
        }

        return $staff;
    }

    protected function getMediaShortNames($region, $companyShortName) {
        $fh = opendir($this->sampleDir . '/' . $region . '/' . $companyShortName . '/image_data');

        $arr = [];
        while(false !== ($file = readdir($fh))) {
            if($file != '.' && $file != '..') {
                $arr[] = substr($file, 0, strlen($file) - 4);
            }
        }

        closedir($fh);

        return $arr;
    }

    protected function getLocationShortNames($region, $companyShortName) {
        $fh = opendir($this->sampleDir . '/' . $region . '/' . $companyShortName . '/locations');

        $arr = [];
        while(false !== ($file = readdir($fh))) {
            if($file != '.' && $file != '..') {
                $arr[] = substr($file, 0, strlen($file) - 4);
            }
        }

        closedir($fh);

        return $arr;
    }

    protected function getStaffShortNames($region, $companyShortName) {
        $fh = opendir($this->sampleDir . '/' . $region . '/' . $companyShortName . '/staff');

        $arr = [];
        while(false !== ($file = readdir($fh))) {
            if($file != '.' && $file != '..') {
                $arr[] = substr($file, 0, strlen($file) - 4);
            }
        }

        closedir($fh);

        return $arr;
    }
}