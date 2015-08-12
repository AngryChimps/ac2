<?php

namespace AngryChimps\ApiBundle\Controller;

use AC\NormBundle\Services\LogTestService;
use AngryChimps\ApiBundle\Services\SearchService;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\RequestStack;
use FOS\RestBundle\View\ViewHandler;
use AngryChimps\ApiBundle\Services\SessionService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use AngryChimps\ApiBundle\Services\ResponseService;

class SearchController extends AbstractController
{
    /** @var  \AngryChimps\ApiBundle\Services\SearchService */
    protected $searchService;

    protected $logTester;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, SearchService $searchService) {
        parent::__construct($requestStack, $sessionService, $responseService);
        $this->searchService = $searchService;
    }

    public function indexPostAction() {
        //Check to see if the token/member_id is valid
        if($debug = $this->sessionService->checkToken()) {
            return $this->responseService->failure(400, ResponseService::INVALID_SESSION_INFORMATION, null, $debug);
        }

        $payload = $this->getPayload();

        $lat = isset($payload['lat']) ? $payload['lat'] : null;
        $lon = isset($payload['lon']) ? $payload['lon'] : null;
        $animal = isset($payload['animal']) ? $payload['animal'] : null;
        $mobileLocation = isset($payload['mobile_location']) ? $payload['mobile_location'] : null;
        $emergency = isset($payload['emergency']) ? $payload['emergency'] : null;
        $walkIn = isset($payload['walk_in']) ? $payload['walk_in'] : null;
        $limit = isset($payload['limit']) ? $payload['limit'] : 10;
        $offset = isset($payload['offset']) ? $payload['offset'] : 0;

        if($lat === null || $lon === null) {
            return $this->responseService->failure(400, ResponseService::LAT_AND_LON_REQUIRED);
        }

        $results = $this->searchService->search($lat, $lon, $mobileLocation,
            $animal, $emergency, $walkIn, $limit, $offset);

        return $this->responseService->success($results);
    }

}
