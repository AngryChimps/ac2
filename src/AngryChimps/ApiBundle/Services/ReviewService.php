<?php


namespace AngryChimps\ApiBundle\Services;

use AC\NormBundle\services\InfoService;
use AngryChimps\GeoBundle\services\GeolocationService;
use Norm\Company;
use Norm\Location;
use Norm\Member;
use Norm\MemberCompany;
use Norm\Review;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use AngryChimps\NormBundle\services\NormService;

class ReviewService extends AbstractRestService {

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
     * @param Review $review
     * @param Member $authenticatedMember
     * @return bool
     */
    public function isOwner($review, Member $authenticatedMember) {
        return ($review->getAuthorId() === $authenticatedMember->getId());
    }

    public function getMultipleByLocation($locationId, $count) {
        return $this->norm->getReviewsByLocation($locationId, $count);
    }

    public function post($endpoint, $data, $additionalData = [])
    {
        /** @var Review $review */
        $review = parent::post($endpoint, $data, $additionalData);

        /** @var Location $location */
        $location = $this->locationService->get('location', $review->getLocationId());
        $location->incrementRatingCount(1);
        $location->incrementRatingTotal($review->getRating());
        $location->setRatingAvg($location->getRatingTotal() / $location->getRatingCount());
        $this->norm->update($location);

        /** @var Company $company */
        $company = $this->companyService->get('company', $location->getCompanyId());
        $company->incrementRatingCount(1);
        $company->incrementRatingTotal($review->getRating());
        $company->setRatingAvg($company->getRatingTotal() / $company->getRatingCount());
        $this->norm->update($company);

    }


    public function get($endpoint, $id)
    {
        /** @var Review $obj */
        $obj = parent::get($endpoint, $id);
        if($obj->getStatus() === Review::PROHIBITED_STATUS) {
            return null;
        }
        return $obj;
    }


    public function getApiPublicArray($obj)
    {
        $arr = parent::getApiPublicArray($obj);

        if(is_array($obj) || strpos(get_class($obj), 'Collection') > 0) {
            $i=0;
            foreach($obj as $object) {
                $this->addReviewerInfo($object, $arr[$i]);
                $i++;
            }
        }
        else {
            $this->addReviewerInfo($obj, $arr);
        }

        return $arr;
    }

    public function getApiPrivateArray($obj)
    {
        $arr =  parent::getApiPrivateArray($obj);

        if(is_array($obj) || strpos(get_class($obj), 'Collection') > 0) {
            $i=0;
            foreach($obj as $object) {
                $this->addReviewerInfo($object, $arr[$i]);
                $i++;
            }
        }
        else {
            $this->addReviewerInfo($obj, $arr);
        }

        return $arr;
    }

    protected function addReviewerInfo(Review $obj, array &$arr) {
        $member = $this->norm->getMember($obj->getAuthorId());
        $arr['reviewer_name'] = $member->getFirst() . ' ' . substr($member->getLast(), 0, 1) . '.';
        $arr['reviewer_photo'] = $member->getPhoto();
    }
}