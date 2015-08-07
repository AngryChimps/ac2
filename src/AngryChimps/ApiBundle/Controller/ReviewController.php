<?php

namespace AngryChimps\ApiBundle\Controller;

use AC\NormBundle\services\InfoService;
use AngryChimps\ApiBundle\Services\ReviewService;
use AngryChimps\GeoBundle\services\GeolocationService;
use Norm\Company;
use Norm\MemberCompany;
use Norm\Review;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AngryChimps\ApiBundle\Services\CompanyService;
use AngryChimps\ApiBundle\Services\ResponseService;
use Symfony\Component\HttpFoundation\RequestStack;
use AngryChimps\ApiBundle\Services\SessionService;

class ReviewController extends AbstractRestController
{
    /** @var  ReviewService */
    protected $reviewService;

    /** @var  InfoService */
    protected $infoService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, ReviewService $reviewService,
                                InfoService $infoService) {
        parent::__construct($requestStack, $sessionService, $responseService, $reviewService, $infoService);

        $this->reviewService = $reviewService;
    }

    public function indexGetMultipleAction()
    {
        if($this->request->get('location_id') === null) {
            return $this->responseService->failure(400, ResponseService::INVALID_LOCATION_ID);
        }
        else {
            $review = $this->reviewService->getMultipleByLocation($this->request->get('location_id'),
                $this->request->get('review_count'));
            return $this->getGetMultipleResponse('review', $review);
        }
    }

    public function indexGetAction($id)
    {
        return $this->getGetResponse('review', $id);
    }

    public function indexPostAction()
    {
        $resp = $this->getPostResponse('review',
            [
                'author_id' => $this->getAuthenticatedUser()->getId(),
                'status' => Review::ENABLED_STATUS,
            ]
        );

        return $resp;
    }

    public function indexPatchAction($id)
    {
        return $this->getPatchResponse('review', $id);
    }

    public function indexDeleteAction($id)
    {
        return $this->getDeleteResponse('review', $id);
    }
}
