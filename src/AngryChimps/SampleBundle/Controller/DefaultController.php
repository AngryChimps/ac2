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

//    public function indexAction($command, $folderToProcess = null, $fileToProcess = null)
//    {
//        switch($command) {
//            case 'generate':
//                $this->generatorService->generate($folderToProcess = null, $fileToProcess = null);
//                break;
//            case 'reset':
//                $this->generatorService->reset();
//                break;
//            default:
//                throw new \Exception('Unknown command: ' . $command);
//        }
//
//        return new Response("done");
//    }

    public function generateAction($folderToProcess = null, $fileToProcess = null) {
        $this->generatorService->generate($folderToProcess, $fileToProcess);
        return new Response("done");
    }

    public function resetAction() {
        $this->generatorService->reset();
        return new Response("done");
    }
}
