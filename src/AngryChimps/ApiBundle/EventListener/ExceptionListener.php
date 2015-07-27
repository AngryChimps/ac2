<?php


namespace AngryChimps\ApiBundle\EventListener;

use AngryChimps\ApiBundle\Services\ResponseService;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener {
    protected $responseService;
    protected $apiDebug;

    public function __construct(ResponseService $responseService, $apiDebug) {
        $this->responseService = $responseService;
        $this->apiDebug = $apiDebug;
    }
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // You get the exception object from the received event
        $exception = $event->getException();

        $response = null;

        switch(get_class($exception)) {
            case 'AngryChimps\\ApiBundle\\Exceptions\\InvalidSessionException':
                $error = array(
                    'code' => 'Api.ExceptionListener.onKernelException.1',
                    'human' => 'Invalid or missing session_id'
                );

                //Only in debug do we specify why it fails
                if($this->apiDebug) {
                    $error['debug'] = $exception->getDebugMessage();
                }

                $response = $this->responseService->failure(403, $error, $exception);
                $event->setResponse($response);
                break;
            default:
                //Do nothing
        }

    }
} 