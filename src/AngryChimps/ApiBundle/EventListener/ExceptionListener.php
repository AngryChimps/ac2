<?php


namespace AngryChimps\ApiBundle\EventListener;

use AngryChimps\ApiBundle\Services\ResponseService;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener {
    protected $responseService;

    public function __construct(ResponseService $responseService) {
        $this->responseService = $responseService;
    }
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // You get the exception object from the received event
        $exception = $event->getException();

        switch(get_class($exception)) {
            case 'AngryChimps\\ApiBundle\\Exceptions\\InvalidSessionException':
                $error = array(
                    'code' => 'Api.ExceptionListener.onKernelException.1',
                    'human' => 'Invalid or missing session_id'
                );
                $response = $this->responseService->failure(403, $error, $exception);
                break;
            default:
                //Do nothing
        }

        if($response !== null) {
            // Send the modified response object to the event
            $event->setResponse($response);
        }
    }
} 