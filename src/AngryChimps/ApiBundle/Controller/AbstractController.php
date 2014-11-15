<?php


namespace AngryChimps\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;

class AbstractController extends FOSRestController {

    protected function getContent(Request $request) {
        return json_decode($request->getContent(), true);
    }

    protected function getView(Request $request, $data, $statusCode,
                            array $errors = array(),
                            \Exception $ex = null)
    {
        if($ex === null) {
            $exArr = array();
        }
        else {
            $exArr = array('type' => get_class($ex),
                'message' => $ex->getMessage(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
                'stack' => $ex->getTraceAsString(),
            );
        }

        $return = array(
            'data' => $data,
            'errors' => $errors,
            'exception' => $exArr,
            'request' => array(
                            'uri' => $request->getUri(),
                            'method' => $request->getMethod(),
                            'payload' => $this->getContent($request),
            ),
       );
        
        $view = parent::view($return, $statusCode);
        return $view->setFormat('json');
    }

    protected function getSuccessView(Request $request, $data = array()) {
        return $this->getView($request, $data, 200, array(), null);
    }

} 