<?php


namespace AngryChimps\ApiBundle\Services;

use AC\NormBundle\services\InfoService;
use AngryChimps\GeoBundle\services\GeolocationService;
use Norm\Company;
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

    public function __construct(ValidatorInterface $validator, NormService $norm, InfoService $infoService,
        CompanyService $companyService, MemberService $memberService) {
        parent::__construct($norm, $infoService, $validator);

        $this->companyService = $companyService;
        $this->memberService = $memberService;
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