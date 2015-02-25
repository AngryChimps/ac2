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
//        $result = $this->searchService->getSampleProviderAdListing();
//        return $this->responseService->success($result);

        $payload = $this->getPayload();
        $text = isset($payload['text']) ? $payload['text'] : null;
        $categories = isset($payload['categories']) ? $payload['categories'] : null;

        $lat = isset($payload['lat']) ? $payload['lat'] : null;
        $long = isset($payload['long']) ? $payload['long'] : null;
        $radius = isset($payload['radius_miles']) ? $payload['radius_miles'] : null;
        $consumerTravels = isset($payload['consumer_travels']) ? $payload['consumer_travels'] : null;
        $startingAt = isset($payload['starting_at']) ? new \DateTime($payload['starting_at']) : null;
        $endingAt = isset($payload['ending_at']) ? new \DateTime($payload['ending_at']) : null;
        $sort = isset($payload['sort']) ? $payload['sort'] : null;
        $limit = isset($payload['limit']) ? $payload['limit'] : 10;
        $offset = isset($payload['offset']) ? $payload['offset'] : 0;

        $results = $this->searchService->search($text, $categories, $lat, $long, $radius, $consumerTravels,
            $startingAt, $endingAt, $sort, $limit, $offset);

        return $this->responseService->success($results);
    }

}
