<?php


namespace AngryChimps\TaskBundle\EventListener;

use AngryChimps\ApiBundle\Services\ResponseService;
use AngryChimps\TaskBundle\Services\TaskerService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Psr\Log\LoggerInterface;

class KernelTerminateListener {
    protected $taskerService;
    protected $logger;

    public function __construct(TaskerService $taskerService, LoggerInterface $logger) {
        $this->taskerService = $taskerService;
        $this->logger = $logger;
    }

    public function onKernelTerminate(PostResponseEvent $event)
    {
        try {
            touch('/tmp/running');
            $this->taskerService->runTasks($this->logger);
            touch('/tmp/ran');
        }
        catch (\Exception $ex) {
            //Do nothing
        }
    }
} 