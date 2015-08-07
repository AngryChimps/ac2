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
}