<?php


namespace AngryChimps\GuzzleBundle\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Message\FutureResponse;

class GuzzleService extends Client {
    /** @var  LoggerInterface */
    protected $loggerService;

    public function __construct(LoggerInterface $loggerService, array $config = [])
    {
        $this->loggerService = $loggerService;
        parent::__construct(); // TODO: Change the autogenerated stub
    }

    /**
     * @param string $method
     * @param null $url
     * @param array $options
     * @return \GuzzleHttp\Message\Request
     */
    public function createRequest($method, $url = null, array $options = [])
    {
        $this->loggerService->info('Guzzle request method: ' . $method);
        $this->loggerService->info('Guzzle request url: ' . $url);
        $this->loggerService->info('Guzzle request options: ' . json_encode($options));

        $request = parent::createRequest($method, $url, $options);

        return $request;
    }

    /**
     * @param RequestInterface $request
     * @return FutureResponse
     */
    public function send(RequestInterface $request)
    {
        $response = parent::send($request);
        $this->loggerService->info('Guzzle response effective url: ' . json_encode($response->getEffectiveUrl()));
        $this->loggerService->info('Guzzle response code: ' . json_encode($response->getStatusCode()));
        $this->loggerService->info('Guzzle response body: ' . json_encode((string) $response->getBody()));
        return $response;
    }



} 