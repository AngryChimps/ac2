<?php

namespace AngryChimps\ApiBundle\Controller;

use AngryChimps\ApiBundle\Services\CommentService;
use AngryChimps\ApiBundle\Services\CompanyService;
use AngryChimps\ApiBundle\Services\MemberService;
use Symfony\Component\HttpFoundation\RequestStack;
use AngryChimps\ApiBundle\Services\SessionService;
use Psr\Log\LoggerInterface;
use AngryChimps\ApiBundle\Services\ResponseService;


class CommentController extends AbstractController
{
    protected $commentService;
    protected $companyService;
    protected $memberService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, CommentService $commentService,
                                MemberService $memberService, CompanyService $companyService) {
        parent::__construct($requestStack, $sessionService, $responseService);

        $this->commentService = $commentService;
        $this->memberService = $memberService;
        $this->companyService = $companyService;
    }

    public function indexGetAction($companyId) {
        $company = $this->companyService->getByPk($companyId);
        if($company === null) {
            $error = array('code' => 'Api.CommentController.indexGetAction.1',
                'human' => 'Invalid company_id');
            return $this->responseService->failure(404, $error);
        }

        $limit = ($this->request->query->get('limit') !== null) ? (int) $this->request->query->get('limit') : 10;
        $offset = ($this->request->query->get('offset') !== null) ? (int) $this->request->query->get('offset') : 0;
        $comments = $this->commentService->getComments($company, $limit, $offset);

        return $this->responseService->success($comments);
    }
        
    public function indexPostAction() {
        $payload = $this->getPayload();
        $comment = isset($payload['comment']) ? $payload['comment'] : null;

        $member = $this->getAuthenticatedUser();
        if($member === null) {
            $error = array('code' => 'Api.CommentController.indexPostAction.1',
                'human' => 'You must be authenticated to use this method');
            return $this->responseService->failure(403, $error);
        }

        $company = $this->companyService->getByPk($payload['company_id']);
        if($company === null) {
            $error = array('code' => 'Api.CommentController.indexPostAction.2',
                'human' => 'Invalid company_id');
            return $this->responseService->failure(400, $error);
        }

        $this->commentService->recordComment($member, $company, $payload['rating'], $comment);

        return $this->responseService->success();
    }
}
