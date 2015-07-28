<?php


namespace AngryChimps\ApiBundle\Controller;

use AC\NormBundle\core\Utils;
use AC\NormBundle\services\InfoService;
use AngryChimps\ApiBundle\services\AbstractRestService;
use AngryChimps\ApiBundle\Services\SessionService;
use Norm\Member;
use Symfony\Component\HttpFoundation\RequestStack;
use Psr\Log\LoggerInterface;
use AngryChimps\ApiBundle\Services\ResponseService;

abstract class AbstractRestController extends AbstractController {
    /** @var  AbstractRestService */
    protected $restService;

    /** @var  InfoService */
    protected $infoService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, AbstractRestService $restService,
                                InfoService $infoService)
    {
        $this->restService = $restService;
        $this->infoService = $infoService;

        parent::__construct($requestStack, $sessionService, $responseService);
    }

    public function getPostResponse($entityName, $additionalData = []) {
        //Check to see if the token/member_id is valid
        if($debug = $this->sessionService->checkToken()) {
            return $this->responseService->failure(400, ResponseService::INVALID_SESSION_INFORMATION, null, $debug);
        }

        //All POST apis other than "member" require authentication
        if($entityName !== 'member' && $this->getAuthenticatedUser() === null) {
            return $this->responseService->failure(401, ResponseService::USER_NOT_AUTHENTICATED);
        }

        $payload = $this->getPayload();

        $error = null;
        if(!$this->isDataValid($entityName, $payload, $error)) {
            return $this->responseService->failure(400, ResponseService::UNKNOWN_POST_DATA_FIELD, null, $error);
        }

        $obj = $this->restService->post($entityName, $payload, $additionalData);

        if($obj === FALSE) {
            return $this->responseService->failure(400, ResponseService::VALIDATION_ERROR);
        }

        return $this->responseService->success([$entityName => ["id" => $obj->getId()]]);
    }

    public function getGetResponse($entityName, $id) {
        //Check to see if the token/member_id is valid
        if($debug = $this->sessionService->checkToken()) {
            return $this->responseService->failure(400, ResponseService::INVALID_SESSION_INFORMATION, null, $debug);
        }

        $obj = $this->restService->get($entityName, $id);

        if($obj === null) {
            return $this->responseService->failure(404, ResponseService::ERROR_404);
        }

        if($this->restService->isOwner($obj, $this->getAuthenticatedUser())) {
            return $this->responseService->success([$entityName => $this->restService->getApiPrivateArray($obj)]);
        }
        else {
            return $this->responseService->success([$entityName => $this->restService->getApiPublicArray($obj)]);
        }
    }

    public function getPatchResponse($entityName, $id) {
        //Check to see if the token/member_id is valid
        if($debug = $this->sessionService->checkToken()) {
            return $this->responseService->failure(400, ResponseService::INVALID_SESSION_INFORMATION, null, $debug);
        }

        //All PATCH apis require authentication
        if($this->getAuthenticatedUser() === null) {
            return $this->responseService->failure(401, ResponseService::USER_NOT_AUTHENTICATED);
        }

        $payload = $this->getPayload();

        $error = null;
        if(!$this->isDataValid($entityName, $payload, $error)) {
            return $this->responseService->failure(400, ResponseService::UNKNOWN_POST_DATA_FIELD, null, $error);
        }

        $obj = $this->restService->get($entityName, $id);

        if($obj === null) {
            return $this->responseService->failure(404, ResponseService::ERROR_404);
        }

        if(!$this->restService->isOwner($obj, $this->getAuthenticatedUser())) {
            return $this->responseService->failure(403, ResponseService::USER_NOT_AUTHORIZED);
        }

        $obj = $this->restService->patch($obj, $payload);

        if($obj === FALSE) {
            return $this->responseService->failure(400, ResponseService::VALIDATION_ERROR);
        }

        return $this->responseService->success();
    }

    public function getDeleteResponse($entityName, $id) {
        //Check to see if the token/member_id is valid
        if($debug = $this->sessionService->checkToken()) {
            return $this->responseService->failure(400, ResponseService::INVALID_SESSION_INFORMATION, null, $debug);
        }

        //All DELETE apis require authentication
        if($this->getAuthenticatedUser() === null) {
            return $this->responseService->failure(401, ResponseService::USER_NOT_AUTHENTICATED);
        }

        $obj = $this->restService->get($entityName, $id);

        if($obj === null) {
            return $this->responseService->failure(404, ResponseService::ERROR_404);
        }

        if(!$this->restService->isOwner($obj, $this->getAuthenticatedUser())) {
            return $this->responseService->failure(403, ResponseService::USER_NOT_AUTHORIZED);
        }

        $this->restService->delete($obj);

        return $this->responseService->success();
    }

    protected function isDataValid($entityName, $data, &$error) {
        $validFields = $this->infoService->getAllApiSettableFields($entityName);

        foreach($data as $key => $val) {
            if(!in_array($key, $validFields)) {
                $error = $key . ' is not a valid field';
                return false;
            }
        }

        return true;
    }

    protected function getEntityName() {
        return Utils::camel2TrainCase(substr(get_called_class(), 0, strlen(get_called_class()) - 10));
    }
}