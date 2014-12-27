<?php

namespace AngryChimps\SampleBundle\Controller;

use AngryChimps\SampleBundle\Services\GeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController
{
    /** @var GeneratorService */
    protected $generatorService;

    public function __construct(GeneratorService $generatorService) {
        $this->generatorService = $generatorService;
    }

    public function indexAction($command)
    {
        switch($command) {
            case 'generate':
                $this->generatorService->generate();
                break;
            case 'reset':
                $this->generatorService->reset();
                break;
            default:
                throw new \Exception('Unknown command: ' . $command);
        }

        return new Response("done");
    }
}
